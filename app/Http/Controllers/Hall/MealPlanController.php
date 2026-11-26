<?php

namespace App\Http\Controllers\Hall;

use App\Http\Controllers\Controller;
use App\Models\MealPlan;
use App\Models\MenuItem;
use App\Models\Recipe;
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
        return view('hall.meal-plans.create', ['dishSuggestions' => $this->dishSuggestions()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:150'],
            'category'         => ['required', 'in:standard,premium,custom'],
            'description'      => ['nullable', 'string', 'max:1000'],
            'items'            => ['nullable', 'array', 'max:'.MealPlan::MAX_ITEMS],
            'items.*'          => ['nullable', 'string', 'max:150'],
            'price_per_person' => ['required', 'numeric', 'min:0'],
            'is_active'        => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['items'] = $this->cleanItems($data['items'] ?? []);

        MealPlan::create($data);

        return redirect()->route('hall.meal-plans.index')->with('success', 'Meal plan created.');
    }

    public function edit(MealPlan $mealPlan): View
    {
        $mealPlan->loadCount('bookings');
        return view('hall.meal-plans.edit', ['mealPlan' => $mealPlan, 'dishSuggestions' => $this->dishSuggestions()]);
    }

    public function update(Request $request, MealPlan $mealPlan): RedirectResponse
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:150'],
            'category'         => ['required', 'in:standard,premium,custom'],
            'description'      => ['nullable', 'string', 'max:1000'],
            'items'            => ['nullable', 'array', 'max:'.MealPlan::MAX_ITEMS],
            'items.*'          => ['nullable', 'string', 'max:150'],
            'price_per_person' => ['required', 'numeric', 'min:0'],
            'is_active'        => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['items'] = $this->cleanItems($data['items'] ?? []);

        $mealPlan->update($data);

        return redirect()->route('hall.meal-plans.index')->with('success', 'Meal plan updated.');
    }

    /** dish names for the typing suggestions: menu catalogue, recipes and dishes already used in other plans */
    private function dishSuggestions(): array
    {
        return collect(MenuItem::orderBy('item_en')->pluck('item_en'))
            ->merge(Recipe::where('is_active', true)->whereNull('superseded_at')->pluck('name'))
            ->merge(MealPlan::get()->flatMap->dishList())
            ->map(fn ($n) => trim((string) $n))->filter()->unique(fn ($n) => mb_strtolower($n))->sort()->values()->take(500)->all();
    }

    /** trimmed, blank rows dropped, duplicates (case-insensitive) removed, order kept */
    private function cleanItems(array $items): ?array
    {
        $out = [];
        foreach ($items as $i) {
            $i = trim(preg_replace('/\s+/u', ' ', (string) $i));
            if ($i !== '' && ! isset($out[mb_strtolower($i)])) {
                $out[mb_strtolower($i)] = $i;
            }
        }

        return $out ? array_values($out) : null;
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
