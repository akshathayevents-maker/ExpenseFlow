<x-admin-layout title="Wallet Ledger">

<x-ds.hero eyebrow="Reports" title="Wallet Ledger"
    :meta="[['icon' => 'bi-journal-text', 'text' => 'All wallet transactions across employees']]">
    <x-slot:actions>
        <a href="{{ route('admin.reports.index') }}" class="ef-btn">
            <i class="bi bi-arrow-left"></i> All Reports
        </a>
    </x-slot:actions>
</x-ds.hero>

<x-ds.card>
    <form method="GET" class="ef-an-filter">
        <div>
            <label class="ef-label">Employee</label>
            <select name="employee_id" class="ef-select" style="min-height:38px;padding:7px 12px;min-width:160px">
                <option value="">All Employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ ($filters['employee_id'] ?? '') == $emp->id ? 'selected' : '' }}>
                        {{ $emp->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="ef-label">Type</label>
            <select name="type" class="ef-select" style="min-height:38px;padding:7px 12px;min-width:140px">
                <option value="">All Types</option>
                <option value="credit" {{ ($filters['type'] ?? '') === 'credit' ? 'selected' : '' }}>Credit</option>
                <option value="debit" {{ ($filters['type'] ?? '') === 'debit' ? 'selected' : '' }}>Debit</option>
                <option value="adjustment" {{ ($filters['type'] ?? '') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                <option value="reimbursement" {{ ($filters['type'] ?? '') === 'reimbursement' ? 'selected' : '' }}>Reimbursement</option>
            </select>
        </div>
        <div class="ef-an-filter-field">
            <label class="ef-label">From</label>
            <input type="date" name="from" class="ef-input" style="min-height:38px;padding:7px 12px" value="{{ $filters['from'] ?? '' }}">
        </div>
        <div class="ef-an-filter-field">
            <label class="ef-label">To</label>
            <input type="date" name="to" class="ef-input" style="min-height:38px;padding:7px 12px" value="{{ $filters['to'] ?? '' }}">
        </div>
        <div class="ef-an-filter-actions">
            <button class="ef-btn ef-btn-dark" style="height:38px">Filter</button>
            <a href="{{ route('admin.reports.ledger') }}" class="ef-btn" style="height:38px;display:inline-flex;align-items:center">Reset</a>
        </div>
    </form>
</x-ds.card>

<x-ds.card :no-pad="true">
    <div style="overflow-x:auto">
        <table class="ef-wlt-txn-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Employee</th>
                    <th>Type</th>
                    <th>Notes / Reference</th>
                    <th class="r">Debit</th>
                    <th class="r">Credit</th>
                    <th class="r">Balance After</th>
                    <th>By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                <tr>
                    <td style="white-space:nowrap">
                        <div style="font-size:.86rem;font-weight:600">{{ $txn->created_at->format('d M Y') }}</div>
                        <div style="color:var(--ef-faint);font-size:.76rem">{{ $txn->created_at->format('h:i A') }}</div>
                    </td>
                    <td style="font-size:.86rem;font-weight:600">{{ $txn->wallet->user->name }}</td>
                    <td><span class="ef-wlt-type-badge --{{ $txn->type }}">{{ $txn->type }}</span></td>
                    <td style="color:var(--ef-faint);font-size:.84rem;max-width:180px">
                        {{ $txn->notes ?? '' }}
                        @if($txn->expenseRequest)
                            <a href="{{ route('admin.expense-requests.show', $txn->expenseRequest) }}"
                               style="display:block;color:var(--ef-emerald);text-decoration:none;font-size:.8rem;margin-top:2px">
                                <i class="bi bi-link-45deg"></i>{{ Str::limit($txn->expenseRequest->title, 25) }}
                            </a>
                        @endif
                    </td>
                    <td class="r">
                        @if($txn->isDebit())
                            <span style="color:var(--ef-danger);font-weight:680">₹{{ number_format($txn->amount, 2) }}</span>
                        @else
                            <span style="color:var(--ef-faint)">—</span>
                        @endif
                    </td>
                    <td class="r">
                        @if($txn->isCredit())
                            <span style="color:var(--ef-emerald);font-weight:680">₹{{ number_format($txn->amount, 2) }}</span>
                        @else
                            <span style="color:var(--ef-faint)">—</span>
                        @endif
                    </td>
                    <td class="r" style="font-weight:680">₹{{ number_format($txn->balance_after, 2) }}</td>
                    <td style="color:var(--ef-faint);font-size:.84rem">{{ $txn->creator->name }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:40px;color:var(--ef-faint)">
                        <i class="bi bi-journal-text" style="font-size:1.5rem;display:block;margin-bottom:8px"></i>
                        No transactions found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
    <div style="padding:12px 18px;border-top:1px solid var(--ef-border);display:flex;align-items:center;justify-content:space-between">
        <div style="color:var(--ef-faint);font-size:.8rem">{{ $transactions->total() }} transactions</div>
        {{ $transactions->links() }}
    </div>
    @endif
</x-ds.card>

</x-admin-layout>
