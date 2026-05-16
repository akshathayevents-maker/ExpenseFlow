<x-admin-layout title="Dashboard">
    <div class="page-header d-flex align-items-center justify-content-between">
        <div>
            <h4 class="mb-0 fw-bold">Admin Dashboard</h4>
            <p class="text-muted mb-0 small">Welcome back, {{ auth()->user()->name }}</p>
        </div>
        <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i>{{ now()->format('d M Y') }}</span>
    </div>

    {{-- Alerts --}}
    @if($stats['low_balance_count'] > 0)
        <div class="alert alert-warning alert-dismissible d-flex align-items-center gap-2 mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
            <div>
                <strong>{{ $stats['low_balance_count'] }} wallet(s)</strong> have low balance (&lt; ₹500).
                <a href="{{ route('admin.wallets.index') }}" class="alert-link">View wallets</a>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($stats['pending_reimb_count'] > 0)
        <div class="alert alert-info alert-dismissible d-flex align-items-center gap-2 mb-3" role="alert">
            <i class="bi bi-arrow-return-left flex-shrink-0"></i>
            <div>
                <strong>{{ $stats['pending_reimb_count'] }} reimbursement(s)</strong> pending
                (₹{{ number_format($stats['pending_reimb_amount'], 2) }}).
                <a href="{{ route('admin.reports.reimbursement') }}" class="alert-link">View report</a>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-currency-rupee"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Month Expenses</p>
                        <h4 class="mb-0 fw-bold">₹{{ number_format($stats['total_expenses_month'], 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Pending Approvals</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['pending_approvals'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box bg-info bg-opacity-10 text-info">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Total Wallet Balance</p>
                        <h4 class="mb-0 fw-bold">₹{{ number_format($stats['total_wallet_balance'], 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box bg-success bg-opacity-10 text-success">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Total Employees</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['total_employees'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Recent requests --}}
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                    <span class="fw-semibold"><i class="bi bi-clock-history me-1 text-primary"></i> Recent Requests</span>
                    <a href="{{ route('admin.expense-requests.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($recentRequests->isEmpty())
                        <div class="text-center text-muted py-4">No requests yet.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th class="d-none d-md-table-cell">Employee</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentRequests as $req)
                                        <tr onclick="window.location='{{ route('admin.expense-requests.show', $req) }}'" style="cursor:pointer">
                                            <td>
                                                <div class="fw-semibold small">{{ Str::limit($req->title, 30) }}</div>
                                                <small class="text-muted">{{ $req->category->name }}</small>
                                            </td>
                                            <td class="d-none d-md-table-cell small text-muted">{{ $req->requester->name }}</td>
                                            <td class="fw-semibold small">₹{{ number_format($req->amount, 2) }}</td>
                                            <td><x-status-badge :status="$req->status" /></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Side panel --}}
        <div class="col-lg-4">
            {{-- Quick actions --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent fw-semibold">
                    <i class="bi bi-lightning-charge me-1 text-warning"></i> Quick Actions
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    <a href="{{ route('admin.expense-requests.index', ['status' => 'pending']) }}" class="btn btn-outline-warning btn-sm text-start">
                        <i class="bi bi-hourglass-split me-2"></i> Review Pending ({{ $stats['pending_approvals'] }})
                    </a>
                    <a href="{{ route('admin.employees.create') }}" class="btn btn-outline-primary btn-sm text-start">
                        <i class="bi bi-person-plus me-2"></i> Add Employee
                    </a>
                    <a href="{{ route('admin.categories.create') }}" class="btn btn-outline-secondary btn-sm text-start">
                        <i class="bi bi-tag me-2"></i> Add Category
                    </a>
                    <a href="{{ route('admin.vendors.create') }}" class="btn btn-outline-secondary btn-sm text-start">
                        <i class="bi bi-shop me-2"></i> Add Vendor
                    </a>
                    <a href="{{ route('admin.wallets.index') }}" class="btn btn-outline-info btn-sm text-start">
                        <i class="bi bi-wallet2 me-2"></i> Manage Wallets
                    </a>
                </div>
            </div>

            {{-- System info --}}
            <div class="card shadow-sm">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-info-circle me-1 text-info"></i> Summary</div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted">Active Users</td><td class="fw-semibold">{{ $stats['active_users'] }}</td></tr>
                        <tr><td class="text-muted">Inactive Users</td><td class="fw-semibold">{{ $stats['inactive_users'] }}</td></tr>
                        <tr><td class="text-muted">Managers</td><td class="fw-semibold">{{ $stats['total_managers'] }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>
