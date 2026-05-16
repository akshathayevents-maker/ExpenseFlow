<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\InventoryItemRequest;
use App\Http\Requests\Inventory\InventoryTransactionRequest;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
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
            ->when($filters['search'] ?? null, fn ($q, $v) =>
                $q->where('name', 'ilike', "%{$v}%")->orWhere('sku', 'ilike', "%{$v}%")
            )
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

        $categories    = InventoryCategory::active()->orderBy('name')->get();
        $lowStockCount = InventoryItem::active()->lowStock()->count();
        $outOfStock    = InventoryItem::active()->outOfStock()->count();

        return view('admin.inventory.items.index', compact('items', 'filters', 'categories', 'lowStockCount', 'outOfStock'));
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
            if (in_array($data['type'], ['purchase'])) {
                $this->inventoryService->addStock(
                    $item, $data['quantity'], $data['notes'], auth()->user(),
                    $data['unit_cost'] ?? null, $data['type']
                );
            } elseif ($data['type'] === 'adjustment') {
                $this->inventoryService->adjustStock($item, $data['quantity'], $data['notes'], auth()->user());
            } else {
                $this->inventoryService->deductStock(
                    $item, $data['quantity'], $data['notes'], auth()->user(), $data['type']
                );
            }

            $this->audit->log($data['type'], 'inventory_item', $item->id, $item->name,
                [], ['quantity' => $data['quantity'], 'type' => $data['type']]
            );

            return back()->with('success', ucfirst($data['type']) . " of {$data['quantity']} {$item->unit} recorded.");
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
