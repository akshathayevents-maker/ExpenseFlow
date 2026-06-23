<x-admin-layout title="Booking #{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}">
@push('styles')
<style>
/* ── Booking Show — bs-* ─────────────────────────────────────────── */
:root {
    --bs-gold:      #a0763a;
    --bs-gold-hi:   #b8882a;
    --bs-ink:       #131110;
    --bs-sub:       #50473f;
    --bs-muted:     #8a827a;
    --bs-faint:     #bab3aa;
    --bs-border:    rgba(100,82,42,.12);
    --bs-border-s:  rgba(100,82,42,.24);
    --bs-surface:   #ffffff;
    --bs-cream:     #faf8f3;
    --bs-r:         14px;
    --bs-r-sm:      10px;
    --bs-shadow:    0 1px 3px rgba(18,14,8,.06), 0 3px 12px rgba(18,14,8,.05);

    --bs-confirmed: #16a34a;
    --bs-completed: #2563eb;
    --bs-cancelled: #dc2626;
    --bs-pending:   #f59e0b;
}

*, *::before, *::after { box-sizing: border-box; }

.bs-wrap {
    max-width: 860px;
    margin: 0 auto;
    padding-bottom: 110px;
}

/* ── Back nav ────────────────────────────────────────────────────── */
.bs-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--bs-muted);
    font-size: .82rem;
    font-weight: 600;
    text-decoration: none;
    margin-bottom: 14px;
    transition: color .14s;
}
.bs-back:hover { color: var(--bs-gold); text-decoration: none; }

/* ── Hero card ───────────────────────────────────────────────────── */
.bs-hero {
    background: linear-gradient(145deg, #181410 0%, #1e1a14 60%, #141210 100%);
    border-radius: 18px;
    padding: 20px;
    margin-bottom: 12px;
    position: relative;
    overflow: hidden;
}
.bs-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
}
.bs-hero.--confirmed::before  { background: var(--bs-confirmed); }
.bs-hero.--completed::before  { background: var(--bs-completed); }
.bs-hero.--cancelled::before  { background: var(--bs-cancelled); }

