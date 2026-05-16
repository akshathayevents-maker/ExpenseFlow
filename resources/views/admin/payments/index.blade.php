<x-admin-layout title="Payments">
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-0 fw-bold">Payments</h4>
        <p class="text-muted mb-0 small">All recorded expense payments</p>
    </div>
    <div class="text-end">
        <div class="text-muted small">Total (this page)</div>
        <div class="fw-bold fs-5">₹{{ number_format($totalPaid, 2) }}</div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Search expense title..." value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-auto">
                <select name="payment_mode" class="form-select form-select-sm">
                    <option value="">All Modes</option>
                    @foreach(\App\Models\ExpensePayment::modeLabels() as $value => $label)
                        <option value="{{ $value }}" {{ ($filters['payment_mode'] ?? '') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
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
                <input type="date" name="from" class="form-control form-control-sm" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="col-auto">
                <input type="date" name="to" class="form-control form-control-sm" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary">Filter</button>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                        <th>Paid At</th>
                        <th>Expense Request</th>
                        <th>Employee</th>
                        <th>Mode</th>
                        <th>Reference</th>
                        <th class="text-end">Amount</th>
                        <th>Paid By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    @php
                        $colors = \App\Models\ExpensePayment::modeColors();
                        $labels = \App\Models\ExpensePayment::modeLabels();
                        $color  = $colors[$payment->payment_mode] ?? 'secondary';
                    @endphp
                    <tr>
                        <td class="text-nowrap small">
                            {{ $payment->paid_at->format('d M Y') }}<br>
                            <span class="text-muted">{{ $payment->paid_at->format('h:i A') }}</span>
                        </td>
                        <td>
                            @if($payment->expenseRequest)
                                <a href="{{ route('admin.expense-requests.show', $payment->expenseRequest) }}"
                                   class="text-decoration-none fw-semibold">
                                    {{ Str::limit($payment->expenseRequest->title, 40) }}
                                </a>
                                @if($payment->expenseRequest->category)
                                    <div class="text-muted small">{{ $payment->expenseRequest->category->name }}</div>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($payment->expenseRequest?->requester)
                                {{ $payment->expenseRequest->requester->name }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle"
                                  style="font-size:.7rem;text-transform:uppercase;letter-spacing:.5px">
                                {{ $labels[$payment->payment_mode] ?? $payment->payment_mode }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $payment->transaction_reference ?? '—' }}</td>
                        <td class="text-end fw-semibold">₹{{ number_format($payment->amount, 2) }}</td>
                        <td class="text-muted small">{{ $payment->payer->name }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-credit-card fs-2 d-block mb-2"></i>
                            No payments found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($payments->hasPages())
    <div class="card-footer bg-transparent border-top d-flex align-items-center justify-content-between">
        <div class="text-muted small">{{ $payments->total() }} total payments</div>
        {{ $payments->links() }}
    </div>
    @endif
</div>
</x-admin-layout>
