<?php

namespace App\Services\Recipes;

use App\Jobs\GenerateRecipeItemJob;
use App\Jobs\PlanRecipeBatchJob;
use App\Models\Recipe;
use App\Models\RecipeGenerationBatch;
use App\Models\RecipeGenerationItem;
use App\Models\User;
use App\Services\Ai\RecipeAiClient;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Creates generation batches and keeps their counters/status honest. One item = one dish = one queued job,
 * so a failure never loses the recipes that already succeeded, and retries never create a second recipe.
 */
class RecipeGenerationService
{
    public function __construct(private RecipeAiClient $ai, private RecipeLibraryService $library, private AuditLogService $audit) {}

    /**
     * @param  array  $config  category, cuisine, food_type, yield_people, count, dishes (list), group, notes
     */
    public function start(array $config, User $user): RecipeGenerationBatch
    {
        if (! $this->ai->isConfigured()) {
            throw new RuntimeException('AI generation is not configured. Set ANTHROPIC_API_KEY in .env.');
        }
        $max = (int) config('recipe_generation.max_per_batch', 50);
        $dishes = array_values(array_unique(array_filter(array_map(fn ($d) => trim((string) $d), $config['dishes'] ?? []))));
        $count = $dishes ? min(count($dishes), $max) : max(1, min((int) ($config['count'] ?? 10), $max));
        $dishes = array_slice($dishes, 0, $max);

        $batch = DB::transaction(function () use ($config, $user, $dishes, $count) {
            $batch = RecipeGenerationBatch::create([
                'created_by' => $user->id, 'status' => 'pending',
                'label' => $config['label'] ?? trim(($config['group'] ?? $config['category'] ?? 'Recipes').' · '.($config['cuisine'] ?? '')),
                'config' => collect($config)->except(['dishes'])->all() + ['dishes' => $dishes],
                'requested_count' => $count, 'provider' => $this->ai->providerName(), 'model' => $this->ai->modelName(),
            ]);
            if ($dishes) {
                $this->addItems($batch, $dishes);
            }

            return $batch;
        });
        $this->audit->log('created', 'recipe_generation_batch', $batch->id, $batch->label, [], ['count' => $count]);

        $dishes ? $this->dispatchItems($batch) : PlanRecipeBatchJob::dispatch($batch->id);

        return $batch;
    }

    /**
     * Initial library: one batch per plan group, only for dishes not already in the library.
     *
     * @return list<RecipeGenerationBatch>
     */
    public function startInitialPlan(User $user, int $yield): array
    {
        $batches = [];
        foreach (config('recipe_generation.initial_plan', []) as $group) {
            $missing = array_values(array_filter($group['dishes'], fn ($d) => ! $this->library->findDuplicate($d)['exact']));
            if (! $missing) {
                continue;
            }
            $batches[] = $this->start([
                'group' => $group['group'], 'category' => $group['category'], 'cuisine' => $group['cuisine'],
                'food_type' => $group['food_type'], 'yield_people' => $yield, 'dishes' => $missing,
                'label' => 'Initial library · '.$group['group'],
            ], $user);
        }

        return $batches;
    }

    /** Adds one item per dish; the unique (batch, dish_key) index makes this safe to repeat. */
    public function addItems(RecipeGenerationBatch $batch, array $dishes): int
    {
        $added = 0;
        foreach ($dishes as $d) {
            $key = RecipeLibraryService::nameKey($d) ?: mb_strtolower($d);
            $item = RecipeGenerationItem::firstOrCreate(
                ['recipe_generation_batch_id' => $batch->id, 'dish_key' => mb_substr($key, 0, 200)],
                ['dish_name' => mb_substr($d, 0, 200), 'status' => 'pending'],
            );
            $added += $item->wasRecentlyCreated ? 1 : 0;
        }

        return $added;
    }

