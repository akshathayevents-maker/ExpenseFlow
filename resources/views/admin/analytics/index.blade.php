<x-admin-layout title="Analytics">
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Analytics & Insights</h4>
        <p class="text-muted mb-0 small">Spending patterns, trends, and breakdowns</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.analytics.inventory') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-boxes me-1"></i> Inventory Analytics
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
                <a href="{{ route('admin.analytics.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Grand total --}}
<div class="alert alert-primary border-0 shadow-sm mb-4 d-flex align-items-center gap-3">
    <i class="bi bi-cash-stack fs-3"></i>
    <div>
        <div class="small text-muted">Total Settled Expenses ({{ \Carbon\Carbon::parse($from)->format('d M') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }})</div>
        <div class="fs-4 fw-bold">₹{{ number_format($grandTotal, 2) }}</div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Top categories --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent fw-semibold">
                <i class="bi bi-tag me-1 text-success"></i> Top Categories
            </div>
            <div class="card-body">
                @forelse($topCategories as $i => $cat)
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <span class="badge bg-success-subtle text-success me-1" style="font-size:.6rem">{{ $i + 1 }}</span>
                        {{ $cat->name }}
                    </div>
                    <div class="fw-semibold text-end">₹{{ number_format($cat->total, 2) }}</div>
                </div>
                @if($grandTotal > 0)
                <div class="progress mb-3" style="height:4px">
                    <div class="progress-bar bg-success" style="width:{{ ($cat->total / $grandTotal) * 100 }}%"></div>
                </div>
                @endif
                @empty
                <div class="text-muted small text-center py-3">No data for period.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Top employees --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent fw-semibold">
                <i class="bi bi-person-lines-fill me-1 text-primary"></i> Top Spenders
            </div>
            <div class="card-body">
                @forelse($topEmployees as $i => $emp)
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <span class="badge bg-primary-subtle text-primary me-1" style="font-size:.6rem">{{ $i + 1 }}</span>
                        {{ $emp->name }}
                    </div>
                    <div class="fw-semibold text-end">₹{{ number_format($emp->total, 2) }}</div>
                </div>
                @if($grandTotal > 0)
                <div class="progress mb-3" style="height:4px">
                    <div class="progress-bar" style="width:{{ ($emp->total / $grandTotal) * 100 }}%"></div>
                </div>
                @endif
                @empty
                <div class="text-muted small text-center py-3">No data for period.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Top vendors --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent fw-semibold">
                <i class="bi bi-shop me-1 text-warning"></i> Top Vendors
            </div>
            <div class="card-body">
                @forelse($topVendors as $i => $vendor)
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <span class="badge bg-warning-subtle text-warning me-1" style="font-size:.6rem">{{ $i + 1 }}</span>
                        {{ $vendor->name }}
                    </div>
                    <div class="fw-semibold text-end">₹{{ number_format($vendor->total, 2) }}</div>
                </div>
                @if($grandTotal > 0)
                <div class="progress mb-3" style="height:4px">
                    <div class="progress-bar bg-warning" style="width:{{ ($vendor->total / $grandTotal) * 100 }}%"></div>
                </div>
                @endif
                @empty
                <div class="text-muted small text-center py-3">No data for period.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Monthly trend --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent fw-semibold">
        <i class="bi bi-graph-up-arrow me-1 text-primary"></i> Monthly Expense Trend
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th class="text-center">Requests</th>
                        <th class="text-end">Total</th>
                        <th style="width:200px">Relative</th>
                    </tr>
                </thead>
                <tbody>
                    @php $maxTotal = $monthlyTrend->max('total') ?: 1; @endphp
                    @forelse($monthlyTrend as $row)
                    <tr>
                        <td class="fw-semibold">{{ $row->month }}</td>
                        <td class="text-center">{{ $row->count }}</td>
                        <td class="text-end fw-semibold">₹{{ number_format($row->total, 2) }}</td>
                        <td>
                            <div class="progress" style="height:8px">
                                <div class="progress-bar bg-primary" style="width:{{ ($row->total / $maxTotal) * 100 }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-4 text-muted">No data for period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-admin-layout>
