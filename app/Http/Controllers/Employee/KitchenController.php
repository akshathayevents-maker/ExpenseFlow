<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Kitchen\RecipeController;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KitchenController extends Controller
{
    /**
     * Calculator landing page.
     * Passes lightweight recipe list (no ingredients/sops) for the selection UI.
     * Full recipe data is fetched per-recipe via the `recipe()` endpoint when selected.
     */
    public function index(): View
    {
        $recipes = Recipe::active()
            ->withCount(['ingredients', 'sops'])
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'yield_per_batch', 'yield_unit', 'prep_time_minutes', 'cook_time_minutes', 'verification_status', 'verified_by', 'verified_at'])
            ->each(fn ($r) => $r->setAttribute('verified', $r->isVerified()));

        return view('employee.kitchen.calculator', [
            'recipes' => $recipes,
            'categories' => Recipe::categories(),
        ]);
    }

    /**
     * AJAX endpoint — returns full recipe data (ingredients + sops) for the calculator.
     * Only active recipes are accessible. No financial data is ever returned here.
     */
    /** Printable kitchen sheet (English / Tamil / both) for an active recipe. */
    public function print(Request $request, Recipe $recipe): View
    {
        abort_unless($recipe->is_active, 404);

        return RecipeController::printView($request, $recipe);
    }

    public function recipe(Recipe $recipe): JsonResponse
    {
        // Guard: employee must not be able to access inactive recipes via direct URL
        if (! $recipe->is_active) {
            abort(404);
        }

        $recipe->load([
            'ingredients' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
            'sops',
        ]);

        return response()->json([
            'id' => $recipe->id,
            'name' => $recipe->name,
            'category' => $recipe->category,
            'yield_per_batch' => $recipe->yield_per_batch,
            'yield_unit' => $recipe->yield_unit,
            'prep_time_minutes' => $recipe->prep_time_minutes,
            'cook_time_minutes' => $recipe->cook_time_minutes,
            'verified' => $recipe->isVerified(),
            'ingredients' => $recipe->ingredients->map(fn ($ing) => [
                'id' => $ing->id,
                'ingredient_name' => $ing->ingredient_name,
                'quantity_per_batch' => $ing->quantity_per_batch,  // null = not scalable
                'quantity_note' => $ing->quantity_note,       // verbatim for non-scalable
                'unit' => $ing->unit,
                'prep_note' => $ing->prep_note,
                'is_optional' => $ing->is_optional,
                'is_scalable' => $ing->isScalable(),
            ]),
            'sops' => $recipe->sops->map(fn ($sop) => [
                'step_number' => $sop->step_number,
                'title' => $sop->title,
                'instruction' => $sop->instruction,
                'duration_minutes' => $sop->duration_minutes,
            ]),
        ]);
    }
}
