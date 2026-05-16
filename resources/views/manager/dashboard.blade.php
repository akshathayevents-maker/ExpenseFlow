<x-admin-layout title="Dashboard">
    <div class="page-header d-flex align-items-center justify-content-between">
        <div>
            <h4 class="mb-0 fw-bold">Manager Dashboard</h4>
            <p class="text-muted mb-0 small">Welcome, {{ auth()->user()->name }}</p>
        </div>
        <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i>{{ now()->format('d M Y') }}</span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box bg-warning bg-opacity-10 text-warning"><i class="bi bi-hourglass-split"></i></div>
                    <div>
                        <p class="text-muted mb-0 small">Pending</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['pending'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle-fill"></i></div>
                    <div>
                        <p class="text-muted mb-0 small">Approved</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['approved'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box bg-danger bg-opacity-10 text-danger"><i class="bi bi-x-circle-fill"></i></div>
                    <div>
                        <p class="text-muted mb-0 small">Rejected</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['rejected'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box bg-info bg-opacity-10 text-info"><i class="bi bi-currency-rupee"></i></div>
                    <div>
                        <p class="text-muted mb-0 small">Approved This Month</p>
                        <h4 class="mb-0 fw-bold">₹{{ number_format($stats['monthly_expense'], 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                    <span class="fw-semibold"><i class="bi bi-hourglass-split me-1 text-warning"></i> Pending Requests</span>
                    <a href="{{ route('manager.expense-requests.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-warning">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($pendingRequests->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-check2-all fs-2 d-block mb-2 text-success"></i>
                            All caught up! No pending requests.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th class="d-none d-md-table-cell">Employee</th>
                                        <th>Amount</th>
                                        <th>Priority</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingRequests as $req)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold small">{{ Str::limit($req->title, 30) }}</div>
                                                <small class="text-muted">{{ $req->category->name }}</small>
                                            </td>
                                            <td class="d-none d-md-table-cell small text-muted">{{ $req->requester->name }}</td>
                                            <td class="fw-semibold small">₹{{ number_format($req->amount, 2) }}</td>
                                            <td><x-priority-badge :priority="$req->priority" /></td>
                                            <td>
                                                <a href="{{ route('manager.expense-requests.show', $req) }}"
                                                   class="btn btn-sm btn-outline-primary">Review</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-lightning-charge me-1 text-warning"></i> Quick Actions</div>
                <div class="card-body d-flex flex-column gap-2">
                    <a href="{{ route('manager.expense-requests.index', ['status' => 'pending']) }}"
                       class="btn btn-outline-warning btn-sm text-start">
                        <i class="bi bi-hourglass-split me-2"></i> Review Pending ({{ $stats['pending'] }})
                    </a>
                    <a href="{{ route('manager.expense-requests.index') }}" class="btn btn-outline-secondary btn-sm text-start">
                        <i class="bi bi-list-ul me-2"></i> All Requests
                    </a>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-center bg-light rounded" style="height:130px">
                    <div class="text-center text-muted">
                        <i class="bi bi-pie-chart fs-2 d-block mb-1 opacity-25"></i>
                        <small>Approval chart — Phase 3</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
