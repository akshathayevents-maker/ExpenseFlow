<x-admin-layout title="Purchase Suggestions">
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Purchase Suggestions</h4>
        <p class="text-muted mb-0 small">Items below minimum stock — auto-generated list</p>
    </div>
    @if($suggestions->isNotEmpty())
    <a href="{{ route('admin.purchase-plans.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Create Purchase Plan
    </a>
    @endif
</div>

@if($suggestions->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-check-circle fs-2 d-block mb-2 text-success"></i>
            All stock levels are healthy. No purchases needed right now.
        </div>
    </div>
@else
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th class="text-end">Current</th>
                        <th class="text-end">Minimum</th>
                        <th class="text-end">Deficit</th>
                        <th class="text-end">Suggested Order</th>
                        <th class="text-center">Priority</th>
                        <th class="text-end">Est. Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suggestions as $item)
                    @php
                        $pc = \App\Models\PurchasePlanItem::priorityColors();
                        $color = $pc[$item->priority] ?? 'secondary';
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('admin.inventory.items.show', $item) }}" class="fw-semibold text-decoration-none">
                                {{ $item->name }}
                            </a>
                            @if($item->isOutOfStock())
                                <span class="badge bg-danger ms-1" style="font-size:.6rem">OUT</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $item->category->name }}</td>
                        <td class="text-end {{ $item->isOutOfStock() ? 'text-danger fw-bold' : 'text-warning fw-semibold' }}">
                            {{ $item->current_stock }} {{ $item->unit }}
                        </td>
                        <td class="text-end text-muted small">{{ $item->minimum_stock }} {{ $item->unit }}</td>
                        <td class="text-end text-danger fw-semibold">{{ $item->deficit }} {{ $item->unit }}</td>
                        <td class="text-end fw-semibold text-primary">{{ $item->suggested_quantity }} {{ $item->unit }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle"
                                  style="font-size:.7rem;text-transform:uppercase;letter-spacing:.5px">
                                {{ $item->priority }}
                            </span>
                        </td>
                        <td class="text-end text-muted small">
                            @if($item->average_cost)
                                ₹{{ number_format($item->average_cost * $item->suggested_quantity, 2) }}
                            @else —
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
</x-admin-layout>
