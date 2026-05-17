<x-admin-layout title="Hall Bookings">
@push('styles')
<style>
/* ── Hall Bookings — ef-bk-* ──────────────────────────────────── */
.ef-bk-shell {
    max-width: 1500px;
    margin: 0 auto;
    padding-bottom: 92px;
}

/* ── Hero — dark dramatic ──────────────────────────────────────── */
.ef-bk-hero {
    align-items: end;
    background: linear-gradient(135deg, #1a1410 0%, #2a2218 50%, #1e1812 100%);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(26,22,18,.16), 0 1px 4px rgba(26,22,18,.1);
    display: grid;
    gap: 20px;
    grid-template-columns: minmax(0, 1fr) auto;
    margin-bottom: 28px;
    overflow: hidden;
    padding: 32px;
    position: relative;
}
.ef-bk-hero::before {
    background: radial-gradient(circle, rgba(160,114,56,.16) 0%, transparent 68%);
    border-radius: 50%;
    content: "";
    height: 420px;
    pointer-events: none;
    position: absolute;
    right: -80px;
    top: -140px;
    width: 420px;
}
.ef-bk-hero::after {
    background: radial-gradient(circle, rgba(26,102,69,.1) 0%, transparent 70%);
    border-radius: 50%;
    bottom: -80px;
    content: "";
    height: 260px;
    left: 30%;
    pointer-events: none;
    position: absolute;
    width: 260px;
}
.ef-bk-kicker {
    color: rgba(160,114,56,.9);
    font-size: .67rem;
    font-weight: 760;
    letter-spacing: .17em;
    text-transform: uppercase;
}
.ef-bk-title {
    color: #fffdfa;
    font-size: clamp(2rem, 4vw, 3.2rem);
    font-weight: 780;
    letter-spacing: -.01em;
    line-height: 1;
    margin: 8px 0 10px;
}
.ef-bk-subtitle {
    color: rgba(255,253,250,.55);
    font-size: .9rem;
    margin-bottom: 6px;
}
.ef-bk-date {
    color: rgba(255,253,250,.32);
    font-size: .78rem;
    font-weight: 640;
    letter-spacing: .03em;
}
.ef-bk-hero-actions {
    align-items: flex-start;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-end;
    position: relative;
    z-index: 1;
}
.ef-bk-hero .ef-btn {
    background: rgba(255,255,255,.08);
    border-color: rgba(255,255,255,.14);
    color: rgba(255,253,250,.85);
}
.ef-bk-hero .ef-btn:hover {
    background: rgba(255,255,255,.14);
    color: #fffdfa;
}

/* ── Insight strip ─────────────────────────────────────────────── */
.ef-bk-insights {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    margin-bottom: 24px;
}
.ef-bk-ins {
    background: rgba(255, 253, 250, .92);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    min-height: 108px;
    padding: 18px;
    transition: border-color .18s var(--ef-ease), box-shadow .18s var(--ef-ease);
}
.ef-bk-ins:hover {
    border-color: var(--ef-border-strong);
    box-shadow: var(--ef-shadow-hover);
}
.ef-bk-ins-label {
    color: var(--ef-faint);
    font-size: .67rem;
    font-weight: 760;
    letter-spacing: .14em;
    text-transform: uppercase;
}
.ef-bk-ins-val {
    color: var(--ef-ink);
    font-size: 1.65rem;
    font-variant-numeric: tabular-nums;
    font-weight: 780;
    line-height: 1;
    margin-top: 12px;
}
.ef-bk-ins-note {
    color: var(--ef-muted);
    font-size: .73rem;
    margin-top: 6px;
}

/* ── Filter bar ────────────────────────────────────────────────── */
.ef-bk-filter-bar {
    background: rgba(255, 253, 250, .96);
    border: 1px solid var(--ef-border);
    border-radius: 14px;
    box-shadow: var(--ef-shadow);
    margin-bottom: 20px;
    padding: 14px 16px;
}
.ef-bk-filter-top {
    align-items: center;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.ef-bk-chips {
    display: flex;
    flex: 1;
    flex-wrap: nowrap;
    gap: 6px;
    min-width: 0;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.ef-bk-chips::-webkit-scrollbar { display: none; }
.ef-bk-chip {
    background: transparent;
    border: 1.5px solid var(--ef-border-strong);
    border-radius: 20px;
    color: var(--ef-muted);
    cursor: pointer;
    display: inline-block;
    font-size: .73rem;
    font-weight: 660;
    padding: 5px 14px;
    text-decoration: none;
    transition: border-color .15s, color .15s, background .15s;
    white-space: nowrap;
}
.ef-bk-chip:hover { border-color: var(--ef-ink); color: var(--ef-ink); }
.ef-bk-chip.--active { background: var(--ef-ink); border-color: var(--ef-ink); color: var(--ef-bg); }

.ef-bk-filter-right {
    align-items: center;
    display: flex;
    flex-shrink: 0;
    gap: 8px;
}
.ef-bk-search {
    background: transparent;
    border: 1.5px solid var(--ef-border-strong);
    border-radius: 10px;
    color: var(--ef-ink);
    font-family: inherit;
    font-size: .84rem;
    min-width: 180px;
    padding: 7px 12px;
    transition: border-color .15s, box-shadow .15s;
}
.ef-bk-search:focus {
    border-color: var(--ef-gold);
    box-shadow: 0 0 0 3px rgba(180, 145, 90, .1);
    outline: none;
}
.ef-bk-search::placeholder { color: var(--ef-faint); }

.ef-bk-filter-toggle {
    align-items: center;
    background: transparent;
    border: 1.5px solid var(--ef-border-strong);
    border-radius: 10px;
    color: var(--ef-muted);
    cursor: pointer;
    display: inline-flex;
    font-family: inherit;
    font-size: .78rem;
    font-weight: 640;
    gap: 6px;
    padding: 7px 12px;
    transition: border-color .15s, color .15s, background .15s;
    white-space: nowrap;
}
.ef-bk-filter-toggle:hover { border-color: var(--ef-ink); color: var(--ef-ink); }
.ef-bk-filter-toggle.--active {
    background: rgba(180, 145, 90, .07);
    border-color: var(--ef-gold);
    color: #8a6c3a;
}

/* Advanced filter panel */
.ef-bk-adv-panel {
    max-height: 0;
    overflow: hidden;
    transition: max-height .3s ease;
}
.ef-bk-adv-panel.--open { max-height: 420px; }
.ef-bk-adv-inner {
    align-items: end;
    border-top: 1px solid var(--ef-border);
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(auto-fill, minmax(168px, 1fr));
    margin-top: 14px;
    padding-top: 14px;
}
.ef-bk-adv-label {
    color: var(--ef-faint);
    display: block;
    font-size: .64rem;
    font-weight: 680;
    letter-spacing: .1em;
    margin-bottom: 5px;
    text-transform: uppercase;
}
.ef-bk-adv-input,
.ef-bk-adv-select {
    background: rgba(255, 253, 250, .7);
    border: 1.5px solid var(--ef-border-strong);
    border-radius: 9px;
    color: var(--ef-ink);
    font-family: inherit;
    font-size: .83rem;
    padding: 8px 11px;
    transition: border-color .15s;
    width: 100%;
}
.ef-bk-adv-input:focus,
.ef-bk-adv-select:focus { border-color: var(--ef-gold); outline: none; }
.ef-bk-adv-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23888' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 11px center;
    padding-right: 30px;
}
.ef-bk-adv-btns { align-items: center; display: flex; gap: 8px; }

/* ── Results bar ───────────────────────────────────────────────── */
.ef-bk-results-bar {
    align-items: center;
    display: flex;
    justify-content: space-between;
    margin-bottom: 16px;
}
.ef-bk-results-count { color: var(--ef-muted); font-size: .8rem; }
.ef-bk-results-count strong { color: var(--ef-ink); }

/* ── Booking grid ──────────────────────────────────────────────── */
.ef-bk-grid {
    display: grid;
    gap: 14px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-bottom: 24px;
}

/* ── Booking card ──────────────────────────────────────────────── */
.ef-bk-card {
    background: rgba(255, 253, 250, .96);
    border: 1px solid var(--ef-border);
    border-radius: 18px;
    box-shadow: var(--ef-shadow);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: border-color .18s var(--ef-ease), box-shadow .18s var(--ef-ease), transform .2s var(--ef-ease);
}
.ef-bk-card:hover {
    border-color: var(--ef-border-strong);
    box-shadow: var(--ef-shadow-hover);
    transform: translateY(-2px);
}
.ef-bk-card.--cancelled { opacity: .62; }

/* Card head */
.ef-bk-card-head {
    align-items: flex-start;
    display: flex;
    gap: 12px;
    padding: 18px 18px 14px;
}
.ef-bk-avatar {
    align-items: center;
    border-radius: 12px;
    color: rgba(255, 255, 255, .93);
    display: flex;
    flex-shrink: 0;
    font-size: .96rem;
    font-weight: 760;
    height: 44px;
    justify-content: center;
    letter-spacing: -.01em;
    width: 44px;
}
.ef-bk-head-info { flex: 1; min-width: 0; }
.ef-bk-customer-name {
    color: var(--ef-ink);
    font-size: 1.02rem;
    font-weight: 720;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ef-bk-customer-mobile {
    color: var(--ef-faint);
    font-size: .71rem;
    margin-top: 2px;
}
.ef-bk-event-badge {
    color: var(--ef-muted);
    font-size: .7rem;
    font-weight: 620;
    letter-spacing: .06em;
    margin-top: 5px;
    text-transform: uppercase;
}

/* Status chips */
.ef-bk-status {
    border-radius: 7px;
    flex-shrink: 0;
    font-size: .59rem;
    font-weight: 760;
    letter-spacing: .09em;
    padding: 3px 9px;
    text-transform: uppercase;
}
.ef-bk-status.--confirmed { background: rgba(60,140,100,.1); border: 1px solid rgba(60,140,100,.2); color: #2a7a54; }
.ef-bk-status.--completed { background: rgba(60,90,160,.1);  border: 1px solid rgba(60,90,160,.2);  color: #3050a0; }
.ef-bk-status.--cancelled { background: rgba(141,74,60,.07); border: 1px solid rgba(141,74,60,.18); color: var(--ef-danger); }

/* Card body */
.ef-bk-card-body {
    border-top: 1px solid var(--ef-border);
    flex: 1;
    padding: 14px 18px;
}
.ef-bk-detail {
    align-items: baseline;
    display: flex;
    gap: 9px;
    margin-bottom: 8px;
    min-width: 0;
}
.ef-bk-detail:last-child { margin-bottom: 0; }
.ef-bk-di {
    color: var(--ef-faint);
    flex-shrink: 0;
    font-size: .76rem;
    line-height: 1.5;
    text-align: center;
    width: 14px;
}
.ef-bk-dt {
    color: var(--ef-ink);
    font-size: .83rem;
    line-height: 1.45;
    min-width: 0;
}
.ef-bk-dt .dim { color: var(--ef-muted); }
.ef-bk-meal-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-top: 5px;
}
.ef-bk-meal-tag {
    background: rgba(180, 145, 90, .07);
    border: 1px solid rgba(180, 145, 90, .17);
    border-radius: 5px;
    color: #8a6c3a;
    font-size: .61rem;
    font-weight: 680;
    letter-spacing: .06em;
    padding: 2px 7px;
    text-transform: uppercase;
}

/* Card footer */
.ef-bk-card-foot {
    align-items: center;
    border-top: 1px solid var(--ef-border);
    display: flex;
    gap: 10px;
    justify-content: space-between;
    padding: 12px 18px;
}
.ef-bk-amount {
    color: var(--ef-ink);
    font-size: 1.08rem;
    font-variant-numeric: tabular-nums;
    font-weight: 760;
    line-height: 1;
}
.ef-bk-pay-chip {
    border-radius: 6px;
    display: inline-block;
    font-size: .59rem;
    font-weight: 760;
    letter-spacing: .08em;
    margin-top: 4px;
    padding: 2px 7px;
    text-transform: uppercase;
}
.ef-bk-pay-chip.--pending { background: rgba(170,120,30,.1); border: 1px solid rgba(170,120,30,.2); color: #8a6020; }
.ef-bk-pay-chip.--partial  { background: rgba(50,90,160,.1);  border: 1px solid rgba(50,90,160,.2);  color: #3050a0; }
.ef-bk-pay-chip.--paid     { background: rgba(60,140,100,.1); border: 1px solid rgba(60,140,100,.2); color: #2a7a54; }

.ef-bk-foot-actions {
    align-items: center;
    display: flex;
    flex-shrink: 0;
    gap: 6px;
}
.ef-bk-action {
    align-items: center;
    border: 1.5px solid var(--ef-border-strong);
    border-radius: 9px;
    color: var(--ef-muted);
    display: inline-flex;
    font-size: .76rem;
    gap: 4px;
    padding: 6px 10px;
    text-decoration: none;
    transition: border-color .15s, color .15s;
    white-space: nowrap;
}
.ef-bk-action:hover { border-color: var(--ef-ink); color: var(--ef-ink); }
.ef-bk-action.--primary { background: var(--ef-ink); border-color: var(--ef-ink); color: var(--ef-bg); }
.ef-bk-action.--primary:hover { color: var(--ef-bg); opacity: .85; }
.ef-bk-action.--wa:hover { border-color: #25d366; color: #25d366; }

.ef-bk-more-btn {
    align-items: center;
    background: transparent;
    border: 1.5px solid var(--ef-border-strong);
    border-radius: 9px;
    color: var(--ef-muted);
    cursor: pointer;
    display: inline-flex;
    font-size: .82rem;
    height: 34px;
    justify-content: center;
    padding: 0 8px;
    transition: border-color .15s, color .15s;
}
.ef-bk-more-btn:hover { border-color: var(--ef-ink); color: var(--ef-ink); }

/* ── Pagination ────────────────────────────────────────────────── */
.ef-bk-pagination { display: flex; justify-content: center; margin-bottom: 28px; }

/* ── Empty state ───────────────────────────────────────────────── */
.ef-bk-empty {
    background: rgba(255, 253, 250, .92);
    border: 1px solid var(--ef-border);
    border-radius: 20px;
    box-shadow: var(--ef-shadow);
    padding: 72px 32px;
    text-align: center;
}
.ef-bk-empty-icon { color: var(--ef-faint); font-size: 3rem; margin-bottom: 18px; }
.ef-bk-empty-title { color: var(--ef-ink); font-size: 1.3rem; font-weight: 720; margin-bottom: 8px; }
.ef-bk-empty-note  { color: var(--ef-muted); font-size: .88rem; margin-bottom: 24px; }

/* ── Flash messages ────────────────────────────────────────────── */
.ef-bk-flash {
    align-items: center;
    border-radius: 12px;
    display: flex;
    font-size: .84rem;
    gap: 10px;
    margin-bottom: 20px;
    padding: 14px 16px;
}
.ef-bk-flash.--success { background: rgba(60,140,100,.08); border: 1px solid rgba(60,140,100,.2); color: #2a7a54; }
.ef-bk-flash.--error   { background: rgba(141,74,60,.08); border: 1px solid rgba(141,74,60,.2); color: var(--ef-danger); }

/* ── Mobile bar ────────────────────────────────────────────────── */
.ef-bk-mob-bar {
    background: rgba(255, 253, 250, .96);
    backdrop-filter: blur(10px);
    border-top: 1px solid var(--ef-border);
    bottom: 0;
    box-shadow: 0 -2px 16px rgba(0,0,0,.06);
    display: none;
    gap: 10px;
    left: 0;
    padding: 12px 16px calc(12px + env(safe-area-inset-bottom, 0px));
    position: fixed;
    right: 0;
    z-index: 100;
}

/* ── Responsive ────────────────────────────────────────────────── */
@media (max-width: 1199.98px) {
    .ef-bk-hero     { grid-template-columns: minmax(0, 1fr); padding: 28px; }
    .ef-bk-hero-actions { justify-content: flex-start; }
    .ef-bk-insights { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
@media (max-width: 991.98px) {
    .ef-bk-grid { grid-template-columns: minmax(0, 1fr); }
}
@media (max-width: 767.98px) {
    .ef-bk-hero { padding: 20px; }
    .ef-bk-insights { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ef-bk-title    { font-size: clamp(1.6rem, 6vw, 2.2rem); }
    .ef-bk-search   { min-width: 0; flex: 1; }
    .ef-bk-card-foot { align-items: flex-start; flex-direction: column; gap: 10px; }
    .ef-bk-foot-actions { flex-wrap: wrap; }
    .ef-bk-mob-bar  { display: flex; }
}
@media (max-width: 479.98px) {
    .ef-bk-insights { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
    .ef-bk-filter-top { flex-wrap: wrap; }
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

$avatarTones = ['#a07238','#4e7a96','#3e8a60','#6a5e8c','#807050'];
@endphp

<div class="ef-bk-shell">

    {{-- Hero ────────────────────────────────────────────── --}}
    <div class="ef-bk-hero">
        <div>
            <p class="ef-bk-kicker">Venue Management</p>
            <h1 class="ef-bk-title">Hall Bookings</h1>
            <p class="ef-bk-subtitle">Manage venue reservations and operational schedules</p>
            <p class="ef-bk-date">{{ now()->format('l, d F Y') }}</p>
        </div>
        <div class="ef-bk-hero-actions">
            <a href="{{ route('hall.bookings.create') }}" class="ef-btn"
               style="background:var(--ef-gold);border-color:var(--ef-gold);color:#fffdfa;">
                <i class="bi bi-plus-circle"></i> New Booking
            </a>
            <a href="{{ route('hall.bookings.calendar') }}" class="ef-btn">
                <i class="bi bi-calendar3"></i> Calendar
            </a>
            <a href="{{ route('hall.bookings.kitchen') }}" class="ef-btn">
                <i class="bi bi-cup-hot"></i> Kitchen
            </a>
        </div>
    </div>

    {{-- Flash ───────────────────────────────────────────── --}}
    @if (session('success'))
        <div class="ef-bk-flash --success">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="ef-bk-flash --error">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Insights ────────────────────────────────────────── --}}
    <div class="ef-bk-insights">
        <div class="ef-bk-ins">
            <div class="ef-bk-ins-label">Today</div>
            <div class="ef-bk-ins-val">{{ $stats['today'] }}</div>
            <div class="ef-bk-ins-note">Active bookings today</div>
        </div>
        <div class="ef-bk-ins">
            <div class="ef-bk-ins-label">Upcoming</div>
            <div class="ef-bk-ins-val">{{ $stats['upcoming'] }}</div>
            <div class="ef-bk-ins-note">Events from today</div>
        </div>
        <div class="ef-bk-ins">
            <div class="ef-bk-ins-label">Pending Pay</div>
            <div class="ef-bk-ins-val">{{ $stats['pending_pay'] }}</div>
            <div class="ef-bk-ins-note">Awaiting payment</div>
        </div>
        <div class="ef-bk-ins">
            <div class="ef-bk-ins-label">Occupancy</div>
            <div class="ef-bk-ins-val">{{ $stats['month_occ'] }}<span style="font-size:.85rem;font-weight:640">%</span></div>
            <div class="ef-bk-ins-note">{{ now()->format('F') }} utilisation</div>
        </div>
        <div class="ef-bk-ins">
            <div class="ef-bk-ins-label">Guests</div>
            <div class="ef-bk-ins-val">{{ number_format($stats['week_guests']) }}</div>
            <div class="ef-bk-ins-note">This week total</div>
        </div>
    </div>

    {{-- Filter bar ───────────────────────────────────────── --}}
    <div class="ef-bk-filter-bar">
        <form method="GET" id="bkFilterForm">

            <div class="ef-bk-filter-top">
                {{-- Quick chips --}}
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

                {{-- Search + toggle --}}
                <div class="ef-bk-filter-right">
                    <input type="text" name="search" class="ef-bk-search"
                           placeholder="Name or mobile…"
                           value="{{ request('search') }}" autocomplete="off">
                    <button type="button" id="bkFilterToggle"
                            class="ef-bk-filter-toggle {{ $hasAdvFilter ? '--active' : '' }}"
                            onclick="bkToggleAdv()">
                        <i class="bi bi-sliders"></i> Filters
                        @if($hasAdvFilter)
                            <span style="background:var(--ef-gold);border-radius:50%;display:inline-block;height:6px;width:6px;flex-shrink:0"></span>
                        @endif
                    </button>
                </div>
            </div>

            {{-- Advanced panel --}}
            <div class="ef-bk-adv-panel {{ $hasAdvFilter ? '--open' : '' }}" id="bkAdvPanel">
                <div class="ef-bk-adv-inner">
                    <div>
                        <label class="ef-bk-adv-label">Hall</label>
                        <select name="hall_id" class="ef-bk-adv-select">
                            <option value="">All Halls</option>
                            @foreach($halls as $h)
                                <option value="{{ $h->id }}" {{ request('hall_id') == $h->id ? 'selected' : '' }}>
                                    {{ $h->name }}
                                </option>
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
                        <label class="ef-bk-adv-label">From Date</label>
                        <input type="date" name="date_from" class="ef-bk-adv-input"
                               value="{{ request('date_from') }}">
                    </div>
                    <div>
                        <label class="ef-bk-adv-label">To Date</label>
                        <input type="date" name="date_to" class="ef-bk-adv-input"
                               value="{{ request('date_to') }}">
                    </div>
                    <div style="display:flex;flex-direction:column;justify-content:flex-end;">
                        <div class="ef-bk-adv-btns">
                            <button type="submit" class="ef-btn"
                                    style="background:var(--ef-gold);border-color:var(--ef-gold);color:#fffdfa;font-size:.8rem;padding:8px 16px;">
                                Apply
                            </button>
                            <a href="{{ route('hall.bookings.index') }}" class="ef-btn"
                               style="font-size:.8rem;padding:8px 16px;">
                                Clear
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

    {{-- Results count ────────────────────────────────────── --}}
    <div class="ef-bk-results-bar">
        <p class="ef-bk-results-count">
            Showing <strong>{{ $bookings->total() }}</strong> {{ Str::plural('booking', $bookings->total()) }}
            @if(request()->hasAny(['search','hall_id','status','payment_status','date_from','date_to']))
                <span style="color:var(--ef-faint)"> · filtered</span>
            @endif
        </p>
    </div>

    {{-- Booking grid ─────────────────────────────────────── --}}
    @if($bookings->isNotEmpty())
    <div class="ef-bk-grid">
        @foreach($bookings as $b)
        @php
            $tone   = $avatarTones[ord(strtoupper($b->customer_name[0] ?? 'A')) % count($avatarTones)];
            $meals  = collect(['Breakfast' => $b->has_breakfast, 'Lunch' => $b->has_lunch, 'Dinner' => $b->has_dinner])->filter()->keys();
            $waUrl  = 'https://wa.me/91' . preg_replace('/\D/', '', $b->customer_mobile ?? '');
            $evType = \App\Models\HallBooking::eventTypes()[$b->event_type] ?? ucwords(str_replace('_', ' ', $b->event_type));
        @endphp

        <div class="ef-bk-card {{ $b->status === 'cancelled' ? '--cancelled' : '' }}">

            {{-- Head --}}
            <div class="ef-bk-card-head">
                <div class="ef-bk-avatar" style="background:{{ $tone }}">
                    {{ strtoupper(mb_substr($b->customer_name, 0, 1)) }}
                </div>
                <div class="ef-bk-head-info">
                    <div class="ef-bk-customer-name">{{ $b->customer_name }}</div>
                    <div class="ef-bk-customer-mobile">{{ $b->customer_mobile }}</div>
                    <div class="ef-bk-event-badge">{{ $evType }}</div>
                </div>
                <span class="ef-bk-status --{{ $b->status }}">{{ $b->status }}</span>
            </div>

            {{-- Body --}}
            <div class="ef-bk-card-body">
                <div class="ef-bk-detail">
                    <span class="ef-bk-di"><i class="bi bi-building"></i></span>
                    <span class="ef-bk-dt">{{ $b->hall->name }}</span>
                </div>
                <div class="ef-bk-detail">
                    <span class="ef-bk-di"><i class="bi bi-calendar3"></i></span>
                    <span class="ef-bk-dt">
                        {{ $b->booking_date->format('d M Y') }}
                        <span class="dim"> · {{ $b->booking_date->format('l') }}</span>
                    </span>
                </div>
                <div class="ef-bk-detail">
                    <span class="ef-bk-di"><i class="bi bi-clock"></i></span>
                    <span class="ef-bk-dt">
                        {{ \Carbon\Carbon::parse($b->start_time)->format('h:i A') }}
                        <span class="dim"> – </span>
                        {{ \Carbon\Carbon::parse($b->end_time)->format('h:i A') }}
                    </span>
                </div>
                <div class="ef-bk-detail">
                    <span class="ef-bk-di"><i class="bi bi-people"></i></span>
                    <span class="ef-bk-dt">{{ number_format($b->number_of_people) }} guests</span>
                </div>
                @if($b->mealPlan || $meals->isNotEmpty())
                <div class="ef-bk-detail">
                    <span class="ef-bk-di"><i class="bi bi-egg-fried"></i></span>
                    <span class="ef-bk-dt">
                        {{ $b->mealPlan?->name ?? 'Catering' }}
                        @if($meals->isNotEmpty())
                        <div class="ef-bk-meal-tags">
                            @foreach($meals as $m)
                                <span class="ef-bk-meal-tag">{{ $m }}</span>
                            @endforeach
                        </div>
                        @endif
                    </span>
                </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="ef-bk-card-foot">
                <div>
                    <div class="ef-bk-amount">₹{{ number_format($b->total_amount) }}</div>
                    <span class="ef-bk-pay-chip --{{ $b->payment_status }}">
                        {{ \App\Models\HallBooking::paymentStatuses()[$b->payment_status] ?? $b->payment_status }}
                    </span>
                </div>
                <div class="ef-bk-foot-actions">
                    <a href="{{ route('hall.bookings.show', $b) }}" class="ef-bk-action --primary">
                        <i class="bi bi-eye"></i> View
                    </a>
                    <a href="{{ route('hall.bookings.invoice', $b) }}" class="ef-bk-action"
                       target="_blank" title="Invoice">
                        <i class="bi bi-receipt"></i>
                    </a>
                    <a href="{{ $waUrl }}" class="ef-bk-action --wa" target="_blank" title="WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                    <div class="dropdown">
                        <button class="ef-bk-more-btn" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false"
                                title="More actions">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end"
                            style="border-radius:12px;border:1px solid var(--ef-border);box-shadow:var(--ef-shadow-hover);font-size:.82rem;min-width:168px;">
                            <li>
                                <a class="dropdown-item py-2"
                                   href="{{ route('hall.bookings.edit', $b) }}">
                                    <i class="bi bi-pencil me-2" style="color:var(--ef-faint)"></i>
                                    Edit Booking
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2"
                                   href="{{ route('hall.bookings.show', $b) }}#record-payment">
                                    <i class="bi bi-cash-coin me-2" style="color:var(--ef-faint)"></i>
                                    Record Payment
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2"
                                   href="{{ route('hall.bookings.invoice.pdf', $b) }}">
                                    <i class="bi bi-file-pdf me-2" style="color:var(--ef-faint)"></i>
                                    Download PDF
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
        <div class="ef-bk-empty-icon"><i class="bi bi-calendar-x"></i></div>
        <h3 class="ef-bk-empty-title">No bookings found</h3>
        <p class="ef-bk-empty-note">
            @if(request()->hasAny(['search','hall_id','status','payment_status','date_from','date_to']))
                No bookings match your current filters. Try broadening your search.
            @else
                No hall bookings yet. Create your first reservation to get started.
            @endif
        </p>
        @if(request()->hasAny(['search','hall_id','status','payment_status','date_from','date_to']))
            <a href="{{ route('hall.bookings.index') }}" class="ef-btn">Clear Filters</a>
        @else
            <a href="{{ route('hall.bookings.create') }}" class="ef-btn"
               style="background:var(--ef-gold);border-color:var(--ef-gold);color:#fffdfa;">
                <i class="bi bi-plus-circle"></i> New Booking
            </a>
        @endif
    </div>
    @endif

    {{-- Pagination ───────────────────────────────────────── --}}
    @if($bookings->hasPages())
        <div class="ef-bk-pagination">
            {{ $bookings->links() }}
        </div>
    @endif

</div>

{{-- Mobile sticky bar ───────────────────────────────────── --}}
<div class="ef-bk-mob-bar">
    <a href="{{ route('hall.bookings.create') }}"
       class="ef-btn w-100 justify-content-center"
       style="background:var(--ef-gold);border-color:var(--ef-gold);color:#fffdfa;">
        <i class="bi bi-plus-circle"></i> New Booking
    </a>
</div>

@push('scripts')
<script>
function bkToggleAdv() {
    const panel = document.getElementById('bkAdvPanel');
    const btn   = document.getElementById('bkFilterToggle');
    const open  = panel.classList.toggle('--open');
    btn.classList.toggle('--active', open);
}

// Disable empty fields before submit to keep URL clean
document.getElementById('bkFilterForm').addEventListener('submit', function () {
    this.querySelectorAll('input[type="text"], input[type="date"], select').forEach(el => {
        if (!el.value.trim()) el.disabled = true;
    });
});
</script>
@endpush

</x-admin-layout>
