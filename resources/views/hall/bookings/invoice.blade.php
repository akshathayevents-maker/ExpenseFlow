<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Booking {{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }} &bull; Akshathay Mini Hall</title>
<style>
/* ─── Reset ─────────────────────────────────────────────────── */
* { box-sizing: border-box; margin: 0; padding: 0; }
html { font-size: 12.5px; }
body {
    background: #f0ebe1;
    color: #1a1a18;
    font-family: "DejaVu Sans", sans-serif;
    line-height: 1.5;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}
/* DomPDF does NOT inherit font-family into table/td/th — force it explicitly */
table, thead, tbody, tfoot, tr, th, td {
    font-family: "DejaVu Sans", sans-serif;
}

/* ─── Page shell ─────────────────────────────────────────────── */
.page {
    width: 760px;
    margin: 24px auto;
    background: #fffdf8;
}

/* ─── Table helpers (DomPDF safe layout) ────────────────────── */
.t    { display: table; width: 100%; border-collapse: collapse; }
.td   { display: table-cell; vertical-align: top; }
.tdm  { display: table-cell; vertical-align: middle; }
.t-right { text-align: right; }

/* ─── Header band — dark green luxury ───────────────────────── */
.hdr {
    background: #132d0f;
    padding: 0;
}
.hdr-inner {
    display: table;
    width: 100%;
    padding: 26px 32px 22px;
}
.hdr-brand { display: table-cell; vertical-align: middle; width: 60%; }
.hdr-meta  { display: table-cell; vertical-align: middle; text-align: right; }

.hdr-orb {
    background: #a8792c;
    border-radius: 5px;
    color: #fff;
    display: inline-block;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: .22em;
    padding: 3px 9px 3px;
    text-transform: uppercase;
    margin-bottom: 9px;
}
.hdr-name {
    color: #f5edda;
    font-size: 22px;
    font-weight: 800;
    letter-spacing: -0.02em;
    line-height: 1.1;
    margin-bottom: 3px;
}
.hdr-tagline {
    color: #a8792c;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: .22em;
    text-transform: uppercase;
    margin-bottom: 12px;
}
.hdr-addr {
    color: rgba(245,237,218,.52);
    font-size: 9.5px;
    line-height: 1.7;
}
.hdr-addr strong { color: rgba(245,237,218,.72); font-weight: 700; }

.hdr-ref {
    color: rgba(245,237,218,.45);
    font-size: 8px;
    font-weight: 700;
    letter-spacing: .14em;
    text-transform: uppercase;
    margin-bottom: 4px;
}
.hdr-num {
    color: #f5edda;
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -0.02em;
    line-height: 1;
    margin-bottom: 8px;
}
.hdr-dateline {
    color: rgba(245,237,218,.55);
    font-size: 9.5px;
    line-height: 1.75;
}

