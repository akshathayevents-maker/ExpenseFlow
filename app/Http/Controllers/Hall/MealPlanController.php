<?php

namespace App\Http\Controllers\Hall;

use App\Http\Controllers\Controller;
use App\Models\MealPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MealPlanController extends Controller
{
    public function index(): View
    {
        $plans = MealPlan::withCount('bookings')
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(20);

        $topPlan  = MealPlan::withCount('bookings')->orderByDesc('bookings_count')->first();
        $active   = MealPlan::active()->get();

        $stats = [
            'total'   => MealPlan::count(),
            'active'  => $active->count(),
            'premium' => MealPlan::where('category', 'premium')->count(),
            'avg_price' => (int) round($active->avg('price_per_person') ?? 0),
            'top_plan'  => $topPlan,
        ];

        return view('hall.meal-plans.index', compact('plans', 'stats'));
    }

    public function create(): View
    {
        return view('hall.meal-plans.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:150'],
            'category'         => ['required', 'in:standard,premium,custom'],
            'description'      => ['nullable', 'string', 'max:1000'],
            'price_per_person' => ['required', 'numeric', 'min:0'],
            'is_active'        => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        MealPlan::create($data);

        return redirect()->route('hall.meal-plans.index')->with('success', 'Meal plan created.');
    }

    public function edit(MealPlan $mealPlan): View
    {
        $mealPlan->loadCount('bookings');
        return view('hall.meal-plans.edit', compact('mealPlan'));
    }

    public function update(Request $request, MealPlan $mealPlan): RedirectResponse
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:150'],
            'category'         => ['required', 'in:standard,premium,custom'],
            'description'      => ['nullable', 'string', 'max:1000'],
            'price_per_person' => ['required', 'numeric', 'min:0'],
            'is_active'        => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $mealPlan->update($data);

        return redirect()->route('hall.meal-plans.index')->with('success', 'Meal plan updated.');
    }

    public function destroy(MealPlan $mealPlan): RedirectResponse
    {
        if ($mealPlan->bookings()->exists()) {
            return back()->with('error', 'Cannot delete meal plan with existing bookings.');
        }
        $mealPlan->delete();
        return redirect()->route('hall.meal-plans.index')->with('success', 'Meal plan deleted.');
    }

    public function toggleStatus(MealPlan $mealPlan): RedirectResponse
    {
        $mealPlan->update(['is_active' => ! $mealPlan->is_active]);
        return back()->with('success', 'Status updated.');
    }
}
