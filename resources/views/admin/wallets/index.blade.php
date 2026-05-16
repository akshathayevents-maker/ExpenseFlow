<x-admin-layout title="Wallets">
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-0 fw-bold">Wallets</h4>
        <p class="text-muted mb-0 small">Employee advance balances</p>
    </div>
</div>

{{-- Summary cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box bg-primary-subtle text-primary"><i class="bi bi-wallet2"></i></div>
                <div>
                    <div class="text-muted small">Total Distributed</div>
                    <div class="fw-bold fs-5">₹{{ number_format($totalBalance, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box bg-danger-subtle text-danger"><i class="bi bi-exclamation-triangle"></i></div>
                <div>
                    <div class="text-muted small">Low Balance (&lt; ₹500)</div>
                    <div class="fw-bold fs-5">{{ $lowBalanceCount }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box bg-warning-subtle text-warning"><i class="bi bi-arrow-return-left"></i></div>
                <div>
                    <div class="text-muted small">Pending Reimbursements</div>
                    <div class="fw-bold fs-5">{{ $pendingReimbCount }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box bg-success-subtle text-success"><i class="bi bi-people"></i></div>
                <div>
                    <div class="text-muted small">Active Wallets</div>
                    <div class="fw-bold fs-5">{{ $wallets->total() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Role</th>
                        <th class="text-end">Balance</th>
                        <th class="text-end">Last Transaction</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wallets as $wallet)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $wallet->user->name }}</div>
                            <div class="text-muted small">{{ $wallet->user->email }}</div>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary role-badge">
                                {{ $wallet->user->role }}
                            </span>
                        </td>
                        <td class="text-end">
                            @if($wallet->isNegative())
                                <span class="text-danger fw-semibold">₹{{ number_format($wallet->balance, 2) }}</span>
                            @elseif($wallet->isLow())
                                <span class="text-warning fw-semibold">₹{{ number_format($wallet->balance, 2) }}</span>
                                <i class="bi bi-exclamation-triangle-fill text-warning ms-1" title="Low balance"></i>
                            @else
                                <span class="fw-semibold">₹{{ number_format($wallet->balance, 2) }}</span>
                            @endif
                        </td>
                        <td class="text-end text-muted small">
                            {{ $wallet->updated_at->diffForHumans() }}
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.wallets.show', $wallet->user) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-wallet2 fs-2 d-block mb-2"></i>
                            No wallets found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($wallets->hasPages())
    <div class="card-footer bg-transparent border-top">
        {{ $wallets->links() }}
    </div>
    @endif
</div>
</x-admin-layout>
