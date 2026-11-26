<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Recipe;
use App\Models\RecipeGenerationBatch;
use App\Models\RecipeIngredient;
use App\Services\Ai\RecipeAiClient;
use App\Services\QueueHealth;
use App\Services\Recipes\RecipeGenerationService;
use App\Services\Recipes\RecipeLibraryService;
use App\Services\Recipes\StarterRecipeImporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

/**
 * AI Recipe Generator (admin): start batches, watch progress, retry/cancel; and the review queue:
 * mark reviewed, verify (explicit, admin only), archive/restore, map ingredients to stock items.
 */
class RecipeAiController extends Controller
{
    public function __construct(private RecipeGenerationService $gen, private RecipeLibraryService $library) {}

    public function index(Request $request, RecipeAiClient $ai, QueueHealth $queue, StarterRecipeImporter $starter): View
    {
        $this->authorize('recipes.generate');
        $batches = RecipeGenerationBatch::with('creator')->latest('id')->paginate(15)->withQueryString();
        $plan = collect(config('recipe_generation.initial_plan', []))->map(function ($g) {
            $missing = collect($g['dishes'])->reject(fn ($d) => $this->library->findDuplicate($d)['exact'])->values();

            return ['group' => $g['group'], 'total' => count($g['dishes']), 'missing' => $missing->count()];
        });

        return view('kitchen.recipes.ai.index', [
            'batches' => $batches, 'plan' => $plan, 'configured' => $ai->isConfigured(), 'model' => $ai->modelName(),
            'categories' => Recipe::categories(), 'cuisines' => Recipe::CUISINES, 'foodTypes' => Recipe::FOOD_TYPES,
            'yields' => config('recipe_generation.yields'), 'max' => (int) config('recipe_generation.max_per_batch', 50),
            'queue' => $queue->status(), 'workerCommand' => QueueHealth::workerCommand(),
            'provider' => $ai->providerName(), 'starterTotal' => count($starter->recipes()), 'starterMissing' => $starter->missingCount(),
            'running' => RecipeGenerationBatch::whereIn('status', ['pending', 'processing'])->count(),
        ]);
    }

