<x-admin-layout title="Wallet — {{ $wallet->user->name }}">
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.wallets.index') }}">Wallets</a></li>
                <li class="breadcrumb-item active">{{ $wallet->user->name }}</li>
            </ol>
        </nav>
        <h4 class="mb-0 fw-bold">{{ $wallet->user->name }}'s Wallet</h4>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#creditModal">
            <i class="bi bi-plus-circle me-1"></i> Credit
        </button>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#debitModal">
            <i class="bi bi-dash-circle me-1"></i> Debit
        </button>
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#adjustModal">
            <i class="bi bi-sliders me-1"></i> Adjust
        </button>
    </div>
</div>

{{-- Balance card --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-4">
                <div class="text-muted small mb-1">Current Balance</div>
                <div id="wallet-balance-display"
                     class="display-6 fw-bold {{ $wallet->isNegative() ? 'text-danger' : ($wallet->isLow() ? 'text-warning' : 'text-success') }}"
                     data-raw="{{ $wallet->balance }}">
                    ₹{{ number_format($wallet->balance, 2) }}
                </div>
                <div id="wallet-balance-badge" class="mt-2">
                    @if($wallet->isLow() && !$wallet->isNegative())
                        <span class="badge bg-warning-subtle text-warning">Low Balance</span>
                    @elseif($wallet->isNegative())
                        <span class="badge bg-danger-subtle text-danger">Negative Balance</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Employee Details</h6>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="text-muted small">Name</div>
                        <div class="fw-semibold">{{ $wallet->user->name }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Email</div>
                        <div>{{ $wallet->user->email }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Role</div>
                        <span class="badge bg-secondary-subtle text-secondary role-badge">{{ $wallet->user->role }}</span>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Phone</div>
                        <div>{{ $wallet->user->phone ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end" data-no-ajax>
            <div class="col-auto">
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Credit</option>
                    <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Debit</option>
                    <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                    <option value="reimbursement" {{ request('type') === 'reimbursement' ? 'selected' : '' }}>Reimbursement</option>
                </select>
            </div>
            <div class="col-auto">
                <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" placeholder="From">
            </div>
            <div class="col-auto">
                <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}" placeholder="To">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary" type="submit">Filter</button>
                <a href="{{ route('admin.wallets.show', $wallet->user) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Transactions table --}}
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
                        <th class="text-end">Before</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">After</th>
                        <th>By</th>
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
                                <a href="{{ route('admin.expense-requests.show', $txn->expenseRequest) }}"
                                   class="text-decoration-none small">
                                    {{ Str::limit($txn->expenseRequest->title, 30) }}
                                </a>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-end text-muted small">₹{{ number_format($txn->balance_before, 2) }}</td>
                        <td class="text-end fw-semibold">
                            @if($txn->isCredit())
                                <span class="text-success">+₹{{ number_format($txn->amount, 2) }}</span>
                            @else
                                <span class="text-danger">−₹{{ number_format($txn->amount, 2) }}</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold">₹{{ number_format($txn->balance_after, 2) }}</td>
                        <td class="small text-muted">{{ $txn->creator->name }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-clock-history fs-2 d-block mb-2"></i>
                            No transactions found.
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

{{-- Credit Modal --}}
<div class="modal fade" id="creditModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="creditForm"
                  method="POST"
                  action="{{ route('admin.wallets.transact', $wallet->user) }}"
                  novalidate>
                @csrf
                <input type="hidden" name="type" value="credit">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle text-success me-2"></i>Credit Wallet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none wallet-error py-2 small" role="alert"></div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control"
                               min="0.01" step="0.01" required
                               placeholder="0.00">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes <span class="text-danger">*</span></label>
                        <textarea name="notes" class="form-control" rows="2"
                                  placeholder="Reason for credit…"
                                  required minlength="3" maxlength="500"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" data-no-loading>
                        <i class="bi bi-plus-circle me-1"></i> Credit Wallet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Debit Modal --}}
<div class="modal fade" id="debitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="debitForm"
                  method="POST"
                  action="{{ route('admin.wallets.transact', $wallet->user) }}"
                  novalidate>
                @csrf
                <input type="hidden" name="type" value="debit">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-dash-circle text-danger me-2"></i>Debit Wallet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none wallet-error py-2 small" role="alert"></div>
                    <div class="alert alert-warning small py-2">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Current balance: <strong id="debit-balance-hint">₹{{ number_format($wallet->balance, 2) }}</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control"
                               id="debitAmount"
                               min="0.01" step="0.01"
                               max="{{ $wallet->balance }}"
                               required
                               placeholder="0.00">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes <span class="text-danger">*</span></label>
                        <textarea name="notes" class="form-control" rows="2"
                                  placeholder="Reason for debit…"
                                  required minlength="3" maxlength="500"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" data-no-loading>
                        <i class="bi bi-dash-circle me-1"></i> Debit Wallet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Adjust Modal --}}
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="adjustForm"
                  method="POST"
                  action="{{ route('admin.wallets.transact', $wallet->user) }}"
                  novalidate>
                @csrf
                <input type="hidden" name="type" value="adjustment">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-sliders text-secondary me-2"></i>Balance Adjustment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none wallet-error py-2 small" role="alert"></div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Balance (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control"
                               min="0" step="0.01"
                               value="{{ $wallet->balance }}" required>
                        <div class="form-text">Set the exact balance. The difference will be recorded.</div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes <span class="text-danger">*</span></label>
                        <textarea name="notes" class="form-control" rows="2"
                                  placeholder="Reason for adjustment…"
                                  required minlength="3" maxlength="500"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" data-no-loading>
                        <i class="bi bi-sliders me-1"></i> Apply Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1100">
    <div id="walletToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-semibold"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const CSRF    = document.querySelector('meta[name="csrf-token"]').content;
    const LOW_TH  = 500;

    const balanceEl    = document.getElementById('wallet-balance-display');
    const badgeEl      = document.getElementById('wallet-balance-badge');
    const debitHint    = document.getElementById('debit-balance-hint');
    const debitAmountEl = document.getElementById('debitAmount');
    const toastEl      = document.getElementById('walletToast');
    const bsToast      = new bootstrap.Toast(toastEl, { delay: 4000 });

    function fmtBalance(n) {
        return '₹' + parseFloat(n).toLocaleString('en-IN', {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        });
    }

    function updateBalanceDisplay(newBalance) {
        balanceEl.textContent = fmtBalance(newBalance);
        balanceEl.className   = 'display-6 fw-bold';
        if (newBalance < 0)      balanceEl.classList.add('text-danger');
        else if (newBalance < LOW_TH) balanceEl.classList.add('text-warning');
        else                     balanceEl.classList.add('text-success');

        if (debitHint)    debitHint.textContent = fmtBalance(newBalance);
        if (debitAmountEl) debitAmountEl.max = newBalance > 0 ? newBalance : 0;

        if (badgeEl) {
            if (newBalance < 0)
                badgeEl.innerHTML = '<span class="badge bg-danger-subtle text-danger">Negative Balance</span>';
            else if (newBalance < LOW_TH)
                badgeEl.innerHTML = '<span class="badge bg-warning-subtle text-warning">Low Balance</span>';
            else
                badgeEl.innerHTML = '';
        }
    }

    function showToast(message, type) {
        toastEl.className = 'toast align-items-center border-0 text-bg-' + type;
        toastEl.querySelector('.toast-body').textContent = message;
        bsToast.show();
    }

    function clearModalErrors(form) {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        const err = form.querySelector('.wallet-error');
        if (err) { err.classList.add('d-none'); err.textContent = ''; }
        form.classList.remove('was-validated');
    }

    function showModalErrors(form, errors) {
        const errBox = form.querySelector('.wallet-error');
        if (errors && typeof errors === 'object') {
            Object.entries(errors).forEach(([field, messages]) => {
                const input = form.querySelector(`[name="${field}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    const fb = input.parentNode.querySelector('.invalid-feedback');
                    if (fb) fb.textContent = Array.isArray(messages) ? messages[0] : messages;
                }
            });
        }
        if (errBox) {
            errBox.textContent = 'Please fix the errors above.';
            errBox.classList.remove('d-none');
        }
    }

    function attachWalletForm(formEl, modalId) {
        const modalEl = document.getElementById(modalId);
        if (!formEl || !modalEl) return;

        const submitBtn = formEl.querySelector('[type="submit"]');

        formEl.addEventListener('submit', function (e) {
            e.preventDefault();
            e.stopPropagation();

            clearModalErrors(formEl);

            if (!formEl.checkValidity()) {
                formEl.classList.add('was-validated');
                return;
            }

            // Duplicate-submission guard
            if (submitBtn.disabled) return;

            const originalHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Processing…';

            fetch(formEl.action, {
                method : 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept'          : 'application/json',
                    'X-CSRF-TOKEN'    : CSRF,
                },
                body: new FormData(formEl),
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw data;
                return data;
            })
            .then(data => {
                bootstrap.Modal.getInstance(modalEl).hide();
                updateBalanceDisplay(data.balance);
                showToast(data.message, 'success');
                // Reload page after toast so transaction list refreshes
                setTimeout(() => window.location.reload(), 1600);
            })
            .catch(err => {
                submitBtn.disabled  = false;
                submitBtn.innerHTML = originalHtml;

                if (err && err.errors) {
                    showModalErrors(formEl, err.errors);
                } else {
                    const errBox = formEl.querySelector('.wallet-error');
                    if (errBox) {
                        errBox.textContent = (err && err.message) || 'Transaction failed. Please try again.';
                        errBox.classList.remove('d-none');
                    }
                    showToast((err && err.message) || 'Transaction failed.', 'danger');
                }
            });
        });

        // Reset form state when modal closes
        modalEl.addEventListener('hidden.bs.modal', function () {
            formEl.reset();
            clearModalErrors(formEl);
            submitBtn.disabled  = false;
            submitBtn.innerHTML = submitBtn.dataset.originalHtml || submitBtn.innerHTML;
        });

        // Stash original HTML for restore
        submitBtn.dataset.originalHtml = submitBtn.innerHTML;
    }

    attachWalletForm(document.getElementById('creditForm'),  'creditModal');
    attachWalletForm(document.getElementById('debitForm'),   'debitModal');
    attachWalletForm(document.getElementById('adjustForm'),  'adjustModal');
})();
</script>
@endpush
</x-admin-layout>
