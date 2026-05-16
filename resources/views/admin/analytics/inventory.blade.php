<x-admin-layout title="Inventory Analytics">
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Inventory Analytics</h4>
        <p class="text-muted mb-0 small">Stock usage, wastage, and valuation</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.analytics.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-bar-chart me-1"></i> Expense Analytics
        </a>
    </div>
</div>

{{-- Date filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto"><label class="form-label small mb-1">From</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}"></div>
            <div class="col-auto"><label class="form-label small mb-1">To</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}"></div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary">Apply</button>
                <a href="{{ route('admin.analytics.inventory') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Summary cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Total Inventory Value</div>
            <div class="fs-4 fw-bold text-success">₹{{ number_format($totalInventoryValue, 2) }}</div>
            <div class="text-muted" style="font-size:.75rem">Current stock × avg cost</div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Wastage Cost (Period)</div>
            <div class="fs-4 fw-bold text-danger">₹{{ number_format($totalWastageCost, 2) }}</div>
            <div class="text-muted" style="font-size:.75rem">{{ \Carbon\Carbon::parse($from)->format('d M') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Top used items --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent fw-semibold">
                <i class="bi bi-arrow-down-circle me-1 text-primary"></i> Top Used Items
            </div>
            <div class="card-body">
                @forelse($topUsed as $i => $item)
                @php $maxUsed = $topUsed->max('used_qty') ?: 1; @endphp
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <div>
                        <span class="badge bg-primary-subtle text-primary me-1" style="font-size:.6rem">{{ $i + 1 }}</span>
                        <a href="{{ route('admin.inventory.items.show', $item) }}" class="text-decoration-none fw-semibold small">
                            {{ $item->name }}
                        </a>
                        <span class="text-muted" style="font-size:.72rem"> · {{ $item->category->name }}</span>
                    </div>
                    <div class="fw-semibold text-end small">
                        {{ number_format($item->used_qty, 3) + 0 }} {{ $item->unit }}
                    </div>
                </div>
                <div class="progress mb-3" style="height:4px">
                    <div class="progress-bar bg-primary" style="width:{{ ($item->used_qty / $maxUsed) * 100 }}%"></div>
                </div>
                @empty
                <div class="text-muted small text-center py-3">No usage data for period.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Top wasted items --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent fw-semibold">
                <i class="bi bi-trash me-1 text-danger"></i> Top Wasted Items
            </div>
            <div class="card-body">
                @forelse($topWasted as $i => $item)
                @php $maxWasted = $topWasted->max('wasted_qty') ?: 1; @endphp
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <div>
                        <span class="badge bg-danger-subtle text-danger me-1" style="font-size:.6rem">{{ $i + 1 }}</span>
                        <a href="{{ route('admin.inventory.items.show', $item) }}" class="text-decoration-none fw-semibold small">
                            {{ $item->name }}
                        </a>
                        <span class="text-muted" style="font-size:.72rem"> · {{ $item->category->name }}</span>
                    </div>
                    <div class="fw-semibold text-end small text-danger">
                        {{ number_format($item->wasted_qty, 3) + 0 }} {{ $item->unit }}
                    </div>
                </div>
                <div class="progress mb-3" style="height:4px">
                    <div class="progress-bar bg-danger" style="width:{{ ($item->wasted_qty / $maxWasted) * 100 }}%"></div>
                </div>
                @empty
                <div class="text-muted small text-center py-3">No wastage recorded for period.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
</x-admin-layout>
