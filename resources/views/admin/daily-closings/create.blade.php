<x-admin-layout title="{{ $date->isToday() ? 'Close Today' : 'Close Past Date' }}">
<div class="page-header">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1 small">
        <li class="breadcrumb-item"><a href="{{ route('admin.daily-closings.index') }}">Daily Closings</a></li>
        <li class="breadcrumb-item active">{{ $date->isToday() ? 'Close Today' : 'Close Past Date' }}</li>
    </ol></nav>
    <h4 class="mb-0 fw-bold">
        @if($date->isToday())
            Daily Closing — Today ({{ $date->format('d M Y') }})
        @else
            <i class="bi bi-clock-history text-warning me-2"></i>Past Date Closing — {{ $date->format('d M Y') }}
        @endif
    </h4>
    @if(!$date->isToday())
        <div class="alert alert-warning border-0 py-2 mt-2 small">
            <i class="bi bi-exclamation-triangle me-1"></i>
            You are creating a closing for a past date. Figures are calculated from data recorded on that date.
        </div>
    @endif
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Expense Total</div>
            <div class="fs-5 fw-bold text-primary">₹{{ number_format($expenseTotal ?? 0, 2) }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Payments Made</div>
            <div class="fs-5 fw-bold text-success">₹{{ number_format($paymentTotal ?? 0, 2) }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Stock Added</div>
            <div class="fs-5 fw-bold text-info">{{ number_format($stockAdditions ?? 0, 3) + 0 }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Stock Deducted</div>
            <div class="fs-5 fw-bold text-warning">{{ number_format($stockDeductions ?? 0, 3) + 0 }}</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent fw-semibold">
                <i class="bi bi-receipt me-1 text-primary"></i>
                Expenses for {{ $date->format('d M Y') }}
                <span class="badge bg-secondary ms-1">{{ $expenseCount ?? 0 }}</span>
            </div>
            <div class="card-body p-0">
                @if($recentExpenses->isEmpty())
                <div class="text-center py-3 text-muted small">No expenses on this date.</div>
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
                            @foreach($recentExpenses as $exp)
                            <tr>
                                <td class="small">{{ $exp->requester->name }}</td>
                                <td class="text-muted small">{{ $exp->category->name }}</td>
                                <td class="text-end fw-semibold small">₹{{ number_format($exp->amount, 2) }}</td>
                                <td class="text-center"><x-status-badge :status="$exp->status" /></td>
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
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-semibold">Record Closing</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.daily-closings.store') }}" novalidate>
                    @csrf
                    <input type="hidden" name="date" value="{{ $date->toDateString() }}">

                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Closing Date</label>
                        <div class="form-control bg-light text-muted small">{{ $date->format('l, d M Y') }}</div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label fw-semibold small">Notes <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                  rows="3"
                                  placeholder="Any remarks for this closing…">{{ old('notes') }}</textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="alert alert-warning border-0 py-2 small">
                        <i class="bi bi-info-circle me-1"></i>
                        Figures are auto-calculated from live data. Closing locks a snapshot.
                    </div>

                    <button type="submit" class="btn btn-primary w-100" data-loading-text="Recording…">
                        <i class="bi bi-lock me-1"></i> Confirm Daily Closing
                    </button>
                    <a href="{{ route('admin.daily-closings.index') }}" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
</x-admin-layout>
