<x-admin-layout title="Monthly Report">
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
            <li class="breadcrumb-item active">Monthly Report</li>
        </ol></nav>
        <h4 class="mb-0 fw-bold">Monthly Expense Aggregates</h4>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label small mb-1">Year</label>
                <select name="year" class="form-select form-select-sm">
                    @foreach($years as $y)
                        <option value="{{ (int)$y }}" {{ (int)$year === (int)$y ? 'selected' : '' }}>{{ (int)$y }}</option>
                    @endforeach
                    @if($years->isEmpty())
                        <option value="{{ now()->year }}" selected>{{ now()->year }}</option>
                    @endif
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary">Apply</button>
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
                        <th>Month</th>
                        <th class="text-center">Requests</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Avg per Request</th>
                        <th style="width:200px">Distribution</th>
                    </tr>
                </thead>
                <tbody>
                    @php $maxTotal = $data->max('total') ?: 1; @endphp
                    @forelse($data as $row)
                    <tr>
                        <td class="fw-semibold">{{ trim($row->month_name) }} {{ $year }}</td>
                        <td class="text-center">{{ $row->count }}</td>
                        <td class="text-end fw-semibold">₹{{ number_format($row->total, 2) }}</td>
                        <td class="text-end text-muted">
                            ₹{{ $row->count > 0 ? number_format($row->total / $row->count, 2) : '0.00' }}
                        </td>
                        <td>
                            <div class="progress" style="height:8px">
                                <div class="progress-bar bg-primary"
                                     style="width:{{ ($row->total / $maxTotal) * 100 }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No data for {{ $year }}.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($data->isNotEmpty())
                <tfoot>
                    <tr class="table-light fw-semibold">
                        <td>Year Total</td>
                        <td class="text-center">{{ $data->sum('count') }}</td>
                        <td class="text-end">₹{{ number_format($data->sum('total'), 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
</x-admin-layout>
