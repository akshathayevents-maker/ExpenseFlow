<x-admin-layout title="Vendor Report">
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
            <li class="breadcrumb-item active">Vendor Report</li>
        </ol></nav>
        <h4 class="mb-0 fw-bold">Vendor-wise Expenses</h4>
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
                <a href="{{ route('admin.reports.vendor') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                        <th>Vendor</th>
                        <th class="text-center">Requests</th>
                        <th class="text-end">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $i => $vendor)
                    <tr>
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $vendor->name }}</div>
                            @if($vendor->phone)
                                <div class="text-muted small">{{ $vendor->phone }}</div>
                            @endif
                        </td>
                        <td class="text-center">{{ $vendor->total_count }}</td>
                        <td class="text-end fw-semibold">₹{{ number_format($vendor->total_amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">No data for selected period.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($data->isNotEmpty())
                <tfoot>
                    <tr class="table-light fw-semibold">
                        <td colspan="2">Total</td>
                        <td class="text-center">{{ $data->sum('total_count') }}</td>
                        <td class="text-end">₹{{ number_format($data->sum('total_amount'), 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
</x-admin-layout>
