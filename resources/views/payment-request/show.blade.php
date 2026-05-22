<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<meta name="robots" content="noindex,nofollow">

{{-- ── WhatsApp / OpenGraph rich preview ─────────────────────── --}}
<meta property="og:type"        content="website">
<meta property="og:site_name"   content="{{ config('app.name') }}">
<meta property="og:title"       content="₹{{ number_format((float)$expense->amount, 2) }} — {{ $expense->title }}">
<meta property="og:description" content="Payment request from {{ $expense->requester?->name ?? 'Employee' }}. Tap to view QR and pay instantly.">
@if($expense->qrUrl())
<meta property="og:image"       content="{{ $expense->qrUrl() }}">
<meta property="og:image:width" content="600">
<meta property="og:image:height" content="600">
<meta property="og:image:alt"   content="Payment QR for {{ $expense->title }}">
@endif
<meta name="twitter:card"       content="summary_large_image">

<title>₹{{ number_format((float)$expense->amount, 2) }} • {{ $expense->title }} • ExpenseFlow</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2">
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=2">
<meta name="theme-color" content="#10b981">

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --green:    #1a6645;
    --green-hi: #22845a;
    --red:      #dc2626;
    --text:     #111827;
    --sub:      #6b7280;
    --border:   #e5e7eb;
}

html { height: 100%; }
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: #f3f4f6;
    min-height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding: 20px 16px 40px;
    -webkit-font-smoothing: antialiased;
}

/* ── Card ────────────────────────────────────────────────── */
.card {
    width: 100%;
    max-width: 400px;
    background: #fff;
    border-radius: 24px;
    box-shadow: 0 4px 24px rgba(0,0,0,.10);
    overflow: hidden;
}

/* ── App bar ─────────────────────────────────────────────── */
.app-bar {
    background: var(--green);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.app-bar-icon {
    width: 32px; height: 32px;
    background: rgba(255,255,255,.2);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .9rem;
    color: #fff;
    flex-shrink: 0;
}
.app-bar-title {
    font-size: .88rem;
    font-weight: 700;
    color: rgba(255,255,255,.9);
}
.app-bar-id {
    margin-left: auto;
    font-size: .72rem;
    font-weight: 600;
    color: rgba(255,255,255,.6);
    background: rgba(255,255,255,.12);
    padding: 3px 10px;
    border-radius: 12px;
}

/* ── Amount hero ─────────────────────────────────────────── */
.amount-hero {
    text-align: center;
    padding: 28px 20px 20px;
    border-bottom: 1px solid #f3f4f6;
}
.amount-lbl {
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--sub);
    margin-bottom: 8px;
}
.amount-val {
    font-size: 3rem;
    font-weight: 800;
    color: var(--text);
    line-height: 1;
    letter-spacing: -.02em;
}
.amount-title {
    font-size: .88rem;
    font-weight: 600;
    color: var(--sub);
    margin-top: 6px;
}

/* ── Employee row ────────────────────────────────────────── */
.emp-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1px solid #f3f4f6;
}
.emp-avatar {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--green), var(--green-hi));
    color: #fff;
    font-size: .85rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.emp-name { font-size: .88rem; font-weight: 700; color: var(--text); }
.emp-role { font-size: .72rem; color: var(--sub); margin-top: 1px; }
.emp-time {
    margin-left: auto;
    font-size: .72rem;
    color: var(--sub);
    text-align: right;
}