/* status badge */
.status-pill {
    border-radius: 20px;
    display: inline-block;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: .14em;
    margin-top: 6px;
    padding: 4px 11px;
    text-transform: uppercase;
}
.status-confirmed { background: rgba(15,123,95,.28); border: 1px solid rgba(15,200,130,.3); color: #5ee8a8; }
.status-completed { background: rgba(60,120,240,.28); border: 1px solid rgba(80,150,255,.3); color: #90c0ff; }
.status-cancelled { background: rgba(200,60,60,.28);  border: 1px solid rgba(255,100,90,.3);  color: #ffaaaa; }

.pay-pill {
    border-radius: 20px;
    display: inline-block;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: .14em;
    margin-left: 5px;
    padding: 4px 11px;
    text-transform: uppercase;
}
.pay-paid    { background: rgba(15,123,95,.22); border: 1px solid rgba(15,200,130,.25); color: #5ee8a8; }
.pay-partial { background: rgba(60,120,240,.22); border: 1px solid rgba(80,150,255,.25); color: #90c0ff; }
.pay-pending { background: rgba(180,140,30,.22); border: 1px solid rgba(230,190,60,.3);  color: #f0d060; }

/* ─── Gold accent rule ───────────────────────────────────────── */
.gold-rule { height: 3px; background: linear-gradient(to right, #a8792c, #d4a84b, #a8792c); }

/* ─── Hero summary band ─────────────────────────────────────── */
.hero {
    background: #fdf5e4;
    border-bottom: 1px solid #e8deca;
    padding: 28px 32px 24px;
    text-align: center;
}
.hero-eyebrow {
    color: #a8792c;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: .22em;
    text-transform: uppercase;
    margin-bottom: 6px;
}
.hero-customer {
    color: #132d0f;
    font-size: 20px;
    font-weight: 800;
    letter-spacing: -0.01em;
    line-height: 1.15;
    margin-bottom: 4px;
}
.hero-event {
    color: #6b6050;
    font-size: 10.5px;
    margin-bottom: 14px;
}
.hero-event strong { color: #132d0f; }
.hero-amount-label {
    color: #9c8a6a;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .15em;
    text-transform: uppercase;
    margin-bottom: 4px;
}
.hero-amount {
    color: #132d0f;
    font-size: 36px;
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1;
}
.hero-balance-note {
    display: inline-block;
    margin-top: 10px;
    font-size: 10px;
    color: #7a6040;
    border: 1px solid #d4b87a;
    border-radius: 20px;
    padding: 3px 12px;
    background: rgba(168,121,44,.07);
}

/* ─── Snapshot cards row ─────────────────────────────────────── */
.snap-grid {
    display: table;
    width: 100%;
    border-collapse: collapse;
    border-bottom: 1px solid #e8deca;
    background: #fffdf8;
}
.snap-cell {
    display: table-cell;
    border-right: 1px solid #e8deca;
    padding: 16px 20px;
    vertical-align: top;
    width: 25%;
}
.snap-cell:last-child { border-right: 0; }
.snap-icon {
    color: #a8792c;
    font-size: 11px;
    margin-bottom: 5px;
}
.snap-label {
    color: #9c8a6a;
    font-size: 7.5px;
    font-weight: 800;
    letter-spacing: .14em;
    text-transform: uppercase;
    margin-bottom: 3px;
}
.snap-value {
    color: #1a1a18;
    font-size: 10.5px;
    font-weight: 700;
    line-height: 1.3;
}
.snap-sub {
    color: #9c8a6a;
    font-size: 8.5px;
    margin-top: 2px;
}

/* ─── Section title ──────────────────────────────────────────── */
.sec { padding: 26px 32px; border-bottom: 1px solid #e8deca; }
.sec:last-of-type { border-bottom: 0; }
.sec-title {
    color: #132d0f;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: .22em;
    text-transform: uppercase;
    border-bottom: 1.5px solid #d4b87a;
    padding-bottom: 8px;
    margin-bottom: 18px;
}
.sec-title span {
    background: #d4b87a;
    color: #132d0f;
    border-radius: 3px;
    font-size: 7px;
    letter-spacing: .1em;
    padding: 1px 6px;
    margin-left: 8px;
    vertical-align: middle;
}

/* ─── Meal tags ──────────────────────────────────────────────── */
.meal-tag {
    background: rgba(168,121,44,.08);
    border: 1px solid rgba(168,121,44,.25);
    border-radius: 4px;
    color: #7a5818;
    display: inline-block;
    font-size: 8.5px;
    font-weight: 700;
    letter-spacing: .06em;
    margin: 0 4px 4px 0;
    padding: 3px 8px;
    text-transform: uppercase;
}
.no-meal { color: #aaa496; font-size: 10px; font-style: italic; }

/* ─── Cost breakdown table ───────────────────────────────────── */
.cost-table {
    width: 100%;
    border-collapse: collapse;
}
.cost-table thead th {
    background: #132d0f;
    color: rgba(245,237,218,.75);
    font-size: 8px;
    font-weight: 800;
    letter-spacing: .15em;
    padding: 9px 12px;
    text-align: left;
    text-transform: uppercase;
}
.cost-table thead th:last-child { text-align: right; }
.cost-table thead th.tc { text-align: center; }
.cost-table thead th.t-right { text-align: right; }

.cost-table tbody tr { background: #fffdf8; }
.cost-table tbody tr.alt { background: #faf6ed; }
.cost-table tbody tr.cat-row td {
    background: #f2ebe0;
    color: #9c8a6a;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: .16em;
    padding: 5px 12px;
    text-transform: uppercase;
    border-bottom: 1px solid #e8deca;
}
.cost-table tbody td {
    border-bottom: 1px solid #eee8db;
    font-size: 10.5px;
    padding: 11px 12px;
    vertical-align: top;
}
.cost-table tbody td.tc { text-align: center; }
.cost-table tbody td.t-right { text-align: right; font-weight: 700; }
.cost-item-name { font-weight: 700; color: #1a1a18; }
.cost-item-sub  { color: #9c8a6a; font-size: 9px; margin-top: 2px; }
.cost-table tfoot tr.subtotal td {
    background: #f2ebe0;
    border-top: 1.5px solid #d4b87a;
    border-bottom: 0;
    font-size: 11px;
    font-weight: 700;
    padding: 11px 12px;
    text-align: right;
    color: #5a3e10;
}
.cost-table tfoot tr.subtotal td:first-child { text-align: left; }
.cost-table tfoot tr.grand td {
    background: #132d0f;
    border-bottom: 0;
    color: #f5edda;
    font-size: 13px;
    font-weight: 800;
    padding: 13px 12px;
    text-align: right;
    letter-spacing: -0.01em;
}
.cost-table tfoot tr.grand td:first-child { text-align: left; }

/* ─── Payment summary panel ──────────────────────────────────── */
.pay-panel {
    display: table;
    width: 100%;
    border-collapse: collapse;
}
.pay-panel-col {
    display: table-cell;
    vertical-align: top;
    width: 50%;
}
.pay-panel-col:first-child { padding-right: 16px; }
.pay-panel-col:last-child  { padding-left: 16px; }

.pay-row {
    display: table;
    width: 100%;
    padding: 9px 0;
    border-bottom: 1px solid #e8deca;
}
.pay-row-label { display: table-cell; color: #6b6050; font-size: 10px; }
.pay-row-value { display: table-cell; text-align: right; font-size: 10px; font-weight: 700; color: #1a1a18; }
.pay-row.final .pay-row-label { font-size: 12px; font-weight: 800; color: #132d0f; border-bottom: 0; }
.pay-row.final .pay-row-value { font-size: 14px; font-weight: 800; color: #132d0f; }
.pay-row.settled .pay-row-label { color: #0a6640; }
.pay-row.settled .pay-row-value { color: #0a6640; }

.pay-status-card {
    border-radius: 8px;
    padding: 14px 16px;
    text-align: center;
}
.pay-status-card.--paid    { background: rgba(15,123,95,.08);  border: 1.5px solid rgba(15,123,95,.22); }
.pay-status-card.--partial { background: rgba(60,120,240,.07); border: 1.5px solid rgba(60,120,240,.2); }
.pay-status-card.--pending { background: rgba(180,140,30,.07); border: 1.5px solid rgba(180,140,30,.2); }

.pay-status-label {
    font-size: 7.5px;
    font-weight: 800;
    letter-spacing: .18em;
    text-transform: uppercase;
    margin-bottom: 5px;
}
.pay-status-label.--paid    { color: #0a6640; }
.pay-status-label.--partial { color: #1a3e8a; }
.pay-status-label.--pending { color: #7a5a18; }

.pay-status-amount {
    font-size: 22px;
    font-weight: 800;
    letter-spacing: -0.02em;
    line-height: 1;
}
.pay-status-amount.--paid    { color: #0a6640; }
.pay-status-amount.--partial { color: #1a3e8a; }
.pay-status-amount.--pending { color: #7a5a18; }

.pay-status-sub {
    font-size: 8.5px;
    margin-top: 4px;
}
.pay-status-sub.--paid    { color: #0a6640; }
.pay-status-sub.--partial { color: #6a7a9a; }
.pay-status-sub.--pending { color: #9a7a30; }

/* ─── Transaction history ────────────────────────────────────── */
.txn-table {
    width: 100%;
    border-collapse: collapse;
}
.txn-table thead th {
    background: #f2ebe0;
    border-bottom: 1px solid #e0d5c0;
    color: #9c8a6a;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: .12em;
    padding: 8px 10px;
    text-align: left;
    text-transform: uppercase;
}
.txn-table thead th:last-child { text-align: right; }
.txn-table tbody td {
    border-bottom: 1px solid #eee8db;
    color: #3d3933;
    font-size: 10px;
    padding: 9px 10px;
}
.txn-table tbody td:last-child { text-align: right; font-weight: 700; }
.txn-table tbody tr:last-child td { border-bottom: 0; }

/* ─── Notes ──────────────────────────────────────────────────── */
.notes-box {
    background: #fdf5e4;
    border: 1px solid #e8deca;
    border-left: 3px solid #a8792c;
    border-radius: 0 4px 4px 0;
    color: #5a4e3a;
    font-size: 10.5px;
    line-height: 1.7;
    padding: 12px 16px;
}

/* ─── Terms ──────────────────────────────────────────────────── */
.terms-grid {
    display: table;
    width: 100%;
    border-collapse: collapse;
}
.terms-col {
    display: table-cell;
    vertical-align: top;
    width: 50%;
    padding-right: 20px;
}
.terms-col:last-child { padding-right: 0; padding-left: 20px; }
.terms-item {
    font-size: 9px;
    color: #6b6050;
    line-height: 1.65;
    margin-bottom: 8px;
    padding-left: 12px;
    position: relative;
}
.terms-item::before {
    content: "•";
    color: #a8792c;
    position: absolute;
    left: 0;
    font-size: 10px;
}
.terms-item strong { color: #3a3228; font-weight: 700; }

/* ─── Signatures ─────────────────────────────────────────────── */
.sig-row {
    display: table;
    width: 100%;
    border-collapse: collapse;
    margin-top: 28px;
}
.sig-cell {
    display: table-cell;
    text-align: center;
    width: 50%;
    padding: 0 24px;
}
.sig-cell:first-child { border-right: 1px solid #e8deca; padding-left: 0; }
.sig-cell:last-child  { padding-right: 0; }
.sig-space {
    height: 42px;
    border-bottom: 1px solid #bfb5a5;
    margin-bottom: 8px;
}
.sig-name {
    color: #1a1a18;
    font-size: 10.5px;
    font-weight: 700;
}
.sig-role {
    color: #9c8a6a;
    font-size: 7.5px;
    font-weight: 800;
    letter-spacing: .14em;
    text-transform: uppercase;
    margin-top: 2px;
}

/* ─── Footer ─────────────────────────────────────────────────── */
.footer {
    background: #132d0f;
    padding: 18px 32px;
}
.footer-inner {
    display: table;
    width: 100%;
}
.footer-left  { display: table-cell; vertical-align: middle; }
.footer-right { display: table-cell; vertical-align: middle; text-align: right; }
.footer-brand {
    color: rgba(245,237,218,.85);
    font-size: 11.5px;
    font-weight: 800;
    letter-spacing: -0.01em;
}
.footer-contact {
    color: rgba(245,237,218,.4);
    font-size: 8.5px;
    line-height: 1.7;
    margin-top: 3px;
}
.footer-stamp {
    color: rgba(245,237,218,.35);
    font-size: 8px;
    line-height: 1.7;
    text-align: right;
}

/* ─── UPI Payment Box ────────────────────────────────────── */
.upi-box {
    background: #fdf5e4;
    border: 1.5px dashed #a8792c;
    border-radius: 6px;
    padding: 16px 20px;
}
.upi-title {
    color: #132d0f;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: .2em;
    text-transform: uppercase;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e8deca;
}
.upi-row {
    display: table;
    width: 100%;
    margin-bottom: 6px;
}
.upi-key {
    display: table-cell;
    color: #9c8a6a;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
    width: 130px;
    vertical-align: middle;
}
.upi-val {
    display: table-cell;
    color: #1a1a18;
    font-size: 11px;
    font-weight: 700;
    vertical-align: middle;
}
.upi-val.upi-amount {
    color: #132d0f;
    font-size: 15px;
    font-weight: 800;
}
.upi-note {
    margin-top: 10px;
    color: #9c8a6a;
    font-size: 8.5px;
    font-style: italic;
}

@media print {
    body { background: #fff; }
    .page { border: 0; margin: 0; width: 100%; }
    @page { size: A4 portrait; margin: 8mm 10mm; }
}
</style>
</head>
<body>
@php
    $rs          = "\u{20B9}";  /* PHP Unicode escape → raw UTF-8 bytes — DejaVu Sans glyph U+20B9 confirmed */
    $totalPaid   = $booking->total_paid;
    $balance     = max(0, $booking->balance_amount);
    $eventTypes  = \App\Models\HallBooking::eventTypes();
    $eventLabel  = $eventTypes[$booking->event_type] ?? \Illuminate\Support\Str::headline($booking->event_type);
    $start       = \Carbon\Carbon::parse($booking->start_time)->format('h:i A');
    $end         = \Carbon\Carbon::parse($booking->end_time)->format('h:i A');
    $bookingRef  = 'BK-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT);
    $mealList    = collect([
        'Breakfast' => $booking->has_breakfast,
        'Lunch'     => $booking->has_lunch,
        'Dinner'    => $booking->has_dinner,
    ])->filter()->keys();
    $hasLineItems = (float)$booking->hall_cost > 0
        || ($booking->mealPlan && (float)$booking->mealPlan->price_per_person > 0)
        || $booking->additionalServices->isNotEmpty();
    $statusClass = [
        'confirmed' => 'status-confirmed',
        'completed' => 'status-completed',
        'cancelled' => 'status-cancelled',
    ][$booking->status] ?? 'status-confirmed';
    $payClass = [
        'paid'    => 'pay-paid',
        'partial' => 'pay-partial',
        'pending' => 'pay-pending',
    ][$booking->payment_status] ?? 'pay-pending';
    $payLabel = [
        'paid'    => 'Fully Paid',
        'partial' => 'Partially Paid',
        'pending' => 'Payment Pending',
    ][$booking->payment_status] ?? 'Pending';
    $initials = strtoupper(collect(explode(' ', $booking->customer_name))->map(fn($w) => $w[0] ?? '')->take(2)->implode(''));
    $altRowIdx = 0;
@endphp

<div class="page">

    {{-- ══════════════════════════════════════════ --}}
    {{-- HEADER — dark green luxury band           --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="hdr">
        <div class="hdr-inner">
            <div class="hdr-brand">
                <div class="hdr-orb">Premium Event &amp; Banquet Hall</div>
                <div class="hdr-name">Akshathay Mini Hall</div>
                <div class="hdr-tagline">Coimbatore&rsquo;s Preferred Event Venue</div>
                <div class="hdr-addr">
                    <strong>144 Nanjundapuram Road,</strong> Coimbatore &ndash; 641036<br>
                    Tamil Nadu, India &nbsp;&bull;&nbsp; 9894594074 / 09789224440<br>
                    contact@akshathay.com
                </div>
            </div>
            <div class="hdr-meta">
                <div class="hdr-ref">Booking Reference</div>
                <div class="hdr-num">{{ $bookingRef }}</div>
                <div class="hdr-dateline">
                    Issued: {{ now()->format('d M Y, h:i A') }}<br>
                    Event: {{ $booking->booking_date->format('D, d M Y') }}
                </div>
                <div style="margin-top:8px">
                    <span class="status-pill {{ $statusClass }}">
                        {{ \App\Models\HallBooking::statuses()[$booking->status] ?? $booking->status }}
                    </span>
                    <span class="pay-pill {{ $payClass }}">
                        {{ \App\Models\HallBooking::paymentStatuses()[$booking->payment_status] ?? $booking->payment_status }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="gold-rule"></div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- HERO — customer + total value              --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="hero">
        <div class="hero-eyebrow">Booking Confirmation</div>
        <div class="hero-customer">{{ $booking->customer_name }}</div>
        <div class="hero-event">
            <strong>{{ $eventLabel }}</strong> &nbsp;&bull;&nbsp; {{ $booking->hall->name }} &nbsp;&bull;&nbsp;
            {{ $booking->booking_date->format('d M Y') }} &nbsp;&bull;&nbsp;
            {{ $start }}&ndash;{{ $end }}
        </div>
        <div class="hero-amount-label">Total Booking Value</div>
        <div class="hero-amount">{!! $rs !!}{{ number_format($booking->total_amount, 2) }}</div>
        @if($balance > 0)
            <div class="hero-balance-note">Balance Due: {!! $rs !!}{{ number_format($balance, 2) }}</div>
        @else
            <div class="hero-balance-note" style="border-color:#0a6640;color:#0a6640;background:rgba(15,123,95,.07)">
                &#10003; Fully Settled
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- SNAPSHOT CARDS — 4-up info grid            --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="snap-grid">
        <div class="snap-cell">
            <div class="snap-label">Customer</div>
            <div class="snap-value">{{ $booking->customer_name }}</div>
            <div class="snap-sub">{{ $booking->customer_mobile }}</div>
            @if($booking->customer_alt_mobile)
                <div class="snap-sub">{{ $booking->customer_alt_mobile }}</div>
            @endif
        </div>
        <div class="snap-cell">
            <div class="snap-label">Event</div>
            <div class="snap-value">{{ $eventLabel }}</div>
            <div class="snap-sub">{{ number_format($booking->number_of_people) }} guests</div>
        </div>
        <div class="snap-cell">
            <div class="snap-label">Venue &amp; Date</div>
            <div class="snap-value">{{ $booking->hall->name }}</div>
            <div class="snap-sub">{{ $booking->booking_date->format('D, d M Y') }}</div>
            <div class="snap-sub">{{ $start }} &ndash; {{ $end }}</div>
        </div>
        <div class="snap-cell">
            <div class="snap-label">Booked By</div>
            <div class="snap-value">{{ $booking->creator?->name ?? 'Reception' }}</div>
            <div class="snap-sub">{{ now()->format('d M Y') }}</div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- MEALS & CATERING                           --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="sec">
        <div class="sec-title">
            Meals &amp; Catering
            @if($mealList->isNotEmpty())
                <span>{{ $mealList->count() }} {{ $mealList->count() === 1 ? 'service' : 'services' }}</span>
            @endif
        </div>
        <div class="t">
            <div class="td" style="width:55%;padding-right:24px">
                @if($booking->mealPlan)
                    <div style="margin-bottom:6px">
                        <span style="color:#9c8a6a;font-size:8px;font-weight:800;letter-spacing:.12em;text-transform:uppercase">Package</span>
                    </div>
                    <div style="font-size:11.5px;font-weight:700;color:#1a1a18;margin-bottom:3px">{{ $booking->mealPlan->name }}</div>
                    @if((float)$booking->mealPlan->price_per_person > 0)
                        <div style="color:#6b6050;font-size:9.5px">
                            {!! $rs !!}{{ number_format($booking->mealPlan->price_per_person, 2) }}/person
                            &times; {{ number_format($booking->number_of_people) }} guests
                            = <strong style="color:#132d0f">{!! $rs !!}{{ number_format($booking->meal_cost, 2) }}</strong>
                        </div>
                    @endif
                @else
                    <span class="no-meal">No meal package selected</span>
                @endif
            </div>
            <div class="td" style="width:45%">
                <div style="margin-bottom:6px">
                    <span style="color:#9c8a6a;font-size:8px;font-weight:800;letter-spacing:.12em;text-transform:uppercase">Meals Included</span>
                </div>
                @if($mealList->isNotEmpty())
                    @foreach($mealList as $ml)
                        <span class="meal-tag">{{ $ml }}</span>
                    @endforeach
                @else
                    <span class="no-meal">No specific meal selections</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- COST BREAKDOWN TABLE                       --}}
    {{-- ══════════════════════════════════════════ --}}
    @if($hasLineItems)
    <div class="sec">
        <div class="sec-title">Cost Breakdown</div>
        <table class="cost-table">
            <thead>
                <tr>
                    <th style="width:46%">Description</th>
                    <th class="tc" style="width:12%">Qty</th>
                    <th class="t-right" style="width:20%">Unit Rate</th>
                    <th class="t-right" style="width:22%">Amount</th>
                </tr>
            </thead>
            <tbody>
                {{-- Hall rental --}}
                @if((float)$booking->hall_cost > 0)
                <tr class="cat-row"><td colspan="4">Hall</td></tr>
                @php $altRowIdx++ @endphp
                <tr class="{{ $altRowIdx % 2 === 0 ? 'alt' : '' }}">
                    <td>
                        <div class="cost-item-name">Hall Rental</div>
                        <div class="cost-item-sub">{{ $booking->hall->name }}</div>
                    </td>
                    <td class="tc">1</td>
                    <td class="t-right">{!! $rs !!}{{ number_format($booking->hall_cost, 2) }}</td>
                    <td class="t-right">{!! $rs !!}{{ number_format($booking->hall_cost, 2) }}</td>
                </tr>
                @endif

                {{-- Meal plan --}}
                @if($booking->mealPlan && (float)$booking->mealPlan->price_per_person > 0)
                <tr class="cat-row"><td colspan="4">Catering</td></tr>
                @php $altRowIdx++ @endphp
                <tr class="{{ $altRowIdx % 2 === 0 ? 'alt' : '' }}">
                    <td>
                        <div class="cost-item-name">{{ $booking->mealPlan->name }}</div>
                        <div class="cost-item-sub">
                            Meal package
                            @if($mealList->isNotEmpty())
                                &mdash; {{ $mealList->join(', ') }}
                            @endif
                        </div>
                    </td>
                    <td class="tc">{{ number_format($booking->number_of_people) }}</td>
                    <td class="t-right">{!! $rs !!}{{ number_format($booking->mealPlan->price_per_person, 2) }}</td>
                    <td class="t-right">{!! $rs !!}{{ number_format($booking->meal_cost, 2) }}</td>
                </tr>
                @endif

                {{-- Additional services --}}
                @if($booking->additionalServices->isNotEmpty())
                <tr class="cat-row"><td colspan="4">Additional Services</td></tr>
                @foreach($booking->additionalServices as $service)
                @php $altRowIdx++ @endphp
                <tr class="{{ $altRowIdx % 2 === 0 ? 'alt' : '' }}">
                    <td>
                        <div class="cost-item-name">{{ $service->service_name }}</div>
                        @if($service->description)
                            <div class="cost-item-sub">{{ $service->description }}</div>
                        @endif
                    </td>
                    <td class="tc">1</td>
                    <td class="t-right">{!! $rs !!}{{ number_format($service->amount, 2) }}</td>
                    <td class="t-right">{!! $rs !!}{{ number_format($service->amount, 2) }}</td>
                </tr>
                @endforeach
                @endif
            </tbody>
            <tfoot>
                @php
                    $subtotal = (float)$booking->hall_cost + (float)$booking->meal_cost + (float)$booking->services_total;
                @endphp
                @if(round($subtotal, 2) !== round((float)$booking->total_amount, 2))
                <tr class="subtotal">
                    <td colspan="3">Subtotal</td>
                    <td>{!! $rs !!}{{ number_format($subtotal, 2) }}</td>
                </tr>
                @endif
                <tr class="grand">
                    <td colspan="3">Total Booking Value</td>
                    <td>{!! $rs !!}{{ number_format($booking->total_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- ══════════════════════════════════════════ --}}
    {{-- PAYMENT SUMMARY                            --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="sec">
        <div class="sec-title">Payment Summary</div>
        <div class="pay-panel">
            <div class="pay-panel-col">
                <div class="pay-row">
                    <div class="pay-row-label">Total Booking Value</div>
                    <div class="pay-row-value">{!! $rs !!}{{ number_format($booking->total_amount, 2) }}</div>
                </div>
                <div class="pay-row">
                    <div class="pay-row-label">Advance Paid</div>
                    <div class="pay-row-value">{!! $rs !!}{{ number_format($booking->advance_amount, 2) }}</div>
                </div>
                <div class="pay-row">
                    <div class="pay-row-label">Total Received</div>
                    <div class="pay-row-value">{!! $rs !!}{{ number_format($totalPaid, 2) }}</div>
                </div>
                <div class="pay-row final {{ $balance <= 0 ? 'settled' : '' }}">
                    <div class="pay-row-label">{{ $balance > 0 ? 'Balance Due' : 'Fully Settled' }}</div>
                    <div class="pay-row-value">{!! $rs !!}{{ number_format($balance, 2) }}</div>
                </div>
            </div>
            <div class="pay-panel-col">
                <div class="pay-status-card --{{ $booking->payment_status }}">
                    <div class="pay-status-label --{{ $booking->payment_status }}">Payment Status</div>
                    <div class="pay-status-amount --{{ $booking->payment_status }}">{!! $rs !!}{{ number_format($balance > 0 ? $balance : $totalPaid, 2) }}</div>
                    <div class="pay-status-sub --{{ $booking->payment_status }}">{{ $payLabel }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- TRANSACTION HISTORY                        --}}
    {{-- ══════════════════════════════════════════ --}}
    @if($booking->payments->count())
    <div class="sec">
        <div class="sec-title">
            Transaction History
            <span>{{ $booking->payments->count() }} {{ $booking->payments->count() === 1 ? 'entry' : 'entries' }}</span>
        </div>
        <table class="txn-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($booking->payments->sortBy('paid_at') as $payment)
                <tr>
                    <td>{{ $payment->paid_at->format('d M Y') }}</td>
                    <td>{{ \App\Models\BookingPayment::types()[$payment->payment_type] ?? \Illuminate\Support\Str::headline($payment->payment_type) }}</td>
                    <td>{{ \App\Models\BookingPayment::methods()[$payment->payment_method] ?? \Illuminate\Support\Str::headline($payment->payment_method) }}</td>
                    <td>{{ $payment->reference_number ?: '&mdash;' }}</td>
                    <td>{!! $rs !!}{{ number_format($payment->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ══════════════════════════════════════════ --}}
    {{-- SPECIAL NOTES                              --}}
    {{-- ══════════════════════════════════════════ --}}
    @if($booking->notes)
    <div class="sec">
        <div class="sec-title">Special Notes &amp; Requirements</div>
        <div class="notes-box">{{ $booking->notes }}</div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════ --}}
    {{-- UPI PAYMENT INFO (balance > 0 only)        --}}
    {{-- ══════════════════════════════════════════ --}}
    @if($balance > 0)
    <div class="sec">
        <div class="sec-title">Pay Balance Due</div>
        <div class="upi-box">
            <div class="upi-title">UPI Payment Details</div>
            <div class="upi-row">
                <div class="upi-key">UPI ID</div>
                <div class="upi-val">9894594074@upi</div>
            </div>
            <div class="upi-row">
                <div class="upi-key">Account Name</div>
                <div class="upi-val">Akshathay Mini Hall</div>
            </div>
            <div class="upi-row">
                <div class="upi-key">Amount Due</div>
                <div class="upi-val upi-amount">{!! $rs !!}{{ number_format($balance, 2) }}</div>
            </div>
            <div class="upi-row">
                <div class="upi-key">Reference</div>
                <div class="upi-val">{{ $bookingRef }} &mdash; {{ $booking->customer_name }}</div>
            </div>
            <div class="upi-note">Please mention the booking reference {{ $bookingRef }} in the payment remarks. Send payment screenshot to 9894594074.</div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════ --}}
    {{-- TERMS & CONDITIONS                         --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="sec">
        <div class="sec-title">Terms &amp; Conditions</div>
        <div class="terms-grid">
            <div class="terms-col">
                <div class="terms-item"><strong>Advance:</strong> Booking confirmed only after advance payment received.</div>
                <div class="terms-item"><strong>Cancellation:</strong> Cancellations within 7 days may forfeit the advance. Contact us to discuss.</div>
                <div class="terms-item"><strong>Timing:</strong> Hall access begins 1 hour before event start. Overtime charged at actuals.</div>
            </div>
            <div class="terms-col">
                <div class="terms-item"><strong>Balance:</strong> Remaining balance due on or before event day.</div>
                <div class="terms-item"><strong>Damages:</strong> Customer liable for any damage to hall property.</div>
                <div class="terms-item"><strong>Support:</strong> 9894594074 / 09789224440 &nbsp;&bull;&nbsp; contact@akshathay.com</div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- SIGNATURES                                 --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="sec" style="border-bottom:0">
        <div style="text-align:center;margin-bottom:22px">
            <span style="color:#a8792c;font-size:9px;font-weight:800;letter-spacing:.18em;text-transform:uppercase">
                Thank you for choosing Akshathay Mini Hall
            </span>
        </div>
        <div class="sig-row">
            <div class="sig-cell">
                <div class="sig-space"></div>
                <div class="sig-name">{{ $booking->customer_name }}</div>
                <div class="sig-role">Customer Signature</div>
            </div>
            <div class="sig-cell">
                <div class="sig-space"></div>
                <div class="sig-name">Akshathay Mini Hall</div>
                <div class="sig-role">Authorized Signatory</div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- FOOTER — dark green band                   --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="gold-rule"></div>
    <div class="footer">
        <div class="footer-inner">
            <div class="footer-left">
                <div class="footer-brand">Akshathay Mini Hall</div>
                <div class="footer-contact">
                    144 Nanjundapuram Road, Coimbatore &ndash; 641036, Tamil Nadu<br>
                    9894594074 &nbsp;&bull;&nbsp; 09789224440 &nbsp;&bull;&nbsp; contact@akshathay.com
                </div>
            </div>
            <div class="footer-right">
                <div class="footer-stamp">
                    {{ $bookingRef }}<br>
                    Generated {{ now()->format('d M Y, h:i A') }}<br>
                    Akshathay Hall Booking System
                </div>
            </div>
        </div>
    </div>

</div>{{-- /page --}}

<script>
if (window.location.search.indexOf('print=1') !== -1) {
    window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 350); });
}
</script>
</body>
</html>
