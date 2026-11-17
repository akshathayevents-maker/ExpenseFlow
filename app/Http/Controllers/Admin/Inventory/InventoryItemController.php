<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\InventoryItemRequest;
use App\Http\Requests\Inventory\InventoryTransactionRequest;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Services\AuditLogService;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryItemController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService,
        private AuditLogService  $audit
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'category_id', 'status', 'stock_status']);

        $items = InventoryItem::with('category')
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where(fn ($w) => $w
                ->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($v) . '%'])
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
        $item = InventoryItem::create($request->validated());
        $this->audit->log('created', 'inventory_item', $item->id, $item->name);
        return redirect()->route('admin.inventory.items.show', $item)->with('success', 'Item created.');
    }

    public function show(InventoryItem $item, Request $request): View
    {
        $item->load('category');

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
        $old = $item->only(['name', 'minimum_stock', 'status']);
        $item->update($request->validated());
        $this->audit->log('updated', 'inventory_item', $item->id, $item->name, $old, $item->fresh()->only(['name', 'minimum_stock', 'status']));
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
}
