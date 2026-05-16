<x-admin-layout title="Category Report">
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
            <li class="breadcrumb-item active">Category Report</li>
        </ol></nav>
        <h4 class="mb-0 fw-bold">Category-wise Expenses</h4>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label small mb-1">From</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
            </div>
            <div class="col-auto">
                <label class="form-label small mb-1">To</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary">Apply</button>
                <a href="{{ route('admin.reports.category') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                        <th>#</th>
                        <th>Category</th>
                        <th class="text-center">Requests</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $i => $cat)
                    <tr>
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $cat->name }}</td>
                        <td class="text-center">{{ $cat->total_count }}</td>
                        <td class="text-end fw-semibold">₹{{ number_format($cat->total_amount, 2) }}</td>
                        <td class="text-end text-muted small">
                            @if($grandTotal > 0)
                                {{ number_format(($cat->total_amount / $grandTotal) * 100, 1) }}%
                                <div class="progress mt-1" style="height:4px">
                                    <div class="progress-bar"
                                         style="width:{{ ($cat->total_amount / $grandTotal) * 100 }}%"></div>
                                </div>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No data for selected period.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($data->isNotEmpty())
                <tfoot>
                    <tr class="table-light fw-semibold">
                        <td colspan="2">Grand Total</td>
                        <td class="text-center">{{ $data->sum('total_count') }}</td>
                        <td class="text-end">₹{{ number_format($grandTotal, 2) }}</td>
                        <td class="text-end">100%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
</x-admin-layout>
