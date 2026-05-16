<x-admin-layout title="Request Sent">

<style>
.success-card {
    max-width: 460px;
    margin: 2rem auto;
    border-radius: 24px;
    border: none;
    box-shadow: 0 8px 40px rgba(0,0,0,.10);
    overflow: hidden;
}
.success-header {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    padding: 2.5rem 2rem 2rem;
    text-align: center;
    color: #fff;
}
.success-header .check-circle {
    width: 72px; height: 72px;
    background: rgba(255,255,255,.25);
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 2.2rem;
    margin-bottom: 1rem;
    animation: pop .4s cubic-bezier(.34,1.56,.64,1);
}
@keyframes pop {
    0%   { transform: scale(0); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
.success-header h4 { font-weight: 800; margin-bottom: .25rem; font-size: 1.3rem; }
.success-header p  { opacity: .88; font-size: .9rem; margin: 0; }

.success-body { padding: 1.75rem; }

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    padding: .6rem 0;
    border-bottom: 1px solid #f1f5f9;
}
.detail-row:last-child { border-bottom: none; }
.detail-label { font-size: .8rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }
.detail-value { font-weight: 600; color: #1e293b; text-align: right; max-width: 60%; word-break: break-word; }
.amount-big   { font-size: 1.35rem; font-weight: 800; color: #16a34a; }

.btn-whatsapp {
    background: #25D366;
    border: none;
    color: #fff;
    font-size: 1rem;
    font-weight: 700;
    border-radius: 14px;
    padding: .85rem 1.5rem;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    text-decoration: none;
    transition: background .2s, transform .1s;
}
.btn-whatsapp:hover  { background: #1ebe5d; color: #fff; transform: translateY(-1px); }
.btn-whatsapp:active { transform: translateY(0); }

.wa-note {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 12px;
    padding: .75rem 1rem;
    font-size: .82rem;
    color: #15803d;
    display: flex;
    align-items: flex-start;
    gap: .5rem;
}
.wa-note i { font-size: 1rem; flex-shrink: 0; margin-top: .05rem; }

@media (max-width: 575px) {
    .success-card { margin: 1rem; border-radius: 20px; }
    .success-body { padding: 1.25rem; }
}
</style>

<div class="success-card card">

    {{-- Header --}}
    <div class="success-header">
        <div class="check-circle">
            <i class="bi bi-check-lg"></i>
        </div>
        <h4>Payment Request Sent!</h4>
        <p>Your request has been submitted successfully</p>
    </div>

    {{-- Body --}}
    <div class="success-body">

        {{-- Request summary --}}
        <div class="mb-3">
            <div class="detail-row">
                <span class="detail-label">Request ID</span>
                <span class="detail-value">#{{ $expenseRequest->id }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Title</span>
                <span class="detail-value">{{ $expenseRequest->title }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Amount</span>
                <span class="detail-value amount-big">₹{{ number_format((float) $expenseRequest->amount, 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Submitted</span>
                <span class="detail-value">{{ $expenseRequest->created_at->format('d M Y, h:i A') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value">
                    <span class="badge bg-info text-dark rounded-pill px-3">Pending Payment</span>
                </span>
            </div>
        </div>

        {{-- WhatsApp CTA --}}
        <div class="wa-note mb-3">
            <i class="bi bi-whatsapp"></i>
            <div>
                Manager will be notified via WhatsApp. Tap below to send the payment details now.
            </div>
        </div>

        <a id="btnWhatsApp"
           href="{{ $expenseRequest->whatsAppUrl() }}"
           target="_blank"
           rel="noopener"
           class="btn-whatsapp mb-3">
            <i class="bi bi-whatsapp fs-5"></i>
            Open WhatsApp to Notify Manager
        </a>

        {{-- Action buttons --}}
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('employee.dashboard') }}"
               class="btn btn-outline-secondary rounded-3 flex-fill">
                <i class="bi bi-house me-1"></i>Dashboard
            </a>
            <a href="{{ route('employee.expense-requests.create') }}"
               class="btn btn-outline-primary rounded-3 flex-fill">
                <i class="bi bi-plus-circle me-1"></i>New Request
            </a>
            <a href="{{ route('employee.expense-requests.show', $expenseRequest) }}"
               class="btn btn-outline-secondary rounded-3 flex-fill">
                <i class="bi bi-eye me-1"></i>View Request
            </a>
        </div>

    </div>
</div>

@push('scripts')
<script>
(function () {
    // Auto-open WhatsApp on mobile after brief delay
    // Only fires once per page load and only on mobile (user-agent check)
    const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
    if (isMobile) {
        const btn = document.getElementById('btnWhatsApp');
        if (btn) {
            setTimeout(function () {
                window.location.href = btn.href;
            }, 800);
        }
    }
})();
</script>
@endpush

</x-admin-layout>
