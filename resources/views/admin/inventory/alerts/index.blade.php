<x-admin-layout title="Stock Alerts">
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Stock Alerts</h4>
        <p class="text-muted mb-0 small">{{ $unresolvedCount }} unresolved alert(s)</p>
    </div>
    @if($unresolvedCount > 0)
    <form method="POST" action="{{ route('admin.inventory.alerts.resolve-all') }}">
        @csrf @method('PATCH')
        <button class="btn btn-sm btn-outline-success">
            <i class="bi bi-check2-all me-1"></i> Resolve All
        </button>
    </form>
    @endif
</div>

{{-- Filter tabs --}}
<div class="d-flex gap-2 mb-3">
    <a href="{{ route('admin.inventory.alerts.index') }}"
       class="btn btn-sm {{ request('resolved') !== '1' ? 'btn-danger' : 'btn-outline-secondary' }}">
        Unresolved
        @if($unresolvedCount > 0)
            <span class="badge bg-white text-danger ms-1">{{ $unresolvedCount }}</span>
        @endif
    </a>
    <a href="{{ route('admin.inventory.alerts.index', ['resolved' => 1]) }}"
       class="btn btn-sm {{ request('resolved') === '1' ? 'btn-secondary' : 'btn-outline-secondary' }}">
        Resolved
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Alert Type</th>
                        <th class="text-end">Stock at Alert</th>
                        <th>Triggered</th>
                        <th>Resolved By</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alerts as $alert)
                    <tr>
                        <td>
                            <a href="{{ route('admin.inventory.items.show', $alert->item) }}"
                               class="fw-semibold text-decoration-none">{{ $alert->item->name }}</a>
                            <div class="text-muted small">
                                Now: {{ $alert->item->current_stock }} {{ $alert->item->unit }}
                                (min: {{ $alert->item->minimum_stock }})
                            </div>
                        </td>
                        <td class="text-muted small">{{ $alert->item->category->name }}</td>
                        <td>
                            @if($alert->alert_type === 'out_of_stock')
                                <span class="badge bg-danger">Out of Stock</span>
                            @else
                                <span class="badge bg-warning text-dark">Low Stock</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold text-danger">
                            {{ $alert->stock_at_alert }} {{ $alert->item->unit }}
                        </td>
                        <td class="small text-muted">{{ $alert->created_at->format('d M Y, h:i A') }}</td>
                        <td class="small text-muted">
                            @if($alert->is_resolved)
                                {{ $alert->resolver?->name ?? '—' }}<br>
                                <span class="text-success small">{{ $alert->resolved_at->format('d M') }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if(! $alert->is_resolved)
                            <form method="POST" action="{{ route('admin.inventory.alerts.resolve', $alert) }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-check-lg"></i> Resolve
                                </button>
                            </form>
                            @else
                                <span class="badge bg-success-subtle text-success">Resolved</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-check-circle fs-2 d-block mb-2"></i>
                            {{ request('resolved') === '1' ? 'No resolved alerts.' : 'No active alerts. Stock levels are healthy!' }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($alerts->hasPages())
    <div class="card-footer bg-transparent border-top">{{ $alerts->links() }}</div>
    @endif
</div>
</x-admin-layout>
