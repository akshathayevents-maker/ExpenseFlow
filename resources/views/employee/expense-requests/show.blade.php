<x-admin-layout title="Request #{{ $expenseRequest->id }}">

<style>
.req-card { border-radius: 16px; border: none; box-shadow: 0 2px 16px rgba(0,0,0,.07); }
.status-pill { font-size: .8rem; font-weight: 700; padding: .4rem .9rem; border-radius: 50px; }
.detail-label { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #64748b; margin-bottom: .2rem; }
.detail-value { font-weight: 600; color: #1e293b; font-size: .95rem; }
.amount-display { font-size: 2rem; font-weight: 800; color: #16a34a; }
.qr-thumb {
    width: 140px; height: 140px;
    object-fit: contain;
    border-radius: 12px;
    border: 2px solid #e2e8f0;
    background: #f8fafc;
    cursor: pointer;
    transition: transform .15s;
}
.qr-thumb:hover { transform: scale(1.03); }
.btn-wa {
    background: #25D366; color: #fff; border: none;
    border-radius: 12px; font-weight: 700;
    padding: .65rem 1.25rem;
    display: inline-flex; align-items: center; gap: .4rem;
    text-decoration: none; font-size: .9rem;
    transition: background .2s;
}
.btn-wa:hover { background: #1ebe5d; color: #fff; }
.timeline-dot {
    width: 12px; height: 12px; border-radius: 50%;
    flex-shrink: 0; margin-top: 3px;
}
</style>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('employee.expense-requests.index') }}"
           class="btn btn-sm btn-outline-secondary rounded-circle"
           style="width:36px;height:36px;padding:0;display:inline-flex;align-items:center;justify-content:center">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h5 class="mb-0 fw-bold">{{ $expenseRequest->title }}</h5>
            <p class="text-muted mb-0" style="font-size:.8rem">
                Request #{{ $expenseRequest->id }} · {{ $expenseRequest->created_at->diffForHumans() }}
            </p>
        </div>
    </div>
    @php $color = \App\Models\ExpenseRequest::statusColors()[$expenseRequest->status] ?? 'secondary'; @endphp
    <span class="status-pill badge bg-{{ $color }}">
        {{ ucwords(str_replace('_', ' ', $expenseRequest->status)) }}
    </span>
</div>

<div class="row g-3 justify-content-center" style="max-width:800px;margin:0 auto">

    {{-- Main card --}}
    <div class="col-12">
        <div class="req-card card">
            <div class="card-body p-4">

                {{-- Amount + title --}}
                <div class="text-center mb-4 pb-3 border-bottom">
                    <p class="detail-label mb-1">Amount Requested</p>
                    <div class="amount-display">₹{{ number_format((float) $expenseRequest->amount, 2) }}</div>
                    <p class="text-muted small mb-0">{{ $expenseRequest->title }}</p>
                </div>

                {{-- QR section --}}
                @if ($expenseRequest->qrUrl())
                    @php $qrUrl = $expenseRequest->qrUrl(); @endphp
                    <div class="text-center mb-4 pb-3 border-bottom">
                        <p class="detail-label mb-2">Payment QR Code</p>
                        <img src="{{ $qrUrl }}"
                             alt="Payment QR"
                             class="qr-thumb"
                             data-bs-toggle="modal"
                             data-bs-target="#qrModal"
                             onerror="this.closest('.text-center').querySelector('.qr-emp-error')?.style.removeProperty('display');this.style.display='none'">
                        <div class="qr-emp-error" style="display:none;font-size:.78rem;color:#dc2626;padding:8px">
                            <i class="bi bi-exclamation-triangle me-1"></i>QR unavailable
                        </div>
                        <p class="small text-muted mt-2 mb-2">Tap to enlarge</p>

                        {{-- WhatsApp resend --}}
                        <a href="{{ $expenseRequest->whatsAppUrl() }}"
                           target="_blank" rel="noopener"
                           class="btn-wa">
                            <i class="bi bi-whatsapp"></i>
                            Send to Manager via WhatsApp
                        </a>
                    </div>
                @endif

                {{-- Details grid --}}
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <p class="detail-label">Status</p>
                        <p class="detail-value mb-0">
                            <span class="badge bg-{{ $color }} rounded-pill px-3">
                                {{ ucwords(str_replace('_', ' ', $expenseRequest->status)) }}
                            </span>
                        </p>
                    </div>
                    <div class="col-6">
                        <p class="detail-label">Submitted</p>
                        <p class="detail-value mb-0">{{ $expenseRequest->created_at->format('d M Y') }}</p>
                    </div>
                    @if ($expenseRequest->notes)
                        <div class="col-12">
                            <p class="detail-label">Notes</p>
                            <p class="detail-value mb-0 fw-normal">{{ $expenseRequest->notes }}</p>
                        </div>
                    @endif
                    @if ($expenseRequest->whatsapp_sent_at)
                        <div class="col-12">
                            <p class="detail-label">WhatsApp Notified</p>
                            <p class="detail-value mb-0 fw-normal text-success">
                                <i class="bi bi-whatsapp me-1"></i>
                                {{ $expenseRequest->whatsapp_sent_at->format('d M Y, h:i A') }}
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Rejection alert --}}
                @if ($expenseRequest->isRejected() && $expenseRequest->rejection_reason)
                    <div class="alert alert-danger rounded-3 mb-3">
                        <i class="bi bi-x-circle me-1"></i>
                        <strong>Rejected:</strong> {{ $expenseRequest->rejection_reason }}
                    </div>
                @endif

                {{-- Approval notice --}}
                @if ($expenseRequest->isApproved() || $expenseRequest->isSettled())
                    <div class="alert alert-success rounded-3 mb-3">
                        <i class="bi bi-check-circle me-1"></i>
                        Approved by <strong>{{ $expenseRequest->approver?->name }}</strong>
                        @if ($expenseRequest->approved_at)
                            on {{ $expenseRequest->approved_at->format('d M Y, h:i A') }}
                        @endif
                    </div>
                @endif

                {{-- Timeline --}}
                <div class="border-top pt-3">
                    <p class="detail-label mb-3">Timeline</p>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="timeline-dot bg-primary"></div>
                            <div>
                                <p class="mb-0 fw-semibold small">Submitted</p>
                                <p class="mb-0 text-muted" style="font-size:.78rem">{{ $expenseRequest->created_at->format('d M Y, h:i A') }}</p>
                            </div>
                        </div>

                        @if ($expenseRequest->isPendingPayment())
                            <div class="d-flex gap-3 align-items-start">
                                <div class="timeline-dot bg-info"></div>
                                <div>
                                    <p class="mb-0 fw-semibold small">Awaiting Payment</p>
                                    <p class="mb-0 text-muted" style="font-size:.78rem">Manager has been notified via WhatsApp</p>
                                </div>
                            </div>
                        @elseif ($expenseRequest->isPending())
                            <div class="d-flex gap-3 align-items-start">
                                <div class="timeline-dot bg-warning"></div>
                                <div>
                                    <p class="mb-0 fw-semibold small">Awaiting Review</p>
                                    <p class="mb-0 text-muted" style="font-size:.78rem">Your request is being reviewed</p>
                                </div>
                            </div>
                        @elseif ($expenseRequest->approved_at)
                            <div class="d-flex gap-3 align-items-start">
                                <div class="timeline-dot bg-{{ $expenseRequest->isRejected() ? 'danger' : 'success' }}"></div>
                                <div>
                                    <p class="mb-0 fw-semibold small">{{ ucfirst($expenseRequest->status) }}</p>
                                    <p class="mb-0 text-muted" style="font-size:.78rem">{{ $expenseRequest->approved_at->format('d M Y, h:i A') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Action row --}}
                <div class="d-flex gap-2 flex-wrap mt-4 pt-3 border-top">
                    <a href="{{ route('employee.expense-requests.index') }}"
                       class="btn btn-outline-secondary rounded-3 flex-fill">
                        <i class="bi bi-list-ul me-1"></i>All Requests
                    </a>
                    <a href="{{ route('employee.expense-requests.create') }}"
                       class="btn btn-outline-primary rounded-3 flex-fill">
                        <i class="bi bi-plus-circle me-1"></i>New Request
                    </a>
                </div>

            </div>
        </div>
    </div>

</div>

{{-- QR lightbox modal --}}
@if ($expenseRequest->qrUrl())
@php $qrUrl = $expenseRequest->qrUrl(); @endphp
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold small">Payment QR — #{{ $expenseRequest->id }}</span>
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ $qrUrl }}" download="payment-qr-{{ $expenseRequest->id }}.jpg"
                       class="btn btn-sm btn-outline-secondary rounded-pill" title="Download QR">
                        <i class="bi bi-download"></i>
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body text-center px-4 pb-4">
                <img src="{{ $qrUrl }}"
                     alt="Payment QR"
                     class="img-fluid rounded-3"
                     style="max-height:320px;object-fit:contain"
                     onerror="this.src='';this.alt='QR unavailable'">
                <p class="small text-muted mt-2 mb-0">₹{{ number_format((float) $expenseRequest->amount, 2) }} — {{ $expenseRequest->title }}</p>
            </div>
        </div>
    </div>
</div>
@endif

</x-admin-layout>
