<x-admin-layout title="Edit Closing — {{ $dailyClosing->date->format('d M Y') }}">
<div class="page-header">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1 small">
        <li class="breadcrumb-item"><a href="{{ route('admin.daily-closings.index') }}">Daily Closings</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.daily-closings.show', $dailyClosing) }}">{{ $dailyClosing->date->format('d M Y') }}</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol></nav>
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Daily Closing — {{ $dailyClosing->date->format('d M Y') }}</h4>
            <div class="mt-1">
                <span class="badge bg-{{ \App\Models\DailyClosing::statusColors()[$dailyClosing->status] ?? 'secondary' }}">
                    {{ ucfirst($dailyClosing->status) }}
                </span>
                @if ($dailyClosing->isFinalized())
                    <span class="badge bg-dark ms-1"><i class="bi bi-lock-fill me-1"></i>Finalized {{ $dailyClosing->finalized_at->format('d M Y') }}</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.daily-closings.show', $dailyClosing) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-eye me-1"></i>View
            </a>
            @if ($dailyClosing->canEdit())
                <button type="button" class="btn btn-sm btn-outline-info" id="btnSyncSnapshot">
                    <i class="bi bi-arrow-repeat me-1"></i>Sync Live Expenses
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning" id="btnPreview">
                    <i class="bi bi-bar-chart me-1"></i>Preview Changes
                </button>
            @endif
            @if ($dailyClosing->canFinalize())
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#finalizeModal">
                    <i class="bi bi-lock me-1"></i>Finalize &amp; Lock
                </button>
            @endif
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Balance Cards --}}
<div class="row g-3 mb-3" id="balanceCards">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="text-muted small mb-1">Opening Balance</div>
                <div class="fs-5 fw-bold" id="cardOpening">₹{{ number_format($totals['opening_balance'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="text-muted small mb-1">Expenses</div>
                <div class="fs-5 fw-bold text-danger" id="cardExpenses">₹{{ number_format($totals['expense_total'], 2) }}</div>
                <div class="text-muted" style="font-size:.75rem" id="cardExpenseCount">{{ $totals['expense_count'] }} item(s)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="text-muted small mb-1">Payments Out</div>
                <div class="fs-5 fw-bold text-warning" id="cardPayments">₹{{ number_format($totals['payment_total'], 2) }}</div>
                <div class="text-muted" style="font-size:.75rem">
                    Adj: <span class="text-success" id="cardCredit">+₹{{ number_format($totals['total_credit'], 2) }}</span>
                    / <span class="text-danger" id="cardDebit">-₹{{ number_format($totals['total_debit'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 bg-primary text-white shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="small mb-1 opacity-75">Closing Balance</div>
                <div class="fs-5 fw-bold" id="cardClosing">₹{{ number_format($totals['closing_balance'], 2) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs" id="closingTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pane-summary" type="button" role="tab">
            <i class="bi bi-clipboard-data me-1"></i>Summary
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-expenses" type="button" role="tab">
            <i class="bi bi-receipt me-1"></i>Expenses
            <span class="badge bg-secondary ms-1" id="expenseBadge">{{ $dailyClosing->snapshotExpenses->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-adjustments" type="button" role="tab">
            <i class="bi bi-sliders me-1"></i>Adjustments
            <span class="badge bg-secondary ms-1" id="adjBadge">{{ $dailyClosing->adjustments->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-audit" type="button" role="tab">
            <i class="bi bi-clock-history me-1"></i>Audit History
        </button>
    </li>
</ul>

<div class="tab-content border border-top-0 rounded-bottom bg-white p-3 shadow-sm mb-4">

    {{-- ── Summary Tab ──────────────────────────────────────────────────────── --}}
    <div class="tab-pane fade show active" id="pane-summary" role="tabpanel">
        @if ($dailyClosing->canEdit())
            <form method="POST" action="{{ route('admin.daily-closings.update', $dailyClosing) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Opening Balance (₹)</label>
                        <input type="number" name="opening_balance"
                            class="form-control @error('opening_balance') is-invalid @enderror"
                            step="0.01" min="0"
                            value="{{ old('opening_balance', number_format((float)$dailyClosing->opening_balance, 2, '.', '')) }}">
                        @error('opening_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-9">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" rows="2"
                            class="form-control @error('notes') is-invalid @enderror"
                            placeholder="Optional internal notes…">{{ old('notes', $dailyClosing->notes) }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary btn-sm" data-loading-text="Saving…">
                        <i class="bi bi-save me-1"></i>Save Changes
                    </button>
                </div>
            </form>
            <hr>
        @endif

        <div class="row g-2 text-center">
            <div class="col-6 col-md-2">
                <div class="p-2 border rounded bg-light">
                    <div class="small text-muted">Expense Total</div>
                    <strong>₹{{ number_format($dailyClosing->expense_total, 2) }}</strong>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="p-2 border rounded bg-light">
                    <div class="small text-muted">Payment Total</div>
                    <strong>₹{{ number_format($dailyClosing->payment_total, 2) }}</strong>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="p-2 border rounded bg-light">
                    <div class="small text-muted">Credits</div>
                    <strong class="text-success">+₹{{ number_format($dailyClosing->total_credit, 2) }}</strong>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="p-2 border rounded bg-light">
                    <div class="small text-muted">Debits</div>
                    <strong class="text-danger">-₹{{ number_format($dailyClosing->total_debit, 2) }}</strong>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="p-2 border rounded bg-light">
                    <div class="small text-muted">Expense Count</div>
                    <strong>{{ $dailyClosing->expense_count }}</strong>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="p-2 border rounded bg-primary text-white">
                    <div class="small opacity-75">Closing Balance</div>
                    <strong>₹{{ number_format($dailyClosing->closing_balance, 2) }}</strong>
                </div>
            </div>
        </div>

        <div class="mt-3 text-muted small">
            Created by {{ optional($dailyClosing->creator)->name ?? '—' }} on {{ $dailyClosing->created_at->format('d M Y, h:i A') }}.
            @if ($dailyClosing->updater)
                Last updated by {{ $dailyClosing->updater->name }} on {{ $dailyClosing->updated_at->format('d M Y, h:i A') }}.
            @endif
        </div>
    </div>

    {{-- ── Expenses Tab ─────────────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="pane-expenses" role="tabpanel">
        @if ($dailyClosing->canEdit())
            <div class="mb-3">
                <button type="button" class="btn btn-sm btn-outline-success" id="btnAddExpense">
                    <i class="bi bi-plus-circle me-1"></i>Add Expense
                </button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Status</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Employee</th>
                        <th class="text-end">Amount</th>
                        <th>Remarks</th>
                        @unless ($dailyClosing->isFinalized())<th class="text-center">Actions</th>@endunless
                    </tr>
                </thead>
                <tbody id="expenseTableBody">
                    @forelse ($dailyClosing->snapshotExpenses as $expense)
                        @include('admin.daily-closings.partials.expense-row', ['closing' => $dailyClosing])
                    @empty
                        <tr id="noExpensesRow">
                            <td colspan="7" class="text-center text-muted py-4">No expenses in this closing.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Adjustments Tab ──────────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="pane-adjustments" role="tabpanel">
        @if ($dailyClosing->canEdit())
            <div class="card mb-3 border-0 bg-light">
                <div class="card-body py-2 px-3">
                    <h6 class="card-title mb-2">Add Adjustment</h6>
                    <form id="formAddAdjustment" class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label form-label-sm">Type</label>
                            <select name="type" class="form-select form-select-sm" required>
                                <option value="credit">Credit (+)</option>
                                <option value="debit">Debit (−)</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label form-label-sm">Amount (₹)</label>
                            <input type="number" name="amount" class="form-control form-control-sm" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form-label-sm">Reason</label>
                            <input type="text" name="reason" class="form-control form-control-sm" maxlength="255" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm">Notes (optional)</label>
                            <input type="text" name="notes" class="form-control form-control-sm" maxlength="1000">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-sm btn-primary w-100">Add</button>
                        </div>
                    </form>
                    <div id="adjFormError" class="text-danger small mt-1 d-none"></div>
                </div>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th class="text-end">Amount</th>
                        <th>Reason</th>
                        <th>Notes</th>
                        <th>By</th>
                        <th>Date</th>
                        @unless ($dailyClosing->isFinalized())<th></th>@endunless
                    </tr>
                </thead>
                <tbody id="adjTableBody">
                    @forelse ($dailyClosing->adjustments as $adj)
                        <tr data-adj-id="{{ $adj->id }}">
                            <td>
                                @if ($adj->isCredit())
                                    <span class="badge bg-success">Credit</span>
                                @else
                                    <span class="badge bg-danger">Debit</span>
                                @endif
                            </td>
                            <td class="text-end">₹{{ number_format($adj->amount, 2) }}</td>
                            <td>{{ $adj->reason }}</td>
                            <td class="text-muted small">{{ $adj->notes ?: '—' }}</td>
                            <td class="text-muted small">{{ optional($adj->creator)->name ?? '—' }}</td>
                            <td class="text-muted small">{{ $adj->created_at->format('d M Y') }}</td>
                            @unless ($dailyClosing->isFinalized())
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-del-adj" data-id="{{ $adj->id }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                            @endunless
                        </tr>
                    @empty
                        <tr id="noAdjRow">
                            <td colspan="7" class="text-center text-muted py-4">No adjustments recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Audit History Tab ────────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="pane-audit" role="tabpanel">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Action</th>
                        <th>Field</th>
                        <th>Old Value</th>
                        <th>New Value</th>
                        <th>Remarks</th>
                        <th>By</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($audits as $a)
                        <tr>
                            <td>
                                <span class="badge bg-{{ \App\Models\DailyClosingAudit::actionColors()[$a->action_type] ?? 'secondary' }}">
                                    {{ \App\Models\DailyClosingAudit::actionLabels()[$a->action_type] ?? ucfirst($a->action_type) }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $a->field_name ?: '—' }}</td>
                            <td class="text-muted small">{{ $a->old_value ?: '—' }}</td>
                            <td class="text-muted small">{{ $a->new_value ?: '—' }}</td>
                            <td class="text-muted small">{{ $a->remarks ?: '—' }}</td>
                            <td class="small">{{ optional($a->user)->name ?? '—' }}</td>
                            <td class="text-muted small">{{ $a->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No audit records yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($audits->hasPages())
            <div class="mt-2">{{ $audits->links() }}</div>
        @endif
    </div>

</div>{{-- /tab-content --}}

{{-- ── Modals ──────────────────────────────────────────────────────────────── --}}

{{-- Add / Edit Expense Modal --}}
<div class="modal fade" id="expenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="expenseModalTitle">Add Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="expModalError" class="alert alert-danger py-2 d-none"></div>
                <div class="mb-3">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" id="expTitle" class="form-control" maxlength="255" required>
                    <div class="invalid-feedback" id="errTitle"></div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col">
                        <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" id="expAmount" class="form-control" step="0.01" min="0.01" required>
                        <div class="invalid-feedback" id="errAmount"></div>
                    </div>
                    <div class="col">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select id="expStatus" class="form-select" required>
                            <option value="approved">Approved</option>
                            <option value="paid">Paid</option>
                            <option value="reimbursement_pending">Reimbursement Pending</option>
                            <option value="reimbursed">Reimbursed</option>
                            <option value="completed">Completed</option>
                        </select>
                        <div class="invalid-feedback" id="errStatus"></div>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col">
                        <label class="form-label">Category</label>
                        <select id="expCategory" class="form-select">
                            <option value="">— None —</option>
                            @foreach ($categories as $catId => $catName)
                                <option value="{{ $catId }}">{{ $catName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col">
                        <label class="form-label">Employee</label>
                        <select id="expEmployee" class="form-select">
                            <option value="">— None —</option>
                            @foreach ($employees as $empId => $empName)
                                <option value="{{ $empId }}">{{ $empName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Remarks</label>
                    <textarea id="expRemarks" class="form-control" rows="2" maxlength="1000"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveExpense">Save Expense</button>
            </div>
        </div>
    </div>
</div>

{{-- Preview Modal --}}
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview: Stored vs Computed</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewBody">
                <div class="text-center py-4"><span class="spinner-border"></span></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Finalize Modal --}}
<div class="modal fade" id="finalizeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-lock me-2"></i>Finalize &amp; Lock</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>This will <strong>lock</strong> the closing for <strong>{{ $dailyClosing->date->format('d M Y') }}</strong>. No further edits will be possible.</p>
                <p class="mb-0 text-muted small">Totals will be recalculated one final time before locking.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('admin.daily-closings.finalize', $dailyClosing) }}" class="d-inline">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-success" data-loading-text="Finalizing…">
                        <i class="bi bi-lock me-1"></i>Yes, Finalize
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100">
    <div id="ajaxToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="ajaxToastBody">Done.</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
const URLS = {
    snapshot:    '{{ route('admin.daily-closings.snapshot', $dailyClosing) }}',
    preview:     '{{ route('admin.daily-closings.preview', $dailyClosing) }}',
    expenses:    '{{ route('admin.daily-closings.expenses.store', $dailyClosing) }}',
    adjustments: '{{ route('admin.daily-closings.adjustments.store', $dailyClosing) }}',
    expenseBase: '{{ url('admin/daily-closings/' . $dailyClosing->id . '/expenses') }}',
    adjBase:     '{{ url('admin/daily-closings/' . $dailyClosing->id . '/adjustments') }}',
};

function showToast(msg, ok = true) {
    const t = document.getElementById('ajaxToast');
    t.className = `toast align-items-center border-0 text-bg-${ok ? 'success' : 'danger'}`;
    document.getElementById('ajaxToastBody').textContent = msg;
    bootstrap.Toast.getOrCreateInstance(t, { delay: 3500 }).show();
}

function fmt(n) {
    return '₹' + parseFloat(n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function updateCards(totals) {
    document.getElementById('cardOpening').textContent      = fmt(totals.opening_balance);
    document.getElementById('cardExpenses').textContent     = fmt(totals.expense_total);
    document.getElementById('cardExpenseCount').textContent = totals.expense_count + ' item(s)';
    document.getElementById('cardPayments').textContent     = fmt(totals.payment_total);
    document.getElementById('cardCredit').textContent       = '+' + fmt(totals.total_credit);
    document.getElementById('cardDebit').textContent        = '-' + fmt(totals.total_debit);
    document.getElementById('cardClosing').textContent      = fmt(totals.closing_balance);
}

async function apiFetch(url, method = 'GET', body = null) {
    const opts = {
        method,
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
    };
    if (body instanceof FormData) {
        opts.body = body;
    } else if (body) {
        opts.headers['Content-Type'] = 'application/json';
        opts.body = JSON.stringify(body);
    }
    const res = await fetch(url, opts);
    const json = await res.json();
    if (!res.ok) throw { status: res.status, data: json };
    return json;
}

function clearExpenseErrors() {
    ['expTitle', 'expAmount', 'expStatus'].forEach(id => {
        document.getElementById(id)?.classList.remove('is-invalid');
    });
    ['errTitle', 'errAmount', 'errStatus'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.textContent = '';
    });
}

function setExpenseErrors(errors) {
    const map = { title: ['expTitle', 'errTitle'], amount: ['expAmount', 'errAmount'], payment_status: ['expStatus', 'errStatus'] };
    for (const [field, [inputId, errId]] of Object.entries(map)) {
        if (errors[field]) {
            document.getElementById(inputId)?.classList.add('is-invalid');
            const errEl = document.getElementById(errId);
            if (errEl) errEl.textContent = errors[field][0];
        }
    }
}

// ── Sync Snapshot ──────────────────────────────────────────────────────────

document.getElementById('btnSyncSnapshot')?.addEventListener('click', async function () {
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Syncing…';
    try {
        const data = await apiFetch(URLS.snapshot, 'PATCH');
        updateCards(data.totals);
        showToast(data.message);
        setTimeout(() => location.reload(), 1200);
    } catch (e) {
        showToast(e.data?.message || 'Sync failed.', false);
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Sync Live Expenses';
    }
});

// ── Preview ────────────────────────────────────────────────────────────────

document.getElementById('btnPreview')?.addEventListener('click', async function () {
    document.getElementById('previewBody').innerHTML = '<div class="text-center py-4"><span class="spinner-border"></span></div>';
    new bootstrap.Modal(document.getElementById('previewModal')).show();
    try {
        const data = await apiFetch(URLS.preview);
        const fields = [
            ['Expense Total',    'expense_total'],
            ['Payment Total',    'payment_total'],
            ['Total Credit',     'total_credit'],
            ['Total Debit',      'total_debit'],
            ['Opening Balance',  'opening_balance'],
            ['Closing Balance',  'closing_balance'],
        ];
        let html = '<table class="table table-sm table-bordered"><thead class="table-light"><tr><th>Field</th><th>Stored</th><th>Computed</th><th></th></tr></thead><tbody>';
        for (const [label, key] of fields) {
            const oldVal = parseFloat(data.old[key] || 0);
            const newVal = parseFloat(data.new[key] || 0);
            const diff   = Math.abs(newVal - oldVal) > 0.005;
            html += `<tr class="${diff ? 'table-warning' : ''}">
                <td>${label}</td>
                <td>${fmt(oldVal)}</td>
                <td>${fmt(newVal)}</td>
                <td>${diff ? '<span class="badge bg-warning text-dark">Changed</span>' : '<span class="badge bg-light text-muted">Same</span>'}</td>
            </tr>`;
        }
        html += '</tbody></table>';
        document.getElementById('previewBody').innerHTML = html;
    } catch (e) {
        document.getElementById('previewBody').innerHTML = '<div class="alert alert-danger">Failed to load preview.</div>';
    }
});

// ── Expense Modal ──────────────────────────────────────────────────────────

const expenseModal   = new bootstrap.Modal(document.getElementById('expenseModal'));
const btnSaveExpense = document.getElementById('btnSaveExpense');

function openExpenseModal(data = null) {
    document.getElementById('expenseModalTitle').textContent = data ? 'Edit Expense' : 'Add Expense';
    document.getElementById('expTitle').value    = data?.title   ?? '';
    document.getElementById('expAmount').value   = data?.amount  ?? '';
    document.getElementById('expStatus').value   = data?.status  ?? 'approved';
    document.getElementById('expRemarks').value  = data?.remarks ?? '';
    document.getElementById('expCategory').value = data?.category_id ?? '';
    document.getElementById('expEmployee').value = data?.employee_id ?? '';
    document.getElementById('expenseModal').dataset.editId = data?.id ?? '';
    document.getElementById('expModalError').classList.add('d-none');
    clearExpenseErrors();
    expenseModal.show();
}

document.getElementById('btnAddExpense')?.addEventListener('click', () => openExpenseModal());

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-edit-expense');
    if (!btn) return;
    openExpenseModal({
        id:          btn.dataset.id,
        title:       btn.dataset.title,
        amount:      btn.dataset.amount,
        remarks:     btn.dataset.remarks,
    });
});

btnSaveExpense?.addEventListener('click', async function () {
    const editId = document.getElementById('expenseModal').dataset.editId;
    const payload = new FormData();
    payload.append('title',          document.getElementById('expTitle').value);
    payload.append('amount',         document.getElementById('expAmount').value);
    payload.append('payment_status', document.getElementById('expStatus').value);
    payload.append('category_id',    document.getElementById('expCategory').value);
    payload.append('employee_id',    document.getElementById('expEmployee').value);
    payload.append('remarks',        document.getElementById('expRemarks').value);

    this.disabled = true;
    this.textContent = 'Saving…';
    document.getElementById('expModalError').classList.add('d-none');
    clearExpenseErrors();

    try {
        let data;
        if (editId) {
            payload.append('_method', 'PUT');
            data = await apiFetch(`${URLS.expenseBase}/${editId}`, 'POST', payload);
        } else {
            data = await apiFetch(URLS.expenses, 'POST', payload);
        }

        const tbody = document.getElementById('expenseTableBody');
        if (editId) {
            const row = tbody.querySelector(`tr[data-expense-id="${editId}"]`);
            if (row) row.outerHTML = data.row_html;
        } else {
            document.getElementById('noExpensesRow')?.remove();
            tbody.insertAdjacentHTML('beforeend', data.row_html);
            const badge = document.getElementById('expenseBadge');
            badge.textContent = parseInt(badge.textContent || 0) + 1;
        }

        updateCards(data.totals);
        expenseModal.hide();
        showToast(data.message);
    } catch (e) {
        if (e.status === 422 && e.data?.errors) {
            setExpenseErrors(e.data.errors);
        } else {
            const errEl = document.getElementById('expModalError');
            errEl.textContent = e.data?.message || 'Save failed.';
            errEl.classList.remove('d-none');
        }
    } finally {
        this.disabled = false;
        this.textContent = 'Save Expense';
    }
});

// ── Remove / Restore Expense ───────────────────────────────────────────────

document.getElementById('expenseTableBody').addEventListener('click', async function (e) {
    const removeBtn  = e.target.closest('.btn-remove-expense');
    const restoreBtn = e.target.closest('.btn-restore-expense');
    if (!removeBtn && !restoreBtn) return;

    const btn    = removeBtn || restoreBtn;
    const id     = btn.dataset.id;
    const action = removeBtn ? 'remove' : 'restore';
    btn.disabled = true;

    try {
        const data = await apiFetch(`${URLS.expenseBase}/${id}/${action}`, 'PATCH');
        const row  = document.querySelector(`tr[data-expense-id="${id}"]`);
        if (row) row.outerHTML = data.row_html;
        updateCards(data.totals);
        showToast(data.message);
    } catch (e) {
        showToast(e.data?.message || 'Action failed.', false);
        btn.disabled = false;
    }
});

// ── Add Adjustment ─────────────────────────────────────────────────────────

document.getElementById('formAddAdjustment')?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const errEl  = document.getElementById('adjFormError');
    const submit = this.querySelector('[type="submit"]');
    errEl.classList.add('d-none');
    submit.disabled = true;
    submit.textContent = 'Adding…';

    try {
        const data = await apiFetch(URLS.adjustments, 'POST', new FormData(this));
        document.getElementById('noAdjRow')?.remove();
        const adj    = data.adjustment;
        const badge  = adj.type === 'credit' ? 'bg-success' : 'bg-danger';
        const label  = adj.type === 'credit' ? 'Credit' : 'Debit';
        document.getElementById('adjTableBody').insertAdjacentHTML('beforeend', `
            <tr data-adj-id="${adj.id}">
                <td><span class="badge ${badge}">${label}</span></td>
                <td class="text-end">₹${parseFloat(adj.amount).toFixed(2)}</td>
                <td>${adj.reason}</td>
                <td class="text-muted small">${adj.notes || '—'}</td>
                <td class="text-muted small">${adj.created_by}</td>
                <td class="text-muted small">${adj.created_at}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-del-adj" data-id="${adj.id}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`);
        const adjBadge = document.getElementById('adjBadge');
        adjBadge.textContent = parseInt(adjBadge.textContent || 0) + 1;
        updateCards(data.totals);
        this.reset();
        showToast(data.message);
    } catch (err) {
        errEl.textContent = err.data?.message || 'Failed to add adjustment.';
        errEl.classList.remove('d-none');
    } finally {
        submit.disabled = false;
        submit.textContent = 'Add';
    }
});

// ── Delete Adjustment ──────────────────────────────────────────────────────

document.getElementById('adjTableBody').addEventListener('click', async function (e) {
    const btn = e.target.closest('.btn-del-adj');
    if (!btn || !confirm('Remove this adjustment?')) return;

    const id = btn.dataset.id;
    btn.disabled = true;

    try {
        const data = await apiFetch(`${URLS.adjBase}/${id}`, 'DELETE');
        document.querySelector(`tr[data-adj-id="${id}"]`)?.remove();
        const adjBadge = document.getElementById('adjBadge');
        adjBadge.textContent = Math.max(0, parseInt(adjBadge.textContent || 1) - 1);
        updateCards(data.totals);
        showToast(data.message);
    } catch (err) {
        showToast(err.data?.message || 'Delete failed.', false);
        btn.disabled = false;
    }
});
</script>
@endpush
</x-admin-layout>
