<x-admin-layout title="Daily Report">
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
            <li class="breadcrumb-item active">Daily Report</li>
        </ol></nav>
        <h4 class="mb-0 fw-bold">Daily Expense Aggregates</h4>
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
                <a href="{{ route('admin.reports.daily') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                        <th>Date</th>
                        <th class="text-center">Requests</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Avg per Request</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                    <tr>
                        <td class="fw-semibold">{{ \Carbon\Carbon::parse($row->date)->format('d M Y, l') }}</td>
                        <td class="text-center">{{ $row->count }}</td>
                        <td class="text-end fw-semibold">₹{{ number_format($row->total, 2) }}</td>
                        <td class="text-end text-muted">
                            ₹{{ $row->count > 0 ? number_format($row->total / $row->count, 2) : '0.00' }}
                        </td>
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
                        <td>Total ({{ $data->count() }} days)</td>
                        <td class="text-center">{{ $data->sum('count') }}</td>
                        <td class="text-end">₹{{ number_format($data->sum('total'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
</x-admin-layout>
