<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
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
            ->get(['id', 'name', 'category', 'yield_per_batch', 'yield_unit', 'prep_time_minutes', 'cook_time_minutes']);

        return view('employee.kitchen.calculator', [
            'recipes'    => $recipes,
            'categories' => Recipe::categories(),
        ]);
    }

    /**
     * AJAX endpoint — returns full recipe data (ingredients + sops) for the calculator.
     * Only active recipes are accessible. No financial data is ever returned here.
     */
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
            'id'               => $recipe->id,
            'name'             => $recipe->name,
            'category'         => $recipe->category,
            'yield_per_batch'  => $recipe->yield_per_batch,
            'yield_unit'       => $recipe->yield_unit,
            'prep_time_minutes'=> $recipe->prep_time_minutes,
            'cook_time_minutes'=> $recipe->cook_time_minutes,
            'ingredients'      => $recipe->ingredients->map(fn ($ing) => [
                'id'                 => $ing->id,
                'ingredient_name'    => $ing->ingredient_name,
                'quantity_per_batch' => $ing->quantity_per_batch,  // null = not scalable
                'quantity_note'      => $ing->quantity_note,       // verbatim for non-scalable
                'unit'               => $ing->unit,
                'prep_note'          => $ing->prep_note,
                'is_optional'        => $ing->is_optional,
                'is_scalable'        => $ing->isScalable(),
            ]),
            'sops' => $recipe->sops->map(fn ($sop) => [
                'step_number'      => $sop->step_number,
                'title'            => $sop->title,
                'instruction'      => $sop->instruction,
                'duration_minutes' => $sop->duration_minutes,
            ]),
        ]);
    }
}