    public function importStarter(Request $request, StarterRecipeImporter $starter): RedirectResponse
    {
        $this->authorize('recipes.generate');
        $r = $starter->import($request->user());
        $msg = count($r['created']).' starter recipes added to the review queue, '.count($r['tamil_updated']).' existing recipes got Tamil, '
            .count($r['skipped']).' already complete'.($r['similar'] ? ', '.count($r['similar']).' flagged as possible duplicates' : '')
            .($r['failed'] ? ', '.count($r['failed']).' failed ('.implode(', ', array_keys($r['failed'])).')' : '').'. They are not verified yet.';

        return redirect()->route('kitchen.recipes.review')->with($r['failed'] ? 'error' : 'success', $msg);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('recipes.generate');
        $max = (int) config('recipe_generation.max_per_batch', 50);
        $data = $request->validate([
            'category' => ['required', Rule::in(Recipe::categories())],
            'cuisine' => ['nullable', 'string', 'max:60'],
            'food_type' => ['required', Rule::in(array_keys(Recipe::FOOD_TYPES))],
            'yield_people' => ['required', 'integer', 'min:5', 'max:2000'],
            'count' => ['nullable', 'integer', 'min:1', 'max:'.$max],
            'dishes' => ['nullable', 'string', 'max:5000'],
            'group' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);
        $data['dishes'] = array_values(array_filter(array_map('trim', preg_split('/[\r\n,;]+/', (string) ($data['dishes'] ?? '')))));
        if (! $data['dishes'] && empty($data['count'])) {
            return back()->withInput()->withErrors(['count' => 'Enter how many recipes to generate, or list the dishes.']);
        }
        try {
            $batch = $this->gen->start($data, $request->user());
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('kitchen.recipes.ai.batch', $batch)->with('success', 'Generation started. Recipes appear below as they are written.');
    }

    public function initialPlan(Request $request): RedirectResponse
    {
        $this->authorize('recipes.generate');
        $yield = (int) $request->validate(['yield_people' => ['required', 'integer', 'min:5', 'max:2000']])['yield_people'];
        try {
            $batches = $this->gen->startInitialPlan($request->user(), $yield);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('kitchen.recipes.ai.index')->with('success', $batches
            ? count($batches).' batches queued ('.collect($batches)->sum('requested_count').' recipes). Only dishes not already in the library are generated.'
            : 'Every dish in the initial plan is already in the library.');
    }

    public function batch(RecipeGenerationBatch $batch, QueueHealth $queue): View
    {
        $this->authorize('recipes.generate');
        $batch->load(['items.recipe', 'creator']);

        return view('kitchen.recipes.ai.batch', ['batch' => $batch, 'queue' => $queue->status(),
            'stalled' => $this->gen->stalledCount($batch), 'workerCommand' => QueueHealth::workerCommand()]);
    }

    /** Polled by the batch page while it runs. */
    public function progress(RecipeGenerationBatch $batch): JsonResponse
    {
        $this->authorize('recipes.generate');
        $batch->load('items.recipe:id,name,verification_status');

        return response()->json([
            'status' => $batch->status, 'label' => $batch->statusLabel(), 'percent' => $batch->progressPercent(),
            'generated' => $batch->generated_count, 'failed' => $batch->failed_count, 'duplicates' => $batch->duplicate_count,
            'requested' => $batch->requested_count, 'finished' => $batch->isFinished(), 'error' => $batch->error,
            'stalled' => $this->gen->stalledCount($batch), 'worker' => app(QueueHealth::class)->status()['worker_alive'],
            'items' => $batch->items->map(fn ($i) => [
                'id' => $i->id, 'dish' => $i->dish_name, 'status' => $i->status, 'error' => $i->error,
                'recipe' => $i->recipe ? ['name' => $i->recipe->name, 'url' => route('kitchen.recipes.show', $i->recipe)] : null,
            ]),
        ]);
    }

    public function retry(Request $request, RecipeGenerationBatch $batch): RedirectResponse
    {
        $this->authorize('recipes.generate');
        try {
            $n = $this->gen->retryFailed($batch, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $n ? "Retrying {$n} failed ".Str::plural('recipe', $n).'.' : 'Nothing to retry.');
    }

    public function cancel(Request $request, RecipeGenerationBatch $batch): RedirectResponse
    {
        $this->authorize('recipes.generate');
        $this->gen->cancel($batch, $request->user());

        return back()->with('success', 'Batch cancelled. Recipes already written are kept.');
    }

    // ── review queue ────────────────────────────────────────────────────────

    public function review(Request $request): View
    {
        $this->authorize('recipes.generate');
        $status = $request->input('status', 'to_review');
        $q = Recipe::query()->withCount('ingredients')
            ->withCount(['ingredients as unmapped_count' => fn ($w) => $w->where('needs_mapping', true)])
            ->with('duplicateOf:id,name', 'generationItem.batch:id,label')
            ->when($status === 'to_review', fn ($w) => $w->whereIn('verification_status', ['ai_generated', 'needs_review']))
            ->when(in_array($status, array_keys(Recipe::VERIFICATION), true), fn ($w) => $w->where('verification_status', $status))
            ->when($request->filled('category'), fn ($w) => $w->where('category', $request->input('category')))
            ->when($request->filled('cuisine'), fn ($w) => $w->where('cuisine', $request->input('cuisine')))
            ->when($request->filled('food_type'), fn ($w) => $w->where('food_type', $request->input('food_type')))
            ->when($request->filled('q'), fn ($w) => $w->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($request->input('q')).'%']))
            ->orderByRaw("CASE verification_status WHEN 'needs_review' THEN 0 WHEN 'ai_generated' THEN 1 WHEN 'reviewed' THEN 2 ELSE 3 END")
            ->latest('id');
        $counts = Recipe::selectRaw('verification_status, COUNT(*) as n')->groupBy('verification_status')->pluck('n', 'verification_status');

        return view('kitchen.recipes.ai.review', [
            'recipes' => $q->paginate(30)->withQueryString(), 'status' => $status, 'counts' => $counts,
            'categories' => Recipe::categories(), 'cuisines' => Recipe::whereNotNull('cuisine')->distinct()->orderBy('cuisine')->pluck('cuisine'),
            'foodTypes' => Recipe::FOOD_TYPES,
        ]);
    }

    public function markReviewed(Request $request, Recipe $recipe): RedirectResponse
    {
        $this->authorize('recipes.generate');

        return $this->act(fn () => $this->library->markReviewed($recipe, $request->user()), 'Marked as reviewed.');
    }

    public function verify(Request $request, Recipe $recipe): RedirectResponse
    {
        $this->authorize('recipes.verify');
        $data = $request->validate(['notes' => ['nullable', 'string', 'max:1000'], 'confirm' => ['accepted'], 'allow_missing_tamil' => ['nullable', 'boolean']]);

        return $this->act(fn () => $this->library->verify($recipe, $request->user(), $data['notes'] ?? null, (bool) ($data['allow_missing_tamil'] ?? false)),
            "“{$recipe->name}” is now Verified and available to the kitchen.");
    }

    public function archive(Request $request, Recipe $recipe): RedirectResponse
    {
        $this->authorize('recipes.verify');

        return $this->act(fn () => $this->library->archive($recipe, $request->user()), 'Recipe archived.');
    }

    public function restore(Request $request, Recipe $recipe): RedirectResponse
    {
        $this->authorize('recipes.verify');

        return $this->act(fn () => $this->library->restore($recipe, $request->user()), 'Recipe restored to the review queue.');
    }

    public function mapIngredient(Request $request, Recipe $recipe, RecipeIngredient $ingredient): RedirectResponse
    {
        $this->authorize('recipes.generate');
        abort_unless($ingredient->recipe_id === $recipe->id, 404);
        $data = $request->validate(['inventory_item_id' => ['nullable', 'integer', 'exists:inventory_items,id']]);
        $this->library->mapIngredient($ingredient, $data['inventory_item_id'] ? InventoryItem::find($data['inventory_item_id']) : null, $request->user());

        return back()->with('success', 'Ingredient mapping saved.');
    }

    private function act(callable $fn, string $ok): RedirectResponse
    {
        try {
            $fn();
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $ok);
    }
}
