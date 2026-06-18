<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Services\AuditLogService;
use App\Services\MenuTranslationService;
use Illuminate\Http\JsonResponse;
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
        $query = MenuItem::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('item_en', 'like', '%' . $search . '%')
                  ->orWhere('item_ta', 'like', '%' . $search . '%');
            });
        }

        if ($cat = $request->input('category')) {
            $query->where('category_key', $cat);
        }

        $items      = $query->ordered()->get();
        $categories = config('menu_categories.items', []);

        return view('menu.items.index', compact('items', 'categories'));
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
            'category_key' => ['required', 'string', 'in:' . implode(',', array_keys(config('menu_categories.items')))],
            'item_en'      => ['required', 'string', 'max:200'],
            'item_ta'      => ['required', 'string', 'max:200'],
            'sort_order'   => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $cat = config('menu_categories.items')[$data['category_key']];

        $item = MenuItem::create([
            'category_key' => $data['category_key'],
            'category_en'  => $cat['en'],
            'category_ta'  => $cat['ta'],
            'item_en'      => trim($data['item_en']),
            'item_ta'      => trim($data['item_ta']),
            'sort_order'   => (int) ($data['sort_order'] ?? 0),
            'is_active'    => true,
        ]);

        $this->audit->log('created', 'menu_item', $item->id, $item->item_en);

        return redirect()->route('menu.items.index')
            ->with('success', '"' . $item->item_en . '" added.');
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
            'category_key' => ['required', 'string', 'in:' . implode(',', array_keys(config('menu_categories.items')))],
            'item_en'      => ['required', 'string', 'max:200'],
            'item_ta'      => ['required', 'string', 'max:200'],
            'sort_order'   => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $cat = config('menu_categories.items')[$data['category_key']];

        $item->update([
            'category_key' => $data['category_key'],
            'category_en'  => $cat['en'],
            'category_ta'  => $cat['ta'],
            'item_en'      => trim($data['item_en']),
            'item_ta'      => trim($data['item_ta']),
            'sort_order'   => (int) ($data['sort_order'] ?? 0),
        ]);

        $this->audit->log('updated', 'menu_item', $item->id, $item->item_en);

        return redirect()->route('menu.items.index')
            ->with('success', '"' . $item->item_en . '" updated.');
    }

    // ── Toggle active ──────────────────────────────────────────────────────

    public function toggle(MenuItem $item): RedirectResponse
    {
        $item->update(['is_active' => ! $item->is_active]);
        $state = $item->is_active ? 'activated' : 'deactivated';
        $this->audit->log($state, 'menu_item', $item->id, $item->item_en);
        return back()->with('success', '"' . $item->item_en . '" ' . $state . '.');
    }

    // ── Destroy ────────────────────────────────────────────────────────────

    public function destroy(MenuItem $item): RedirectResponse
    {
        $name = $item->item_en;
        $id   = $item->id;
        $item->delete();
        $this->audit->log('deleted', 'menu_item', $id, $name);
        return redirect()->route('menu.items.index')
            ->with('success', '"' . $name . '" deleted.');
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
                $query->where('item_en', 'like', '%' . $q . '%')
                      ->orWhere('item_ta', 'like', '%' . $q . '%')
                      ->orWhere('category_en', 'like', '%' . $q . '%');
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
