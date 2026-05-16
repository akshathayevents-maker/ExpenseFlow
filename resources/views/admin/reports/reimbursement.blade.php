<x-admin-layout title="Reimbursement Report">
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
            <li class="breadcrumb-item active">Reimbursement Report</li>
        </ol></nav>
        <h4 class="mb-0 fw-bold">Reimbursement Tracking</h4>
    </div>
</div>

{{-- Totals --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box bg-warning-subtle text-warning"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="text-muted small">Pending Reimbursement</div>
                    <div class="fw-bold fs-5">₹{{ number_format($totals['pending'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box bg-success-subtle text-success"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="text-muted small">Total Reimbursed</div>
                    <div class="fw-bold fs-5">₹{{ number_format($totals['reimbursed'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="reimbursement_pending" {{ $status === 'reimbursement_pending' ? 'selected' : '' }}>Pending</option>
                    <option value="reimbursed" {{ $status === 'reimbursed' ? 'selected' : '' }}>Reimbursed</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div class="col-auto">
                <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
            </div>
            <div class="col-auto">
                <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary">Filter</button>
                <a href="{{ route('admin.reports.reimbursement') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                        <th>Employee</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th class="text-end">Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $req)
                    <tr>
                        <td class="small text-muted text-nowrap">{{ $req->created_at->format('d M Y') }}</td>
                        <td class="fw-semibold small">{{ $req->requester->name }}</td>
                        <td>{{ Str::limit($req->title, 40) }}</td>
                        <td class="text-muted small">{{ $req->category->name }}</td>
                        <td><x-status-badge :status="$req->status"/></td>
                        <td class="text-end fw-semibold">₹{{ number_format($req->amount, 2) }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.expense-requests.show', $req) }}"
                               class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No reimbursement requests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($data->hasPages())
    <div class="card-footer bg-transparent border-top">
        {{ $data->links() }}
    </div>
    @endif
</div>
</x-admin-layout>
