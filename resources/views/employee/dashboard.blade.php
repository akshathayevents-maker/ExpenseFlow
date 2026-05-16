<x-admin-layout title="My Dashboard">
    <div class="page-header d-flex align-items-center justify-content-between">
        <div>
            <h4 class="mb-0 fw-bold">My Dashboard</h4>
            <p class="text-muted mb-0 small">Welcome, {{ auth()->user()->name }}</p>
        </div>
        <a href="{{ route('employee.expense-requests.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> New Request
        </a>
    </div>

    {{-- Wallet alerts --}}
    @if($stats['wallet_negative'])
        <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-3" role="alert">
            <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
            <div>Your wallet balance is <strong>negative (₹{{ number_format($stats['wallet_balance'], 2) }})</strong>. Please contact admin.</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @elseif($stats['wallet_low'])
        <div class="alert alert-warning alert-dismissible d-flex align-items-center gap-2 mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
            <div>Your wallet balance is low (<strong>₹{{ number_format($stats['wallet_balance'], 2) }}</strong>). Contact admin to top up.</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box bg-primary bg-opacity-10 text-primary"><i class="bi bi-files"></i></div>
                    <div>
                        <p class="text-muted mb-0 small">My Requests</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['my_requests'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box bg-warning bg-opacity-10 text-warning"><i class="bi bi-hourglass-split"></i></div>
                    <div>
                        <p class="text-muted mb-0 small">Pending</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['pending_requests'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle-fill"></i></div>
                    <div>
                        <p class="text-muted mb-0 small">Approved Amount</p>
                        <h4 class="mb-0 fw-bold">₹{{ number_format($stats['approved_amount'], 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <a href="{{ route('employee.wallet.show') }}" class="text-decoration-none">
                <div class="card stat-card shadow-sm h-100 {{ $stats['wallet_negative'] ? 'border-danger' : ($stats['wallet_low'] ? 'border-warning' : '') }}">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="icon-box {{ $stats['wallet_negative'] ? 'bg-danger bg-opacity-10 text-danger' : ($stats['wallet_low'] ? 'bg-warning bg-opacity-10 text-warning' : 'bg-info bg-opacity-10 text-info') }}">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small">Wallet Balance</p>
                            <h4 class="mb-0 fw-bold {{ $stats['wallet_negative'] ? 'text-danger' : ($stats['wallet_low'] ? 'text-warning' : '') }}">
                                ₹{{ number_format($stats['wallet_balance'], 2) }}
                            </h4>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                    <span class="fw-semibold"><i class="bi bi-clock-history me-1 text-primary"></i> Recent Requests</span>
                    <a href="{{ route('employee.expense-requests.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($recentRequests->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-file-earmark-plus fs-2 d-block mb-2"></i>
                            No requests yet.
                            <a href="{{ route('employee.expense-requests.create') }}" class="d-block mt-2">Submit your first request</a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th class="d-none d-sm-table-cell">Category</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentRequests as $req)
                                        <tr onclick="window.location='{{ route('employee.expense-requests.show', $req) }}'" style="cursor:pointer">
                                            <td class="fw-semibold small">{{ Str::limit($req->title, 30) }}</td>
                                            <td class="d-none d-sm-table-cell small text-muted">{{ $req->category?->name ?? '—' }}</td>
                                            <td class="small">₹{{ number_format($req->amount, 2) }}</td>
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

        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-lightning-charge me-1 text-warning"></i> Quick Actions</div>
                <div class="card-body d-flex flex-column gap-2">
                    <a href="{{ route('employee.expense-requests.create') }}" class="btn btn-primary btn-sm text-start">
                        <i class="bi bi-plus-circle me-2"></i> Submit New Request
                    </a>
                    <a href="{{ route('employee.expense-requests.index', ['status' => 'pending']) }}"
                       class="btn btn-outline-warning btn-sm text-start">
                        <i class="bi bi-hourglass-split me-2"></i> My Pending ({{ $stats['pending_requests'] }})
                    </a>
                    <a href="{{ route('employee.expense-requests.index', ['status' => 'approved']) }}"
                       class="btn btn-outline-success btn-sm text-start">
                        <i class="bi bi-check-circle me-2"></i> Approved ({{ $stats['approved_requests'] }})
                    </a>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-transparent fw-semibold">
                    <i class="bi bi-wallet2 me-1 text-info"></i> My Wallet
                </div>
                <div class="card-body text-center py-3">
                    <div class="display-6 fw-bold {{ $stats['wallet_negative'] ? 'text-danger' : ($stats['wallet_low'] ? 'text-warning' : 'text-success') }}">
                        ₹{{ number_format($stats['wallet_balance'], 2) }}
                    </div>
                    <a href="{{ route('employee.wallet.show') }}" class="btn btn-sm btn-outline-info mt-2">
                        View Transactions
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
