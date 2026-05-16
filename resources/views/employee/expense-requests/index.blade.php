<x-admin-layout title="My Requests">
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">My Requests</h4>
            <p class="text-muted mb-0 small">Your expense submission history</p>
        </div>
        <a href="{{ route('employee.expense-requests.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> New Request
        </a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="d-flex flex-wrap gap-2 align-items-end">
                <div class="input-group input-group-sm" style="max-width:220px">
                    <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search…" value="{{ $filters['search'] ?? '' }}">
                </div>
                <select name="status" class="form-select form-select-sm" style="max-width:120px">
                    <option value="">All Status</option>
                    @foreach(['pending','approved','rejected','paid','completed'] as $s)
                        <option value="{{ $s }}" {{ ($filters['status'] ?? '') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <select name="priority" class="form-select form-select-sm" style="max-width:120px">
                    <option value="">All Priority</option>
                    @foreach(['low','medium','high','urgent'] as $p)
                        <option value="{{ $p }}" {{ ($filters['priority'] ?? '') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-outline-primary px-3">Filter</button>
                @if(array_filter($filters))
                    <a href="{{ route('employee.expense-requests.index') }}" class="btn btn-sm btn-outline-secondary px-3">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th class="d-none d-sm-table-cell">Category</th>
                            <th>Amount</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th class="d-none d-md-table-cell">Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr>
                                <td class="text-muted small">{{ $req->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ Str::limit($req->title, 35) }}</div>
                                    @if($req->vendor)
                                        <small class="text-muted"><i class="bi bi-shop"></i> {{ $req->vendor->name }}</small>
                                    @endif
                                </td>
                                <td class="d-none d-sm-table-cell small text-muted">{{ $req->category->name }}</td>
                                <td class="fw-semibold">₹{{ number_format($req->amount, 2) }}</td>
                                <td><x-priority-badge :priority="$req->priority" /></td>
                                <td><x-status-badge :status="$req->status" /></td>
                                <td class="d-none d-md-table-cell text-muted small">{{ $req->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('employee.expense-requests.show', $req) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-file-earmark-text fs-2 d-block mb-2"></i>
                                    No requests yet.
                                    <a href="{{ route('employee.expense-requests.create') }}" class="d-block mt-2">Submit your first request</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($requests->hasPages())
            <div class="card-footer bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
                <p class="text-muted small mb-0">Showing {{ $requests->firstItem() }}–{{ $requests->lastItem() }} of {{ $requests->total() }}</p>
                {{ $requests->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</x-admin-layout>
