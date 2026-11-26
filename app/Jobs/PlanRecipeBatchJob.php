<?php

namespace App\Jobs;

use App\Models\RecipeGenerationBatch;
use App\Services\Ai\RecipeAiClient;
use App\Services\Ai\RecipeAiException;
use App\Services\Recipes\RecipeGenerationService;
use App\Services\Recipes\RecipeLibraryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/** For a batch without a dish list: ask the AI for dish names (excluding the library), then queue one job per dish. */
class PlanRecipeBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public int $timeout = 600;

    public function __construct(public int $batchId) {}

    public function handle(RecipeAiClient $ai, RecipeGenerationService $gen, RecipeLibraryService $library): void
    {
        $batch = RecipeGenerationBatch::find($this->batchId);
        if (! $batch || $batch->status === 'cancelled' || $batch->items()->exists()) {
            $batch && $batch->items()->where('status', 'pending')->exists() && $gen->dispatchItems($batch);   // re-run after a crash

            return;
        }
        $batch->update(['status' => 'processing', 'started_at' => $batch->started_at ?? now()]);

        try {
            $names = $ai->planDishes($batch->config, $batch->requested_count, $library->existingNames());
        } catch (RecipeAiException $e) {
            if ($e->retryable && $this->attempts() < $this->tries) {
                $this->release($this->backoff[$this->attempts() - 1] ?? 300);

                return;
            }
            $batch->update(['status' => 'failed', 'error' => $e->getMessage(), 'completed_at' => now()]);

            return;
        }
        $names = array_values(array_filter($names, fn ($n) => ! $library->findDuplicate($n)['exact']));
        $names = array_slice($names, 0, $batch->requested_count);
        if (! $names) {
            $batch->update(['status' => 'failed', 'error' => 'No new dishes were proposed (all already exist).', 'completed_at' => now()]);

            return;
        }
        $gen->addItems($batch, $names);
        $gen->dispatchItems($batch);
    }

    public function failed(\Throwable $e): void
    {
        RecipeGenerationBatch::whereKey($this->batchId)->update(['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 1000), 'completed_at' => now()]);
    }
}