    public function dispatchItems(RecipeGenerationBatch $batch): void
    {
        $batch->update(['status' => 'processing', 'started_at' => $batch->started_at ?? now()]);
        foreach ($batch->items()->where('status', 'pending')->pluck('id') as $id) {
            GenerateRecipeItemJob::dispatch($id);
        }
        $this->refresh($batch);
    }

    public const STALL_MINUTES = 15;

    /** Items queued/processing but untouched for a while: their job is lost or no worker is running. */
    public function stalledCount(RecipeGenerationBatch $batch): int
    {
        if ($batch->isFinished()) {
            return 0;
        }

        return $batch->items()->whereIn('status', ['pending', 'processing'])
            ->where('updated_at', '<', now()->subMinutes(self::STALL_MINUTES))->count();
    }

    /** Re-queue failed and stalled items (successful recipes are kept; jobs are idempotent). */
    public function retryFailed(RecipeGenerationBatch $batch, User $user): int
    {
        if ($batch->status === 'cancelled') {
            throw new RuntimeException('This batch was cancelled.');
        }
        $n = $batch->items()->where('status', 'failed')->update(['status' => 'pending', 'error' => null, 'attempts' => 0]);
        $stalled = $batch->items()->whereIn('status', ['pending', 'processing'])
            ->where('updated_at', '<', now()->subMinutes(self::STALL_MINUTES))->update(['status' => 'pending', 'updated_at' => now()]);
        $n += $stalled;
        if ($stalled && $batch->status !== 'cancelled') {
            $batch->update(['status' => 'processing']);
        }
        if ($n === 0 && $batch->status === 'failed' && $batch->items()->count() === 0) {
            $batch->update(['status' => 'pending', 'error' => null]);
            PlanRecipeBatchJob::dispatch($batch->id);
            $this->audit->log('retried', 'recipe_generation_batch', $batch->id, $batch->label);

            return 1;
        }
        if ($n > 0) {
            $batch->update(['completed_at' => null]);
            $this->dispatchItems($batch);
            $this->audit->log('retried', 'recipe_generation_batch', $batch->id, $batch->label, [], ['items' => $n]);
        }

        return $n;
    }

    public function cancel(RecipeGenerationBatch $batch, User $user): void
    {
        $batch->items()->where('status', 'pending')->update(['status' => 'cancelled']);
        $batch->update(['status' => 'cancelled', 'completed_at' => now()]);
        $this->audit->log('cancelled', 'recipe_generation_batch', $batch->id, $batch->label);
    }

    /** Recount from the items (source of truth) and settle the batch status. */
    public function refresh(RecipeGenerationBatch $batch): RecipeGenerationBatch
    {
        return DB::transaction(function () use ($batch) {
            $b = RecipeGenerationBatch::lockForUpdate()->find($batch->id);
            $counts = $b->items()->selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status');
            $done = (int) ($counts['done'] ?? 0);
            $failed = (int) ($counts['failed'] ?? 0);
            $dupes = (int) ($counts['duplicate'] ?? 0);
            $open = (int) ($counts['pending'] ?? 0) + (int) ($counts['processing'] ?? 0);
            $status = $b->status;
            if ($b->status !== 'cancelled' && $b->items()->exists() && $open === 0) {
                $status = $failed === 0 ? 'completed' : ($done > 0 ? 'partially_completed' : 'failed');
            }
            $b->update([
                'generated_count' => $done, 'failed_count' => $failed, 'duplicate_count' => $dupes,
                'requested_count' => max($b->requested_count, $b->items()->count()),
                'status' => $status,
                'completed_at' => in_array($status, ['completed', 'partially_completed', 'failed'], true) ? ($b->completed_at ?? now()) : $b->completed_at,
            ]);

            return $b;
        });
    }

    /** Recipes created by a batch (for the review screen). */
    public function recipesOf(RecipeGenerationBatch $batch)
    {
        return Recipe::whereIn('generation_item_id', $batch->items()->pluck('id'))->withCount('ingredients')->get();
    }
}
