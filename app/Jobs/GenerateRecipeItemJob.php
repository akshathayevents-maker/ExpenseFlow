<?php

namespace App\Jobs;

use App\Models\Recipe;
use App\Models\RecipeGenerationItem;
use App\Services\Ai\RecipeAiClient;
use App\Services\Ai\RecipeAiException;
use App\Services\Recipes\DuplicateRecipe;
use App\Services\Recipes\InvalidRecipeOutput;
use App\Services\Recipes\RecipeGenerationService;
use App\Services\Recipes\RecipeLibraryService;
use App\Services\Recipes\RecipeOutputValidator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Generates and saves ONE recipe. Idempotent: a finished item, or an item that already has a recipe
 * (e.g. the worker died after saving), is never generated again. Never creates stock transactions.
 */
class GenerateRecipeItemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public int $timeout = 600;

    public function __construct(public int $itemId) {}

    public function handle(RecipeAiClient $ai, RecipeOutputValidator $validator, RecipeLibraryService $library, RecipeGenerationService $gen): void
    {
        $item = $this->claim();
        if (! $item) {
            return;
        }
        $batch = $item->batch;

        try {
            $out = $ai->generateRecipe($item->dish_name, $batch->config);
            $recipe = $validator->validate($out['recipe'], $batch->config);
            $saved = DB::transaction(function () use ($item, $recipe, $out, $batch, $library, $ai) {
                $fresh = RecipeGenerationItem::lockForUpdate()->find($item->id);
                if ($fresh->recipe_id || Recipe::where('generation_item_id', $item->id)->exists()) {
                    return null;   // another attempt already saved it
                }
                $r = $library->createFromAi($fresh, $recipe, [
                    'provider' => $ai->providerName(), 'model' => $out['model'] ?? $ai->modelName(),
                    'request_id' => $out['request_id'] ?? null, 'batch_id' => $batch->id, 'dish' => $item->dish_name,
                    'generated_at' => now()->toIso8601String(),
                ], $batch->creator);
                $fresh->update(['status' => 'done', 'recipe_id' => $r->id, 'error' => null, 'request_id' => $out['request_id'] ?? null]);

                return $r;
            });
            if ($saved === null) {
                $this->settle($item, 'done');
            }
        } catch (DuplicateRecipe $e) {
            $item->update(['status' => 'duplicate', 'error' => $e->getMessage()]);
        } catch (RecipeAiException|InvalidRecipeOutput $e) {
            $retryable = $e instanceof InvalidRecipeOutput || $e->retryable;
            if ($retryable && $this->attempts() < $this->tries) {
                $item->update(['status' => 'pending', 'error' => $e->getMessage()]);
                $this->release($this->backoff[$this->attempts() - 1] ?? 300);

                return;
            }
            $item->update(['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 1000)]);
        }
        $gen->refresh($batch);
    }

    /** Lock the item and mark it processing, or return null if there is nothing to do. */
    private function claim(): ?RecipeGenerationItem
    {
        return DB::transaction(function () {
            $item = RecipeGenerationItem::with('batch.creator')->lockForUpdate()->find($this->itemId);
            if (! $item || in_array($item->status, ['done', 'duplicate', 'cancelled'], true)) {
                return null;
            }
            if ($item->batch->status === 'cancelled') {
                $item->update(['status' => 'cancelled']);

                return null;
            }
            if ($item->recipe_id || ($rid = Recipe::where('generation_item_id', $item->id)->value('id'))) {
                $item->update(['status' => 'done', 'recipe_id' => $item->recipe_id ?? $rid]);

                return null;
            }
            $item->update(['status' => 'processing', 'attempts' => $item->attempts + 1]);

            return $item;
        });
    }

    private function settle(RecipeGenerationItem $item, string $status): void
    {
        $item->refresh();
        if (! in_array($item->status, ['done', 'duplicate'], true)) {
            $item->update(['status' => $status]);
        }
    }

    public function failed(\Throwable $e): void
    {
        $item = RecipeGenerationItem::find($this->itemId);
        if ($item && ! in_array($item->status, ['done', 'duplicate', 'cancelled'], true)) {
            $item->update(['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 1000)]);
            app(RecipeGenerationService::class)->refresh($item->batch);
        }
    }
}
