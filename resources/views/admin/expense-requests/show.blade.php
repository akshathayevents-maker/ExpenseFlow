<x-admin-layout title="Expense Request #{{ $expenseRequest->id }}">
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.expense-requests.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
            <div>
                <h4 class="mb-0 fw-bold">{{ $expenseRequest->title }}</h4>
                <p class="text-muted mb-0 small">Request #{{ $expenseRequest->id }} · {{ $expenseRequest->created_at->format('d M Y, h:i A') }}</p>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <x-status-badge :status="$expenseRequest->status" />
            <x-priority-badge :priority="$expenseRequest->priority" />
        </div>
    </div>

    <div class="row g-3">
        {{-- Left column: Details --}}
        <div class="col-lg-8">
            {{-- Request details --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent fw-semibold">
                    <i class="bi bi-info-circle me-1 text-primary"></i> Request Details
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <p class="text-muted small mb-1">Requested By</p>
                            <p class="fw-semibold mb-0">{{ $expenseRequest->requester->name }}</p>
                            <small class="text-muted">{{ $expenseRequest->requester->email }}</small>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small mb-1">Category</p>
                            <p class="fw-semibold mb-0">{{ $expenseRequest->category->name }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small mb-1">Amount</p>
                            <p class="fw-bold fs-5 mb-0 text-primary">₹{{ number_format($expenseRequest->amount, 2) }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small mb-1">Vendor</p>
                            <p class="fw-semibold mb-0">{{ $expenseRequest->vendor?->name ?? '—' }}</p>
                        </div>
                        @if($expenseRequest->settlement_type)
                            <div class="col-sm-6">
                                <p class="text-muted small mb-1">Settlement Type</p>
                                <p class="fw-semibold mb-0">{{ ucwords(str_replace('_', ' ', $expenseRequest->settlement_type)) }}</p>
                            </div>
                        @endif
                        @if($expenseRequest->notes)
                            <div class="col-12">
                                <p class="text-muted small mb-1">Notes</p>
                                <p class="mb-0">{{ $expenseRequest->notes }}</p>
                            </div>
                        @endif
                        @if($expenseRequest->isRejected() && $expenseRequest->rejection_reason)
                            <div class="col-12">
                                <div class="alert alert-danger mb-0">
                                    <strong><i class="bi bi-x-circle me-1"></i> Rejection Reason:</strong>
                                    {{ $expenseRequest->rejection_reason }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Payment details --}}
            @if($expenseRequest->payment)
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent fw-semibold">
                    <i class="bi bi-credit-card me-1 text-success"></i> Payment Details
                </div>
                <div class="card-body">
                    @php
                        $p = $expenseRequest->payment;
                        $modeLabels = \App\Models\ExpensePayment::modeLabels();
                        $modeColors = \App\Models\ExpensePayment::modeColors();
                        $modeColor  = $modeColors[$p->payment_mode] ?? 'secondary';
                    @endphp
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <p class="text-muted small mb-1">Payment Mode</p>
                            <span class="badge bg-{{ $modeColor }}-subtle text-{{ $modeColor }} border border-{{ $modeColor }}-subtle"
                                  style="font-size:.75rem">
                                {{ $modeLabels[$p->payment_mode] ?? $p->payment_mode }}
                            </span>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small mb-1">Amount Paid</p>
                            <p class="fw-semibold mb-0 text-success">₹{{ number_format($p->amount, 2) }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small mb-1">Paid At</p>
                            <p class="fw-semibold mb-0">{{ $p->paid_at->format('d M Y, h:i A') }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small mb-1">Paid By</p>
                            <p class="fw-semibold mb-0">{{ $p->payer->name }}</p>
                        </div>
                        @if($p->transaction_reference)
                            <div class="col-sm-6">
                                <p class="text-muted small mb-1">Transaction Reference</p>
                                <p class="fw-semibold mb-0 font-monospace">{{ $p->transaction_reference }}</p>
                            </div>
                        @endif
                        @if($p->payment_notes)
                            <div class="col-12">
                                <p class="text-muted small mb-1">Payment Notes</p>
                                <p class="mb-0">{{ $p->payment_notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Bills --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent fw-semibold">
                    <i class="bi bi-paperclip me-1 text-primary"></i> Uploaded Bills
                    <span class="badge bg-secondary ms-1">{{ $expenseRequest->bills->count() }}</span>
                </div>
                <div class="card-body">
                    @if($expenseRequest->bills->isEmpty())
                        <p class="text-muted mb-0 small">No bills uploaded.</p>
                    @else
                        <div class="row g-2">
                            @foreach($expenseRequest->bills as $bill)
                                <div class="col-6 col-sm-4 col-md-3">
                                    <div class="border rounded overflow-hidden position-relative" style="aspect-ratio:1">
                                        @if($bill->isImage())
                                            <img src="{{ $bill->url() }}" alt="{{ $bill->original_name }}"
                                                 class="w-100 h-100 object-fit-cover"
                                                 style="object-fit:cover;cursor:pointer"
                                                 data-bs-toggle="modal" data-bs-target="#billModal{{ $bill->id }}">
                                        @else
                                            <div class="d-flex flex-column align-items-center justify-content-center h-100 bg-light text-muted">
                                                <i class="bi bi-file-earmark-pdf fs-2 text-danger"></i>
                                                <small class="mt-1 text-center px-1">{{ Str::limit($bill->original_name, 18) }}</small>
                                            </div>
                                        @endif
                                        <div class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-50 text-white px-2 py-1" style="font-size:.65rem">
                                            {{ $bill->humanSize() }}
                                        </div>
                                    </div>
                                    <a href="{{ $bill->url() }}" target="_blank"
                                       class="btn btn-sm btn-outline-secondary w-100 mt-1" style="font-size:.75rem">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                </div>

                                @if($bill->isImage())
                                    <div class="modal fade" id="billModal{{ $bill->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h6 class="modal-title">{{ $bill->original_name }}</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-2 text-center">
                                                    <img src="{{ $bill->url() }}" class="img-fluid rounded" alt="{{ $bill->original_name }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right column: Actions + Timeline --}}
        <div class="col-lg-4">
            {{-- Pending: Approve / Reject --}}
            @if($expenseRequest->isPending())
                <div class="card shadow-sm border-warning mb-3">
                    <div class="card-header bg-warning bg-opacity-10 fw-semibold text-warning">
                        <i class="bi bi-clock me-1"></i> Pending Action
                    </div>
                    <div class="card-body d-grid gap-2">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i class="bi bi-check-circle me-1"></i> Approve Request
                        </button>
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle me-1"></i> Reject Request
                        </button>
                    </div>
                </div>
            @endif

            {{-- Approved: Settlement options --}}
            @if($expenseRequest->isApproved())
                <div class="card shadow-sm border-info mb-3">
                    <div class="card-header bg-info bg-opacity-10 fw-semibold text-info">
                        <i class="bi bi-cash-coin me-1"></i> Settle Payment
                    </div>
                    <div class="card-body d-grid gap-2">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#walletModal">
                            <i class="bi bi-wallet2 me-1"></i> Settle via Wallet
                        </button>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#directModal">
                            <i class="bi bi-cash me-1"></i> Record Direct Payment
                        </button>
                        <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#reimbPendingModal">
                            <i class="bi bi-arrow-return-left me-1"></i> Mark Reimbursement Pending
                        </button>
                    </div>
                </div>
            @endif

            {{-- Reimbursement pending: Reimburse --}}
            @if($expenseRequest->isReimbursementPending())
                <div class="card shadow-sm border-primary mb-3">
                    <div class="card-header bg-primary bg-opacity-10 fw-semibold text-primary">
                        <i class="bi bi-arrow-return-left me-1"></i> Reimbursement Due
                    </div>
                    <div class="card-body d-grid">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reimburseModal">
                            <i class="bi bi-cash-coin me-1"></i> Record Reimbursement
                        </button>
                    </div>
                </div>
            @endif

            {{-- Paid or Reimbursed: Mark completed --}}
            @if($expenseRequest->isPaid() || $expenseRequest->isReimbursed())
                <div class="card shadow-sm border-secondary mb-3">
                    <div class="card-body d-grid">
                        <button type="button" class="btn btn-secondary w-100"
                                data-bs-toggle="modal" data-bs-target="#completeModal">
                            <i class="bi bi-check2-all me-1"></i> Mark as Completed
                        </button>
                    </div>
                </div>
            @endif

            {{-- Timeline --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent fw-semibold">
                    <i class="bi bi-clock-history me-1 text-primary"></i> Status Timeline
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="d-flex gap-2 mb-3">
                            <div class="rounded-circle flex-shrink-0 bg-primary" style="width:10px;height:10px;margin-top:4px"></div>
                            <div>
                                <p class="mb-0 small fw-semibold">Submitted</p>
                                <p class="mb-0 text-muted" style="font-size:.75rem">{{ $expenseRequest->created_at->format('d M Y, h:i A') }}</p>
                                <p class="mb-0 text-muted" style="font-size:.75rem">by {{ $expenseRequest->requester->name }}</p>
                            </div>
                        </div>

                        @if($expenseRequest->approved_at)
                            <div class="d-flex gap-2 mb-3">
                                <div class="rounded-circle flex-shrink-0 bg-{{ $expenseRequest->isRejected() ? 'danger' : 'success' }}"
                                     style="width:10px;height:10px;margin-top:4px"></div>
                                <div>
                                    <p class="mb-0 small fw-semibold">{{ ucfirst($expenseRequest->isRejected() ? 'Rejected' : 'Approved') }}</p>
                                    <p class="mb-0 text-muted" style="font-size:.75rem">{{ $expenseRequest->approved_at->format('d M Y, h:i A') }}</p>
                                    <p class="mb-0 text-muted" style="font-size:.75rem">by {{ $expenseRequest->approver?->name }}</p>
                                </div>
                            </div>
                        @endif

                        @if($expenseRequest->isReimbursementPending() || $expenseRequest->isReimbursed())
                            <div class="d-flex gap-2 mb-3">
                                <div class="rounded-circle flex-shrink-0 bg-warning" style="width:10px;height:10px;margin-top:4px"></div>
                                <div>
                                    <p class="mb-0 small fw-semibold">Reimbursement Pending</p>
                                    <p class="mb-0 text-muted" style="font-size:.75rem">{{ $expenseRequest->updated_at->format('d M Y, h:i A') }}</p>
                                </div>
                            </div>
                        @endif

                        @if($expenseRequest->isPaid() || $expenseRequest->isReimbursed() || $expenseRequest->isCompleted())
                            <div class="d-flex gap-2 {{ $expenseRequest->isCompleted() ? 'mb-3' : '' }}">
                                <div class="rounded-circle flex-shrink-0 bg-info" style="width:10px;height:10px;margin-top:4px"></div>
                                <div>
                                    <p class="mb-0 small fw-semibold">
                                        @if($expenseRequest->isReimbursed()) Reimbursed
                                        @elseif($expenseRequest->isCompleted()) Paid
                                        @else Paid
                                        @endif
                                    </p>
                                    @if($expenseRequest->payment)
                                        <p class="mb-0 text-muted" style="font-size:.75rem">{{ $expenseRequest->payment->paid_at->format('d M Y, h:i A') }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($expenseRequest->isCompleted())
                            <div class="d-flex gap-2">
                                <div class="rounded-circle flex-shrink-0 bg-secondary" style="width:10px;height:10px;margin-top:4px"></div>
                                <div>
                                    <p class="mb-0 small fw-semibold">Completed</p>
                                    <p class="mb-0 text-muted" style="font-size:.75rem">{{ $expenseRequest->updated_at->format('d M Y, h:i A') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Delete --}}
            <button type="button" class="btn btn-sm btn-outline-danger w-100"
                    data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="bi bi-trash me-1"></i> Delete Request
            </button>
        </div>
    </div>

    {{-- Approve Modal --}}
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="bi bi-check-circle text-success me-2"></i>Approve Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Approve <strong>{{ $expenseRequest->title }}</strong> for <strong>₹{{ number_format($expenseRequest->amount, 2) }}</strong>?</p>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('admin.expense-requests.approve', $expenseRequest) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-success" data-loading-text="Approving…">Confirm Approve</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="bi bi-x-circle text-danger me-2"></i>Reject Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.expense-requests.reject', $expenseRequest) }}">
                    @csrf @method('PATCH')
                    <div class="modal-body">
                        <p>Provide a reason for rejecting <strong>{{ $expenseRequest->title }}</strong>.</p>
                        <textarea name="rejection_reason" class="form-control @error('rejection_reason') is-invalid @enderror"
                                  rows="3" placeholder="Enter reason (required)…" required minlength="5"></textarea>
                        @error('rejection_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" data-loading-text="Rejecting…">Confirm Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Settle via Wallet Modal --}}
    <div class="modal fade" id="walletModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="bi bi-wallet2 text-primary me-2"></i>Settle via Wallet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small py-2">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>₹{{ number_format($expenseRequest->amount, 2) }}</strong> will be deducted from
                        <strong>{{ $expenseRequest->requester->name }}'s</strong> wallet.
                    </div>
                    <p class="text-muted small">The request will be marked as <strong>paid</strong> and a wallet debit transaction will be recorded.</p>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('admin.expense-requests.settle-wallet', $expenseRequest) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-primary" data-loading-text="Processing…">Confirm Wallet Deduction</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Record Direct Payment Modal --}}
    <div class="modal fade" id="directModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.expense-requests.settle-direct', $expenseRequest) }}">
                    @csrf @method('PATCH')
                    <div class="modal-header border-0">
                        <h5 class="modal-title"><i class="bi bi-cash text-success me-2"></i>Record Direct Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_mode" class="form-select" required>
                                <option value="">Select mode…</option>
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control"
                                   value="{{ $expenseRequest->amount }}" min="0.01" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Transaction Reference</label>
                            <input type="text" name="transaction_reference" class="form-control" placeholder="UTR / cheque / ref no.">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Paid At <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="paid_at" class="form-control"
                                   value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Payment Notes</label>
                            <textarea name="payment_notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" data-loading-text="Recording…">Record Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Mark Reimbursement Pending Modal --}}
    <div class="modal fade" id="reimbPendingModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="bi bi-arrow-return-left text-warning me-2"></i>Mark Reimbursement Pending</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Mark <strong>{{ $expenseRequest->title }}</strong> as reimbursement pending?</p>
                    <p class="text-muted small">The employee paid out-of-pocket and is awaiting reimbursement of <strong>₹{{ number_format($expenseRequest->amount, 2) }}</strong>.</p>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('admin.expense-requests.reimbursement-pending', $expenseRequest) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-warning" data-loading-text="Processing…">Confirm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Mark as Completed Modal --}}
    <div class="modal fade" id="completeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="bi bi-check2-all text-secondary me-2"></i>Mark as Completed</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Mark <strong>{{ $expenseRequest->title }}</strong> as completed?</p>
                    <div class="alert alert-secondary small py-2 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Amount: <strong>₹{{ number_format($expenseRequest->amount, 2) }}</strong> ·
                        Requester: <strong>{{ $expenseRequest->requester->name }}</strong>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('admin.expense-requests.mark-completed', $expenseRequest) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-secondary" data-loading-text="Completing…">
                            <i class="bi bi-check2-all me-1"></i> Confirm
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="bi bi-trash text-danger me-2"></i>Delete Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger small py-2">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <strong>This action cannot be undone.</strong>
                    </div>
                    <p class="mb-0">Permanently delete <strong>{{ $expenseRequest->title }}</strong>
                        (₹{{ number_format($expenseRequest->amount, 2) }})?</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('admin.expense-requests.destroy', $expenseRequest) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger" data-loading-text="Deleting…">
                            <i class="bi bi-trash me-1"></i> Delete Permanently
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Reimburse Modal --}}
    <div class="modal fade" id="reimburseModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.expense-requests.reimburse', $expenseRequest) }}">
                    @csrf @method('PATCH')
                    <div class="modal-header border-0">
                        <h5 class="modal-title"><i class="bi bi-cash-coin text-primary me-2"></i>Record Reimbursement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info small py-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Reimbursing <strong>₹{{ number_format($expenseRequest->amount, 2) }}</strong> to
                            <strong>{{ $expenseRequest->requester->name }}</strong>.
                            This will also credit their wallet.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_mode" class="form-select" required>
                                <option value="">Select mode…</option>
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control"
                                   value="{{ $expenseRequest->amount }}" min="0.01" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Transaction Reference</label>
                            <input type="text" name="transaction_reference" class="form-control" placeholder="UTR / ref no.">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Paid At <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="paid_at" class="form-control"
                                   value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="payment_notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" data-loading-text="Recording…">Record Reimbursement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
