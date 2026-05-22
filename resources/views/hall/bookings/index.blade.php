<x-admin-layout title="Hall Bookings">
@push('styles')
<style>
/* ── Hall Bookings — ef-bk-* (v2 compact) ─────────────────────── */

:root {
    --bk-ink:      #131110;
    --bk-sub:      #50473f;
    --bk-muted:    #8a827a;
    --bk-faint:    #bab3aa;
    --bk-gold:     #8a6c30;
    --bk-gold-hi:  #b89040;
    --bk-gold-soft:#d4b06a;
    --bk-surface:  #fdfaf5;
    --bk-cream:    #f7f3ec;
    --bk-border:   rgba(100,82,42,.11);
    --bk-border-s: rgba(100,82,42,.24);
    --bk-shadow:   0 1px 3px rgba(18,14,8,.06), 0 3px 10px rgba(18,14,8,.05);
    --bk-shadow-h: 0 4px 18px rgba(18,14,8,.12), 0 1px 4px rgba(18,14,8,.06);
    --bk-r:        12px;
    --bk-ease:     cubic-bezier(.25,.46,.45,.94);
}

.ef-bk-shell {
    max-width: 1400px;
    margin: 0 auto;
    padding-bottom: 80px;
}

/* ── Flash ─────────────────────────────────────────────────────── */
.ef-bk-flash {
    align-items: center;
    border-radius: 10px;
    display: flex;
    font-size: .83rem;
    gap: 9px;
    margin-bottom: 14px;
    padding: 11px 14px;
}
.ef-bk-flash.--success { background: rgba(15,120,80,.07); border: 1px solid rgba(15,120,80,.18); color: #0A5C40; }
.ef-bk-flash.--error   { background: rgba(180,60,50,.07); border: 1px solid rgba(180,60,50,.18); color: #8B2020; }

/* ── Hero ── compact luxury header ─────────────────────────────── */
.ef-bk-hero {
    background: linear-gradient(135deg, #10180d 0%, #182414 50%, #0e1a0c 100%);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 14px;
    overflow: hidden;
    padding: 16px 20px;
    position: relative;
}
.ef-bk-hero::before {
    background: radial-gradient(circle, rgba(180,145,60,.18) 0%, transparent 65%);
    border-radius: 50%;
    content: "";
    height: 260px;
    pointer-events: none;
    position: absolute;
    right: -50px;
    top: -100px;
    width: 260px;
}
.ef-bk-hero-left { position: relative; z-index: 1; min-width: 0; }
.ef-bk-hero-kicker {
    color: rgba(180,145,60,.8);
    font-size: .62rem;
    font-weight: 760;
    letter-spacing: .18em;
    text-transform: uppercase;
    margin-bottom: 3px;
}
.ef-bk-hero-title {
    color: #f8f5ee;
    font-size: 1.35rem;
    font-weight: 780;
    letter-spacing: -.02em;
    line-height: 1.1;
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ef-bk-hero-sub {
    color: rgba(248,245,238,.42);
    font-size: .73rem;
    line-height: 1;
}
.ef-bk-hero-actions {
    align-items: center;
    display: flex;
    flex-shrink: 0;
    gap: 6px;
    position: relative;
    z-index: 1;
}
.ef-bk-hbtn {
    align-items: center;
    border-radius: 9px;
    cursor: pointer;
    display: inline-flex;
    font-family: inherit;
    font-size: .76rem;
    font-weight: 660;
    gap: 5px;
    padding: 7px 12px;
    text-decoration: none;
    transition: all .15s var(--bk-ease);
    white-space: nowrap;
    border: 1.5px solid rgba(255,255,255,.12);
    background: rgba(255,255,255,.07);
    color: rgba(248,245,238,.8);
}
.ef-bk-hbtn:hover { background: rgba(255,255,255,.14); color: #f8f5ee; border-color: rgba(255,255,255,.2); }
.ef-bk-hbtn.--gold {
    background: rgba(180,145,60,.25);
    border-color: rgba(180,145,60,.45);
    color: #e8c870;
}
.ef-bk-hbtn.--gold:hover { background: rgba(180,145,60,.38); color: #f5d882; }

/* ── KPI scroll strip ──────────────────────────────────────────── */
.ef-bk-kpi-scroll {
    overflow-x: auto;
    scrollbar-width: none;
    margin-bottom: 14px;
    /* negative margin trick for full-bleed on tiny screens */
}
.ef-bk-kpi-scroll::-webkit-scrollbar { display: none; }
.ef-bk-kpi-row {
    display: grid;
    grid-template-columns: repeat(5, minmax(108px, 1fr));
    gap: 8px;
    min-width: 580px;
}
.ef-bk-kpi {
    background: var(--bk-surface);
    border: 1px solid var(--bk-border);
    border-radius: var(--bk-r);
    box-shadow: var(--bk-shadow);
    padding: 10px 13px;
}
.ef-bk-kpi-label {
    color: var(--bk-muted);
    font-size: .62rem;
    font-weight: 720;
    letter-spacing: .1em;
    text-transform: uppercase;
    margin-bottom: 5px;
}
.ef-bk-kpi-val {
    color: var(--bk-ink);
    font-size: 1.35rem;
    font-weight: 800;
    line-height: 1;
    letter-spacing: -.02em;
    font-variant-numeric: tabular-nums;
}
.ef-bk-kpi-val.--gold   { color: var(--bk-gold); }
.ef-bk-kpi-val.--red    { color: #8B2020; }
.ef-bk-kpi-val.--green  { color: #0A6640; }
.ef-bk-kpi-note {
    color: var(--bk-faint);
    font-size: .67rem;
    margin-top: 3px;
}
.ef-bk-kpi-pulse {
    animation: bk-pulse 1.8s ease-in-out infinite;
    background: var(--bk-gold);
    border-radius: 50%;
    display: inline-block;
    height: 6px;
    margin-right: 4px;
    vertical-align: middle;
    width: 6px;
}
@keyframes bk-pulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(138,108,48,.5); }
    50%      { box-shadow: 0 0 0 4px rgba(138,108,48,0); }
}

/* ── Filter bar — sticky compact ───────────────────────────────── */
.ef-bk-filter {
    background: rgba(253,250,245,.97);
    backdrop-filter: blur(12px);
    border: 1px solid var(--bk-border);
    border-radius: 12px;
    box-shadow: var(--bk-shadow);
    margin-bottom: 12px;
    padding: 10px 12px;
    position: sticky;
    top: 8px;
    z-index: 30;
}
.ef-bk-filter-row1 {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-bottom: 8px;
}
.ef-bk-search-wrap { position: relative; flex: 1; min-width: 0; }
.ef-bk-search-ico {
    color: var(--bk-faint);
    font-size: .8rem;
    left: 10px;
    pointer-events: none;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
}
.ef-bk-search {
    background: var(--bk-cream);
    border: 1.5px solid var(--bk-border-s);
    border-radius: 8px;
    color: var(--bk-ink);
    font-family: inherit;
    font-size: .82rem;
    outline: none;
    padding: 7px 10px 7px 28px;
    transition: border-color .14s;
    width: 100%;
}
.ef-bk-search:focus { border-color: var(--bk-gold); background: #fff; }
.ef-bk-search::placeholder { color: var(--bk-faint); }
.ef-bk-filter-btns { display: flex; gap: 6px; flex-shrink: 0; }
.ef-bk-filt-btn {
    align-items: center;
    background: var(--bk-cream);
    border: 1.5px solid var(--bk-border-s);
    border-radius: 8px;
    color: var(--bk-muted);
    cursor: pointer;
    display: inline-flex;
    font-family: inherit;
    font-size: .76rem;
    font-weight: 640;
    gap: 5px;
    padding: 7px 11px;
    transition: all .14s;
    white-space: nowrap;
}
.ef-bk-filt-btn:hover { border-color: var(--bk-gold-hi); color: var(--bk-gold); }
.ef-bk-filt-btn.--active {
    background: rgba(138,108,48,.08);
    border-color: var(--bk-gold);
    color: var(--bk-gold);
}

/* chips row */
.ef-bk-chips {
    display: flex;
    gap: 5px;
    overflow-x: auto;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
}
.ef-bk-chips::-webkit-scrollbar { display: none; }
.ef-bk-chip {
    background: transparent;
    border: 1.5px solid var(--bk-border-s);
    border-radius: 20px;
    color: var(--bk-muted);
    cursor: pointer;
    display: inline-block;
    flex-shrink: 0;
    font-size: .72rem;
    font-weight: 660;
    padding: 4px 12px;
    text-decoration: none;
    transition: all .14s;
    white-space: nowrap;
}
.ef-bk-chip:hover { border-color: var(--bk-ink); color: var(--bk-ink); }
.ef-bk-chip.--active {
    background: var(--bk-ink);
    border-color: var(--bk-ink);
    color: var(--bk-surface);
}

/* advanced drawer */
.ef-bk-adv {
    max-height: 0;
    overflow: hidden;
    transition: max-height .28s ease;
}
.ef-bk-adv.--open { max-height: 380px; }
.ef-bk-adv-inner {
    align-items: end;
    border-top: 1px solid var(--bk-border);
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
    margin-top: 10px;
    padding-top: 10px;
}
.ef-bk-adv-label {
    color: var(--bk-muted);
    display: block;
    font-size: .62rem;
    font-weight: 700;
    letter-spacing: .1em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
.ef-bk-adv-input,
.ef-bk-adv-select {
    background: #fff;
    border: 1.5px solid var(--bk-border-s);
    border-radius: 8px;
    color: var(--bk-ink);
    font-family: inherit;
    font-size: .82rem;
    outline: none;
    padding: 7px 10px;
    transition: border-color .14s;
    width: 100%;
}
.ef-bk-adv-input:focus,
.ef-bk-adv-select:focus { border-color: var(--bk-gold); }
.ef-bk-adv-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='11' height='11' viewBox='0 0 12 12'%3E%3Cpath fill='%23aaa' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 26px;
}
.ef-bk-adv-row { display: flex; gap: 7px; align-items: center; }
.ef-bk-btn-apply {
    background: var(--bk-ink);
    border: none;
    border-radius: 8px;
    color: var(--bk-surface);
    cursor: pointer;
    font-family: inherit;
    font-size: .8rem;
    font-weight: 660;
    padding: 7px 16px;
    white-space: nowrap;
}
.ef-bk-btn-clear {
    background: transparent;
    border: 1.5px solid var(--bk-border-s);
    border-radius: 8px;
    color: var(--bk-muted);
    font-size: .8rem;
    font-weight: 640;
    padding: 7px 12px;
    text-decoration: none;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
}

/* ── Results meta ──────────────────────────────────────────────── */
.ef-bk-meta-bar {
    align-items: center;
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}
.ef-bk-meta-bar span { color: var(--bk-muted); font-size: .76rem; }
.ef-bk-meta-bar strong { color: var(--bk-ink); }

/* ── Booking list ──────────────────────────────────────────────── */
.ef-bk-list {
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-bottom: 20px;
}

/* ── Booking card — compact 4-row ──────────────────────────────── */
.ef-bk-card {
    background: var(--bk-surface);
    border: 1px solid var(--bk-border);
    border-left: 3px solid transparent;
    border-radius: var(--bk-r);
    box-shadow: var(--bk-shadow);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: box-shadow .16s var(--bk-ease), transform .16s var(--bk-ease);
}
.ef-bk-card:hover { box-shadow: var(--bk-shadow-h); transform: translateY(-1px); }
.ef-bk-card.--confirmed { border-left-color: #0F7B5F; }
.ef-bk-card.--completed { border-left-color: #2F6FED; }
.ef-bk-card.--cancelled { border-left-color: #C84B44; opacity: .7; }

/* Row 1: avatar + name + event + status */
.ef-bk-r1 {
    align-items: center;
    display: flex;
    gap: 8px;
    padding: 10px 12px 8px;
}
.ef-bk-av {
    align-items: center;
    border-radius: 8px;
    color: rgba(255,255,255,.92);
    display: flex;
    flex-shrink: 0;
    font-size: .78rem;
    font-weight: 800;
    height: 32px;
    justify-content: center;
    letter-spacing: -.01em;
    width: 32px;
}
.ef-bk-r1-text { flex: 1; min-width: 0; }
.ef-bk-name {
    color: var(--bk-ink);
    font-size: .86rem;
    font-weight: 720;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ef-bk-evtype {
    color: var(--bk-muted);
    font-size: .66rem;
    font-weight: 620;
    letter-spacing: .05em;
    text-transform: uppercase;
}

/* status badge */
.ef-bk-badge {
    border-radius: 6px;
    flex-shrink: 0;
    font-size: .57rem;
    font-weight: 780;
    letter-spacing: .09em;
    padding: 2px 7px;
    text-transform: uppercase;
    white-space: nowrap;
}
.ef-bk-badge.--confirmed { background: rgba(15,123,95,.1);  border: 1px solid rgba(15,123,95,.22);  color: #0A5240; }
.ef-bk-badge.--completed { background: rgba(47,111,237,.1); border: 1px solid rgba(47,111,237,.22); color: #1A3E8A; }
.ef-bk-badge.--cancelled { background: rgba(200,75,68,.08); border: 1px solid rgba(200,75,68,.2);   color: #8B2020; }

/* Rows 2-3: meta info */
.ef-bk-rows {
    padding: 0 12px 8px;
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.ef-bk-mrow {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 2px 8px;
    line-height: 1;
}
.ef-bk-mitem {
    align-items: center;
    color: var(--bk-sub);
    display: inline-flex;
    font-size: .73rem;
    gap: 3px;
}
.ef-bk-mitem i { color: var(--bk-faint); font-size: .68rem; }
.ef-bk-mdot {
    background: var(--bk-border-s);
    border-radius: 50%;
    flex-shrink: 0;
    height: 3px;
    width: 3px;
}

/* meal tags */
.ef-bk-meal-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 3px;
    margin-top: 4px;
    padding: 0 12px;
}
.ef-bk-meal-tag {
    background: rgba(138,108,48,.07);
    border: 1px solid rgba(138,108,48,.16);
    border-radius: 4px;
    color: var(--bk-gold);
    font-size: .58rem;
    font-weight: 700;
    letter-spacing: .06em;
    padding: 1px 6px;
    text-transform: uppercase;
}

/* footer: amount + actions */
.ef-bk-foot {
    align-items: center;
    border-top: 1px solid var(--bk-border);
    display: flex;
    gap: 8px;
    justify-content: space-between;
    margin-top: auto;
    padding: 8px 12px;
}
.ef-bk-money { min-width: 0; }
.ef-bk-amt {
    color: var(--bk-ink);
    font-size: .96rem;
    font-variant-numeric: tabular-nums;
    font-weight: 780;
    line-height: 1;
}
.ef-bk-pchip {
    border-radius: 5px;
    display: inline-block;
    font-size: .57rem;
    font-weight: 780;
    letter-spacing: .08em;
    margin-top: 3px;
    padding: 1px 6px;
    text-transform: uppercase;
}
.ef-bk-pchip.--pending { background: rgba(138,108,48,.09); border: 1px solid rgba(138,108,48,.2);  color: #7A5A18; }
.ef-bk-pchip.--partial { background: rgba(47,111,237,.09); border: 1px solid rgba(47,111,237,.2);  color: #1A3E8A; }
.ef-bk-pchip.--paid    { background: rgba(15,123,95,.09);  border: 1px solid rgba(15,123,95,.2);   color: #0A5240; }

.ef-bk-acts {
    align-items: center;
    display: flex;
    flex-shrink: 0;
    gap: 4px;
}
.ef-bk-act {
    align-items: center;
    border: 1.5px solid var(--bk-border-s);
    border-radius: 7px;
    color: var(--bk-muted);
    cursor: pointer;
    display: inline-flex;
    font-family: inherit;
    font-size: .72rem;
    font-weight: 660;
    gap: 3px;
    padding: 5px 9px;
    text-decoration: none;
    transition: all .13s;
    white-space: nowrap;
}
.ef-bk-act:hover { border-color: var(--bk-ink); color: var(--bk-ink); }
.ef-bk-act.--primary { background: var(--bk-ink); border-color: var(--bk-ink); color: var(--bk-surface); }
.ef-bk-act.--primary:hover { opacity: .84; color: var(--bk-surface); }
.ef-bk-act.--ico { padding: 5px 7px; }
.ef-bk-act.--wa:hover { border-color: #25d366; color: #25d366; }
.ef-bk-more {
    align-items: center;
    background: transparent;
    border: 1.5px solid var(--bk-border-s);
    border-radius: 7px;
    color: var(--bk-muted);
    cursor: pointer;
    display: inline-flex;
    font-size: .8rem;
    height: 28px;
    justify-content: center;
    padding: 0 6px;
    transition: all .13s;
}
.ef-bk-more:hover { border-color: var(--bk-ink); color: var(--bk-ink); }

/* ── Empty state ───────────────────────────────────────────────── */
.ef-bk-empty {
    background: var(--bk-surface);
    border: 1px solid var(--bk-border);
    border-radius: 16px;
    box-shadow: var(--bk-shadow);
    padding: 52px 24px;
    text-align: center;
}
.ef-bk-empty-orb {
    align-items: center;
    background: var(--bk-cream);
    border: 1.5px solid var(--bk-border-s);
    border-radius: 50%;
    color: var(--bk-faint);
    display: inline-flex;
    font-size: 1.6rem;
    height: 64px;
    justify-content: center;
    margin-bottom: 14px;
    width: 64px;
}
.ef-bk-empty-title { color: var(--bk-ink); font-size: .98rem; font-weight: 740; margin-bottom: 6px; }
.ef-bk-empty-note  { color: var(--bk-muted); font-size: .82rem; margin-bottom: 18px; }

/* ── Pagination ────────────────────────────────────────────────── */
.ef-bk-pagination { display: flex; justify-content: center; margin-bottom: 16px; }

/* ── FAB (mobile new booking) ──────────────────────────────────── */
.ef-bk-fab {
    align-items: center;
    background: linear-gradient(135deg, #8a6c30 0%, #b89040 100%);
    border: none;
    border-radius: 50%;
    bottom: 20px;
    box-shadow: 0 4px 16px rgba(138,108,48,.4), 0 2px 6px rgba(0,0,0,.12);
    color: #fff;
    cursor: pointer;
    display: none;
    font-size: 1.3rem;
    height: 52px;
    justify-content: center;
    position: fixed;
    right: 18px;
    text-decoration: none;
    transition: transform .2s var(--bk-ease), box-shadow .2s var(--bk-ease);
    width: 52px;
    z-index: 80;
}
.ef-bk-fab:hover { color: #fff; transform: scale(1.08); box-shadow: 0 6px 22px rgba(138,108,48,.5); }
.ef-bk-fab:active { transform: scale(.96); }

/* ── Responsive ────────────────────────────────────────────────── */
@media (max-width: 1199.98px) {
    .ef-bk-list { grid-template-columns: minmax(0, 1fr); }
}
@media (max-width: 991.98px) {
    .ef-bk-list { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 767.98px) {
    .ef-bk-hero { padding: 13px 16px; border-radius: 12px; }
    .ef-bk-hero-title { font-size: 1.2rem; }
    .ef-bk-hbtn span { display: none; }        /* icon-only on small screens */
    .ef-bk-hbtn { padding: 7px 9px; }
    .ef-bk-fab  { display: flex; }
    .ef-bk-filter { top: 4px; }
}
@media (max-width: 639.98px) {
    .ef-bk-list { grid-template-columns: minmax(0, 1fr); }
}
@media (max-width: 479.98px) {
    .ef-bk-hero-sub  { display: none; }
    .ef-bk-kpi-row   { grid-template-columns: repeat(5, minmax(96px, 1fr)); }
}
</style>
@endpush

@php
$today = now()->toDateString();
$hasAny = fn(array $keys) => collect($keys)->some(fn($k) => request()->filled($k));

$allActive      = !request()->hasAny(['status','payment_status','date_from','date_to','search','hall_id']);
$todayActive    = request('date_from') === $today && request('date_to') === $today
                  && !$hasAny(['status','payment_status','search','hall_id']);
$upcomingActive = request('date_from') === $today && !request()->filled('date_to')
                  && !$hasAny(['status','payment_status','search','hall_id']);
$pendingActive  = request('payment_status') === 'pending'
                  && !$hasAny(['status','date_from','date_to','search','hall_id']);
$paidActive     = request('payment_status') === 'paid'
                  && !$hasAny(['status','date_from','date_to','search','hall_id']);
$confirmedActive= request('status') === 'confirmed'
                  && !$hasAny(['payment_status','date_from','date_to','search','hall_id']);
$hasAdvFilter   = request()->hasAny(['hall_id','status','payment_status','date_from','date_to']);

$avatarTones = ['#7a5a28','#3e6a5a','#4a5e8a','#6a4e7a','#5a6840'];
@endphp

<div class="ef-bk-shell">

{{-- Flash handled by global toast in admin-layout — no page-level duplicate --}}

{{-- Hero ───────────────────────────────────────────────────────── --}}
<div class="ef-bk-hero">
    <div class="ef-bk-hero-left">
        <p class="ef-bk-hero-kicker">Venue Management</p>
        <h1 class="ef-bk-hero-title">Hall Bookings</h1>
        <p class="ef-bk-hero-sub">{{ now()->format('l, d F Y') }}</p>
    </div>
    <div class="ef-bk-hero-actions">
        <a href="{{ route('hall.bookings.create') }}" class="ef-bk-hbtn --gold">
            <i class="bi bi-plus-circle"></i><span>New Booking</span>
        </a>
        <a href="{{ route('hall.bookings.calendar') }}" class="ef-bk-hbtn">
            <i class="bi bi-calendar3"></i><span>Calendar</span>
        </a>
        <a href="{{ route('hall.bookings.kitchen') }}" class="ef-bk-hbtn">
            <i class="bi bi-cup-hot"></i><span>Kitchen</span>
        </a>
    </div>
</div>

{{-- KPI strip ──────────────────────────────────────────────────── --}}
<div class="ef-bk-kpi-scroll">
    <div class="ef-bk-kpi-row">
        <div class="ef-bk-kpi">
            <div class="ef-bk-kpi-label">
                @if($stats['today'] > 0)<span class="ef-bk-kpi-pulse"></span>@endif
                Today
            </div>
            <div class="ef-bk-kpi-val {{ $stats['today'] > 0 ? '--gold' : '' }}">{{ $stats['today'] }}</div>
            <div class="ef-bk-kpi-note">Active now</div>
        </div>
        <div class="ef-bk-kpi">
            <div class="ef-bk-kpi-label">Upcoming</div>
            <div class="ef-bk-kpi-val">{{ $stats['upcoming'] }}</div>
            <div class="ef-bk-kpi-note">From today</div>
        </div>
        <div class="ef-bk-kpi">
            <div class="ef-bk-kpi-label">Pending Pay</div>
            <div class="ef-bk-kpi-val {{ $stats['pending_pay'] > 0 ? '--red' : '' }}">{{ $stats['pending_pay'] }}</div>
            <div class="ef-bk-kpi-note">Awaiting</div>
        </div>
        <div class="ef-bk-kpi">
            <div class="ef-bk-kpi-label">Occupancy</div>
            <div class="ef-bk-kpi-val --green">{{ $stats['month_occ'] }}<span style="font-size:.75rem;font-weight:640">%</span></div>
            <div class="ef-bk-kpi-note">{{ now()->format('M') }}</div>
        </div>
        <div class="ef-bk-kpi">
            <div class="ef-bk-kpi-label">Guests</div>
            <div class="ef-bk-kpi-val">{{ number_format($stats['week_guests']) }}</div>
            <div class="ef-bk-kpi-note">This week</div>
        </div>
    </div>
</div>

{{-- Filter bar ──────────────────────────────────────────────────── --}}
<div class="ef-bk-filter">
    <form method="GET" id="bkFilterForm">
        {{-- Row 1: search + buttons --}}
        <div class="ef-bk-filter-row1">
            <div class="ef-bk-search-wrap">
                <i class="bi bi-search ef-bk-search-ico"></i>
                <input type="text" name="search" class="ef-bk-search"
                       placeholder="Name or mobile…"
                       value="{{ request('search') }}" autocomplete="off">
            </div>
            <div class="ef-bk-filter-btns">
                <button type="button" id="bkFiltToggle"
                        class="ef-bk-filt-btn {{ $hasAdvFilter ? '--active' : '' }}"
                        onclick="bkToggleAdv()">
                    <i class="bi bi-sliders2"></i>
                    @if($hasAdvFilter)<span style="background:var(--bk-gold);border-radius:50%;display:inline-block;height:5px;width:5px;flex-shrink:0"></span>@endif
                </button>
                <button type="submit" class="ef-bk-btn-apply">Search</button>
            </div>
        </div>

        {{-- Row 2: quick chips --}}
        <div class="ef-bk-chips">
            <a href="{{ route('hall.bookings.index') }}"
               class="ef-bk-chip {{ $allActive ? '--active' : '' }}">All</a>
            <a href="{{ route('hall.bookings.index', ['date_from' => $today, 'date_to' => $today]) }}"
               class="ef-bk-chip {{ $todayActive ? '--active' : '' }}">Today</a>
            <a href="{{ route('hall.bookings.index', ['date_from' => $today]) }}"
               class="ef-bk-chip {{ $upcomingActive ? '--active' : '' }}">Upcoming</a>
            <a href="{{ route('hall.bookings.index', ['payment_status' => 'pending']) }}"
               class="ef-bk-chip {{ $pendingActive ? '--active' : '' }}">Pending Pay</a>
            <a href="{{ route('hall.bookings.index', ['status' => 'confirmed']) }}"
               class="ef-bk-chip {{ $confirmedActive ? '--active' : '' }}">Confirmed</a>
            <a href="{{ route('hall.bookings.index', ['payment_status' => 'paid']) }}"
               class="ef-bk-chip {{ $paidActive ? '--active' : '' }}">Paid</a>
        </div>

        {{-- Advanced drawer --}}
        <div class="ef-bk-adv {{ $hasAdvFilter ? '--open' : '' }}" id="bkAdvPanel">
            <div class="ef-bk-adv-inner">
                <div>
                    <label class="ef-bk-adv-label">Hall</label>
                    <select name="hall_id" class="ef-bk-adv-select">
                        <option value="">All Halls</option>
                        @foreach($halls as $h)
                            <option value="{{ $h->id }}" {{ request('hall_id') == $h->id ? 'selected' : '' }}>{{ $h->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ef-bk-adv-label">Status</label>
                    <select name="status" class="ef-bk-adv-select">
                        <option value="">All Status</option>
                        @foreach(\App\Models\HallBooking::statuses() as $v => $l)
                            <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ef-bk-adv-label">Payment</label>
                    <select name="payment_status" class="ef-bk-adv-select">
                        <option value="">All Payments</option>
                        @foreach(\App\Models\HallBooking::paymentStatuses() as $v => $l)
                            <option value="{{ $v }}" {{ request('payment_status') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ef-bk-adv-label">From</label>
                    <input type="date" name="date_from" class="ef-bk-adv-input" value="{{ request('date_from') }}">
                </div>
                <div>
                    <label class="ef-bk-adv-label">To</label>
                    <input type="date" name="date_to" class="ef-bk-adv-input" value="{{ request('date_to') }}">
                </div>
                <div style="display:flex;flex-direction:column;justify-content:flex-end">
                    <div class="ef-bk-adv-row">
                        <button type="submit" class="ef-bk-btn-apply">Apply</button>
                        <a href="{{ route('hall.bookings.index') }}" class="ef-bk-btn-clear">Clear</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Results meta ───────────────────────────────────────────────── --}}
<div class="ef-bk-meta-bar">
    <span>
        <strong>{{ $bookings->total() }}</strong> {{ Str::plural('booking', $bookings->total()) }}
        @if(request()->hasAny(['search','hall_id','status','payment_status','date_from','date_to']))
            <span style="color:var(--bk-faint)"> · filtered ·</span>
            <a href="{{ route('hall.bookings.index') }}"
               style="color:var(--bk-gold);font-weight:660;text-decoration:none">Clear</a>
        @endif
    </span>
    <span>By date</span>
</div>

{{-- Booking list ───────────────────────────────────────────────── --}}
@if($bookings->isNotEmpty())
<div class="ef-bk-list">
    @foreach($bookings as $b)
    @php
        $tone  = $avatarTones[ord(strtoupper($b->customer_name[0] ?? 'A')) % count($avatarTones)];
        $meals = collect(['Breakfast' => $b->has_breakfast, 'Lunch' => $b->has_lunch, 'Dinner' => $b->has_dinner])->filter()->keys();
        $waUrl = 'https://wa.me/91' . preg_replace('/\D/', '', $b->customer_mobile ?? '');
        $evType = \App\Models\HallBooking::eventTypes()[$b->event_type] ?? ucwords(str_replace('_', ' ', $b->event_type));
    @endphp

    <div class="ef-bk-card --{{ $b->status }}">

        {{-- Row 1: Avatar + Name + Event + Status --}}
        <div class="ef-bk-r1">
            <div class="ef-bk-av" style="background:{{ $tone }}">
                {{ strtoupper(mb_substr($b->customer_name, 0, 1)) }}
            </div>
            <div class="ef-bk-r1-text">
                <div class="ef-bk-name">{{ $b->customer_name }}</div>
                <div class="ef-bk-evtype">{{ $evType }}</div>
            </div>
            <span class="ef-bk-badge --{{ $b->status }}">{{ $b->status }}</span>
        </div>

        {{-- Rows 2-3: Hall + Date + Time + Guests --}}
        <div class="ef-bk-rows">
            <div class="ef-bk-mrow">
                <span class="ef-bk-mitem"><i class="bi bi-building"></i> {{ $b->hall->name }}</span>
                <span class="ef-bk-mdot"></span>
                <span class="ef-bk-mitem"><i class="bi bi-calendar3"></i> {{ $b->booking_date->format('d M, D') }}</span>
            </div>
            <div class="ef-bk-mrow">
                <span class="ef-bk-mitem">
                    <i class="bi bi-clock"></i>
                    {{ \Carbon\Carbon::parse($b->start_time)->format('h:i A') }}–{{ \Carbon\Carbon::parse($b->end_time)->format('h:i A') }}
                </span>
                <span class="ef-bk-mdot"></span>
                <span class="ef-bk-mitem"><i class="bi bi-people"></i> {{ number_format($b->number_of_people) }}</span>
                @if($b->mealPlan || $meals->isNotEmpty())
                    <span class="ef-bk-mdot"></span>
                    <span class="ef-bk-mitem"><i class="bi bi-egg-fried"></i> {{ $b->mealPlan?->name ?? 'Catering' }}</span>
                @endif
            </div>
        </div>

        {{-- Meal tags (if any) --}}
        @if($meals->isNotEmpty())
            <div class="ef-bk-meal-tags">
                @foreach($meals as $m)
                    <span class="ef-bk-meal-tag">{{ $m }}</span>
                @endforeach
            </div>
        @endif

        {{-- Footer: Amount + Pay status + Actions --}}
        <div class="ef-bk-foot">
            <div class="ef-bk-money">
                <div class="ef-bk-amt">₹{{ number_format($b->total_amount) }}</div>
                <span class="ef-bk-pchip --{{ $b->payment_status }}">
                    {{ \App\Models\HallBooking::paymentStatuses()[$b->payment_status] ?? $b->payment_status }}
                </span>
            </div>
            <div class="ef-bk-acts">
                <a href="{{ route('hall.bookings.show', $b) }}" class="ef-bk-act --primary">
                    <i class="bi bi-eye"></i> View
                </a>
                <a href="{{ route('hall.bookings.invoice', $b) }}" class="ef-bk-act --ico"
                   target="_blank" title="Invoice">
                    <i class="bi bi-receipt"></i>
                </a>
                <a href="{{ $waUrl }}" class="ef-bk-act --ico --wa" target="_blank" title="WhatsApp">
                    <i class="bi bi-whatsapp"></i>
                </a>
                <div class="dropdown">
                    <button class="ef-bk-more" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end"
                        style="border-radius:10px;border:1px solid var(--bk-border);box-shadow:var(--bk-shadow-h);font-size:.8rem;min-width:158px;">
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('hall.bookings.edit', $b) }}">
                                <i class="bi bi-pencil me-2" style="color:var(--bk-faint)"></i>Edit Booking
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('hall.bookings.show', $b) }}#record-payment">
                                <i class="bi bi-cash-coin me-2" style="color:var(--bk-faint)"></i>Record Payment
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('hall.bookings.invoice.pdf', $b) }}">
                                <i class="bi bi-file-pdf me-2" style="color:var(--bk-faint)"></i>Download PDF
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
    @endforeach
</div>
@else
{{-- Empty state --}}
<div class="ef-bk-empty">
    <div class="ef-bk-empty-orb">
        <i class="bi bi-{{ request()->hasAny(['search','hall_id','status','payment_status','date_from','date_to']) ? 'search' : 'calendar-x' }}"></i>
    </div>
    <h3 class="ef-bk-empty-title">No bookings found</h3>
    <p class="ef-bk-empty-note">
        @if(request()->hasAny(['search','hall_id','status','payment_status','date_from','date_to']))
            No bookings match your filters. Try broadening your search.
        @else
            No hall bookings yet. Create your first reservation.
        @endif
    </p>
    @if(request()->hasAny(['search','hall_id','status','payment_status','date_from','date_to']))
        <a href="{{ route('hall.bookings.index') }}" class="ef-bk-btn-clear">Clear Filters</a>
    @else
        <a href="{{ route('hall.bookings.create') }}" class="ef-bk-hbtn --gold" style="display:inline-flex">
            <i class="bi bi-plus-circle"></i><span>New Booking</span>
        </a>
    @endif
</div>
@endif

{{-- Pagination ─────────────────────────────────────────────────── --}}
@if($bookings->hasPages())
    <div class="ef-bk-pagination">
        {{ $bookings->links() }}
    </div>
@endif

</div>{{-- /shell --}}

{{-- FAB (mobile) ───────────────────────────────────────────────── --}}
<a href="{{ route('hall.bookings.create') }}" class="ef-bk-fab" title="New Booking">
    <i class="bi bi-plus"></i>
</a>

@push('scripts')
<script>
function bkToggleAdv() {
    const panel = document.getElementById('bkAdvPanel');
    const btn   = document.getElementById('bkFiltToggle');
    const open  = panel.classList.toggle('--open');
    btn.classList.toggle('--active', open);
}

document.getElementById('bkFilterForm').addEventListener('submit', function () {
    this.querySelectorAll('input[type="text"], input[type="date"], select').forEach(el => {
        if (!el.value.trim()) el.disabled = true;
    });
});
</script>
@endpush

</x-admin-layout>