.bs-hero-ref {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: var(--bs-gold);
    margin-bottom: 4px;
}
.bs-hero-name {
    font-size: 1.5rem;
    font-weight: 900;
    color: #fff;
    line-height: 1.15;
    margin-bottom: 6px;
    letter-spacing: -.02em;
}
.bs-hero-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 14px;
}
.bs-hchip {
    font-size: .68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .06em;
    padding: 3px 9px;
    border-radius: 100px;
    border: 1px solid;
    white-space: nowrap;
}
.bs-hchip.--confirmed { background: rgba(22,163,74,.18); border-color: rgba(22,163,74,.4); color: #4ade80; }
.bs-hchip.--completed { background: rgba(37,99,235,.18); border-color: rgba(37,99,235,.4); color: #93c5fd; }
.bs-hchip.--cancelled { background: rgba(220,38,38,.18); border-color: rgba(220,38,38,.4); color: #fca5a5; }
.bs-hchip.--pending   { background: rgba(245,158,11,.15); border-color: rgba(245,158,11,.35); color: #fcd34d; }
.bs-hchip.--partial   { background: rgba(37,99,235,.15); border-color: rgba(37,99,235,.35); color: #93c5fd; }
.bs-hchip.--paid      { background: rgba(22,163,74,.18); border-color: rgba(22,163,74,.4); color: #4ade80; }
.bs-hchip.--neutral   { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.15); color: rgba(255,255,255,.7); }

.bs-hero-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 6px 14px;
    margin-bottom: 16px;
}
.bs-hero-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: .78rem;
    color: rgba(255,255,255,.65);
    white-space: nowrap;
}
.bs-hero-meta-item i { color: var(--bs-gold); font-size: .75rem; }

.bs-hero-contact {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.bs-hero-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    height: 44px;
    padding: 0 18px;
    border-radius: var(--bs-r-sm);
    font-size: .83rem;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    border: none;
    transition: all .14s;
    white-space: nowrap;
    flex: 1;
    min-width: 120px;
}
.bs-hero-btn.--call { background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2); color: #fff; }
.bs-hero-btn.--call:hover { background: rgba(255,255,255,.18); color: #fff; text-decoration: none; }
.bs-hero-btn.--wa   { background: #25d366; color: #fff; }
.bs-hero-btn.--wa:hover { background: #1ebe5c; color: #fff; text-decoration: none; }

/* ── KPI strip ───────────────────────────────────────────────────── */
.bs-kpis {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0;
    background: var(--bs-surface);
    border: 1.5px solid var(--bs-border);
    border-radius: var(--bs-r);
    overflow: hidden;
    margin-bottom: 12px;
    box-shadow: var(--bs-shadow);
}
.bs-kpi {
    padding: 14px 16px;
    text-align: center;
    position: relative;
}
.bs-kpi + .bs-kpi::before {
    content: '';
    position: absolute;
    left: 0; top: 20%; bottom: 20%;
    width: 1px;
    background: var(--bs-border);
}
.bs-kpi-label {
    font-size: .66rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--bs-faint);
    margin-bottom: 5px;
}
.bs-kpi-val {
    font-size: 1.2rem;
    font-weight: 900;
    color: var(--bs-ink);
    letter-spacing: -.02em;
    line-height: 1;
}
.bs-kpi-val.--due { color: var(--bs-cancelled); }
.bs-kpi-val.--paid { color: var(--bs-confirmed); }
.bs-kpi-sub {
    font-size: .67rem;
    color: var(--bs-faint);
    margin-top: 3px;
}

/* Progress bar under KPIs */
.bs-progress-wrap {
    background: var(--bs-surface);
    border: 1.5px solid var(--bs-border);
    border-top: none;
    border-radius: 0 0 var(--bs-r) var(--bs-r);
    padding: 0 14px 10px;
    margin-top: -12px;
    margin-bottom: 12px;
}
.bs-progress-bar-bg {
    height: 5px;
    background: var(--bs-cream);
    border-radius: 3px;
    overflow: hidden;
}
.bs-progress-bar-fill {
    height: 100%;
    border-radius: 3px;
    background: linear-gradient(90deg, var(--bs-confirmed), #22c55e);
    transition: width .4s;
}

/* ── Action bar ──────────────────────────────────────────────────── */
.bs-actions {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-bottom: 16px;
}
.bs-act {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    height: 60px;
    border-radius: var(--bs-r-sm);
    background: var(--bs-surface);
    border: 1.5px solid var(--bs-border);
    color: var(--bs-sub);
    font-size: .7rem;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    transition: all .14s;
    padding: 0;
    box-shadow: var(--bs-shadow);
}
.bs-act i { font-size: 1.1rem; color: var(--bs-gold); }
.bs-act:hover { border-color: var(--bs-gold); color: var(--bs-gold); text-decoration: none; }
.bs-act:hover i { color: var(--bs-gold); }

/* ── Cards ───────────────────────────────────────────────────────── */
.bs-card {
    background: var(--bs-surface);
    border: 1.5px solid var(--bs-border);
    border-radius: var(--bs-r);
    box-shadow: var(--bs-shadow);
    margin-bottom: 12px;
    overflow: hidden;
}
.bs-card-hdr {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 13px 16px;
    border-bottom: 1px solid var(--bs-border);
    background: var(--bs-cream);
}
.bs-card-title {
    font-size: .82rem;
    font-weight: 800;
    color: var(--bs-ink);
    text-transform: uppercase;
    letter-spacing: .05em;
}
.bs-card-aside {
    font-size: .72rem;
    color: var(--bs-muted);
    font-weight: 600;
}
.bs-card-body { padding: 0; }

/* ── Field rows ──────────────────────────────────────────────────── */
.bs-fields {
    display: flex;
    flex-direction: column;
}
.bs-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 11px 16px;
    border-bottom: 1px solid var(--bs-border);
    min-height: 46px;
}
.bs-row:last-child { border-bottom: none; }
.bs-row-label {
    font-size: .75rem;
    color: var(--bs-muted);
    font-weight: 600;
    flex-shrink: 0;
    min-width: 100px;
}
.bs-row-val {
    font-size: .85rem;
    color: var(--bs-ink);
    font-weight: 700;
    text-align: right;
    flex: 1;
}
.bs-row-val.--muted { color: var(--bs-muted); font-weight: 400; }
.bs-row-val a { color: var(--bs-gold); text-decoration: none; }
.bs-row-val a:hover { text-decoration: underline; }

/* Meal chips inline */
.bs-meal-chips { display: inline-flex; gap: 5px; flex-wrap: wrap; justify-content: flex-end; }
.bs-meal-chip {
    background: var(--bs-cream);
    border: 1px solid var(--bs-border);
    border-radius: 6px;
    padding: 2px 8px;
    font-size: .72rem;
    font-weight: 700;
    color: var(--bs-sub);
}

/* ── Payment timeline ────────────────────────────────────────────── */
.bs-timeline { padding: 14px 16px; display: flex; flex-direction: column; gap: 0; }
.bs-tl-empty {
    padding: 20px 16px;
    text-align: center;
    font-size: .82rem;
    color: var(--bs-faint);
}
.bs-tl-item {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    padding: 10px 0;
    border-bottom: 1px solid var(--bs-border);
    position: relative;
}
.bs-tl-item:last-child { border-bottom: none; }
.bs-tl-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--bs-gold);
    flex-shrink: 0;
    margin-top: 6px;
}
.bs-tl-date {
    font-size: .72rem;
    color: var(--bs-muted);
    font-weight: 700;
    flex-shrink: 0;
    width: 52px;
    line-height: 1.35;
    text-align: right;
}
.bs-tl-body { flex: 1; min-width: 0; }
.bs-tl-type {
    font-size: .83rem;
    font-weight: 800;
    color: var(--bs-ink);
}
.bs-tl-meta {
    font-size: .72rem;
    color: var(--bs-muted);
    margin-top: 2px;
    line-height: 1.4;
}
.bs-tl-amount {
    font-size: .92rem;
    font-weight: 900;
    color: var(--bs-confirmed);
    flex-shrink: 0;
}

/* ── Payment form ────────────────────────────────────────────────── */
.bs-form { padding: 16px; }
.bs-form-group { margin-bottom: 14px; }
.bs-form-label {
    display: block;
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--bs-muted);
    margin-bottom: 5px;
}
.bs-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.bs-input,
.bs-select {
    width: 100%;
    height: 46px;
    padding: 0 12px;
    border: 1.5px solid var(--bs-border);
    border-radius: var(--bs-r-sm);
    font-size: .88rem;
    color: var(--bs-ink);
    background: var(--bs-surface);
    outline: none;
    appearance: none;
    -webkit-appearance: none;
}
.bs-input:focus, .bs-select:focus { border-color: var(--bs-gold); }
.bs-form-error { font-size: .72rem; color: #dc2626; margin-top: 4px; }
.bs-submit {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    height: 50px;
    border-radius: var(--bs-r-sm);
    background: var(--bs-ink);
    color: #fff;
    font-size: .9rem;
    font-weight: 800;
    border: none;
    cursor: pointer;
    transition: background .14s;
    margin-top: 16px;
}
.bs-submit:hover { background: #2d2820; }

/* ── Follow-up card ──────────────────────────────────────────────── */
.bs-followup {
    background: linear-gradient(145deg, #fffdf9, #fdf8ef);
    border: 1.5px solid rgba(160,114,58,.25);
    border-radius: var(--bs-r);
    padding: 16px;
    margin-bottom: 12px;
    box-shadow: 0 2px 10px rgba(160,114,58,.08);
}
.bs-followup-hdr {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}
.bs-followup-ico { font-size: 1.3rem; }
.bs-followup-title { font-size: .88rem; font-weight: 800; color: var(--bs-ink); }
.bs-followup-sub { font-size: .73rem; color: var(--bs-muted); margin-top: 1px; }
.bs-followup-sent {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: .74rem;
    font-weight: 600;
    color: #16a34a;
    background: rgba(22,163,74,.08);
    border: 1px solid rgba(22,163,74,.2);
    border-radius: 8px;
    padding: 7px 10px;
    margin-bottom: 10px;
}
.bs-followup-flash {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: .74rem;
    font-weight: 600;
    color: var(--bs-gold);
    background: rgba(160,114,58,.08);
    border: 1px solid rgba(160,114,58,.2);
    border-radius: 8px;
    padding: 7px 10px;
    margin-bottom: 10px;
}
.bs-wa-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    height: 50px;
    background: #25d366;
    color: #fff;
    border: none;
    border-radius: var(--bs-r-sm);
    font-size: .9rem;
    font-weight: 800;
    cursor: pointer;
    text-decoration: none;
    box-shadow: 0 3px 12px rgba(37,211,102,.28);
    transition: background .13s, box-shadow .13s;
}
.bs-wa-btn:hover { background: #1ebe5c; color: #fff; text-decoration: none; box-shadow: 0 5px 18px rgba(37,211,102,.38); }

/* ── Notes ───────────────────────────────────────────────────────── */
.bs-notes {
    padding: 14px 16px;
    font-size: .84rem;
    color: var(--bs-sub);
    line-height: 1.6;
}
.bs-notes-empty { color: var(--bs-faint); font-style: italic; }

/* ── Danger zone ─────────────────────────────────────────────────── */
.bs-delete-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    width: 100%;
    height: 46px;
    border-radius: var(--bs-r-sm);
    background: none;
    border: 1.5px solid rgba(220,38,38,.3);
    color: #dc2626;
    font-size: .82rem;
    font-weight: 700;
    cursor: pointer;
    padding: 0 16px;
    transition: all .14s;
    margin-top: 8px;
}
.bs-delete-btn:hover { background: rgba(220,38,38,.06); border-color: #dc2626; }

/* ── Sticky bottom payment bar ───────────────────────────────────── */
.bs-sticky-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(255,255,255,.96);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-top: 1.5px solid var(--bs-border);
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    z-index: 400;
    box-shadow: 0 -4px 20px rgba(18,14,8,.08);
}
.bs-sticky-due {
    flex: 1;
}
.bs-sticky-due-label {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--bs-muted);
}
.bs-sticky-due-val {
    font-size: 1.15rem;
    font-weight: 900;
    color: var(--bs-cancelled);
    letter-spacing: -.01em;
}
.bs-sticky-btn {
    display: flex;
    align-items: center;
    gap: 7px;
    height: 48px;
    padding: 0 22px;
    background: var(--bs-ink);
    color: #fff;
    border-radius: var(--bs-r-sm);
    font-size: .88rem;
    font-weight: 800;
    text-decoration: none;
    border: none;
    transition: background .14s;
    white-space: nowrap;
}
.bs-sticky-btn:hover { background: #2d2820; color: #fff; text-decoration: none; }

/* ── Flash messages ──────────────────────────────────────────────── */
.bs-flash {
    display: flex;
    align-items: center;
    gap: 8px;
    border-radius: var(--bs-r-sm);
    font-size: .82rem;
    font-weight: 600;
    padding: 10px 14px;
    margin-bottom: 12px;
}
.bs-flash.--success { background: rgba(22,163,74,.07); border: 1px solid rgba(22,163,74,.18); color: #0A5C40; }
.bs-flash.--error   { background: rgba(220,38,38,.07); border: 1px solid rgba(220,38,38,.18); color: #8B2020; }
.bs-flash.--info    { background: rgba(160,114,58,.07); border: 1px solid rgba(160,114,58,.2);  color: #6b4a12; }

@media (max-width: 480px) {
    .bs-hero-name { font-size: 1.25rem; }
    .bs-kpi-val   { font-size: 1rem; }
    .bs-actions   { grid-template-columns: repeat(4, 1fr); gap: 6px; }
    .bs-act       { height: 54px; font-size: .64rem; }
    .bs-act i     { font-size: .95rem; }
    .bs-form-row  { grid-template-columns: 1fr; }
}
</style>
@endpush

@php
    $totalPaid   = $booking->total_paid;
    $balance     = max(0, $booking->balance_amount);
    $paidPct     = (float) $booking->total_amount > 0
                    ? min(100, round(($totalPaid / (float) $booking->total_amount) * 100))
                    : 0;
    $eventTypes  = \App\Models\HallBooking::eventTypes();
    $eventLabel  = $eventTypes[$booking->event_type] ?? Str::headline($booking->event_type);
    $start       = \Carbon\Carbon::parse($booking->start_time);
    $end         = \Carbon\Carbon::parse($booking->end_time);
    $dur         = $start->diff($end);
    $durLabel    = trim(($dur->h ? "{$dur->h}h " : '') . ($dur->i ? "{$dur->i}m" : '')) ?: '—';
    $bookingRef  = '#' . str_pad($booking->id, 4, '0', STR_PAD_LEFT);
    $cleanMobile = preg_replace('/\D/', '', $booking->customer_mobile ?? '');
    $isLive      = now()->between($start, $end);

    $mealLabels  = array_filter([
        $booking->has_breakfast ? 'Breakfast' : null,
        $booking->has_lunch     ? 'Lunch'     : null,
        $booking->has_dinner    ? 'Dinner'    : null,
    ]);
    $mealStr = implode(', ', $mealLabels) ?: '—';

    $locationLabel = $booking->location_label;

    // Status/payment chip classes
    $statusChipClass  = ['confirmed' => '--confirmed', 'completed' => '--completed', 'cancelled' => '--cancelled'][$booking->status] ?? '--neutral';
    $payChipClass     = ['pending' => '--pending', 'partial' => '--partial', 'paid' => '--paid'][$booking->payment_status] ?? '--neutral';
    $statusLabel      = \App\Models\HallBooking::statuses()[$booking->status] ?? Str::headline($booking->status);
    $payLabel         = \App\Models\HallBooking::paymentStatuses()[$booking->payment_status] ?? Str::headline($booking->payment_status);

    // WA share message
    $waMessage = ($isLive ? "🔴 *Function Currently Live*" : "🎉 *Upcoming Function Alert*") . "\n\n"
        . ($booking->isFoodOnly() ? "🍽️ Location: {$locationLabel}\n" : "🏛️ Hall: {$locationLabel}\n")
        . "🎊 Event: {$eventLabel}\n"
        . "👤 Customer: {$booking->customer_name}\n"
        . "📞 Contact: {$booking->customer_mobile}\n\n"
        . "📅 Date: {$booking->booking_date->format('d M Y')}\n"
        . "⏰ Time: {$start->format('h:i A')} – {$end->format('h:i A')}\n\n"
        . "👥 Guests: " . number_format($booking->number_of_people) . "\n"
        . ($booking->mealPlan ? "🍽️ Meal Plan: {$booking->mealPlan->name}\n" : "")
        . "\n💰 Payment:\n"
        . "₹" . number_format($totalPaid) . " Paid\n"
        . ($balance > 0 ? "₹" . number_format($balance) . " Pending\n" : "Fully Settled\n")
        . "\n━━━━━━━━━━━━━━━\n"
        . "📌 Kitchen: {$mealStr} – " . number_format($booking->number_of_people) . " Covers\n"
        . "\n📄 Invoice: " . route('hall.bookings.invoice', $booking) . "\n"
        . "Shared from ExpenseFlow Hall Operations";
    $waShareUrl  = 'https://wa.me/?text=' . rawurlencode($waMessage);
    $progressPct = $paidPct . '%';

    // Follow-up
    $fuReviewUrl = 'https://share.google/bBrlCMCM0fpojB4TE';
    $fuInstaUrl  = 'https://www.instagram.com/akshathay_events/';
    $fuMsg = "🙏 Dear Customer,\n\n"
        . "✨ Thank you for choosing *Akshathay Events* for your special occasion!\n\n"
        . "🍽️ We sincerely hope that you and your guests enjoyed our food and service.\n\n"
        . "💬 Your valuable feedback means a lot to us.\n\n"
        . "⭐ *Google Review:*\n{$fuReviewUrl}\n\n"
        . "📸 *Instagram:*\n{$fuInstaUrl}\n\n"
        . "🎊 Thank you once again for your trust and support!\n\n"
        . "Warm regards,\n*Akshathay Events* 🌟";
    $fuWaUrl = $cleanMobile
        ? 'https://wa.me/91' . $cleanMobile . '?text=' . rawurlencode($fuMsg)
        : 'https://wa.me/?text=' . rawurlencode($fuMsg);

    $directCallUrl = 'tel:' . $booking->customer_mobile;
    $directWaUrl   = 'https://wa.me/91' . $cleanMobile;
@endphp

<div class="bs-wrap">

    {{-- Back --}}
    <a href="{{ route('hall.bookings.index') }}" class="bs-back">
        <i class="bi bi-arrow-left"></i> Hall Bookings
    </a>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="bs-flash --success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
    @endif
    @if(session('followup_success'))
        <div class="bs-flash --info"><i class="bi bi-check-circle-fill"></i> {{ session('followup_success') }}</div>
    @endif
    @if(session('error') || $errors->any())
        <div class="bs-flash --error"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') ?? $errors->first() }}</div>
    @endif

    {{-- ── Hero card ──────────────────────────────────────────────── --}}
    <div class="bs-hero --{{ $booking->status }}">
        <div class="bs-hero-ref">{{ $bookingRef }} · {{ $booking->booking_date->format('d M Y') }}</div>
        <div class="bs-hero-name">{{ $booking->customer_name }}</div>

        <div class="bs-hero-chips">
            <span class="bs-hchip {{ $statusChipClass }}">{{ $statusLabel }}</span>
            <span class="bs-hchip {{ $payChipClass }}">{{ $payLabel }}</span>
            <span class="bs-hchip --neutral">{{ \App\Models\HallBooking::bookingTypes()[$booking->booking_type] ?? $booking->booking_type }}</span>
            @if($isLive)
                <span class="bs-hchip --pending">🔴 Live now</span>
            @endif
        </div>

        <div class="bs-hero-meta">
            <span class="bs-hero-meta-item">
                <i class="bi {{ $booking->isFoodOnly() ? 'bi-cup-hot' : 'bi-building' }}"></i>
                {{ $locationLabel }}
            </span>
            <span class="bs-hero-meta-item">
                <i class="bi bi-clock"></i>
                {{ $start->format('h:i A') }} – {{ $end->format('h:i A') }}
            </span>
            <span class="bs-hero-meta-item">
                <i class="bi bi-people-fill"></i>
                {{ number_format($booking->number_of_people) }} guests
            </span>
            <span class="bs-hero-meta-item">
                <i class="bi bi-egg-fried"></i>
                {{ $mealStr }}
            </span>
            <span class="bs-hero-meta-item">
                <i class="bi bi-tag"></i>
                {{ $eventLabel }}
            </span>
        </div>

        <div class="bs-hero-contact">
            <a href="{{ $directCallUrl }}" class="bs-hero-btn --call">
                <i class="bi bi-telephone-fill"></i> {{ $booking->customer_mobile }}
            </a>
            <a href="{{ $directWaUrl }}" target="_blank" rel="noopener" class="bs-hero-btn --wa">
                <i class="bi bi-whatsapp"></i> WhatsApp
            </a>
        </div>
    </div>

    {{-- ── KPI strip ───────────────────────────────────────────────── --}}
    <div class="bs-kpis">
        <div class="bs-kpi">
            <div class="bs-kpi-label">Total</div>
            <div class="bs-kpi-val">₹{{ number_format($booking->total_amount) }}</div>
            <div class="bs-kpi-sub">booking value</div>
        </div>
        <div class="bs-kpi">
            <div class="bs-kpi-label">Collected</div>
            <div class="bs-kpi-val {{ $paidPct >= 100 ? '--paid' : '' }}">₹{{ number_format($totalPaid) }}</div>
            <div class="bs-kpi-sub">{{ $paidPct }}% received</div>
        </div>
        <div class="bs-kpi">
            <div class="bs-kpi-label">Due</div>
            <div class="bs-kpi-val {{ $balance > 0 ? '--due' : '--paid' }}">₹{{ number_format($balance) }}</div>
            <div class="bs-kpi-sub">{{ $balance > 0 ? 'pending' : 'settled' }}</div>
        </div>
    </div>
    @if($paidPct > 0)
    <div class="bs-progress-wrap">
        <div class="bs-progress-bar-bg">
            <div class="bs-progress-bar-fill" style="width:{{ $progressPct }}"></div>
        </div>
    </div>
    @endif

    {{-- ── Action bar ──────────────────────────────────────────────── --}}
    <div class="bs-actions">
        <a href="{{ route('hall.bookings.invoice', $booking) }}?print=1" target="_blank" class="bs-act">
            <i class="bi bi-printer"></i> Print
        </a>
        <a href="{{ route('hall.bookings.invoice.pdf', $booking) }}" class="bs-act">
            <i class="bi bi-file-earmark-pdf"></i> PDF
        </a>
        <a href="{{ $waShareUrl }}" target="_blank" rel="noopener" class="bs-act">
            <i class="bi bi-share"></i> Share
        </a>
        <a href="{{ route('hall.bookings.edit', $booking) }}" class="bs-act">
            <i class="bi bi-pencil"></i> Edit
        </a>
    </div>

    {{-- ── Event Details card (merged) ────────────────────────────── --}}
    <div class="bs-card">
        <div class="bs-card-hdr">
            <span class="bs-card-title">Event Details</span>
            <span class="bs-card-aside">{{ $bookingRef }}</span>
        </div>
        <div class="bs-card-body">
            <div class="bs-fields">
                <div class="bs-row">
                    <span class="bs-row-label">{{ $booking->isFoodOnly() ? 'Location' : 'Venue' }}</span>
                    <span class="bs-row-val">{{ $locationLabel }}</span>
                </div>
                <div class="bs-row">
                    <span class="bs-row-label">Date</span>
                    <span class="bs-row-val">{{ $booking->booking_date->format('l, d M Y') }}</span>
                </div>
                <div class="bs-row">
                    <span class="bs-row-label">Time</span>
                    <span class="bs-row-val">{{ $start->format('h:i A') }} – {{ $end->format('h:i A') }} ({{ $durLabel }})</span>
                </div>
                <div class="bs-row">
                    <span class="bs-row-label">Event Type</span>
                    <span class="bs-row-val">{{ $eventLabel }}</span>
                </div>
                <div class="bs-row">
                    <span class="bs-row-label">Guests</span>
                    <span class="bs-row-val">{{ number_format($booking->number_of_people) }}</span>
                </div>
                @unless($booking->isHallOnly())
                <div class="bs-row">
                    <span class="bs-row-label">Meals</span>
                    <div class="bs-row-val">
                        @if(!empty($mealLabels))
                            <div class="bs-meal-chips">
                                @foreach($mealLabels as $m)
                                    <span class="bs-meal-chip">{{ $m }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="--muted">None selected</span>
                        @endif
                    </div>
                </div>
                @if($booking->mealPlan)
                <div class="bs-row">
                    <span class="bs-row-label">Meal Plan</span>
                    <span class="bs-row-val">{{ $booking->mealPlan->name }}@if($booking->mealPlan->price_per_person) · ₹{{ number_format($booking->mealPlan->price_per_person, 2) }}/person @endif</span>
                </div>
                @endif
                @endunless
                <div class="bs-row">
                    <span class="bs-row-label">Alt. Contact</span>
                    <span class="bs-row-val {{ $booking->customer_alt_mobile ? '' : '--muted' }}">
                        @if($booking->customer_alt_mobile)
                            <a href="tel:{{ $booking->customer_alt_mobile }}">{{ $booking->customer_alt_mobile }}</a>
                        @else
                            Not provided
                        @endif
                    </span>
                </div>
                <div class="bs-row">
                    <span class="bs-row-label">Advance Paid</span>
                    <span class="bs-row-val">₹{{ number_format($booking->advance_amount) }}</span>
                </div>
                <div class="bs-row">
                    <span class="bs-row-label">Created By</span>
                    <span class="bs-row-val --muted">{{ $booking->creator?->name ?? 'System' }} · {{ $booking->created_at->format('d M Y') }}</span>
                </div>
                @if($booking->notes)
                <div class="bs-row" style="align-items:flex-start">
                    <span class="bs-row-label" style="padding-top:2px">Notes</span>
                    <span class="bs-row-val" style="text-align:left;line-height:1.5;word-break:break-word">{{ $booking->notes }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Additional Services ─────────────────────────────────────── --}}
    @if($booking->additionalServices->isNotEmpty())
    <div class="bs-card">
        <div class="bs-card-hdr">
            <span class="bs-card-title">Additional Services</span>
            <span class="bs-card-aside">{{ $booking->additionalServices->count() }} item{{ $booking->additionalServices->count() !== 1 ? 's' : '' }}</span>
        </div>
        <div class="bs-card-body">
            <div class="bs-fields">
                @foreach($booking->additionalServices as $svc)
                <div class="bs-row">
                    <span class="bs-row-label" style="max-width:200px">{{ $svc->service_name }}</span>
                    <span class="bs-row-val">₹{{ number_format($svc->amount) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ── Payment History ─────────────────────────────────────────── --}}
    <div class="bs-card" id="payment-history">
        <div class="bs-card-hdr">
            <span class="bs-card-title">Payment History</span>
            @if($booking->payments->isNotEmpty())
                <span class="bs-card-aside">{{ $booking->payments->count() }} transaction{{ $booking->payments->count() !== 1 ? 's' : '' }}</span>
            @endif
        </div>
        <div class="bs-card-body">
            @if($booking->payments->isEmpty())
                <div class="bs-tl-empty">
                    <i class="bi bi-receipt" style="font-size:1.5rem;display:block;margin-bottom:6px;opacity:.3"></i>
                    No payments recorded yet
                </div>
            @else
                <div class="bs-timeline">
                    @foreach($booking->payments->sortByDesc('paid_at') as $pmt)
                    <div class="bs-tl-item">
                        <div class="bs-tl-date">
                            {{ $pmt->paid_at->format('d M') }}<br>
                            <span style="font-size:.66rem;color:var(--bs-faint)">{{ $pmt->paid_at->format('Y') }}</span>
                        </div>
                        <div class="bs-tl-dot"></div>
                        <div class="bs-tl-body">
                            <div class="bs-tl-type">{{ \App\Models\BookingPayment::types()[$pmt->payment_type] ?? Str::headline($pmt->payment_type) }}</div>
                            <div class="bs-tl-meta">
                                {{ \App\Models\BookingPayment::methods()[$pmt->payment_method] ?? Str::headline($pmt->payment_method) }}
                                @if($pmt->reference_number) · Ref {{ $pmt->reference_number }}@endif
                                · {{ $pmt->recorder?->name ?? 'System' }}
                                @if($pmt->notes) · {{ $pmt->notes }}@endif
                            </div>
                        </div>
                        <div class="bs-tl-amount">₹{{ number_format($pmt->amount) }}</div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ── Record Payment ──────────────────────────────────────────── --}}
    @if($balance > 0 && !$booking->isCancelled())
    <div class="bs-card" id="record-payment">
        <div class="bs-card-hdr">
            <span class="bs-card-title">Collect Payment</span>
            <span class="bs-card-aside" style="color:#dc2626;font-weight:800">₹{{ number_format($balance) }} due</span>
        </div>
        <div class="bs-card-body">
            <form method="POST" action="{{ route('hall.bookings.payments.add', $booking) }}" class="bs-form">
                @csrf
                <div class="bs-form-group">
                    <label class="bs-form-label" for="amount">Amount (₹)</label>
                    <input id="amount" type="number" name="amount" class="bs-input"
                           step="0.01" min="0.01" value="{{ old('amount', $balance) }}" required>
                    @error('amount')<div class="bs-form-error">{{ $message }}</div>@enderror
                </div>
                <div class="bs-form-row bs-form-group">
                    <div>
                        <label class="bs-form-label" for="payment_method">Method</label>
                        <select id="payment_method" name="payment_method" class="bs-select" required>
                            @foreach(\App\Models\BookingPayment::methods() as $val => $lbl)
                                <option value="{{ $val }}" @selected(old('payment_method') === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="bs-form-label" for="payment_type">Type</label>
                        <select id="payment_type" name="payment_type" class="bs-select" required>
                            @foreach(\App\Models\BookingPayment::types() as $val => $lbl)
                                <option value="{{ $val }}" @selected(old('payment_type', 'balance') === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="bs-form-group">
                    <label class="bs-form-label" for="paid_at">Payment Date</label>
                    <input id="paid_at" type="date" name="paid_at" class="bs-input"
                           value="{{ old('paid_at', today()->toDateString()) }}" required>
                </div>
                <div class="bs-form-row bs-form-group">
                    <div>
                        <label class="bs-form-label" for="reference_number">Reference / UTR</label>
                        <input id="reference_number" type="text" name="reference_number" class="bs-input"
                               value="{{ old('reference_number') }}" placeholder="Optional">
                    </div>
                    <div>
                        <label class="bs-form-label" for="notes">Notes</label>
                        <input id="notes" type="text" name="notes" class="bs-input"
                               value="{{ old('notes') }}" placeholder="Optional">
                    </div>
                </div>
                <button type="submit" class="bs-submit">
                    <i class="bi bi-check2-circle"></i> Record Payment
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- ── Follow-up (above nothing now — notes absorbed into event details) ── --}}
    @if($booking->isFollowUpEligible())
    <div class="bs-followup" id="ef-followup">
        <div class="bs-followup-hdr">
            <span class="bs-followup-ico">⭐</span>
            <div>
                <div class="bs-followup-title">Event Follow-up</div>
                <div class="bs-followup-sub">Send a thank-you and request customer feedback.</div>
            </div>
        </div>

        @if($booking->review_requested_at)
            <div class="bs-followup-sent">
                <i class="bi bi-check-circle-fill"></i>
                Thank-you message sent on {{ $booking->review_requested_at->format('d M Y') }}
            </div>
        @endif

        <form method="POST" action="{{ route('hall.bookings.mark-review', $booking) }}">
            @csrf
            <button type="submit" class="bs-wa-btn"
                    onclick="window.open('{{ $fuWaUrl }}', '_blank')">
                <i class="bi bi-whatsapp"></i> Send Thank You Message
            </button>
        </form>

        @unless($cleanMobile)
            <div style="font-size:.72rem;color:var(--bs-faint);margin-top:8px;display:flex;gap:5px;align-items:flex-start">
                <i class="bi bi-info-circle"></i>
                No mobile number saved — WhatsApp will open without a pre-filled number.
            </div>
        @endunless
    </div>
    @endif

    {{-- ── Danger zone ─────────────────────────────────────────────── --}}
    <form method="POST" action="{{ route('hall.bookings.destroy', $booking) }}"
          onsubmit="return confirm('Permanently delete this booking? This cannot be undone.')">
        @csrf
        @method('DELETE')
        <button type="submit" class="bs-delete-btn">
            <i class="bi bi-trash3"></i> Delete Booking
        </button>
    </form>

</div>

{{-- ── Sticky payment bar (only when balance due) ────────────────── --}}
@if($balance > 0 && !$booking->isCancelled())
<div class="bs-sticky-bar">
    <div class="bs-sticky-due">
        <div class="bs-sticky-due-label">Amount Due</div>
        <div class="bs-sticky-due-val">₹{{ number_format($balance) }}</div>
    </div>
    <a href="#record-payment" class="bs-sticky-btn">
        <i class="bi bi-plus-lg"></i> Collect Payment
    </a>
</div>
@endif

</x-admin-layout>
