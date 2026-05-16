<x-admin-layout title="Inventory Items">
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Inventory</h4>
        <p class="text-muted mb-0 small">Stock levels and item management</p>
    </div>
    <div class="d-flex gap-2">
        @if($lowStockCount + $outOfStock > 0)
            <a href="{{ route('admin.inventory.alerts.index') }}" class="btn btn-warning btn-sm">
                <i class="bi bi-exclamation-triangle me-1"></i>
                {{ $lowStockCount + $outOfStock }} Alert(s)
            </a>
        @endif
        <a href="{{ route('admin.inventory.bills.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-clock-history me-1"></i> Bill History
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="bi bi-cloud-upload me-1"></i> Upload Bill
        </button>
        <a href="{{ route('admin.inventory.items.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Item
        </a>
    </div>
</div>

@include('admin.inventory.bills._upload-modal')

{{-- Quick stats --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-4">{{ $items->total() }}</div>
            <div class="text-muted small">Total Items</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <a href="?stock_status=low{{ request()->except('stock_status') ? '&' . http_build_query(request()->except('stock_status')) : '' }}" class="text-decoration-none">
            <div class="card border-{{ $lowStockCount > 0 ? 'warning' : '0' }} shadow-sm text-center py-3 {{ $lowStockCount > 0 ? 'border' : '' }}">
                <div class="fw-bold fs-4 {{ $lowStockCount > 0 ? 'text-warning' : '' }}">{{ $lowStockCount }}</div>
                <div class="text-muted small">Low Stock</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="?stock_status=out{{ request()->except('stock_status') ? '&' . http_build_query(request()->except('stock_status')) : '' }}" class="text-decoration-none">
            <div class="card border-{{ $outOfStock > 0 ? 'danger' : '0' }} shadow-sm text-center py-3 {{ $outOfStock > 0 ? 'border' : '' }}">
                <div class="fw-bold fs-4 {{ $outOfStock > 0 ? 'text-danger' : '' }}">{{ $outOfStock }}</div>
                <div class="text-muted small">Out of Stock</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.purchase-plans.suggestions') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fw-bold fs-4 text-primary"><i class="bi bi-cart3"></i></div>
                <div class="text-muted small">Purchase Plan</div>
            </div>
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Search name / SKU…" value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-auto">
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="stock_status" class="form-select form-select-sm">
                    <option value="">All Stock</option>
                    <option value="low"      {{ ($filters['stock_status'] ?? '') === 'low'      ? 'selected' : '' }}>Low Stock</option>
                    <option value="out"      {{ ($filters['stock_status'] ?? '') === 'out'      ? 'selected' : '' }}>Out of Stock</option>
                    <option value="critical" {{ ($filters['stock_status'] ?? '') === 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="active"   {{ ($filters['status'] ?? '') === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary">Filter</button>
                <a href="{{ route('admin.inventory.items.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>SKU</th>
                        <th class="text-end">Current Stock</th>
                        <th class="text-end">Min Stock</th>
                        <th class="text-end">Avg Cost</th>
                        <th class="text-center">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr class="{{ $item->isOutOfStock() ? 'table-danger' : ($item->isLowStock() ? 'table-warning' : '') }}">
                        <td>
                            <a href="{{ route('admin.inventory.items.show', $item) }}" class="fw-semibold text-decoration-none">
                                {{ $item->name }}
                            </a>
                            @if($item->isOutOfStock())
                                <span class="badge bg-danger ms-1" style="font-size:.6rem">OUT</span>
                            @elseif($item->isLowStock())
                                <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem">LOW</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $item->category->name }}</td>
                        <td class="text-muted small font-monospace">{{ $item->sku ?? '—' }}</td>
                        <td class="text-end fw-semibold {{ $item->isOutOfStock() ? 'text-danger' : ($item->isLowStock() ? 'text-warning' : '') }}">
                            {{ number_format($item->current_stock, 2) }} {{ $item->unit }}
                        </td>
                        <td class="text-end text-muted small">{{ number_format($item->minimum_stock, 2) }} {{ $item->unit }}</td>
                        <td class="text-end text-muted small">
                            {{ $item->average_cost ? '₹' . number_format($item->average_cost, 2) : '—' }}
                        </td>
                        <td class="text-center">
                            @if($item->status === 'active')
                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.65rem">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size:.65rem">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.inventory.items.show', $item) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-boxes fs-2 d-block mb-2"></i>No items found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($items->hasPages())
    <div class="card-footer bg-transparent border-top d-flex align-items-center justify-content-between">
        <div class="text-muted small">{{ $items->total() }} items</div>
        {{ $items->links() }}
    </div>
    @endif
</div>
</x-admin-layout>