/* ── QR section ──────────────────────────────────────────── */
.qr-section {
    padding: 24px 20px;
    text-align: center;
    border-bottom: 1px solid #f3f4f6;
}
.qr-lbl {
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--sub);
    margin-bottom: 16px;
}
.qr-box {
    background: #fff;
    border: 2px solid var(--border);
    border-radius: 16px;
    padding: 16px;
    display: inline-block;
    position: relative;
}
/* Corner accent marks */
.qr-box::before, .qr-box::after {
    content: '';
    position: absolute;
    width: 20px; height: 20px;
    border-color: var(--green);
    border-style: solid;
}
.qr-box::before {
    top: -1px; left: -1px;
    border-width: 2px 0 0 2px;
    border-radius: 14px 0 0 0;
}
.qr-box::after {
    bottom: -1px; right: -1px;
    border-width: 0 2px 2px 0;
    border-radius: 0 0 14px 0;
}
.qr-img {
    max-width: 240px;
    max-height: 240px;
    width: 100%;
    display: block;
    border-radius: 8px;
}
.qr-hint {
    font-size: .75rem;
    color: var(--sub);
    margin-top: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

/* No QR placeholder */
.no-qr {
    background: #f9fafb;
    border: 2px dashed var(--border);
    border-radius: 16px;
    padding: 32px 24px;
    text-align: center;
    color: var(--sub);
    font-size: .85rem;
}
.no-qr-icon { font-size: 2.5rem; margin-bottom: 10px; opacity: .4; }

/* ── Status row ──────────────────────────────────────────── */
.status-row {
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f3f4f6;
}
.status-lbl { font-size: .78rem; font-weight: 600; color: var(--sub); }
.status-pill {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 20px;
    border: 1px solid;
}
.pill-pending         { background: #fef3c7; color: #92400e; border-color: #fde68a; }
.pill-approved        { background: #dcfce7; color: #14532d; border-color: #bbf7d0; }
.pill-paid            { background: #cffafe; color: #164e63; border-color: #a5f3fc; }
.pill-rejected        { background: #fee2e2; color: #7f1d1d; border-color: #fecaca; }
.pill-pending_payment { background: #e0f2fe; color: #0c4a6e; border-color: #bae6fd; }
.pill-reimbursement_pending { background: #ede9fe; color: #3b0764; border-color: #ddd6fe; }
.pill-reimbursed      { background: #dcfce7; color: #14532d; border-color: #bbf7d0; }
.pill-completed       { background: #f0fdf4; color: #14532d; border-color: #bbf7d0; }

/* ── Footer ──────────────────────────────────────────────── */
.page-footer {
    padding: 14px 20px;
    text-align: center;
    font-size: .72rem;
    color: #9ca3af;
}
.brand { font-weight: 700; color: var(--sub); }

/* ── Expired / invalid state ──────────────────────────────── */
.error-card {
    width: 100%;
    max-width: 360px;
    background: #fff;
    border-radius: 20px;
    padding: 40px 24px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,.08);
}

/* ── Payment action card ──────────────────────────────────── */
.action-card {
    padding: 20px;
    border-top: 2px solid #f0fdf4;
    background: linear-gradient(135deg, #f0fdf4 0%, #fff 100%);
}
.action-card-title {
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--green);
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 14px 20px;
    background: linear-gradient(135deg, var(--green) 0%, var(--green-hi) 100%);
    color: #fff;
    font-size: .9rem;
    font-weight: 700;
    border: none;
    border-radius: 14px;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(26,102,69,.30);
    transition: transform .15s, box-shadow .15s;
    -webkit-tap-highlight-color: transparent;
}
.action-btn:active { transform: scale(.97); box-shadow: 0 2px 8px rgba(26,102,69,.25); }
.action-btn-icon { font-size: 1.1rem; }

/* ── Confirmation modal ───────────────────────────────────── */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.55);
    z-index: 1000;
    align-items: flex-end;
    justify-content: center;
    padding: 0 0 env(safe-area-inset-bottom);
    -webkit-backdrop-filter: blur(2px);
    backdrop-filter: blur(2px);
}
.modal-overlay.open { display: flex; }
.modal-sheet {
    background: #fff;
    border-radius: 24px 24px 0 0;
    width: 100%;
    max-width: 480px;
    padding: 24px 20px 32px;
    box-shadow: 0 -8px 40px rgba(0,0,0,.15);
    animation: slideUp .25s ease-out;
}
@keyframes slideUp {
    from { transform: translateY(100%); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
.modal-handle {
    width: 40px; height: 4px;
    background: #e5e7eb;
    border-radius: 2px;
    margin: 0 auto 20px;
}
.modal-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 4px;
}
.modal-sub {
    font-size: .8rem;
    color: var(--sub);
    margin-bottom: 18px;
}
.modal-summary {
    background: #f9fafb;
    border-radius: 12px;
    padding: 12px 14px;
    margin-bottom: 18px;
    font-size: .82rem;
}
.modal-summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 4px;
}
.modal-summary-row:last-child { margin-bottom: 0; }
.modal-summary-lbl { color: var(--sub); }
.modal-summary-val { font-weight: 700; color: var(--text); }
.modal-input {
    width: 100%;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: 10px 12px;
    font-size: .88rem;
    color: var(--text);
    margin-bottom: 10px;
    outline: none;
    font-family: inherit;
    transition: border-color .15s;
}
.modal-input:focus { border-color: var(--green); }
.modal-input::placeholder { color: #adb5bd; }
.modal-actions {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 10px;
    margin-top: 4px;
}
.modal-cancel {
    padding: 13px;
    background: #f3f4f6;
    color: var(--sub);
    font-weight: 700;
    font-size: .88rem;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-family: inherit;
}
.modal-confirm {
    padding: 13px;
    background: linear-gradient(135deg, var(--green), var(--green-hi));
    color: #fff;
    font-weight: 700;
    font-size: .88rem;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-family: inherit;
    box-shadow: 0 3px 12px rgba(26,102,69,.25);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.modal-confirm:disabled { opacity: .6; }

/* ── Success banner ───────────────────────────────────────── */
.paid-banner {
    background: linear-gradient(135deg, #dcfce7, #f0fdf4);
    border: 1.5px solid #86efac;
    border-radius: 14px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 16px 20px;
}
.paid-banner-icon { font-size: 1.6rem; flex-shrink: 0; }
.paid-banner-text { font-size: .85rem; font-weight: 600; color: #14532d; }

@media (min-width: 480px) {
    .qr-img { max-width: 260px; max-height: 260px; }
    .modal-overlay { align-items: center; padding: 20px; }
    .modal-sheet { border-radius: 24px; max-width: 400px; }
}
</style>
</head>
<body>

<div class="card">

    {{-- App bar --}}
    <div class="app-bar">
        <div class="app-bar-icon">💰</div>
        <span class="app-bar-title">Payment Request</span>
        <span class="app-bar-id">#{{ $expense->id }}</span>
    </div>

    {{-- Amount --}}
    <div class="amount-hero">
        <p class="amount-lbl">Amount to Pay</p>
        <div class="amount-val">₹{{ number_format((float)$expense->amount, 2) }}</div>
        <p class="amount-title">{{ $expense->title }}</p>
    </div>

    {{-- Employee --}}
    @php
        $name = $expense->requester?->name ?? 'Employee';
        $initials = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $name), 0, 2))));
        $role = ucfirst($expense->requester?->role ?? 'employee');
    @endphp
    <div class="emp-row">
        <div class="emp-avatar">{{ $initials }}</div>
        <div>
            <div class="emp-name">{{ $name }}</div>
            <div class="emp-role">{{ $role }}</div>
        </div>
        <div class="emp-time">
            {{ $expense->created_at->format('d M') }}<br>
            {{ $expense->created_at->format('h:i A') }}
        </div>
    </div>

    {{-- QR / Receipt --}}
    <div class="qr-section">
        <p class="qr-lbl">Scan QR to Pay</p>
        @if($expense->qrUrl())
        <div class="qr-box" id="qrBox">
            <img src="{{ $expense->qrUrl() }}"
                 class="qr-img"
                 id="qrImage"
                 alt="Payment QR — {{ $expense->title }}"
                 loading="eager"
                 onerror="document.getElementById('qrBox').style.display='none';document.getElementById('qrError').style.display='block';">
        </div>
        <div id="qrError" style="display:none">
            <div class="no-qr">
                <div class="no-qr-icon">⚠️</div>
                <p style="font-weight:700;color:#374151;margin-bottom:6px">QR image unavailable</p>
                <p style="font-size:.78rem;margin-bottom:14px">The image could not load. Try opening the original link.</p>
                <a href="{{ $expense->qrUrl() }}"
                   target="_blank"
                   rel="noopener"
                   style="display:inline-flex;align-items:center;gap:6px;background:#1a6645;color:#fff;font-size:.8rem;font-weight:700;padding:9px 16px;border-radius:10px;text-decoration:none">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Open / Download QR
                </a>
            </div>
        </div>
        <p class="qr-hint">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
            Scan with any UPI app to pay
        </p>
        @else
        <div class="no-qr">
            <div class="no-qr-icon">🖼️</div>
            <p>No QR image attached to this request.</p>
        </div>
        @endif
    </div>

    {{-- Status --}}
    <div class="status-row">
        <span class="status-lbl">Request Status</span>
        <span class="status-pill pill-{{ $expense->status }}">
            {{ str_replace('_', ' ', $expense->status) }}
        </span>
    </div>

    {{-- Payment confirmation success banner --}}
    @if(session('paid_success'))
    <div class="paid-banner">
        <div class="paid-banner-icon">✅</div>
        <div class="paid-banner-text">Payment marked as received. Employee has been notified.</div>
    </div>
    @endif

    {{-- Action card — visible only to logged-in admin/manager --}}
    @auth
    @if(in_array(auth()->user()->role, ['admin','manager']) && !$expense->isSettled())
    <div class="action-card">
        <p class="action-card-title">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Payment Confirmation
        </p>
        <button type="button" class="action-btn" id="btnMarkPaid">
            <span class="action-btn-icon">💳</span>
            Mark as Paid
        </button>
    </div>
    @endif
    @endauth

    {{-- Footer --}}
    <div class="page-footer">
        <span class="brand">{{ config('app.name') }}</span> · Request #{{ $expense->id }} · {{ $expense->created_at->format('d M Y') }}
    </div>

</div>

{{-- Mark as Paid modal --}}
@auth
@if(in_array(auth()->user()->role, ['admin','manager']) && !$expense->isSettled())
<div class="modal-overlay" id="paidModal" role="dialog" aria-modal="true" aria-label="Confirm Payment">
    <div class="modal-sheet">
        <div class="modal-handle"></div>
        <p class="modal-title">Confirm Payment Received</p>
        <p class="modal-sub">This will mark the expense as paid and notify the employee.</p>

        <div class="modal-summary">
            <div class="modal-summary-row">
                <span class="modal-summary-lbl">Employee</span>
                <span class="modal-summary-val">{{ $expense->requester?->name ?? '—' }}</span>
            </div>
            <div class="modal-summary-row">
                <span class="modal-summary-lbl">Amount</span>
                <span class="modal-summary-val">₹{{ number_format((float)$expense->amount, 2) }}</span>
            </div>
            <div class="modal-summary-row">
                <span class="modal-summary-lbl">Title</span>
                <span class="modal-summary-val">{{ $expense->title }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('payment-request.mark-paid', $expense) }}" id="markPaidForm">
            @csrf
            <input type="text"
                   name="payment_reference"
                   class="modal-input"
                   placeholder="UTR / Reference No. (optional)"
                   maxlength="100">
            <textarea name="payment_note"
                      class="modal-input"
                      rows="2"
                      placeholder="Payment note (optional)"
                      maxlength="500"
                      style="resize:none"></textarea>
            <div class="modal-actions">
                <button type="button" class="modal-cancel" id="btnModalCancel">Cancel</button>
                <button type="submit" class="modal-confirm" id="btnModalConfirm">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Confirm Paid
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const modal  = document.getElementById('paidModal');
    const btnOpen   = document.getElementById('btnMarkPaid');
    const btnCancel = document.getElementById('btnModalCancel');
    const form   = document.getElementById('markPaidForm');
    const btnConfirm = document.getElementById('btnModalConfirm');

    btnOpen.addEventListener('click', () => modal.classList.add('open'));
    btnCancel.addEventListener('click', () => modal.classList.remove('open'));
    modal.addEventListener('click', e => { if (e.target === modal) modal.classList.remove('open'); });

    form.addEventListener('submit', () => {
        btnConfirm.disabled = true;
        btnConfirm.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Processing…';
    });
})();
</script>
@endif
@endauth

</body>
</html>
