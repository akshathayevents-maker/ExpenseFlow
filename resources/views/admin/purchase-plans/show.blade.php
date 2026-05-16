<x-admin-layout title="{{ $purchasePlan->title }}">
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.purchase-plans.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h4 class="mb-0 fw-bold">{{ $purchasePlan->title }}</h4>
            <p class="text-muted mb-0 small">Planned: {{ $purchasePlan->planned_date->format('d M Y') }}</p>
        </div>
    </div>
    @php $colors = \App\Models\PurchasePlan::statusColors(); $color = $colors[$purchasePlan->status] ?? 'secondary'; @endphp
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle py-2 px-3"
              style="font-size:.8rem;text-transform:uppercase">{{ $purchasePlan->status }}</span>

        @if($purchasePlan->isDraft())
        <form method="POST" action="{{ route('admin.purchase-plans.approve', $purchasePlan) }}">
            @csrf @method('PATCH')
            <button class="btn btn-sm btn-success">
                <i class="bi bi-check-circle me-1"></i> Approve Plan
            </button>
        </form>
        @endif

        @if($purchasePlan->isApproved())
        <form method="POST" action="{{ route('admin.purchase-plans.status', $purchasePlan) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="ordered">
            <button class="btn btn-sm btn-primary">Mark Ordered</button>
        </form>
        @endif

        @if($purchasePlan->isOrdered())
        <form method="POST" action="{{ route('admin.purchase-plans.status', $purchasePlan) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="completed">
            <button class="btn btn-sm btn-info text-white">Mark Completed</button>
        </form>
        @endif
    </div>
</div>

@if($purchasePlan->notes)
<div class="alert alert-light border mb-3">
    <i class="bi bi-sticky me-1"></i> {{ $purchasePlan->notes }}
</div>
@endif

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-semibold">
                <i class="bi bi-list-ul me-1 text-primary"></i> Items
                <span class="badge bg-secondary ms-1">{{ $purchasePlan->items->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Category</th>
                                <th class="text-center">Priority</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Unit Cost</th>
                                <th class="text-end">Total</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchasePlan->items as $item)
                            @php $pc = \App\Models\PurchasePlanItem::priorityColors(); $pc = $pc[$item->priority] ?? 'secondary'; @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('admin.inventory.items.show', $item->inventoryItem) }}"
                                       class="fw-semibold text-decoration-none small">
                                        {{ $item->inventoryItem->name }}
                                    </a>
                                    <div class="text-muted" style="font-size:.72rem">
                                        Stock: {{ $item->inventoryItem->current_stock }} {{ $item->inventoryItem->unit }}
                                    </div>
                                </td>
                                <td class="text-muted small">{{ $item->inventoryItem->category->name }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $pc }}-subtle text-{{ $pc }} border border-{{ $pc }}-subtle"
                                          style="font-size:.65rem;text-transform:uppercase">
                                        {{ $item->priority }}
                                    </span>
                                </td>
                                <td class="text-end fw-semibold">
                                    {{ $item->suggested_quantity }} {{ $item->inventoryItem->unit }}
                                </td>
                                <td class="text-end text-muted small">
                                    {{ $item->estimated_unit_cost ? '₹' . number_format($item->estimated_unit_cost, 2) : '—' }}
                                </td>
                                <td class="text-end fw-semibold">
                                    {{ $item->estimatedTotal() > 0 ? '₹' . number_format($item->estimatedTotal(), 2) : '—' }}
                                </td>
                                <td class="text-muted small">{{ $item->notes ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-semibold">
                                <td colspan="5">Estimated Total</td>
                                <td class="text-end">
                                    @if($purchasePlan->estimatedTotal() > 0)
                                        ₹{{ number_format($purchasePlan->estimatedTotal(), 2) }}
                                    @else —
                                    @endif
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-semibold">Plan Details</div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Created By</td><td class="fw-semibold">{{ $purchasePlan->creator->name }}</td></tr>
                    <tr><td class="text-muted">Planned Date</td><td>{{ $purchasePlan->planned_date->format('d M Y') }}</td></tr>
                    @if($purchasePlan->approver)
                    <tr><td class="text-muted">Approved By</td><td class="fw-semibold">{{ $purchasePlan->approver->name }}</td></tr>
                    <tr><td class="text-muted">Approved At</td><td>{{ $purchasePlan->approved_at->format('d M Y, h:i A') }}</td></tr>
                    @endif
                    <tr><td class="text-muted">Total Items</td><td>{{ $purchasePlan->items->count() }}</td></tr>
                </table>
            </div>
        </div>

        @if(!$purchasePlan->isCompleted() && !$purchasePlan->isCancelled ?? false)
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.purchase-plans.status', $purchasePlan) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="cancelled">
                    <button class="btn btn-outline-danger w-100 btn-sm"
                            onclick="return confirm('Cancel this plan?')">
                        <i class="bi bi-x-circle me-1"></i> Cancel Plan
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
</x-admin-layout>
