<x-admin-layout title="Daily Closing — {{ $dailyClosing->date->format('d M Y') }}">
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.daily-closings.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h4 class="mb-0 fw-bold">{{ $dailyClosing->date->format('d M Y') }}</h4>
            <p class="text-muted mb-0 small">Daily Closing Report</p>
        </div>
    </div>
    @php $colors = \App\Models\DailyClosing::statusColors(); $color = $colors[$dailyClosing->status] ?? 'secondary'; @endphp
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle py-2 px-3"
              style="font-size:.8rem;text-transform:uppercase">{{ $dailyClosing->status }}</span>

        @if($dailyClosing->isDraft())
        <form method="POST" action="{{ route('admin.daily-closings.verify', $dailyClosing) }}">
            @csrf @method('PATCH')
            <button class="btn btn-sm btn-success">
                <i class="bi bi-check-circle me-1"></i> Verify Closing
            </button>
        </form>
        @endif
    </div>
</div>

{{-- Summary cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Expense Total</div>
            <div class="fs-5 fw-bold text-primary">₹{{ number_format($dailyClosing->expense_total, 2) }}</div>
            <div class="text-muted" style="font-size:.75rem">{{ $dailyClosing->expense_count }} requests</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Payments Made</div>
            <div class="fs-5 fw-bold text-success">₹{{ number_format($dailyClosing->payment_total, 2) }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Stock Added</div>
            <div class="fs-5 fw-bold text-info">{{ number_format($dailyClosing->stock_additions, 3) + 0 }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Stock Deducted</div>
            <div class="fs-5 fw-bold text-warning">{{ number_format($dailyClosing->stock_deductions, 3) + 0 }}</div>
        </div>
    </div>
</div>

@if($dailyClosing->notes)
<div class="alert alert-light border mb-3">
    <i class="bi bi-sticky me-1"></i> {{ $dailyClosing->notes }}
</div>
@endif

<div class="row g-3">
    <div class="col-lg-8">
        {{-- Expenses --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent fw-semibold">
                <i class="bi bi-receipt me-1 text-primary"></i> Expenses
                <span class="badge bg-secondary ms-1">{{ $expenses->count() }}</span>
            </div>
            <div class="card-body p-0">
                @if($expenses->isEmpty())
                <div class="text-center py-3 text-muted small">No expenses for this date.</div>
                @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Category</th>
                                <th class="text-end">Amount</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expenses as $exp)
                            <tr>
                                <td class="small">{{ $exp->requester->name }}</td>
                                <td class="text-muted small">{{ $exp->category->name }}</td>
                                <td class="text-end fw-semibold small">₹{{ number_format($exp->amount, 2) }}</td>
                                <td class="text-center"><x-status-badge :status="$exp->status" /></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-semibold">
                                <td colspan="2">Total</td>
                                <td class="text-end">₹{{ number_format($expenses->sum('amount'), 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
            </div>
        </div>

        {{-- Payments --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-semibold">
                <i class="bi bi-credit-card me-1 text-success"></i> Payments
                <span class="badge bg-secondary ms-1">{{ $payments->count() }}</span>
            </div>
            <div class="card-body p-0">
                @if($payments->isEmpty())
                <div class="text-center py-3 text-muted small">No payments for this date.</div>
                @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Mode</th>
                                <th>Reference</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                            <tr>
                                <td class="small">{{ $payment->expenseRequest?->requester?->name ?? '—' }}</td>
                                <td class="text-muted small">{{ $payment->payment_mode }}</td>
                                <td class="text-muted small">{{ $payment->reference_number ?? '—' }}</td>
                                <td class="text-end fw-semibold small">₹{{ number_format($payment->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-semibold">
                                <td colspan="3">Total</td>
                                <td class="text-end">₹{{ number_format($payments->sum('amount'), 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-semibold">Closing Details</div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Date</td><td class="fw-semibold">{{ $dailyClosing->date->format('d M Y') }}</td></tr>
                    <tr><td class="text-muted">Status</td>
                        <td><span class="badge bg-{{ $color }}-subtle text-{{ $color }}" style="text-transform:uppercase">{{ $dailyClosing->status }}</span></td>
                    </tr>
                    <tr><td class="text-muted">Recorded By</td><td>{{ $dailyClosing->creator->name }}</td></tr>
                    <tr><td class="text-muted">Recorded At</td><td class="small">{{ $dailyClosing->created_at->format('h:i A') }}</td></tr>
                    @if($dailyClosing->verifier)
                    <tr><td class="text-muted">Verified By</td><td class="fw-semibold">{{ $dailyClosing->verifier->name }}</td></tr>
                    <tr><td class="text-muted">Verified At</td><td class="small">{{ $dailyClosing->verified_at->format('h:i A') }}</td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
</x-admin-layout>
