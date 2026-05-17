<x-admin-layout title="Venue Operations Calendar">
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
<style>
/* ── Calendar header: dark dramatic override ───────────────────── */
.ef-cal-header {
    background: linear-gradient(135deg, #1a1410 0%, #2a2218 50%, #1e1812 100%);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(26,22,18,.16), 0 1px 4px rgba(26,22,18,.1);
    margin-bottom: 24px;
    overflow: hidden;
    padding: 32px;
    position: relative;
}
.ef-cal-header::before {
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
.ef-cal-header::after {
    background: radial-gradient(circle, rgba(26,102,69,.1) 0%, transparent 70%);
    border-radius: 50%;
    bottom: -80px;
    content: "";
    height: 260px;
    left: 28%;
    pointer-events: none;
    position: absolute;
    width: 260px;
}
.ef-cal-kicker  { color: rgba(160,114,56,.9) !important; }
.ef-cal-title   { color: #fffdfa !important; }
.ef-cal-subtitle { color: rgba(255,253,250,.52) !important; }
.ef-cal-controls { position: relative; z-index: 1; }
.ef-cal-controls .ef-btn {
    background: rgba(255,255,255,.08);
    border-color: rgba(255,255,255,.14);
    color: rgba(255,253,250,.85);
}
.ef-cal-controls .ef-btn:hover {
    background: rgba(255,255,255,.14);
    color: #fffdfa;
}
.ef-cal-controls .ef-btn-dark {
    background: #a07238;
    border-color: #a07238;
    color: #fff;
}
.ef-cal-controls .ef-btn-dark:hover {
    background: #b8854a;
    border-color: #b8854a;
}
.ef-cal-select,
.ef-cal-search {
    background: rgba(255,255,255,.07);
    border-color: rgba(255,255,255,.14);
    color: rgba(255,253,250,.88);
}
.ef-cal-select::placeholder,
.ef-cal-search::placeholder { color: rgba(255,253,250,.32); }
.ef-cal-select:focus,
.ef-cal-search:focus {
    border-color: rgba(160,114,56,.6);
    box-shadow: 0 0 0 3px rgba(160,114,56,.12);
}

/* ── Mobile overrides (hide desktop elements) ─────────────────── */
@media (max-width: 767.98px) {
    .ef-cal-header   { display: none !important; }
    .ef-cal-insights { display: none !important; }
    .ef-calendar-card { display: none !important; }
    .ef-agenda-panel  { display: none !important; }
    .ef-preview       { display: none !important; }
}

/* ── Mobile shell (hidden on desktop) ─────────────────────────── */
.ef-mob-shell { display: none; }

@media (max-width: 767.98px) {
    .ef-mob-shell {
        display: flex;
        flex-direction: column;
        padding-bottom: 84px;
    }
}

/* ── Mobile header ─────────────────────────────────────────────── */
.ef-mob-hdr {
    align-items: center;
    display: flex;
    gap: 10px;
    justify-content: space-between;
    padding: 10px 0 14px;
}
.ef-mob-month-nav {
    align-items: center;
    display: flex;
    gap: 2px;
}
.ef-mob-month-label {
    color: var(--ef-ink);
    font-size: 1.1rem;
    font-weight: 760;
    letter-spacing: -.01em;
    min-width: 118px;
    text-align: center;
}
.ef-mob-nav-btn {
    align-items: center;
    background: rgba(20,20,18,.05);
    border: 1px solid var(--ef-border);
    border-radius: 9px;
    color: var(--ef-ink-2);
    cursor: pointer;
    display: inline-flex;
    font-size: .9rem;
    height: 32px;
    justify-content: center;
    transition: background .12s;
    width: 32px;
}
.ef-mob-nav-btn:active { background: rgba(20,20,18,.12); }
.ef-mob-hdr-actions {
    align-items: center;
    display: flex;
    gap: 5px;
}
.ef-mob-hdr-btn {
    align-items: center;
    background: rgba(255,253,250,.9);
    border: 1px solid var(--ef-border);
    border-radius: 9px;
    color: var(--ef-ink-2);
    cursor: pointer;
    display: inline-flex;
    font-size: .73rem;
    font-weight: 700;
    height: 32px;
    padding: 0 9px;
    transition: background .12s;
    white-space: nowrap;
    -webkit-appearance: none;
    appearance: none;
}
.ef-mob-hdr-btn:active { background: var(--ef-surface-2); }
.ef-mob-hdr-btn.--new {
    background: var(--ef-ink);
    border-color: var(--ef-ink);
    color: #fffdfa;
    font-size: 1rem;
    padding: 0 11px;
    text-decoration: none;
}

/* ── Insight strip ─────────────────────────────────────────────── */
.ef-mob-insights {
    background: rgba(255,253,250,.92);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    display: grid;
    gap: 1px;
    grid-template-columns: repeat(3, minmax(0,1fr));
    margin-bottom: 12px;
    overflow: hidden;
}
.ef-mob-ins {
    background: rgba(255,253,250,.92);
    display: flex;
    flex-direction: column;
    gap: 3px;
    padding: 11px 10px;
    position: relative;
}
.ef-mob-ins + .ef-mob-ins::before {
    background: var(--ef-border);
    bottom: 18%;
    content: '';
    left: 0;
    position: absolute;
    top: 18%;
    width: 1px;
}
.ef-mob-ins-val {
    color: var(--ef-ink);
    font-size: 1.2rem;
    font-variant-numeric: tabular-nums;
    font-weight: 780;
    line-height: 1;
}
.ef-mob-ins-lbl {
    color: var(--ef-faint);
    font-size: .6rem;
    font-weight: 720;
    letter-spacing: .1em;
    text-transform: uppercase;
}
.ef-mob-ins-val.--emerald { color: var(--ef-emerald); }
.ef-mob-ins-val.--gold    { color: var(--ef-gold); }
.ef-mob-ins-val.--danger  { color: var(--ef-danger); }

/* ── Month calendar grid ───────────────────────────────────────── */
.ef-mob-cal-wrap {
    background: rgba(255,253,250,.94);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    margin-bottom: 14px;
    overflow: hidden;
    padding: 12px 10px 8px;
}
.ef-mob-cal-dow {
    display: grid;
    grid-template-columns: repeat(7,1fr);
    margin-bottom: 2px;
}
.ef-mob-cal-dow-cell {
    color: var(--ef-faint);
    font-size: .6rem;
    font-weight: 760;
    letter-spacing: .08em;
    padding: 4px 0;
    text-align: center;
    text-transform: uppercase;
}
.ef-mob-cal-dow-cell:nth-child(6),
.ef-mob-cal-dow-cell:nth-child(7) { color: var(--ef-muted); }

.ef-mob-cal-grid {
    display: grid;
    gap: 2px;
    grid-template-columns: repeat(7,1fr);
}
.ef-mob-cal-day {
    aspect-ratio: 1;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    padding: 3px 1px;
    position: relative;
    transition: background .1s;
    -webkit-tap-highlight-color: transparent;
}
.ef-mob-cal-day:active { opacity: .7; }
.ef-mob-cal-day.--empty,
.ef-mob-cal-day.--other { cursor: default; opacity: .28; pointer-events: none; }
.ef-mob-cal-num {
    align-items: center;
    border-radius: 50%;
    color: var(--ef-ink-2);
    display: inline-flex;
    font-size: .76rem;
    font-weight: 700;
    height: 24px;
    justify-content: center;
    line-height: 1;
    transition: background .1s, color .1s;
    width: 24px;
}
.ef-mob-cal-day.--weekend .ef-mob-cal-num { color: var(--ef-muted); }
.ef-mob-cal-day.--today .ef-mob-cal-num {
    background: var(--ef-ink);
    color: #fffdfa;
}
.ef-mob-cal-day.--selected .ef-mob-cal-num {
    background: var(--ef-gold);
    color: #fffdfa;
}
/* Occupancy heatmap */
.ef-mob-cal-day.--occ-1 { background: rgba(169,131,56,.09); }
.ef-mob-cal-day.--occ-2 { background: rgba(169,131,56,.17); }
.ef-mob-cal-day.--occ-3 { background: rgba(61,115,88,.13); }
.ef-mob-cal-day.--selected { background: rgba(169,131,56,.18) !important; }
/* Occupancy dots */
.ef-mob-cal-dots {
    align-items: center;
    display: flex;
    gap: 2px;
    justify-content: center;
    min-height: 5px;
}
.ef-mob-cal-dot {
    border-radius: 50%;
    flex-shrink: 0;
    height: 4px;
    width: 4px;
}
.ef-mob-cal-day.--occ-1 .ef-mob-cal-dot { background: rgba(169,131,56,.65); }
.ef-mob-cal-day.--occ-2 .ef-mob-cal-dot { background: rgba(169,131,56,.85); }
.ef-mob-cal-day.--occ-3 .ef-mob-cal-dot { background: var(--ef-emerald); }

/* ── Upcoming list ─────────────────────────────────────────────── */
.ef-mob-sec-hdr {
    align-items: center;
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    padding: 0 2px;
}
.ef-mob-sec-title {
    color: var(--ef-faint);
    font-size: .62rem;
    font-weight: 760;
    letter-spacing: .13em;
    text-transform: uppercase;
}
.ef-mob-sec-action {
    background: none;
    border: none;
    color: var(--ef-muted);
    cursor: pointer;
    font-size: .7rem;
    font-weight: 700;
    padding: 0;
}
.ef-mob-upcoming-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.ef-mob-ev-row {
    align-items: center;
    background: rgba(255,253,250,.92);
    border: 1px solid var(--ef-border);
    border-left: 3px solid var(--ef-gold);
    border-radius: 11px;
    box-shadow: 0 1px 3px rgba(24,22,18,.04);
    color: inherit;
    display: grid;
    gap: 10px;
    grid-template-columns: minmax(0,1fr) auto;
    padding: 10px 12px;
    text-decoration: none;
    -webkit-tap-highlight-color: transparent;
}
.ef-mob-ev-row:active { opacity: .8; }
.ef-mob-ev-row.--confirmed { border-left-color: var(--ef-emerald); }
.ef-mob-ev-row.--completed { border-left-color: var(--ef-bluegray); }
.ef-mob-ev-row.--cancelled { border-left-color: var(--ef-danger); opacity: .7; }
.ef-mob-ev-name {
    color: var(--ef-ink);
    font-size: .82rem;
    font-weight: 760;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ef-mob-ev-meta {
    color: var(--ef-muted);
    font-size: .71rem;
    margin-top: 2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ef-mob-ev-date {
    color: var(--ef-ink);
    font-size: .68rem;
    font-variant-numeric: tabular-nums;
    font-weight: 700;
    text-align: right;
    white-space: nowrap;
}
.ef-mob-ev-amt {
    color: var(--ef-muted);
    font-size: .67rem;
    margin-top: 2px;
    text-align: right;
}
.ef-mob-chip {
    background: rgba(20,20,18,.045);
    border: 1px solid rgba(20,20,18,.065);
    border-radius: 999px;
    display: inline-block;
    font-size: .57rem;
    font-weight: 720;
    margin-top: 3px;
    padding: 2px 6px;
    text-transform: uppercase;
}
.ef-mob-chip.--pending { background: rgba(169,131,56,.1);  color: #806127; }
.ef-mob-chip.--partial { background: rgba(96,112,128,.1); color: #566777; }
.ef-mob-chip.--paid    { background: rgba(61,115,88,.1);  color: #3d7358; }
.ef-mob-empty {
    color: var(--ef-muted);
    font-size: .82rem;
    padding: 18px 0 8px;
    text-align: center;
}

/* ── Bottom sheet ──────────────────────────────────────────────── */
.ef-mob-overlay {
    background: rgba(14,13,12,.52);
    bottom: 0;
    left: 0;
    opacity: 0;
    pointer-events: none;
    position: fixed;
    right: 0;
    top: 0;
    transition: opacity .22s;
    z-index: 1060;
}
.ef-mob-overlay.--on {
    opacity: 1;
    pointer-events: auto;
}
.ef-mob-sheet {
    background: var(--ef-surface);
    border-radius: 20px 20px 0 0;
    bottom: 0;
    box-shadow: 0 -10px 56px rgba(14,13,12,.18);
    display: flex;
    flex-direction: column;
    left: 0;
    max-height: 80dvh;
    position: fixed;
    right: 0;
    transform: translateY(100%);
    transition: transform .28s cubic-bezier(.18,.82,.16,1);
    z-index: 1061;
}
.ef-mob-sheet.--open { transform: translateY(0); }
.ef-mob-sheet-handle {
    background: var(--ef-border-strong);
    border-radius: 999px;
    height: 4px;
    margin: 10px auto 0;
    width: 36px;
}
.ef-mob-sheet-hdr {
    align-items: center;
    border-bottom: 1px solid var(--ef-border);
    display: flex;
    justify-content: space-between;
    padding: 12px 18px 13px;
}
.ef-mob-sheet-day-kicker {
    color: var(--ef-faint);
    font-size: .6rem;
    font-weight: 760;
    letter-spacing: .12em;
    margin-bottom: 3px;
    text-transform: uppercase;
}
.ef-mob-sheet-date {
    color: var(--ef-ink);
    font-size: 1rem;
    font-weight: 760;
}
.ef-mob-sheet-close {
    align-items: center;
    background: rgba(20,20,18,.055);
    border: 1px solid var(--ef-border);
    border-radius: 50%;
    color: var(--ef-muted);
    cursor: pointer;
    display: inline-flex;
    font-size: .85rem;
    height: 30px;
    justify-content: center;
    width: 30px;
}
.ef-mob-sheet-body {
    flex: 1;
    overflow-y: auto;
    overscroll-behavior: contain;
    padding: 0 16px;
    -webkit-overflow-scrolling: touch;
}
.ef-mob-sheet-booking {
    border-bottom: 1px solid var(--ef-border);
    padding: 13px 0;
}
.ef-mob-sheet-booking:last-child { border-bottom: 0; }
.ef-mob-sheet-bname {
    color: var(--ef-ink);
    font-size: .88rem;
    font-weight: 760;
    margin-bottom: 4px;
}
.ef-mob-sheet-bmeta {
    color: var(--ef-muted);
    font-size: .74rem;
    line-height: 1.5;
    margin-bottom: 8px;
}
.ef-mob-sheet-brow {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    justify-content: space-between;
}
.ef-mob-sheet-amt {
    color: var(--ef-ink);
    font-size: .86rem;
    font-variant-numeric: tabular-nums;
    font-weight: 760;
}
.ef-mob-sheet-bal {
    color: var(--ef-muted);
    font-size: .7rem;
    margin-top: 1px;
}
.ef-mob-sheet-actions {
    align-items: center;
    display: flex;
    gap: 5px;
}
.ef-mob-sheet-act {
    align-items: center;
    background: rgba(20,20,18,.04);
    border: 1px solid var(--ef-border);
    border-radius: 8px;
    color: var(--ef-ink-2);
    display: inline-flex;
    font-size: .71rem;
    font-weight: 700;
    gap: 4px;
    height: 28px;
    padding: 0 9px;
    text-decoration: none;
    white-space: nowrap;
}
.ef-mob-sheet-act.--wa {
    background: rgba(37,211,102,.1);
    border-color: rgba(37,211,102,.22);
    color: #1a7a3d;
}
.ef-mob-sheet-foot {
    border-top: 1px solid var(--ef-border);
    padding: 11px 16px 14px;
}
.ef-mob-sheet-create {
    align-items: center;
    background: var(--ef-ink);
    border-radius: 12px;
    color: #fffdfa;
    display: flex;
    font-size: .83rem;
    font-weight: 760;
    gap: 8px;
    justify-content: center;
    padding: 13px 18px;
    text-decoration: none;
}
.ef-mob-sheet-create:hover,
.ef-mob-sheet-create:active { background: var(--ef-ink-2); color: #fffdfa; }

/* ── Premium FAB (circular on mobile) ─────────────────────────── */
@media (max-width: 767.98px) {
    .ef-mobile-fab {
        align-items: center;
        background: var(--ef-ink);
        border-radius: 50%;
        bottom: 24px;
        box-shadow: 0 8px 28px rgba(14,13,12,.28), 0 2px 6px rgba(14,13,12,.14);
        color: #fffdfa;
        display: inline-flex;
        font-size: 1.2rem;
        height: 54px;
        justify-content: center;
        padding: 0;
        position: fixed;
        right: 20px;
        text-decoration: none;
        width: 54px;
        z-index: 1040;
    }
    .ef-mobile-fab .ef-mob-fab-label { display: none; }
}
</style>
@endpush

@php
    $currentMonth = now()->format('F Y');
    $todayLabel   = now()->format('l, d M Y');
    $shareText    = "Akshathay Mini Hall schedule for " . now()->format('d M Y') . "\n" . route('hall.bookings.calendar');
@endphp

<div class="ef-cal-shell">

    {{-- ══ DESKTOP HEADER ══════════════════════════════════════════ --}}
    <header class="ef-cal-header">
        <div>
            <div class="ef-cal-kicker">Luxury Venue Operations</div>
            <h1 class="ef-cal-title">Calendar Overview</h1>
            <div class="ef-cal-subtitle">
                <span id="calendarPeriod">{{ $currentMonth }}</span>
                <span>{{ $summary['total_bookings'] }} bookings</span>
                <span>{{ $summary['occupancy'] }}% occupancy</span>
                <span>{{ $todayLabel }}</span>
            </div>
        </div>
        <div class="ef-cal-controls">
            <select id="hallFilter" class="ef-cal-select" aria-label="Filter by hall">
                <option value="">All Halls</option>
                @foreach($halls as $hall)
                    <option value="{{ $hall->id }}">{{ $hall->name }}</option>
                @endforeach
            </select>
            <input id="calendarSearch" type="search" class="ef-cal-search" placeholder="Search customer, hall, event">
            <button type="button" class="ef-btn" id="printSchedule"><i class="bi bi-printer"></i> Print</button>
            <button type="button" class="ef-btn" id="exportSchedule"><i class="bi bi-download"></i> Export</button>
            <a href="https://wa.me/?text={{ rawurlencode($shareText) }}" target="_blank" rel="noopener" class="ef-btn">
                <i class="bi bi-whatsapp"></i> Share
            </a>
            <a href="{{ route('hall.bookings.create') }}" class="ef-btn ef-btn-dark">
                <i class="bi bi-plus-lg"></i> New Booking
            </a>
        </div>
    </header>

    {{-- ══ DESKTOP INSIGHTS ════════════════════════════════════════ --}}
    <section class="ef-cal-insights" aria-label="Monthly booking insights">
        <div class="ef-cal-insight">
            <span class="ef-label">Month Bookings</span>
            <div class="ef-cal-insight-value">{{ number_format($summary['total_bookings']) }}</div>
            <div class="ef-cal-insight-caption">confirmed operational load</div>
        </div>
        <div class="ef-cal-insight">
            <span class="ef-label">Upcoming</span>
            <div class="ef-cal-insight-value">{{ number_format($summary['upcoming_events']) }}</div>
            <div class="ef-cal-insight-caption">events from today</div>
        </div>
        <div class="ef-cal-insight">
            <span class="ef-label">Revenue</span>
            <div class="ef-cal-insight-value">₹{{ number_format($summary['revenue'], 0) }}</div>
            <div class="ef-cal-insight-caption">booked this month</div>
        </div>
        <div class="ef-cal-insight">
            <span class="ef-label">Occupancy</span>
            <div class="ef-cal-insight-value">{{ $summary['occupancy'] }}%</div>
            <div class="ef-cal-insight-caption">days with bookings</div>
        </div>
        <div class="ef-cal-insight">
            <span class="ef-label">Pending Pay</span>
            <div class="ef-cal-insight-value">{{ number_format($summary['pending_payments']) }}</div>
            <div class="ef-cal-insight-caption">need follow-up</div>
        </div>
        <div class="ef-cal-insight">
            <span class="ef-label">Catering Load</span>
            <div class="ef-cal-insight-value">{{ number_format($summary['catering_load']) }}</div>
            <div class="ef-cal-insight-caption">guest covers planned</div>
        </div>
    </section>

    {{-- ══ DESKTOP FULLCALENDAR ════════════════════════════════════ --}}
    <section class="ef-calendar-card">
        <div class="ef-calendar-toolbar">
            <div class="ef-cal-nav">
                <button type="button" class="ef-btn ef-btn-icon" id="calPrev" aria-label="Previous period"><i class="bi bi-chevron-left"></i></button>
                <button type="button" class="ef-btn" id="calToday">Today</button>
                <button type="button" class="ef-btn ef-btn-icon" id="calNext" aria-label="Next period"><i class="bi bi-chevron-right"></i></button>
            </div>
            <div class="ef-cal-month" id="calendarTitle">{{ $currentMonth }}</div>
            <div class="ef-view-switcher" aria-label="Calendar view">
                <button type="button" class="ef-view-btn active" data-view="dayGridMonth">Month</button>
                <button type="button" class="ef-view-btn" data-view="timeGridWeek">Week</button>
                <button type="button" class="ef-view-btn" data-view="timeGridDay">Day</button>
                <button type="button" class="ef-view-btn" data-view="listWeek">Agenda</button>
            </div>
        </div>
        <div class="ef-calendar-wrap">
            <div id="venueCalendar"></div>
        </div>
    </section>

    <section class="ef-agenda-panel" id="mobileAgenda" aria-label="Mobile agenda"></section>

    {{-- ══ MOBILE CALENDAR SHELL ═══════════════════════════════════ --}}
    <div class="ef-mob-shell" id="mobShell" aria-label="Mobile calendar">

        {{-- Compact header --}}
        <div class="ef-mob-hdr">
            <div class="ef-mob-month-nav">
                <button type="button" class="ef-mob-nav-btn" id="mobPrev" aria-label="Previous month">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <span class="ef-mob-month-label" id="mobMonthLabel">{{ $currentMonth }}</span>
                <button type="button" class="ef-mob-nav-btn" id="mobNext" aria-label="Next month">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
            <div class="ef-mob-hdr-actions">
                <button type="button" class="ef-mob-hdr-btn" id="mobTodayBtn">Today</button>
                <select id="mobHallFilter" class="ef-mob-hdr-btn" aria-label="Filter hall">
                    <option value="">All</option>
                    @foreach($halls as $hall)
                        <option value="{{ $hall->id }}">{{ $hall->name }}</option>
                    @endforeach
                </select>
                <a href="{{ route('hall.bookings.create') }}" class="ef-mob-hdr-btn --new" aria-label="New booking">
                    <i class="bi bi-plus-lg"></i>
                </a>
            </div>
        </div>

        {{-- 3-item insight strip --}}
        <div class="ef-mob-insights">
            <div class="ef-mob-ins">
                <div class="ef-mob-ins-val --emerald">{{ $summary['occupancy'] }}%</div>
                <div class="ef-mob-ins-lbl">Occupancy</div>
            </div>
            <div class="ef-mob-ins">
                <div class="ef-mob-ins-val --gold" id="mobPendingVal">{{ $summary['pending_payments'] }}</div>
                <div class="ef-mob-ins-lbl">Pending Pay</div>
            </div>
            <div class="ef-mob-ins">
                <div class="ef-mob-ins-val" id="mobUpcomingVal">{{ $summary['upcoming_events'] }}</div>
                <div class="ef-mob-ins-lbl">Upcoming</div>
            </div>
        </div>

        {{-- Custom month grid --}}
        <div class="ef-mob-cal-wrap">
            <div class="ef-mob-cal-dow" aria-hidden="true">
                <div class="ef-mob-cal-dow-cell">Mo</div>
                <div class="ef-mob-cal-dow-cell">Tu</div>
                <div class="ef-mob-cal-dow-cell">We</div>
                <div class="ef-mob-cal-dow-cell">Th</div>
                <div class="ef-mob-cal-dow-cell">Fr</div>
                <div class="ef-mob-cal-dow-cell">Sa</div>
                <div class="ef-mob-cal-dow-cell">Su</div>
            </div>
            <div class="ef-mob-cal-grid" id="mobCalGrid" role="grid" aria-label="Calendar dates"></div>
        </div>

        {{-- Upcoming events --}}
        <div>
            <div class="ef-mob-sec-hdr">
                <span class="ef-mob-sec-title">Upcoming Bookings</span>
                <button type="button" class="ef-mob-sec-action" id="mobToggleAll">See all</button>
            </div>
            <div class="ef-mob-upcoming-list" id="mobUpcomingList"></div>
        </div>

    </div>
</div>

{{-- ══ FAB ══════════════════════════════════════════════════════════ --}}
<a href="{{ route('hall.bookings.create') }}" class="ef-mobile-fab" aria-label="New Booking">
    <i class="bi bi-plus-lg"></i>
    <span class="ef-mob-fab-label"> Booking</span>
</a>

{{-- ══ DESKTOP HOVER PREVIEW ════════════════════════════════════════ --}}
<div class="ef-preview" id="bookingPreview" aria-live="polite"></div>

{{-- ══ QUICK BOOKING MODAL ══════════════════════════════════════════ --}}
<div class="modal fade ef-quick-modal" id="quickBookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="ef-label mb-1">Fast Operation</div>
                    <h2 class="modal-title fs-5 fw-bold mb-0">Create booking</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="ef-shell-note mb-4">Start a new booking from the selected date. The full booking form will open with the date and hall context attached.</p>
                <div class="ef-info-grid">
                    <div>
                        <span class="ef-label">Selected Date</span>
                        <div class="ef-value ef-value-strong" id="quickDateLabel">-</div>
                    </div>
                    <div>
                        <span class="ef-label">Hall Context</span>
                        <div class="ef-value ef-value-strong" id="quickHallLabel">All Halls</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="ef-btn" data-bs-dismiss="modal">Cancel</button>
                <a href="{{ route('hall.bookings.create') }}" class="ef-btn ef-btn-dark" id="quickCreateLink">
                    Continue <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ══ MOBILE BOTTOM SHEET ══════════════════════════════════════════ --}}
<div class="ef-mob-overlay" id="mobOverlay" aria-hidden="true"></div>
<div class="ef-mob-sheet" id="mobSheet" role="dialog" aria-modal="true" aria-label="Day detail">
    <div class="ef-mob-sheet-handle" aria-hidden="true"></div>
    <div class="ef-mob-sheet-hdr">
        <div>
            <div class="ef-mob-sheet-day-kicker" id="mobSheetKicker">Date</div>
            <div class="ef-mob-sheet-date" id="mobSheetDate">—</div>
        </div>
        <button type="button" class="ef-mob-sheet-close" id="mobSheetClose" aria-label="Close">
            <i class="bi bi-x"></i>
        </button>
    </div>
    <div class="ef-mob-sheet-body" id="mobSheetBody"></div>
    <div class="ef-mob-sheet-foot">
        <a href="{{ route('hall.bookings.create') }}" class="ef-mob-sheet-create" id="mobSheetCreate">
            <i class="bi bi-plus-lg"></i> New Booking for this Date
        </a>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isMob = () => window.innerWidth < 768;

    /* ── shared ─────────────────────────────────────────────────── */
    const eventsUrl  = @json(route('hall.bookings.calendar-events'));
    const createBase = @json(route('hall.bookings.create'));

    let mobEvents    = [];
    let mobYear      = new Date().getFullYear();
    let mobMonth     = new Date().getMonth();
    let selectedDate = null;
    let sheetOpen    = false;
    let showAll      = false;

    /* ── utils ──────────────────────────────────────────────────── */
    const money = v => '₹' + Number(v || 0).toLocaleString('en-IN', { maximumFractionDigits: 0 });
    const esc   = v => String(v ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[c]);
    const pad   = n => String(n).padStart(2, '0');
    const ds    = (y, m, d) => `${y}-${pad(m + 1)}-${pad(d)}`;
    const todayDs = () => { const t = new Date(); return ds(t.getFullYear(), t.getMonth(), t.getDate()); };

    /* ── date counts map ────────────────────────────────────────── */
    function countsByDate(events) {
        const c = {};
        events.forEach(ev => {
            const d = (ev.start || '').slice(0, 10);
            if (d) c[d] = (c[d] || 0) + 1;
        });
        return c;
    }

    /* ── build month grid ───────────────────────────────────────── */
    function buildGrid(year, month, counts) {
        document.getElementById('mobMonthLabel').textContent =
            new Date(year, month, 1).toLocaleDateString('en-IN', { month: 'long', year: 'numeric' });

        const today     = todayDs();
        const firstDow  = new Date(year, month, 1).getDay(); // 0=Sun
        const offset    = firstDow === 0 ? 6 : firstDow - 1; // Mon-start
        const daysTotal = new Date(year, month + 1, 0).getDate();

        let html = '';

        for (let i = 0; i < offset; i++)
            html += '<div class="ef-mob-cal-day --empty" aria-hidden="true"></div>';

        for (let d = 1; d <= daysTotal; d++) {
            const dateStr  = ds(year, month, d);
            const count    = counts[dateStr] || 0;
            const isToday  = dateStr === today;
            const isSel    = dateStr === selectedDate;
            const dow      = new Date(year, month, d).getDay();
            const isWknd   = dow === 0 || dow === 6;
            const occCls   = count >= 3 ? ' --occ-3' : count === 2 ? ' --occ-2' : count === 1 ? ' --occ-1' : '';
            const dotCount = Math.min(count, 3);

            let dots = '<div class="ef-mob-cal-dots" aria-hidden="true">';
            for (let di = 0; di < dotCount; di++) dots += '<span class="ef-mob-cal-dot"></span>';
            dots += '</div>';

            const ariaLbl = `${d}${count ? ', ' + count + ' booking' + (count > 1 ? 's' : '') : ''}`;

            html += `<div class="ef-mob-cal-day${occCls}${isToday ? ' --today' : ''}${isSel ? ' --selected' : ''}${isWknd ? ' --weekend' : ''}"
                data-date="${dateStr}" role="gridcell" tabindex="0" aria-label="${ariaLbl}">
                <span class="ef-mob-cal-num">${d}</span>
                ${dotCount ? dots : '<div class="ef-mob-cal-dots" aria-hidden="true"></div>'}
            </div>`;
        }

        const trailing = (7 - ((offset + daysTotal) % 7)) % 7;
        for (let i = 0; i < trailing; i++)
            html += '<div class="ef-mob-cal-day --empty" aria-hidden="true"></div>';

        const grid = document.getElementById('mobCalGrid');
        grid.innerHTML = html;

        grid.querySelectorAll('.ef-mob-cal-day:not(.--empty)').forEach(cell => {
            cell.addEventListener('click', () => openSheet(cell.dataset.date));
            cell.addEventListener('keydown', e => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openSheet(cell.dataset.date); }
            });
        });
    }

    /* ── upcoming list ──────────────────────────────────────────── */
    function renderUpcoming(events, all = false) {
        const todayObj = new Date(); todayObj.setHours(0,0,0,0);
        const list = document.getElementById('mobUpcomingList');

        const rows = events
            .filter(ev => { const d = new Date(ev.start); d.setHours(0,0,0,0); return d >= todayObj; })
            .sort((a, b) => new Date(a.start) - new Date(b.start))
            .slice(0, all ? 60 : 5);

        if (!rows.length) {
            list.innerHTML = '<div class="ef-mob-empty">No upcoming bookings</div>';
            return;
        }

        list.innerHTML = rows.map(ev => {
            const p   = ev.extendedProps || {};
            const st  = (ev.classNames || []).find(c => c.startsWith('is-'))?.replace('is-', '') || 'confirmed';
            const mls = (p.meals || []).join(' + ') || 'No meals';
            return `<a href="${esc(p.url)}" class="ef-mob-ev-row --${esc(st)}">
                <div>
                    <div class="ef-mob-ev-name">${esc(p.customer)}</div>
                    <div class="ef-mob-ev-meta">${esc(p.hall)} · ${Number(p.people||0).toLocaleString('en-IN')} guests · ${esc(mls)}</div>
                </div>
                <div>
                    <div class="ef-mob-ev-date">${esc(p.date)}</div>
                    <div class="ef-mob-ev-amt">${money(p.amount)}</div>
                    <div><span class="ef-mob-chip --${esc(p.payment_status)}">${esc(p.payment_status_label)}</span></div>
                </div>
            </a>`;
        }).join('');
    }

    /* ── fetch for month ────────────────────────────────────────── */
    function fetchMonth(year, month) {
        const start  = ds(year, month, 1);
        const endDay = new Date(year, month + 1, 0).getDate();
        const end    = ds(year, month, endDay);
        const hall   = document.getElementById('mobHallFilter')?.value || '';
        const params = new URLSearchParams({ start, end });
        if (hall) params.set('hall_id', hall);

        fetch(eventsUrl + '?' + params)
            .then(r => r.json())
            .then(evs => {
                mobEvents = evs;
                buildGrid(year, month, countsByDate(evs));
                renderUpcoming(evs, showAll);
            })
            .catch(err => console.warn('Mobile calendar fetch failed', err));
    }

    /* ── bottom sheet ───────────────────────────────────────────── */
    function openSheet(dateStr) {
        selectedDate = dateStr;

        document.querySelectorAll('.ef-mob-cal-day').forEach(c =>
            c.classList.toggle('--selected', c.dataset.date === dateStr));

        const d   = new Date(dateStr + 'T00:00:00');
        const kicker = d.toLocaleDateString('en-IN', { weekday: 'long' });
        const full   = d.toLocaleDateString('en-IN', { day: '2-digit', month: 'long', year: 'numeric' });

        document.getElementById('mobSheetKicker').textContent = kicker;
        document.getElementById('mobSheetDate').textContent   = full;

        const dayEvs = mobEvents.filter(ev => (ev.start || '').slice(0, 10) === dateStr);
        const body   = document.getElementById('mobSheetBody');

        if (!dayEvs.length) {
            body.innerHTML = '<div class="ef-mob-empty">No bookings on this date</div>';
        } else {
            body.innerHTML = dayEvs.map(ev => {
                const p   = ev.extendedProps || {};
                const mls = (p.meals || []).join(' + ') || 'No meals';
                const bal = p.balance > 0
                    ? `<div class="ef-mob-sheet-bal">Balance ${money(p.balance)}</div>` : '';
                return `<div class="ef-mob-sheet-booking">
                    <div class="ef-mob-sheet-bname">${esc(p.customer)}</div>
                    <div class="ef-mob-sheet-bmeta">
                        ${esc(p.hall)} · ${esc(p.start_time)} – ${esc(p.end_time)}<br>
                        ${Number(p.people||0).toLocaleString('en-IN')} guests · ${esc(mls)}<br>
                        ${esc(p.event_type)}
                    </div>
                    <div class="ef-mob-sheet-brow">
                        <div>
                            <div class="ef-mob-sheet-amt">${money(p.amount)}</div>
                            ${bal}
                        </div>
                        <div class="ef-mob-sheet-actions">
                            <span class="ef-mob-chip --${esc(p.payment_status)}">${esc(p.payment_status_label)}</span>
                            <a href="${esc(p.url)}" class="ef-mob-sheet-act">Open</a>
                            <a href="${esc(p.whatsapp_url)}" target="_blank" rel="noopener" class="ef-mob-sheet-act --wa">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>`;
            }).join('');
        }

        const hall = document.getElementById('mobHallFilter')?.value || '';
        const p    = new URLSearchParams({ date: dateStr });
        if (hall) p.set('hall_id', hall);
        document.getElementById('mobSheetCreate').href = createBase + '?' + p;

        document.getElementById('mobOverlay').classList.add('--on');
        document.getElementById('mobSheet').classList.add('--open');
        document.body.style.overflow = 'hidden';
        sheetOpen = true;
    }

    function closeSheet() {
        document.getElementById('mobOverlay').classList.remove('--on');
        document.getElementById('mobSheet').classList.remove('--open');
        document.body.style.overflow = '';
        sheetOpen = false;
    }

    /* ── mobile listeners ───────────────────────────────────────── */
    document.getElementById('mobPrev')?.addEventListener('click', () => {
        if (--mobMonth < 0) { mobMonth = 11; mobYear--; }
        fetchMonth(mobYear, mobMonth);
    });
    document.getElementById('mobNext')?.addEventListener('click', () => {
        if (++mobMonth > 11) { mobMonth = 0; mobYear++; }
        fetchMonth(mobYear, mobMonth);
    });
    document.getElementById('mobTodayBtn')?.addEventListener('click', () => {
        const now = new Date();
        mobYear = now.getFullYear(); mobMonth = now.getMonth();
        fetchMonth(mobYear, mobMonth);
    });
    document.getElementById('mobHallFilter')?.addEventListener('change', () => fetchMonth(mobYear, mobMonth));
    document.getElementById('mobSheetClose')?.addEventListener('click', closeSheet);
    document.getElementById('mobOverlay')?.addEventListener('click', closeSheet);

    document.getElementById('mobToggleAll')?.addEventListener('click', function () {
        showAll = !showAll;
        this.textContent = showAll ? 'Show less' : 'See all';
        renderUpcoming(mobEvents, showAll);
    });

    /* ── swipe month nav ────────────────────────────────────────── */
    let swipeX = 0;
    const mobShell = document.getElementById('mobShell');
    mobShell?.addEventListener('touchstart', e => { swipeX = e.touches[0].clientX; }, { passive: true });
    mobShell?.addEventListener('touchend', e => {
        if (sheetOpen) return;
        const dx = e.changedTouches[0].clientX - swipeX;
        if (Math.abs(dx) < 50) return;
        if (dx < 0) { if (++mobMonth > 11) { mobMonth = 0; mobYear++; } }
        else         { if (--mobMonth < 0)  { mobMonth = 11; mobYear--; } }
        fetchMonth(mobYear, mobMonth);
    }, { passive: true });

    /* ── init ───────────────────────────────────────────────────── */
    if (isMob()) {
        fetchMonth(mobYear, mobMonth);
    } else {
        initDesktop();
    }

    /* ══ DESKTOP FULLCALENDAR ════════════════════════════════════ */
    function initDesktop() {
        const calEl       = document.getElementById('venueCalendar');
        const hallFilter  = document.getElementById('hallFilter');
        const searchInput = document.getElementById('calendarSearch');
        const preview     = document.getElementById('bookingPreview');
        const quickModal  = new bootstrap.Modal(document.getElementById('quickBookingModal'));
        const quickLink   = document.getElementById('quickCreateLink');
        const quickDate   = document.getElementById('quickDateLabel');
        const quickHall   = document.getElementById('quickHallLabel');

        let deskEvents   = [];
        let lockedPv     = false;
        let searchTerm   = '';

        const matches = ev => {
            if (!searchTerm) return true;
            const p = ev.extendedProps || {};
            return [ev.title, p.customer, p.hall, p.event_type, p.payment_status_label]
                .filter(Boolean).join(' ').toLowerCase().includes(searchTerm);
        };

        const renderEv = info => {
            const p   = info.event.extendedProps;
            const mls = (p.meals || []).slice(0, 2).join(' + ') || 'No meals';
            return { html: `<div class="ef-event-card">
                <div class="ef-event-title">${esc(p.customer)}</div>
                <div class="ef-event-meta">${esc(p.hall)} · ${Number(p.people||0).toLocaleString('en-IN')} guests</div>
                <div class="ef-event-sub">${esc(mls)} · ${esc(p.start_time)}-${esc(p.end_time)}</div>
                <div class="ef-event-foot">
                    <span class="ef-event-mini pay-${esc(p.payment_status)}">${esc(p.payment_status_label)}</span>
                    <span class="ef-event-mini">${esc(p.status_label)}</span>
                </div>
            </div>` };
        };

        const updateTitles = () => {
            const t = calendar.view.title;
            document.getElementById('calendarTitle').textContent = t;
            document.getElementById('calendarPeriod').textContent = t;
        };

        const applyDensity = () => {
            const c = {};
            calendar.getEvents().forEach(ev => {
                if (!matches(ev)) return;
                const k = ev.startStr.slice(0, 10);
                c[k] = (c[k] || 0) + 1;
            });
            calEl.querySelectorAll('.fc-daygrid-day').forEach(cell => {
                cell.classList.remove('ef-busy-soft', 'ef-busy-mid', 'ef-busy-full');
                const n = c[cell.dataset.date] || 0;
                if (n >= 3) cell.classList.add('ef-busy-full');
                else if (n === 2) cell.classList.add('ef-busy-mid');
                else if (n === 1) cell.classList.add('ef-busy-soft');
            });
        };

        const showPv = (event, jsEvent, lock = false) => {
            const p = event.extendedProps;
            lockedPv = lock;
            preview.innerHTML = `
                <div class="ef-preview-title">${esc(p.customer)}</div>
                <div class="ef-preview-meta">${esc(p.event_type)} · ${esc(p.hall)}<br>${esc(p.date)} · ${esc(p.start_time)}-${esc(p.end_time)}</div>
                <div class="ef-preview-grid">
                    <div><div class="ef-preview-label">Guests</div><div class="ef-preview-value">${Number(p.people||0).toLocaleString('en-IN')}</div></div>
                    <div><div class="ef-preview-label">Meals</div><div class="ef-preview-value">${esc((p.meals||[]).join(', ')||'None')}</div></div>
                    <div><div class="ef-preview-label">Total</div><div class="ef-preview-value">${money(p.amount)}</div></div>
                    <div><div class="ef-preview-label">Balance</div><div class="ef-preview-value">${money(p.balance)}</div></div>
                </div>
                <div class="ef-preview-actions">
                    <a href="${p.url}">Open</a>
                    <a href="${p.payment_url}">Payment</a>
                    <a href="${p.whatsapp_url}" target="_blank" rel="noopener">WhatsApp</a>
                </div>`;
            const mg = 18, w = 320;
            preview.style.left = Math.max(mg, Math.min(jsEvent.clientX + 16, window.innerWidth - w - mg)) + 'px';
            preview.style.top  = Math.max(mg, Math.min(jsEvent.clientY + 16, window.innerHeight - preview.offsetHeight - mg)) + 'px';
            preview.classList.add('show');
        };

        const hidePv = (force = false) => {
            if (lockedPv && !force) return;
            preview.classList.remove('show');
            lockedPv = false;
        };

        const openQuick = dateStr => {
            const params = new URLSearchParams({ date: dateStr });
            if (hallFilter.value) params.set('hall_id', hallFilter.value);
            quickDate.textContent = new Date(dateStr + 'T00:00:00').toLocaleDateString('en-IN', {
                weekday: 'long', day: '2-digit', month: 'short', year: 'numeric'
            });
            quickHall.textContent = hallFilter.options[hallFilter.selectedIndex]?.text || 'All Halls';
            quickLink.href = createBase + '?' + params;
            quickModal.show();
        };

        const calendar = new FullCalendar.Calendar(calEl, {
            initialView: 'dayGridMonth',
            headerToolbar: false,
            height: 'auto',
            firstDay: 1,
            nowIndicator: true,
            dayMaxEvents: 3,
            eventDisplay: 'block',
            selectable: true,
            selectMirror: true,
            slotMinTime: '06:00:00',
            slotMaxTime: '23:00:00',
            allDaySlot: false,
            events: (info, success, failure) => {
                const params = new URLSearchParams({ start: info.startStr, end: info.endStr, hall_id: hallFilter.value });
                fetch(eventsUrl + '?' + params)
                    .then(r => r.json())
                    .then(evs => {
                        deskEvents = evs;
                        success(evs.filter(ev => {
                            const p = ev.extendedProps || {};
                            return !searchTerm || [ev.title, p.customer, p.hall, p.event_type, p.payment_status_label]
                                .filter(Boolean).join(' ').toLowerCase().includes(searchTerm);
                        }));
                    }).catch(failure);
            },
            datesSet:     () => { updateTitles(); setTimeout(applyDensity, 0); },
            eventsSet:    () => applyDensity(),
            eventContent: renderEv,
            select:       info => { openQuick(info.startStr.slice(0, 10)); calendar.unselect(); },
            dateClick:    info => openQuick(info.dateStr),
            eventClick:   info => { info.jsEvent.preventDefault(); showPv(info.event, info.jsEvent, true); },
            eventMouseEnter: info => { if (!lockedPv) showPv(info.event, info.jsEvent, false); },
            eventMouseMove:  info => {
                if (!lockedPv) {
                    const mg = 18, w = 320;
                    preview.style.left = Math.max(mg, Math.min(info.jsEvent.clientX + 16, window.innerWidth - w - mg)) + 'px';
                    preview.style.top  = Math.max(mg, Math.min(info.jsEvent.clientY + 16, window.innerHeight - preview.offsetHeight - mg)) + 'px';
                }
            },
            eventMouseLeave: () => hidePv(),
        });

        calendar.render();
        updateTitles();

        document.getElementById('calPrev').addEventListener('click', () => { hidePv(true); calendar.prev(); });
        document.getElementById('calNext').addEventListener('click', () => { hidePv(true); calendar.next(); });
        document.getElementById('calToday').addEventListener('click', () => { hidePv(true); calendar.today(); });

        document.querySelectorAll('.ef-view-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.ef-view-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                calendar.changeView(btn.dataset.view);
            });
        });

        hallFilter.addEventListener('change', () => { hidePv(true); calendar.refetchEvents(); });
        searchInput.addEventListener('input', () => {
            searchTerm = searchInput.value.trim().toLowerCase();
            calendar.removeAllEvents();
            calendar.addEventSource(deskEvents.filter(ev => {
                const p = ev.extendedProps || {};
                return !searchTerm || [ev.title, p.customer, p.hall, p.event_type, p.payment_status_label]
                    .filter(Boolean).join(' ').toLowerCase().includes(searchTerm);
            }));
        });

        document.getElementById('printSchedule').addEventListener('click', () => window.print());
        document.getElementById('exportSchedule').addEventListener('click', () => {
            const rows = calendar.getEvents().filter(matches).map(ev => {
                const p = ev.extendedProps;
                return [p.date, p.start_time, p.end_time, p.customer, p.hall, p.event_type,
                    p.people, (p.meals||[]).join(' + '), p.payment_status_label, p.amount, p.balance, p.url];
            });
            const headers = ['Date','Start','End','Customer','Hall','Event','Guests','Meals','Payment','Amount','Balance','URL'];
            const csv = [headers, ...rows].map(r => r.map(v => `"${String(v??'').replace(/"/g,'""')}"`).join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url  = URL.createObjectURL(blob);
            const a    = document.createElement('a');
            a.href = url; a.download = 'akshathay-booking-schedule.csv';
            document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
        });

        document.addEventListener('click', ev => {
            if (!preview.contains(ev.target) && !ev.target.closest('.fc-event')) hidePv(true);
        });
    }
});
</script>
@endpush
</x-admin-layout>
