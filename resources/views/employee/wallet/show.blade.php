<x-admin-layout title="My Wallet">
<div class="page-header">
    <h4 class="mb-0 fw-bold">My Wallet</h4>
    <p class="text-muted mb-0 small">Your advance balance and transaction history</p>
</div>

{{-- Balance card --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center">
            <div class="card-body py-4">
                <i class="bi bi-wallet2 fs-2 text-primary mb-2 d-block"></i>
                <div class="text-muted small mb-1">Current Balance</div>
                <div class="display-6 fw-bold {{ $wallet->isNegative() ? 'text-danger' : ($wallet->isLow() ? 'text-warning' : 'text-success') }}">
                    ₹{{ number_format($wallet->balance, 2) }}
                </div>
                @if($wallet->isLow() && !$wallet->isNegative())
                    <div class="alert alert-warning py-2 mt-3 mb-0 small">
                        <i class="bi bi-exclamation-triangle me-1"></i> Low balance. Contact admin to top up.
                    </div>
                @elseif($wallet->isNegative())
                    <div class="alert alert-danger py-2 mt-3 mb-0 small">
                        <i class="bi bi-exclamation-circle me-1"></i> Negative balance. Please contact admin.
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Quick Stats</h6>
                @php
                    $credited  = $transactions->getCollection()->where('type', 'credit')->sum('amount')
                                + $transactions->getCollection()->where('type', 'reimbursement')->sum('amount');
                    $debited   = $transactions->getCollection()->where('type', 'debit')->sum('amount');
                @endphp
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 rounded bg-success-subtle">
                            <div class="text-muted small">Total Credited (this page)</div>
                            <div class="fw-bold text-success">₹{{ number_format($credited, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded bg-danger-subtle">
                            <div class="text-muted small">Total Debited (this page)</div>
                            <div class="fw-bold text-danger">₹{{ number_format($debited, 2) }}</div>
                        </div>
                    </div>
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
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Credit</option>
                    <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Debit</option>
                    <option value="reimbursement" {{ request('type') === 'reimbursement' ? 'selected' : '' }}>Reimbursement</option>
                    <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                </select>
            </div>
            <div class="col-auto">
                <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
            </div>
            <div class="col-auto">
                <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary">Filter</button>
                <a href="{{ route('employee.wallet.show') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Transactions --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Notes</th>
                        <th>Expense Request</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Balance After</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                    @php
                        $colors = \App\Models\WalletTransaction::typeColors();
                        $color  = $colors[$txn->type] ?? 'secondary';
                    @endphp
                    <tr>
                        <td class="text-nowrap small">
                            {{ $txn->created_at->format('d M Y') }}<br>
                            <span class="text-muted">{{ $txn->created_at->format('h:i A') }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle"
                                  style="font-size:.7rem;text-transform:uppercase;letter-spacing:.5px">
                                {{ $txn->type }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $txn->notes ?? '—' }}</td>
                        <td>
                            @if($txn->expenseRequest)
                                <a href="{{ route('employee.expense-requests.show', $txn->expenseRequest) }}"
                                   class="text-decoration-none small">
                                    {{ Str::limit($txn->expenseRequest->title, 30) }}
                                </a>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold">
                            @if($txn->isCredit())
                                <span class="text-success">+₹{{ number_format($txn->amount, 2) }}</span>
                            @else
                                <span class="text-danger">−₹{{ number_format($txn->amount, 2) }}</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold">₹{{ number_format($txn->balance_after, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-clock-history fs-2 d-block mb-2"></i>
                            No transactions yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($transactions->hasPages())
    <div class="card-footer bg-transparent border-top">
        {{ $transactions->links() }}
    </div>
    @endif
</div>
</x-admin-layout>
