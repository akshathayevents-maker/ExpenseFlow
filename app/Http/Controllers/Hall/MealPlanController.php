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
    public function index(Request $request): View
    {
        $f = $request->validate([
            'q'        => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'in:'.implode(',', array_keys(MealPlan::categories()))],
        ]);
        // Unknown ?status= is ignored (shows all), like the other list pages.
        $status = in_array($request->query('status'), ['active', 'inactive'], true) ? $request->query('status') : null;
        $search = trim((string) ($f['q'] ?? ''));
        $today  = now()->toDateString();

        $plans = MealPlan::withCount([
            'bookings',
            'bookings as upcoming_count' => fn ($q) => $q->whereDate('booking_date', '>=', $today)->where('status', '!=', 'cancelled'),
        ])
            ->when($status, fn ($q) => $q->where('is_active', $status === 'active'))
            ->when($f['category'] ?? null, fn ($q, $c) => $q->where('category', $c))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")))
            ->orderByDesc('is_active')->orderBy('category')->orderBy('name')
            ->paginate(20)->withQueryString();

        $byActive = MealPlan::selectRaw('is_active, COUNT(*) as n, AVG(price_per_person) as avg_price')->groupBy('is_active')->get();
        $activeRow = $byActive->first(fn ($r) => (bool) $r->is_active);
        $stats = [
            'all'       => (int) $byActive->sum('n'),
            'active'    => (int) ($activeRow->n ?? 0),
            'inactive'  => (int) $byActive->reject(fn ($r) => (bool) $r->is_active)->sum('n'),
            'avg_price' => (int) round((float) ($activeRow->avg_price ?? 0)),
        ];
        $catCounts = MealPlan::selectRaw('category, COUNT(*) as n')->groupBy('category')->pluck('n', 'category');

        $topPlans = MealPlan::select(['id', 'name', 'price_per_person', 'is_active'])->withCount('bookings')
            ->orderByDesc('bookings_count')->orderBy('name')->limit(5)->get()->where('bookings_count', '>', 0)->values();

        $filters = ['q' => $search, 'category' => $f['category'] ?? null];

        return view('hall.meal-plans.index', compact('plans', 'stats', 'status', 'catCounts', 'topPlans', 'filters'));
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
            'meal_times'       => ['nullable', 'array'],
            'meal_times.*'     => ['in:breakfast,lunch,dinner'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        // keep breakfast → lunch → dinner order; none ticked = not tagged (shown for any meal)
        $data['meal_times'] = array_values(array_intersect(array_keys(MealPlan::MEAL_TIMES), $data['meal_times'] ?? [])) ?: null;
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
            'meal_times'       => ['nullable', 'array'],
            'meal_times.*'     => ['in:breakfast,lunch,dinner'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        // keep breakfast → lunch → dinner order; none ticked = not tagged (shown for any meal)
        $data['meal_times'] = array_values(array_intersect(array_keys(MealPlan::MEAL_TIMES), $data['meal_times'] ?? [])) ?: null;
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
