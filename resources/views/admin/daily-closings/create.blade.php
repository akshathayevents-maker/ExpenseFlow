<x-admin-layout title="Close Today">
<div class="page-header">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1 small">
        <li class="breadcrumb-item"><a href="{{ route('admin.daily-closings.index') }}">Daily Closings</a></li>
        <li class="breadcrumb-item active">Close Today</li>
    </ol></nav>
    <h4 class="mb-0 fw-bold">Daily Closing — {{ $date->format('d M Y') }}</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Expense Total</div>
            <div class="fs-5 fw-bold text-primary">₹{{ number_format($expenseTotal, 2) }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Payments Made</div>
            <div class="fs-5 fw-bold text-success">₹{{ number_format($paymentTotal, 2) }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Stock Added</div>
            <div class="fs-5 fw-bold text-info">{{ number_format($stockAdditions, 3) + 0 }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Stock Deducted</div>
            <div class="fs-5 fw-bold text-warning">{{ number_format($stockDeductions, 3) + 0 }}</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        {{-- Today's expenses --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent fw-semibold">
                <i class="bi bi-receipt me-1 text-primary"></i>
                Today's Expenses
                <span class="badge bg-secondary ms-1">{{ $expenseCount }}</span>
            </div>
            <div class="card-body p-0">
                @if($recentExpenses->isEmpty())
                <div class="text-center py-3 text-muted small">No expenses today.</div>
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
                <form method="POST" action="{{ route('admin.daily-closings.store') }}">
                    @csrf
                    <input type="hidden" name="date" value="{{ $date->toDateString() }}">

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Any remarks for today...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="alert alert-warning border-0 py-2 small">
                        <i class="bi bi-info-circle me-1"></i>
                        Figures above are auto-calculated. Closing records today's snapshot.
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-lock me-1"></i> Confirm Daily Closing
                    </button>
                    <a href="{{ route('admin.daily-closings.index') }}" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
</x-admin-layout>
