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

<title>₹{{ number_format((float)$expense->amount, 2) }} — {{ $expense->title }}</title>

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

@media (min-width: 480px) {
    .qr-img { max-width: 260px; max-height: 260px; }
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
        <div class="qr-box">
            <img src="{{ $expense->qrUrl() }}"
                 class="qr-img"
                 alt="Payment QR — {{ $expense->title }}"
                 loading="eager">
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

    {{-- Footer --}}
    <div class="page-footer">
        <span class="brand">{{ config('app.name') }}</span> · Request #{{ $expense->id }} · {{ $expense->created_at->format('d M Y') }}
    </div>

</div>

</body>
</html>
