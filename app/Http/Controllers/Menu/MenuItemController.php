<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\EventRequestItem;
use App\Models\MealPlan;
use App\Models\MenuItem;
use App\Models\Recipe;
use App\Services\AuditLogService;
use App\Services\MenuTranslationService;
use App\Services\Recipes\RecipeLibraryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
        private MenuTranslationService $translator,
    ) {}
    // ── Index ──────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $categories = config('menu_categories.items', []);
        $f = $request->validate([
            'search'   => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:60'],
            'page'     => ['nullable', 'integer', 'min:1'],
        ]);
        // Quick filters: unknown values are ignored (show everything), like other list pages.
        $recipeF = in_array($request->query('recipe'), ['none', 'unverified', 'verified'], true) ? $request->query('recipe') : null;
        $tamilF  = $request->query('tamil') === 'missing' ? 'missing' : null;
        $stateF  = $request->query('state') === 'inactive' ? 'inactive' : null;
        $cat     = isset($categories[$f['category'] ?? '']) ? $f['category'] : null;
        $search  = trim((string) ($f['search'] ?? ''));

        $all = MenuItem::query()->with('eventRequestItem:id,price_per_person,is_active')->ordered()->get();

        // recipe per dish (spelling-insensitive name match) and where each dish is used
        $recipes = Recipe::where('verification_status', '!=', 'archived')->whereNull('superseded_at')
            ->get(['id', 'name', 'verification_status', 'verified_by', 'verified_at'])
            ->keyBy(fn ($r) => RecipeLibraryService::nameKey($r->name));
        $requestUse = EventRequestItem::selectRaw('menu_item_id, COUNT(DISTINCT event_request_id) as n')
            ->groupBy('menu_item_id')->pluck('n', 'menu_item_id');
        $planUse = [];
        foreach (MealPlan::where('is_active', true)->get() as $plan) {
            foreach ($plan->dishList() as $d) {
                $planUse[mb_strtolower(trim($d))] = ($planUse[mb_strtolower(trim($d))] ?? 0) + 1;
            }
        }
        foreach ($all as $it) {
            $it->setAttribute('recipe', $recipes[RecipeLibraryService::nameKey($it->item_en)] ?? null);
            $it->setAttribute('used_requests', (int) ($requestUse[$it->event_request_menu_item_id] ?? 0));
            $it->setAttribute('used_plans', (int) ($planUse[mb_strtolower(trim($it->item_en))] ?? 0));
        }

        // search + category narrow the counts; quick filters (tiles) narrow the list
        $scope = $all
            ->when($search !== '', fn ($c) => $c->filter(fn ($i) => mb_stripos($i->item_en, $search) !== false || mb_stripos((string) $i->item_ta, $search) !== false))
            ->when($cat, fn ($c) => $c->where('category_key', $cat))
            ->values();
        $stats = [
            'total'         => $scope->count(),
            'with_recipe'   => $scope->whereNotNull('recipe')->count(),
            'verified'      => $scope->filter(fn ($i) => $i->recipe?->isVerified())->count(),
            'tamil_missing' => $scope->filter(fn ($i) => $i->item_ta === $i->item_en)->count(),
            'inactive'      => $scope->where('is_active', false)->count(),
            'used'          => $scope->filter(fn ($i) => $i->used_requests || $i->used_plans)->count(),
        ];
        $stats['no_recipe']  = $stats['total'] - $stats['with_recipe'];
        $stats['unverified'] = $stats['with_recipe'] - $stats['verified'];

        $catCounts = $all
            ->when($search !== '', fn ($c) => $c->filter(fn ($i) => mb_stripos($i->item_en, $search) !== false || mb_stripos((string) $i->item_ta, $search) !== false))
            ->countBy('category_key');

        $list = $scope
            ->when($recipeF === 'none', fn ($c) => $c->whereNull('recipe'))
            ->when($recipeF === 'unverified', fn ($c) => $c->filter(fn ($i) => $i->recipe && ! $i->recipe->isVerified()))
            ->when($recipeF === 'verified', fn ($c) => $c->filter(fn ($i) => $i->recipe?->isVerified()))
            ->when($tamilF, fn ($c) => $c->filter(fn ($i) => $i->item_ta === $i->item_en))
            ->when($stateF, fn ($c) => $c->where('is_active', false))
            ->values();

        $perPage = 50;
        $page = (int) ($f['page'] ?? 1);
        $items = new LengthAwarePaginator($list->forPage($page, $perPage)->values(), $list->count(), $perPage, $page, [
            'path' => $request->url(), 'query' => $request->except('page'),
        ]);
        $quick = $recipeF ? 'recipe_'.$recipeF : ($tamilF ? 'tamil' : ($stateF ? 'inactive' : 'all'));
        $filters = ['search' => $search, 'category' => $cat];

        return view('menu.items.index', compact('items', 'categories', 'stats', 'catCounts', 'quick', 'filters'));
    }

    // ── Create ─────────────────────────────────────────────────────────────

    public function create(): View
    {
        $categories = config('menu_categories.items', []);

        return view('menu.items.create', compact('categories'));
    }

    // ── Store ──────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_key' => ['required', 'string', 'in:'.implode(',', array_keys(config('menu_categories.items')))],
            'item_en' => ['required', 'string', 'max:200'],
            'item_ta' => ['required', 'string', 'max:200'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $cat = config('menu_categories.items')[$data['category_key']];

        $item = MenuItem::create([
            'category_key' => $data['category_key'],
            'category_en' => $cat['en'],
            'category_ta' => $cat['ta'],
            'item_en' => trim($data['item_en']),
            'item_ta' => trim($data['item_ta']),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => true,
        ]);

        $this->audit->log('created', 'menu_item', $item->id, $item->item_en);

        return redirect()->route('menu.items.index')
            ->with('success', '"'.$item->item_en.'" added.');
    }

    // ── Edit ───────────────────────────────────────────────────────────────

    public function edit(MenuItem $item): View
    {
        $categories = config('menu_categories.items', []);

        return view('menu.items.edit', compact('item', 'categories'));
    }

    // ── Update ─────────────────────────────────────────────────────────────

    public function update(Request $request, MenuItem $item): RedirectResponse
    {
        $data = $request->validate([
            'category_key' => ['required', 'string', 'in:'.implode(',', array_keys(config('menu_categories.items')))],
            'item_en' => ['required', 'string', 'max:200'],
            'item_ta' => ['required', 'string', 'max:200'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $cat = config('menu_categories.items')[$data['category_key']];

        $item->update([
            'category_key' => $data['category_key'],
            'category_en' => $cat['en'],
            'category_ta' => $cat['ta'],
            'item_en' => trim($data['item_en']),
            'item_ta' => trim($data['item_ta']),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        $this->audit->log('updated', 'menu_item', $item->id, $item->item_en);

        return redirect()->route('menu.items.index')
            ->with('success', '"'.$item->item_en.'" updated.');
    }

    // ── Toggle active ──────────────────────────────────────────────────────

    public function toggle(MenuItem $item): RedirectResponse
    {
        $item->update(['is_active' => ! $item->is_active]);
        $state = $item->is_active ? 'activated' : 'deactivated';
        $this->audit->log($state, 'menu_item', $item->id, $item->item_en);

        return back()->with('success', '"'.$item->item_en.'" '.$state.'.');
    }

    // ── Destroy ────────────────────────────────────────────────────────────

    public function destroy(MenuItem $item): RedirectResponse
    {
        $name = $item->item_en;
        $id = $item->id;
        $item->delete();
        $this->audit->log('deleted', 'menu_item', $id, $name);

        return redirect()->route('menu.items.index')
            ->with('success', '"'.$name.'" deleted.');
    }

    // ── AJAX search (JSON) ─────────────────────────────────────────────────

    public function search(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $items = MenuItem::active()
            ->where(function ($query) use ($q) {
                $query->where('item_en', 'like', '%'.$q.'%')
                    ->orWhere('item_ta', 'like', '%'.$q.'%')
                    ->orWhere('category_en', 'like', '%'.$q.'%');
            })
            ->ordered()
            ->limit(30)
            ->get(['id', 'category_key', 'category_en', 'category_ta', 'item_en', 'item_ta']);

        return response()->json($items);
    }

    public function translate(Request $request): JsonResponse
    {
        $english = substr(trim($request->input('q', '')), 0, 200);

        if ($english === '') {
            return response()->json(['tamil' => null]);
        }

        $tamil = $this->translator->translate($english);

        return response()->json(['tamil' => $tamil]);
    }
}
