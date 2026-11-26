<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeSop;
use App\Services\Recipes\RecipeLibraryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RecipeController extends Controller
{
    // ── Index ──────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        // Library shows every recipe except archived ones (unless asked for); badges say which are verified.
        $vstatus = $request->input('verification');
        $query = Recipe::withCount(['ingredients', 'sops'])->with('verifier:id,name')
            ->when($vstatus === 'archived', fn ($q) => $q->where('verification_status', 'archived'),
                fn ($q) => $q->where('verification_status', '!=', 'archived'))
            ->when($vstatus === 'to_review', fn ($q) => $q->whereIn('verification_status', ['ai_generated', 'needs_review']))
            ->when(in_array($vstatus, ['verified', 'reviewed', 'unverified', 'ai_generated', 'needs_review'], true), fn ($q) => $q->where('verification_status', $vstatus));

        if ($search = $request->input('search')) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $recipes = $query->orderBy('category')->orderBy('name')->get();

        return view('kitchen.recipes.index', [
            'recipes' => $recipes,
            'categories' => Recipe::categories(),
            'filters' => $request->only(['search', 'category', 'status', 'verification']),
            'vcounts' => Recipe::selectRaw('verification_status, COUNT(*) as n')->groupBy('verification_status')->pluck('n', 'verification_status'),
        ]);
    }

    // ── Create ─────────────────────────────────────────────────────────────────

    public function create(): View
    {
        return view('kitchen.recipes.create', [
            'categories' => Recipe::categories(),
            'inventoryItems' => InventoryItem::active()->orderBy('name')->get(['id', 'name', 'unit']),
        ]);
    }

    // ── Store ──────────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRecipe($request);

        // Case-insensitive duplicate name check — no DB unique constraint needed
        if ($this->nameExists($request->input('name'))) {
            return back()
                ->withErrors(['name' => 'A recipe named "'.$request->input('name').'" already exists.'])
                ->withInput();
        }

        DB::transaction(function () use ($data, $request) {
            $recipe = Recipe::create([
                'name' => $data['name'],
                'category' => $data['category'],
                'description' => $data['description'] ?? null,
                'name_ta' => trim((string) ($data['name_ta'] ?? '')) ?: null,
                'kitchen_notes' => trim((string) ($data['kitchen_notes'] ?? '')) ?: null,
                'kitchen_notes_ta' => trim((string) ($data['kitchen_notes_ta'] ?? '')) ?: null,
                'prep_time_minutes' => $data['prep_time_minutes'] ?? null,
                'cook_time_minutes' => $data['cook_time_minutes'] ?? null,
                'yield_per_batch' => $data['yield_per_batch'],
                'yield_unit' => $data['yield_unit'],
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);

            $this->syncIngredients($recipe, $request->input('ingredients', []));
            $this->syncSops($recipe, $request->input('sops', []));
        });

        return redirect()->route('kitchen.recipes.index')
            ->with('success', 'Recipe "'.$data['name'].'" created successfully.');
    }

    // ── Show ───────────────────────────────────────────────────────────────────

    public function show(Recipe $recipe): View
    {
        $recipe->load(['ingredients' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'), 'ingredients.inventoryItem:id,name,unit',
            'sops', 'createdBy', 'verifier', 'versions.verifier', 'versions.changer', 'duplicateOf:id,name']);

        return view('kitchen.recipes.show', compact('recipe'));
    }

    // ── Print (kitchen sheet: English / Tamil / both, scaled) ──────────────────

    public function print(Request $request, Recipe $recipe): View
    {
        return self::printView($request, $recipe);
    }

    /** Shared with the employee kitchen calculator route. */
    public static function printView(Request $request, Recipe $recipe): View
    {
        $data = $request->validate(['people' => ['nullable', 'integer', 'min:1', 'max:5000'], 'lang' => ['nullable', 'in:en,ta,both']]);
        $people = (int) ($data['people'] ?? max(1, (int) round((float) $recipe->yield_per_batch)));
        $recipe->load(['ingredients.inventoryItem:id,name,name_ta', 'sops']);

        return view('kitchen.recipes.print', [
            'recipe' => $recipe, 'people' => $people, 'lang' => $data['lang'] ?? 'both',
            'scaled' => $recipe->scaled($people), 'plan' => $recipe->batchPlan($people),
        ]);
    }

    // ── Edit ───────────────────────────────────────────────────────────────────

    public function edit(Recipe $recipe): View
    {
        $recipe->load(['ingredients' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'), 'ingredients.inventoryItem:id,name,unit', 'sops']);

        return view('kitchen.recipes.edit', [
            'recipe' => $recipe,
            'categories' => Recipe::categories(),
            'inventoryItems' => InventoryItem::active()->orderBy('name')->get(['id', 'name', 'unit']),
        ]);
    }

    // ── Update ─────────────────────────────────────────────────────────────────

    public function update(Request $request, Recipe $recipe): RedirectResponse
    {
        $data = $this->validateRecipe($request);

        // Duplicate check excludes the current recipe
        if ($this->nameExists($request->input('name'), $recipe->id)) {
            return back()
                ->withErrors(['name' => 'Another recipe named "'.$request->input('name').'" already exists.'])
                ->withInput();
        }

        $before = $recipe->materialSnapshot();
        DB::transaction(function () use ($data, $request, $recipe) {
            $recipe->update([
                'name' => $data['name'],
                'category' => $data['category'],
                'description' => $data['description'] ?? null,
                'name_ta' => trim((string) ($data['name_ta'] ?? '')) ?: null,
                'kitchen_notes' => trim((string) ($data['kitchen_notes'] ?? '')) ?: null,
                'kitchen_notes_ta' => trim((string) ($data['kitchen_notes_ta'] ?? '')) ?: null,
                'prep_time_minutes' => $data['prep_time_minutes'] ?? null,
                'cook_time_minutes' => $data['cook_time_minutes'] ?? null,
                'yield_per_batch' => $data['yield_per_batch'],
                'yield_unit' => $data['yield_unit'],
            ]);

            // Delete and re-insert to respect submitted ordering
            $recipe->ingredients()->delete();
            $recipe->sops()->delete();

            $this->syncIngredients($recipe, $request->input('ingredients', []));
            $this->syncSops($recipe, $request->input('sops', []));
        });

        // a material change (yield, ingredients, quantities, steps) keeps the old version and resets verification
        $lostVerification = app(RecipeLibraryService::class)->afterEdit($recipe, $before, $request->user());

        return redirect()->route('kitchen.recipes.show', $recipe)
            ->with($lostVerification ? 'warning' : 'success', $lostVerification
                ? 'Recipe updated. Ingredients, quantities, yield or steps changed, so it is no longer verified — the previous verified version is kept in its history. Verify it again when it is ready.'
                : 'Recipe updated successfully.');
    }

    // ── Destroy ────────────────────────────────────────────────────────────────

    public function destroy(Recipe $recipe): RedirectResponse
    {
        if ($recipe->isVerified() || $recipe->versions()->exists()) {
            return back()->with('error', 'Verified recipes (and recipes with history) cannot be deleted — archive them instead.');
        }
        $name = $recipe->name;
        $recipe->delete(); // cascades to ingredients + sops via FK

        return redirect()->route('kitchen.recipes.index')
            ->with('success', 'Recipe "'.$name.'" deleted.');
    }

    // ── Toggle active ──────────────────────────────────────────────────────────

    public function toggleActive(Recipe $recipe): RedirectResponse
    {
        if (! $recipe->is_active && in_array($recipe->source, ['ai', 'starter'], true) && ! $recipe->isVerified()) {
            return back()->with('error', 'AI-generated and starter recipes become available to the kitchen when they are verified.');
        }
        $recipe->update(['is_active' => ! $recipe->is_active]);

        $state = $recipe->is_active ? 'activated' : 'deactivated';

        return back()->with('success', '"'.$recipe->name.'" '.$state.'.');
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function validateRecipe(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'category' => ['nullable', 'in:'.implode(',', Recipe::categories())],
            'description' => ['nullable', 'string'],
            'name_ta' => ['nullable', 'string', 'max:200'],
            'kitchen_notes' => ['nullable', 'string', 'max:3000'],
            'kitchen_notes_ta' => ['nullable', 'string', 'max:3000'],
            'ingredients.*.ingredient_name_ta' => ['nullable', 'string', 'max:200'],
            'sops.*.title_ta' => ['nullable', 'string', 'max:200'],
            'sops.*.instruction_ta' => ['nullable', 'string', 'max:5000'],
            'sops.*.note' => ['nullable', 'string', 'max:500'],
            'sops.*.note_ta' => ['nullable', 'string', 'max:500'],
            'yield_per_batch' => ['required', 'numeric', 'min:0.01'],
            'yield_unit' => ['required', 'string', 'max:50'],
            'prep_time_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'cook_time_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],

            'ingredients' => ['nullable', 'array'],
            'ingredients.*.ingredient_name' => ['required', 'string', 'max:200'],
            'ingredients.*.inventory_item_id' => ['nullable', 'integer', 'exists:inventory_items,id'],
            'ingredients.*.quantity_per_batch' => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.quantity_note' => ['nullable', 'string', 'max:200'],
            'ingredients.*.unit' => ['nullable', 'string', 'max:50'],
            'ingredients.*.prep_note' => ['nullable', 'string', 'max:200'],
            'ingredients.*.is_optional' => ['nullable', 'boolean'],
            'ingredients.*.is_approximate' => ['nullable', 'boolean'],

            'sops' => ['nullable', 'array'],
            'sops.*.title' => ['required', 'string', 'max:200'],
            'sops.*.instruction' => ['required', 'string'],
            'sops.*.duration_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
        ]);
    }

    /**
     * Case-insensitive name uniqueness check.
     * "Upma", "upma", "UPMA" all resolve to the same recipe.
     *
     * @param  int|null  $excludeId  Exclude this recipe ID from the check (for updates).
     */
    private function nameExists(string $name, ?int $excludeId = null): bool
    {
        return Recipe::whereRaw('LOWER(name) = ?', [strtolower(trim($name))])
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }

    private function syncIngredients(Recipe $recipe, array $ingredients): void
    {
        foreach ($ingredients as $index => $row) {
            if (empty(trim($row['ingredient_name'] ?? ''))) {
                continue; // skip blank rows the user left open
            }

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'inventory_item_id' => ($row['inventory_item_id'] ?? null) ?: null,
                'ingredient_name' => trim($row['ingredient_name']),
                'ingredient_name_ta' => trim((string) ($row['ingredient_name_ta'] ?? '')) ?: null,
                'quantity_per_batch' => isset($row['quantity_per_batch']) && $row['quantity_per_batch'] !== ''
                    ? (float) $row['quantity_per_batch']
                    : null,
                'quantity_note' => trim($row['quantity_note'] ?? '') ?: null,
                'unit' => trim($row['unit'] ?? '') ?: null,
                'prep_note' => trim($row['prep_note'] ?? '') ?: null,
                'is_optional' => (bool) ($row['is_optional'] ?? false),
                'is_approximate' => (bool) ($row['is_approximate'] ?? false),
                // AI recipes: an ingredient without a stock item stays flagged for mapping
                'needs_mapping' => $recipe->source === 'ai' && empty($row['inventory_item_id'])
                    && ! in_array(mb_strtolower(trim($row['ingredient_name'])), ['water', 'hot water', 'cold water', 'warm water'], true),
                'sort_order' => $index,
            ]);
        }
    }

    private function syncSops(Recipe $recipe, array $sops): void
    {
        $step = 1;
        foreach ($sops as $row) {
            if (empty(trim($row['title'] ?? '')) && empty(trim($row['instruction'] ?? ''))) {
                continue; // skip blank SOP rows
            }

            RecipeSop::create([
                'recipe_id' => $recipe->id,
                'step_number' => $step++,
                'title' => trim($row['title']),
                'title_ta' => trim((string) ($row['title_ta'] ?? '')) ?: null,
                'instruction' => trim($row['instruction']),
                'instruction_ta' => trim((string) ($row['instruction_ta'] ?? '')) ?: null,
                'note' => trim((string) ($row['note'] ?? '')) ?: null,
                'note_ta' => trim((string) ($row['note_ta'] ?? '')) ?: null,
                'duration_minutes' => isset($row['duration_minutes']) && $row['duration_minutes'] !== ''
                    ? (int) $row['duration_minutes']
                    : null,
            ]);
        }
    }
}
