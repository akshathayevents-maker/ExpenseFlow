<x-admin-layout title="Expense Requests">
    <div class="page-header">
        <h4 class="mb-0 fw-bold">Expense Requests</h4>
        <p class="text-muted mb-0 small">Review and approve requests</p>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label small fw-semibold mb-1">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Title…" value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-lg-2">
                    <label class="form-label small fw-semibold mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach(['pending','approved','rejected','paid','completed'] as $s)
                            <option value="{{ $s }}" {{ ($filters['status'] ?? '') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-sm-4 col-lg-2">
                    <label class="form-label small fw-semibold mb-1">Priority</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach(['low','medium','high','urgent'] as $p)
                            <option value="{{ $p }}" {{ ($filters['priority'] ?? '') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-sm-4 col-lg-2">
                    <label class="form-label small fw-semibold mb-1">Category</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-sm-3 col-lg-2">
                    <label class="form-label small fw-semibold mb-1">From</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ $filters['from'] ?? '' }}">
                </div>
                <div class="col-6 col-sm-3 col-lg-2">
                    <label class="form-label small fw-semibold mb-1">To</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ $filters['to'] ?? '' }}">
                </div>
                <div class="col-12 col-sm-6 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill">Filter</button>
                    <a href="{{ route('manager.expense-requests.index') }}" class="btn btn-sm btn-outline-secondary flex-fill">Reset</a>
                </div>
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
                            <th class="d-none d-md-table-cell">Employee</th>
                            <th class="d-none d-sm-table-cell">Category</th>
                            <th>Amount</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr>
                                <td class="text-muted small">{{ $req->id }}</td>
                                <td><div class="fw-semibold">{{ Str::limit($req->title, 35) }}</div></td>
                                <td class="d-none d-md-table-cell small text-muted">{{ $req->requester->name }}</td>
                                <td class="d-none d-sm-table-cell small text-muted">{{ $req->category->name }}</td>
                                <td class="fw-semibold">₹{{ number_format($req->amount, 2) }}</td>
                                <td><x-priority-badge :priority="$req->priority" /></td>
                                <td><x-status-badge :status="$req->status" /></td>
                                <td>
                                    <a href="{{ route('manager.expense-requests.show', $req) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-file-earmark-text fs-2 d-block mb-2"></i>
                                    No requests found.
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
