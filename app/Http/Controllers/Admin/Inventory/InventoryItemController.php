<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\InventoryItemRequest;
use App\Http\Requests\Inventory\InventoryTransactionRequest;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryItemAlias;
use App\Models\InventoryUnitConversion;
use App\Models\InventoryTransaction;
use App\Services\AuditLogService;
use App\Services\Inventory\ItemCatalogueService;
use App\Services\Inventory\ItemSearchService;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryItemController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService,
        private AuditLogService  $audit,
        private ItemSearchService $search,
        private ItemCatalogueService $catalogue,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'category_id', 'status', 'stock_status']);

        $items = InventoryItem::with('category')
            // shared catalogue search (English / Tamil / aliases) + SKU
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where(fn ($w) => $w
                ->whereIn('id', $this->search->matchingIds($v) ?: [0])
                ->orWhereRaw('LOWER(sku) LIKE ?', ['%' . mb_strtolower($v) . '%'])
            ))
            ->when($filters['category_id'] ?? null, fn ($q, $v) => $q->where('inventory_category_id', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['stock_status'] ?? null, function ($q, $v) {
                match ($v) {
                    'low'     => $q->lowStock(),
                    'out'     => $q->outOfStock(),
                    'critical'=> $q->critical(),
                    default   => null,
                };
            })
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $categories = InventoryCategory::active()->orderBy('name')->get();

        $stats = [
            'total_active'   => InventoryItem::active()->count(),
            'low_stock'      => InventoryItem::active()->lowStock()->count(),
            'out_of_stock'   => InventoryItem::active()->outOfStock()->count(),
            'critical'       => InventoryItem::active()->critical()->count(),
            'inventory_value' => (float) InventoryItem::active()
                ->selectRaw('COALESCE(SUM(current_stock * average_cost), 0) as val')
                ->value('val'),
            'monthly_spend' => (float) InventoryTransaction::whereIn('type', ['purchase', 'purchase_receipt'])
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->selectRaw('COALESCE(SUM(quantity * unit_cost), 0) as val')
                ->value('val'),
        ];

        return view('admin.inventory.items.index', compact('items', 'filters', 'categories', 'stats'));
    }

    public function create(): View
    {
        $categories = InventoryCategory::active()->orderBy('name')->get();
        return view('admin.inventory.items.create', compact('categories'));
    }

    public function store(InventoryItemRequest $request): RedirectResponse
    {
        if ($warn = $this->duplicateWarning($request)) {
            return $warn;
        }
        $item = InventoryItem::create(collect($request->validated())->except('confirm_duplicate')->all());
        $this->audit->log('created', 'inventory_item', $item->id, $item->name);
        return redirect()->route('admin.inventory.items.show', $item)->with('success', 'Item created.');
    }

    public function show(InventoryItem $item, Request $request): View
    {
        $item->load(['category', 'aliases' => fn ($q) => $q->orderByRaw('vendor_id IS NOT NULL')->orderBy('alias'), 'aliases.vendor:id,name', 'unitConversions']);

        $transactions = $item->transactions()
            ->with('creator')
            ->when($request->get('type'), fn ($q, $v) => $q->where('type', $v))
            ->when($request->get('from'), fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->get('to'),   fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->paginate(20)
            ->withQueryString();

        $activeAlerts = $item->activeAlerts()->get();

        return view('admin.inventory.items.show', compact('item', 'transactions', 'activeAlerts'));
    }

    public function edit(InventoryItem $item): View
    {
        $categories = InventoryCategory::active()->orderBy('name')->get();
        return view('admin.inventory.items.edit', compact('item', 'categories'));
    }

    public function update(InventoryItemRequest $request, InventoryItem $item): RedirectResponse
    {
        if ($warn = $this->duplicateWarning($request, $item)) {
            return $warn;
        }
        $old = $item->only(['name', 'name_ta', 'minimum_stock', 'status']);
        $item->update(collect($request->validated())->except('confirm_duplicate')->all());
        $this->audit->log('updated', 'inventory_item', $item->id, $item->name, $old, $item->fresh()->only(['name', 'name_ta', 'minimum_stock', 'status']));
        return redirect()->route('admin.inventory.items.show', $item)->with('success', 'Item updated.');
    }

    public function transact(InventoryTransactionRequest $request, InventoryItem $item): RedirectResponse
    {
        $data = $request->validated();

        try {
            $qty = (float) $data['quantity'];
            if ($data['type'] === 'adjustment') {
                $this->inventoryService->adjustStock($item, $qty, $data['notes'], auth()->user());
            } else {
                if ($qty <= 0) {
                    return back()->with('error', 'Quantity must be greater than zero.');
                }
                $signed = ($data['type'] === 'correction' && ($data['direction'] ?? 'out') === 'in') ? $qty : -$qty;
                $this->inventoryService->post($item, $signed, $data['type'], auth()->user(), [
                    'notes' => $data['notes'], 'reference_label' => 'Manual entry',
                ]);
            }

            $this->audit->log($data['type'], 'inventory_item', $item->id, $item->name,
                [], ['quantity' => $data['quantity'], 'type' => $data['type']]
            );

            return back()->with('success', InventoryTransaction::typeLabels()[$data['type']] . ' recorded.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function toggleStatus(InventoryItem $item): RedirectResponse
    {
        $item->update(['status' => $item->status === 'active' ? 'inactive' : 'active']);
        return back()->with('success', 'Status updated.');
    }

    // ─── Catalogue: names, aliases, purchase units, duplicates ──────────────

    /** Similar existing items -> back to the form with a warning unless the admin confirmed. */
    private function duplicateWarning(InventoryItemRequest $request, ?InventoryItem $item = null): ?RedirectResponse
    {
        if ($request->boolean('confirm_duplicate')) {
            return null;
        }
        $nameChanged = ! $item || $item->name !== $request->input('name') || $item->name_ta !== $request->input('name_ta');
        if (! $nameChanged) {
            return null;
        }
        $similar = collect($this->search->similarTo((string) $request->input('name'), $item?->id))
            ->merge($request->filled('name_ta') ? $this->search->similarTo((string) $request->input('name_ta'), $item?->id) : [])
            ->unique('id')->values()->all();

        return $similar ? back()->withInput()->with('duplicates', $similar) : null;
    }

    public function storeAlias(Request $request, InventoryItem $item): RedirectResponse
    {
        $data = $request->validate([
            'alias' => ['required', 'string', 'max:200'],
            'language' => ['required', 'in:'.implode(',', array_keys(InventoryItemAlias::LANGUAGES))],
        ]);
        $r = $this->catalogue->addAlias($item, $data['alias'], $data['language'], auth()->id());

        return match ($r['status']) {
            'created' => tap(back()->with('success', "“{$data['alias']}” added."), fn () => $this->audit->log('alias_added', 'inventory_item', $item->id, $item->name, [], ['alias' => $data['alias'], 'language' => $data['language']])),
            'exists', 'redundant' => back()->with('success', "“{$data['alias']}” already finds this item."),
            'conflict' => back()->withInput()->withErrors(['alias' => "“{$data['alias']}” already belongs to {$r['other']?->name}. Remove it there first if it is wrong."]),
            default => back()->withInput()->withErrors(['alias' => 'Enter letters (English or Tamil).']),
        };
    }

    public function destroyAlias(InventoryItem $item, InventoryItemAlias $alias): RedirectResponse
    {
        abort_unless($alias->inventory_item_id === $item->id, 404);
        $alias->delete();
        $this->audit->log('alias_removed', 'inventory_item', $item->id, $item->name, ['alias' => $alias->alias], []);

        return back()->with('success', "“{$alias->alias}” removed.");
    }

    /** Item-specific purchase unit, e.g. 1 bag = 25 kg. Never invented: entered by an admin. */
    public function storeUnit(Request $request, InventoryItem $item): RedirectResponse
    {
        $units = config('inventory.units');
        $data = $request->validate([
            'unit' => ['required', 'in:'.implode(',', array_keys($units))],
            'factor' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'notes' => ['nullable', 'string', 'max:200'],
        ]);
        $base = $units[$item->unit] ?? null;
        if ($data['unit'] === $item->unit || ($base && $units[$data['unit']]['dimension'] === $base['dimension'])) {
            return back()->withErrors(['unit' => "{$data['unit']} already converts to {$item->unit} automatically."]);
        }
        $conv = InventoryUnitConversion::updateOrCreate(
            ['inventory_item_id' => $item->id, 'unit' => $data['unit']],
            ['factor' => $data['factor'], 'notes' => $data['notes'] ?? null, 'created_by' => auth()->id()],
        );
        $this->audit->log('unit_rule_saved', 'inventory_item', $item->id, $item->name, [], ['unit' => $conv->unit, 'factor' => (float) $conv->factor]);

        return back()->with('success', "1 {$conv->unit} = ".rtrim(rtrim(number_format($conv->factor, 3, '.', ''), '0'), '.')." {$item->unit} saved.");
    }

    public function destroyUnit(InventoryItem $item, InventoryUnitConversion $conversion): RedirectResponse
    {
        abort_unless($conversion->inventory_item_id === $item->id, 404);
        $conversion->delete();   // past lines keep their stored base quantities
        $this->audit->log('unit_rule_removed', 'inventory_item', $item->id, $item->name, ['unit' => $conversion->unit, 'factor' => (float) $conversion->factor], []);

        return back()->with('success', "{$conversion->unit} removed as a purchase unit.");
    }

    public function duplicates(): View
    {
        $groups = $this->catalogue->possibleDuplicates();

        return view('admin.inventory.items.duplicates', compact('groups'));
    }
}
