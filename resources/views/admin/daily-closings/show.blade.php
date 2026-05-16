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
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle py-2 px-3"
              style="font-size:.8rem;text-transform:uppercase">{{ $dailyClosing->status }}</span>

        @if($hasDrift)
            <span class="badge bg-warning-subtle text-warning border border-warning-subtle py-2 px-3"
                  style="font-size:.75rem">
                <i class="bi bi-exclamation-triangle me-1"></i>Data drift detected
            </span>
        @endif

        @if($dailyClosing->isDraft())
            <form method="POST" action="{{ route('admin.daily-closings.verify', $dailyClosing) }}">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-sm btn-success" data-loading-text="Verifying…">
                    <i class="bi bi-check-circle me-1"></i> Verify Closing
                </button>
            </form>
        @endif

        @if($dailyClosing->canEdit())
            <a href="{{ route('admin.daily-closings.edit', $dailyClosing) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            <form method="POST" action="{{ route('admin.daily-closings.recalculate', $dailyClosing) }}">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-sm btn-outline-info" data-loading-text="Recalculating…">
                    <i class="bi bi-arrow-repeat me-1"></i> Recalculate
                </button>
            </form>
            <button type="button" class="btn btn-sm btn-outline-danger"
                    data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="bi bi-trash me-1"></i> Delete
            </button>
        @endif
    </div>
</div>

{{-- Drift alert --}}
@if($hasDrift)
<div class="alert alert-warning border-warning d-flex align-items-start gap-2 mb-3">
    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
    <div>
        <strong>Data drift detected.</strong>
        The stored figures differ from current live data (expense records or payments may have been modified after this closing was created).
        <form method="POST" action="{{ route('admin.daily-closings.recalculate', $dailyClosing) }}" class="d-inline">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-sm btn-warning ms-2" data-loading-text="Recalculating…">
                <i class="bi bi-arrow-repeat me-1"></i> Recalculate Now
            </button>
        </form>
    </div>
</div>
@endif

{{-- Summary cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Expense Total</div>
            <div class="fs-5 fw-bold text-primary">₹{{ number_format($dailyClosing->expense_total, 2) }}</div>
            <div class="text-muted" style="font-size:.75rem">{{ $dailyClosing->expense_count }} requests</div>
            @if(abs($liveFigures['expense_total'] - (float)$dailyClosing->expense_total) > 0.005)
                <div class="text-warning" style="font-size:.7rem">Live: ₹{{ number_format($liveFigures['expense_total'], 2) }}</div>
            @endif
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Payments Made</div>
            <div class="fs-5 fw-bold text-success">₹{{ number_format($dailyClosing->payment_total, 2) }}</div>
            @if(abs($liveFigures['payment_total'] - (float)$dailyClosing->payment_total) > 0.005)
                <div class="text-warning" style="font-size:.7rem">Live: ₹{{ number_format($liveFigures['payment_total'], 2) }}</div>
            @endif
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
                                <td class="text-muted small">{{ $payment->transaction_reference ?? '—' }}</td>
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
                    <tr><td class="text-muted">Recorded by</td><td>{{ $dailyClosing->creator->name }}</td></tr>
                    <tr><td class="text-muted">Recorded at</td><td class="small">{{ $dailyClosing->created_at->format('d M Y, h:i A') }}</td></tr>
                    @if($dailyClosing->updater)
                    <tr>
                        <td class="text-muted">Last edited by</td>
                        <td class="fw-semibold text-warning">{{ $dailyClosing->updater->name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Edited at</td>
                        <td class="small">{{ $dailyClosing->updated_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    @endif
                    @if($dailyClosing->verifier)
                    <tr><td class="text-muted">Verified by</td><td class="fw-semibold text-success">{{ $dailyClosing->verifier->name }}</td></tr>
                    <tr><td class="text-muted">Verified at</td><td class="small">{{ $dailyClosing->verified_at->format('d M Y, h:i A') }}</td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
@if($dailyClosing->canDelete())
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title"><i class="bi bi-trash text-danger me-2"></i>Delete Closing</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body small">
                <div class="alert alert-danger py-2 mb-2 small">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Cannot be undone.
                </div>
                Delete closing for <strong>{{ $dailyClosing->date->format('d M Y') }}</strong>?
            </div>
            <div class="modal-footer border-0 py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('admin.daily-closings.destroy', $dailyClosing) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" data-loading-text="Deleting…">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
</x-admin-layout>
