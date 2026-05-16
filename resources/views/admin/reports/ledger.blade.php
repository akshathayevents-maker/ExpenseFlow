<x-admin-layout title="Wallet Ledger">
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
            <li class="breadcrumb-item active">Wallet Ledger</li>
        </ol></nav>
        <h4 class="mb-0 fw-bold">Wallet Ledger</h4>
        <p class="text-muted mb-0 small">All wallet transactions across employees</p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <select name="employee_id" class="form-select form-select-sm">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ ($filters['employee_id'] ?? '') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="credit" {{ ($filters['type'] ?? '') === 'credit' ? 'selected' : '' }}>Credit</option>
                    <option value="debit" {{ ($filters['type'] ?? '') === 'debit' ? 'selected' : '' }}>Debit</option>
                    <option value="adjustment" {{ ($filters['type'] ?? '') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                    <option value="reimbursement" {{ ($filters['type'] ?? '') === 'reimbursement' ? 'selected' : '' }}>Reimbursement</option>
                </select>
            </div>
            <div class="col-auto">
                <input type="date" name="from" class="form-control form-control-sm" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="col-auto">
                <input type="date" name="to" class="form-control form-control-sm" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary">Filter</button>
                <a href="{{ route('admin.reports.ledger') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                        <th>Type</th>
                        <th>Notes / Reference</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                        <th class="text-end">Balance After</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                    @php
                        $colors = \App\Models\WalletTransaction::typeColors();
                        $color  = $colors[$txn->type] ?? 'secondary';
                        $isDebit = in_array($txn->type, ['debit']);
                    @endphp
                    <tr>
                        <td class="text-nowrap small">
                            {{ $txn->created_at->format('d M Y') }}<br>
                            <span class="text-muted">{{ $txn->created_at->format('h:i A') }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold small">{{ $txn->wallet->user->name }}</div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle"
                                  style="font-size:.65rem;text-transform:uppercase;letter-spacing:.5px">
                                {{ $txn->type }}
                            </span>
                        </td>
                        <td class="text-muted small">
                            {{ $txn->notes ?? '' }}
                            @if($txn->expenseRequest)
                                <a href="{{ route('admin.expense-requests.show', $txn->expenseRequest) }}"
                                   class="d-block text-decoration-none small">
                                    <i class="bi bi-link-45deg"></i>{{ Str::limit($txn->expenseRequest->title, 25) }}
                                </a>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($txn->isDebit())
                                <span class="text-danger fw-semibold">₹{{ number_format($txn->amount, 2) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($txn->isCredit())
                                <span class="text-success fw-semibold">₹{{ number_format($txn->amount, 2) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold">₹{{ number_format($txn->balance_after, 2) }}</td>
                        <td class="small text-muted">{{ $txn->creator->name }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-text fs-2 d-block mb-2"></i>
                            No transactions found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($transactions->hasPages())
    <div class="card-footer bg-transparent border-top d-flex align-items-center justify-content-between">
        <div class="text-muted small">{{ $transactions->total() }} transactions</div>
        {{ $transactions->links() }}
    </div>
    @endif
</div>
</x-admin-layout>
