<x-admin-layout title="Purchase Plans">
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Purchase Plans</h4>
        <p class="text-muted mb-0 small">Planned procurement from low stock suggestions</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.purchase-plans.suggestions') }}" class="btn btn-outline-warning btn-sm">
            <i class="bi bi-lightbulb me-1"></i> View Suggestions
        </a>
        <a href="{{ route('admin.purchase-plans.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> New Plan
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Planned Date</th>
                        <th class="text-center">Items</th>
                        <th class="text-end">Est. Total</th>
                        <th class="text-center">Status</th>
                        <th>Created By</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                    @php $colors = \App\Models\PurchasePlan::statusColors(); $color = $colors[$plan->status] ?? 'secondary'; @endphp
                    <tr>
                        <td class="fw-semibold">{{ $plan->title }}</td>
                        <td class="text-muted small">{{ $plan->planned_date->format('d M Y') }}</td>
                        <td class="text-center">{{ $plan->items->count() }}</td>
                        <td class="text-end fw-semibold">
                            @if($plan->estimatedTotal() > 0)
                                ₹{{ number_format($plan->estimatedTotal(), 2) }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle"
                                  style="font-size:.7rem;text-transform:uppercase;letter-spacing:.5px">
                                {{ $plan->status }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $plan->creator->name }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.purchase-plans.show', $plan) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-cart3 fs-2 d-block mb-2"></i>No purchase plans yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($plans->hasPages())
    <div class="card-footer bg-transparent border-top">{{ $plans->links() }}</div>
    @endif
</div>
</x-admin-layout>
