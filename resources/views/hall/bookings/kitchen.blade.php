<x-admin-layout title="Kitchen Summary">
@push('styles')
<style>
/*
 * KITCHEN COMMAND CENTER — ef-kit-* namespace
 * Aesthetic: warm amber · copper · kitchen heat
 */

/* ── Design tokens ─────────────────────────────────────────────── */
:root {
    --kit-amber:    #d97706;
    --kit-amber-hi: #f59e0b;
    --kit-copper:   #b45309;
    --kit-orange:   #ea580c;
    --kit-green:    #059669;
    --kit-danger:   #dc2626;
    --kit-ink:      #1c1007;
    --kit-muted:    #78716c;
    --kit-faint:    #fefce8;
    --kit-border:   rgba(217,119,6,.14);
    --kit-border-s: rgba(217,119,6,.30);
    --kit-shadow:   0 1px 3px rgba(28,16,7,.07),0 4px 12px rgba(28,16,7,.06);
    --kit-shadow-h: 0 4px 20px rgba(28,16,7,.13),0 1px 4px rgba(28,16,7,.07);
    --kit-radius:   14px;
    --kit-ease:     cubic-bezier(.25,.46,.45,.94);
}

/* ── Hero ──────────────────────────────────────────────────────── */
.ef-kit-hero {
    background: linear-gradient(135deg, #1a1208 0%, #2c1810 40%, #3d2314 100%);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 20px;
    margin-bottom: 20px;
    overflow: hidden;
    padding: 28px 28px 24px;
    position: relative;
    display: flex;
    align-items: center;
    gap: 24px;
}
.ef-kit-hero::before {
    background: radial-gradient(circle, rgba(245,158,11,.18) 0%, transparent 65%);
    border-radius: 50%;
    content: "";
    height: 440px;
    pointer-events: none;
    position: absolute;
    right: -80px;
    top: -140px;
    width: 440px;
}
.ef-kit-hero::after {
    background: radial-gradient(circle, rgba(180,83,9,.12) 0%, transparent 65%);
    bottom: -90px;
    content: "";
    height: 260px;
    left: 20%;
    pointer-events: none;
    position: absolute;
    width: 260px;
    border-radius: 50%;
}
.ef-kit-hero-main { flex: 1; position: relative; z-index: 1; }
.ef-kit-eyebrow {
    color: rgba(245,158,11,.85);
    font-size: .65rem;
    font-weight: 760;
    letter-spacing: .18em;
    margin-bottom: 6px;
    text-transform: uppercase;
}
.ef-kit-hero-title {
    color: #fef9f0;
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -.02em;
    line-height: 1.2;
    margin-bottom: 4px;
}
.ef-kit-hero-sub {
    color: rgba(254,249,240,.45);
    font-size: .83rem;
}
.ef-kit-hero-chips {
    display: flex;
    gap: 12px;
    position: relative;
    z-index: 1;
    flex-shrink: 0;
    align-items: center;
}
.ef-kit-hero-chip {
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 12px;
    padding: 10px 16px;
    text-align: center;
}
.ef-kit-hero-chip-val {
    color: #fef9f0;
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -.02em;
    line-height: 1;
}
.ef-kit-hero-chip-val.amber  { color: #fbbf24; }
.ef-kit-hero-chip-val.orange { color: #fb923c; }
.ef-kit-hero-chip-lbl {
    color: rgba(254,249,240,.4);
    font-size: .67rem;
    font-weight: 700;
    letter-spacing: .04em;
    margin-top: 3px;
    text-transform: uppercase;
    white-space: nowrap;
}

/* ── Date navigator ────────────────────────────────────────────── */
.ef-kit-date-nav {
    align-items: center;
    background: #fff;
    border: 1px solid var(--kit-border);
    border-radius: 16px;
    box-shadow: var(--kit-shadow);
    display: flex;
    gap: 10px;
    justify-content: space-between;
    margin-bottom: 20px;
    padding: 12px 16px;
    flex-wrap: wrap;
}
.ef-kit-date-nav-btn {
    align-items: center;
    background: #fef9f0;
    border: 1.5px solid var(--kit-border);
    border-radius: 10px;
    color: var(--kit-amber);
    cursor: pointer;
    display: inline-flex;
    font-size: .82rem;
    font-weight: 700;
    gap: 6px;
    padding: 8px 16px;
    text-decoration: none;
    transition: all .15s var(--kit-ease);
    white-space: nowrap;
}
.ef-kit-date-nav-btn:hover { background: var(--kit-faint); border-color: var(--kit-amber); color: var(--kit-copper); }
.ef-kit-date-nav-btn.today {
    background: var(--kit-amber);
    border-color: var(--kit-amber);
    color: #fff;
}
.ef-kit-date-nav-btn.today:hover { background: var(--kit-copper); border-color: var(--kit-copper); }
.ef-kit-date-center { display: flex; align-items: center; gap: 12px; }
.ef-kit-date-chip {
    background: var(--kit-faint);
    border: 1.5px solid var(--kit-border);
    border-radius: 12px;
    color: var(--kit-ink);
    font-size: .9rem;
    font-weight: 800;
    padding: 8px 20px;
    text-align: center;
    white-space: nowrap;
}
.ef-kit-date-chip span {
    color: var(--kit-muted);
    font-size: .72rem;
    font-weight: 600;
    display: block;
}
.ef-kit-date-picker-btn {
    align-items: center;
    background: none;
    border: 1.5px solid var(--kit-border);
    border-radius: 8px;
    color: var(--kit-muted);
    cursor: pointer;
    display: inline-flex;
    font-size: .82rem;
    padding: 8px 10px;
    transition: border-color .14s, color .14s;
}
.ef-kit-date-picker-btn:hover { border-color: var(--kit-amber); color: var(--kit-amber); }
/* hidden real input */
.ef-kit-date-hidden { position: absolute; opacity: 0; pointer-events: none; width: 1px; height: 1px; }

/* ── Meal KPI cards ─────────────────────────────────────────────── */
.ef-kit-meals {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 20px;
}
.ef-kit-meal-card {
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,.1);
    overflow: hidden;
    padding: 20px 20px 16px;
    position: relative;
}
.ef-kit-meal-card.breakfast {
    background: linear-gradient(135deg, #431407 0%, #7c2d12 100%);
}
.ef-kit-meal-card.lunch {
    background: linear-gradient(135deg, #451a03 0%, #92400e 60%, #78350f 100%);
}
.ef-kit-meal-card.dinner {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #1e1b4b 100%);
}
.ef-kit-meal-card::before {
    background: radial-gradient(circle, rgba(255,255,255,.08) 0%, transparent 60%);
    border-radius: 50%;
    content: "";
    height: 200px;
    pointer-events: none;
    position: absolute;
    right: -40px;
    top: -60px;
    width: 200px;
}
.ef-kit-meal-icon {
    color: rgba(255,255,255,.5);
    font-size: 1.4rem;
    margin-bottom: 10px;
    display: block;
}
.ef-kit-meal-count {
    color: #fff;
    font-size: 2.2rem;
    font-weight: 900;
    letter-spacing: -.04em;
    line-height: 1;
    margin-bottom: 2px;
}
.ef-kit-meal-label {
    color: rgba(255,255,255,.6);
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.ef-kit-meal-time {
    color: rgba(255,255,255,.45);
    font-size: .72rem;
    font-weight: 600;
}
.ef-kit-meal-bar-track {
    background: rgba(255,255,255,.12);
    border-radius: 4px;
    height: 4px;
    margin-top: 12px;
    overflow: hidden;
}
.ef-kit-meal-bar-fill {
    background: rgba(255,255,255,.55);
    border-radius: 4px;
    height: 4px;
    transition: width .6s var(--kit-ease);
}

/* ── Section label ─────────────────────────────────────────────── */
.ef-kit-section-label {
    color: var(--kit-muted);
    font-size: .7rem;
    font-weight: 720;
    letter-spacing: .06em;
    margin-bottom: 12px;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ef-kit-section-label::after {
    background: var(--kit-border);
    content: "";
    flex: 1;
    height: 1px;
}

/* ── Event cards ───────────────────────────────────────────────── */
.ef-kit-cards { display: flex; flex-direction: column; gap: 12px; }

.ef-kit-event-card {
    background: #fff;
    border: 1px solid var(--kit-border);
    border-left: 4px solid var(--kit-amber);
    border-radius: var(--kit-radius);
    box-shadow: var(--kit-shadow);
    overflow: hidden;
    transition: box-shadow .16s var(--kit-ease), transform .16s var(--kit-ease);
}
.ef-kit-event-card:hover { box-shadow: var(--kit-shadow-h); transform: translateY(-2px); }

/* urgency border tones */
.ef-kit-event-card.urgent  { border-left-color: var(--kit-danger); }
.ef-kit-event-card.warning { border-left-color: var(--kit-orange); }
.ef-kit-event-card.normal  { border-left-color: var(--kit-green); }
.ef-kit-event-card.past    { border-left-color: #94a3b8; opacity: .8; }

.ef-kit-event-top {
    align-items: flex-start;
    display: flex;
    gap: 16px;
    padding: 18px 20px 14px;
}

/* time column */
.ef-kit-event-time-col {
    flex-shrink: 0;
    min-width: 68px;
    text-align: center;
    background: var(--kit-faint);
    border: 1px solid var(--kit-border);
    border-radius: 10px;
    padding: 8px 10px;
}
.ef-kit-event-time-val {
    color: var(--kit-copper);
    font-size: .95rem;
    font-weight: 800;
    line-height: 1.2;
}
.ef-kit-event-time-end {
    color: var(--kit-muted);
    font-size: .68rem;
    font-weight: 600;
    margin-top: 2px;
}

/* main body */
.ef-kit-event-main { flex: 1; min-width: 0; }
.ef-kit-event-customer {
    color: var(--kit-ink);
    font-size: 1rem;
    font-weight: 800;
    margin-bottom: 2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ef-kit-event-meta {
    color: var(--kit-muted);
    font-size: .78rem;
    margin-bottom: 8px;
}
.ef-kit-event-chips { display: flex; gap: 6px; flex-wrap: wrap; }

/* right: pax + countdown */
.ef-kit-event-right {
    flex-shrink: 0;
    text-align: right;
}
.ef-kit-pax-val {
    color: var(--kit-ink);
    font-size: 1.6rem;
    font-weight: 900;
    letter-spacing: -.04em;
    line-height: 1;
}
.ef-kit-pax-label {
    color: var(--kit-muted);
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    margin-top: 2px;
}
.ef-kit-countdown {
    border-radius: 8px;
    display: inline-block;
    font-size: .72rem;
    font-weight: 760;
    margin-top: 6px;
    padding: 3px 10px;
    white-space: nowrap;
}
.ef-kit-countdown.soon   { background: #fef2f2; color: var(--kit-danger); }
.ef-kit-countdown.prep   { background: #fff7ed; color: var(--kit-orange); }
.ef-kit-countdown.ahead  { background: #f0fdf4; color: var(--kit-green); }
.ef-kit-countdown.active { background: #fef3c7; color: var(--kit-amber);
    animation: kit-pulse 1.8s ease-in-out infinite; }
.ef-kit-countdown.past   { background: #f1f5f9; color: #94a3b8; }
@keyframes kit-pulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(217,119,6,.35); }
    50%      { box-shadow: 0 0 0 4px rgba(217,119,6,0); }
}

/* chips */
.ef-kit-chip {
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    font-size: .68rem;
    font-weight: 760;
    gap: 4px;
    letter-spacing: .03em;
    padding: 3px 8px;
}
.ef-kit-chip.bf  { background: #fff7ed; color: #c2410c; }
.ef-kit-chip.ln  { background: #fefce8; color: #a16207; }
.ef-kit-chip.dn  { background: #eef2ff; color: #4338ca; }
.ef-kit-chip.evt { background: #f1f5f9; color: #475569; }
.ef-kit-chip.hall{ background: var(--kit-faint); color: var(--kit-copper); }
.ef-kit-chip.mp  { background: #f0fdf4; color: #166534; }

/* event footer */
.ef-kit-event-footer {
    align-items: center;
    background: #fafaf9;
    border-top: 1px solid #f5f0eb;
    display: flex;
    gap: 10px;
    justify-content: space-between;
    padding: 10px 20px;
    flex-wrap: wrap;
}
.ef-kit-event-note {
    color: var(--kit-muted);
    font-size: .76rem;
    font-style: italic;
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ef-kit-event-actions { display: flex; gap: 6px; flex-shrink: 0; }
.ef-kit-btn {
    align-items: center;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    font-size: .78rem;
    font-weight: 700;
    gap: 5px;
    padding: 6px 12px;
    text-decoration: none;
    transition: all .14s;
}
.ef-kit-btn-view {
    background: #f5f0e8;
    border: 1.5px solid #e8d8c0;
    color: var(--kit-copper);
}
.ef-kit-btn-view:hover { background: #eee0cc; color: var(--kit-copper); }
.ef-kit-btn-primary {
    background: var(--kit-amber);
    border: 1.5px solid var(--kit-amber);
    color: #fff;
}
.ef-kit-btn-primary:hover { background: var(--kit-copper); border-color: var(--kit-copper); color: #fff; }

/* ── Empty state ───────────────────────────────────────────────── */
.ef-kit-empty {
    background: #fff;
    border: 1px solid var(--kit-border);
    border-radius: 16px;
    box-shadow: var(--kit-shadow);
    padding: 64px 24px;
    text-align: center;
}
.ef-kit-empty-orb {
    align-items: center;
    background: linear-gradient(135deg, #1a1208, #3d2314);
    border: 1px solid rgba(245,158,11,.2);
    border-radius: 50%;
    color: rgba(245,158,11,.6);
    display: inline-flex;
    font-size: 2rem;
    height: 72px;
    justify-content: center;
    margin-bottom: 18px;
    width: 72px;
}
.ef-kit-empty h5 { color: var(--kit-ink); font-size: 1rem; font-weight: 800; margin-bottom: 6px; }
.ef-kit-empty p  { color: var(--kit-muted); font-size: .86rem; margin-bottom: 24px; }

/* ── Responsive ────────────────────────────────────────────────── */
@media (max-width: 767.98px) {
    .ef-kit-hero { flex-direction: column; align-items: flex-start; gap: 16px; padding: 20px 18px; border-radius: 16px; }
    .ef-kit-hero-title { font-size: 1.3rem; }
    .ef-kit-hero-chips { gap: 8px; }
    .ef-kit-meals { grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .ef-kit-meal-card { padding: 14px 12px 12px; }
    .ef-kit-meal-count { font-size: 1.8rem; }
    .ef-kit-date-nav { flex-direction: column; gap: 10px; align-items: stretch; }
    .ef-kit-date-center { justify-content: center; }
    .ef-kit-date-nav > div { display: flex; gap: 8px; justify-content: center; }
    .ef-kit-event-top { flex-wrap: wrap; gap: 12px; }
    .ef-kit-event-right { display: flex; align-items: center; gap: 12px; }
    .ef-kit-pax-val { font-size: 1.3rem; }
}
@media (max-width: 440px) {
    .ef-kit-meals { grid-template-columns: repeat(3, 1fr); gap: 6px; }
    .ef-kit-meal-card { padding: 12px 8px 10px; }
    .ef-kit-meal-count { font-size: 1.5rem; }
    .ef-kit-meal-icon { font-size: 1.1rem; }
}
</style>
@endpush

@php
    $eventTypes  = \App\Models\HallBooking::eventTypes();
    $dateObj     = \Carbon\Carbon::parse($date);
    $isToday     = $dateObj->isToday();
    $prevDate    = $dateObj->copy()->subDay()->toDateString();
    $nextDate    = $dateObj->copy()->addDay()->toDateString();
    $todayDate   = today()->toDateString();

    // Meal tallies
    $bfCount = $bookings->where('has_breakfast', true)->sum('number_of_people');
    $lnCount = $bookings->where('has_lunch',     true)->sum('number_of_people');
    $dnCount = $bookings->where('has_dinner',    true)->sum('number_of_people');
    $totalCovers = $bfCount + $lnCount + $dnCount;
    $maxCovers   = max($bfCount, $lnCount, $dnCount, 1);

    // Now for urgency calc
    $now = now();

    function kitUrgency($startTime, $date, $now): string {
        try {
            $start = \Carbon\Carbon::parse($date . ' ' . $startTime);
            $mins  = $now->diffInMinutes($start, false);
            if ($start->isPast() && abs($mins) < 120) return 'active';
            if ($start->isPast()) return 'past';
            if ($mins <= 90)  return 'urgent';
            if ($mins <= 360) return 'warning';
            return 'normal';
        } catch (\Throwable $e) {
            return 'normal';
        }
    }

    function kitCountdownLabel($startTime, $date, $now): string {
        try {
            $start = \Carbon\Carbon::parse($date . ' ' . $startTime);
            $mins  = $now->diffInMinutes($start, false);
            if ($start->isPast() && abs($mins) < 120) return 'In Progress';
            if ($start->isPast()) return 'Completed';
            if ($mins < 60)  return "Starts in {$mins}m";
            $hrs = floor($mins / 60);
            $rem = $mins % 60;
            return $rem > 0 ? "Starts in {$hrs}h {$rem}m" : "Starts in {$hrs}h";
        } catch (\Throwable $e) {
            return '—';
        }
    }

    function kitCountdownCls($urgency): string {
        return match($urgency) {
            'urgent'  => 'soon',
            'warning' => 'prep',
            'normal'  => 'ahead',
            'active'  => 'active',
            'past'    => 'past',
            default   => 'ahead',
        };
    }
@endphp

{{-- ── Hero ────────────────────────────────────────────────────── --}}
<section class="ef-kit-hero">
    <div class="ef-kit-hero-main">
        <div class="ef-kit-eyebrow">Catering Operations</div>
        <h1 class="ef-kit-hero-title">Kitchen Summary</h1>
        <div class="ef-kit-hero-sub">Live meal planning and banquet preparation overview</div>
    </div>
    <div class="ef-kit-hero-chips">
        <div class="ef-kit-hero-chip">
            <div class="ef-kit-hero-chip-val amber">{{ $bookings->count() }}</div>
            <div class="ef-kit-hero-chip-lbl">Events</div>
        </div>
        <div class="ef-kit-hero-chip">
            <div class="ef-kit-hero-chip-val orange">{{ number_format($bookings->sum('number_of_people')) }}</div>
            <div class="ef-kit-hero-chip-lbl">Total Guests</div>
        </div>
        <div class="ef-kit-hero-chip">
            <div class="ef-kit-hero-chip-val">{{ number_format($totalCovers) }}</div>
            <div class="ef-kit-hero-chip-lbl">Total Covers</div>
        </div>
    </div>
</section>

{{-- ── Date Navigator ──────────────────────────────────────────── --}}
<div class="ef-kit-date-nav">
    <div>
        <a href="{{ route('hall.bookings.kitchen', ['date' => $prevDate]) }}" class="ef-kit-date-nav-btn">
            <i class="bi bi-arrow-left"></i> Previous
        </a>
    </div>
    <div class="ef-kit-date-center">
        @if(!$isToday)
            <a href="{{ route('hall.bookings.kitchen', ['date' => $todayDate]) }}" class="ef-kit-date-nav-btn today">
                <i class="bi bi-calendar-check"></i> Today
            </a>
        @endif
        <div class="ef-kit-date-chip">
            {{ $dateObj->format('d F Y') }}
            <span>{{ $isToday ? 'Today' : $dateObj->format('l') }}</span>
        </div>
        <form method="GET" id="datePickerForm" style="position:relative">
            <input type="date"
                   name="date"
                   id="datePicker"
                   class="ef-kit-date-hidden"
                   value="{{ $date }}"
                   onchange="document.getElementById('datePickerForm').submit()">
            <button type="button"
                    class="ef-kit-date-picker-btn"
                    onclick="document.getElementById('datePicker').showPicker()">
                <i class="bi bi-calendar3"></i>
            </button>
        </form>
    </div>
    <div>
        <a href="{{ route('hall.bookings.kitchen', ['date' => $nextDate]) }}" class="ef-kit-date-nav-btn">
            Next <i class="bi bi-arrow-right"></i>
        </a>
    </div>
</div>

{{-- ── Meal KPI Cards ──────────────────────────────────────────── --}}
<div class="ef-kit-meals">
    <div class="ef-kit-meal-card breakfast">
        <i class="bi bi-cup ef-kit-meal-icon"></i>
        <div class="ef-kit-meal-count">{{ $bfCount }}</div>
        <div class="ef-kit-meal-label">Breakfast</div>
        <div class="ef-kit-meal-time">06:00 AM – 10:00 AM</div>
        <div class="ef-kit-meal-bar-track">
            <div class="ef-kit-meal-bar-fill" style="width:{{ $maxCovers > 0 ? round(($bfCount / $maxCovers) * 100) : 0 }}%"></div>
        </div>
    </div>
    <div class="ef-kit-meal-card lunch">
        <i class="bi bi-sun ef-kit-meal-icon"></i>
        <div class="ef-kit-meal-count">{{ $lnCount }}</div>
        <div class="ef-kit-meal-label">Lunch</div>
        <div class="ef-kit-meal-time">11:30 AM – 03:00 PM</div>
        <div class="ef-kit-meal-bar-track">
            <div class="ef-kit-meal-bar-fill" style="width:{{ $maxCovers > 0 ? round(($lnCount / $maxCovers) * 100) : 0 }}%"></div>
        </div>
    </div>
    <div class="ef-kit-meal-card dinner">
        <i class="bi bi-moon-stars ef-kit-meal-icon"></i>
        <div class="ef-kit-meal-count">{{ $dnCount }}</div>
        <div class="ef-kit-meal-label">Dinner</div>
        <div class="ef-kit-meal-time">07:00 PM – 10:00 PM</div>
        <div class="ef-kit-meal-bar-track">
            <div class="ef-kit-meal-bar-fill" style="width:{{ $maxCovers > 0 ? round(($dnCount / $maxCovers) * 100) : 0 }}%"></div>
        </div>
    </div>
</div>

{{-- ── Event Timeline Cards ────────────────────────────────────── --}}
@if($bookings->isEmpty())
    <div class="ef-kit-empty">
        <div class="ef-kit-empty-orb"><i class="bi bi-cup-hot"></i></div>
        <h5>No catering operations scheduled</h5>
        <p>No events on {{ $dateObj->format('d F Y') }}. Kitchen is clear.</p>
        <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
            <a href="{{ route('hall.bookings.kitchen', ['date' => $todayDate]) }}"
               class="ef-kit-btn ef-kit-btn-primary">
                <i class="bi bi-calendar-check"></i> Jump to Today
            </a>
            <a href="{{ route('hall.bookings.create') }}" class="ef-kit-btn ef-kit-btn-view">
                <i class="bi bi-plus-lg"></i> New Booking
            </a>
        </div>
    </div>
@else
    <div class="ef-kit-section-label">
        <i class="bi bi-list-ul"></i>
        {{ $bookings->count() }} event{{ $bookings->count() === 1 ? '' : 's' }} · sorted by start time
    </div>

    <div class="ef-kit-cards">
        @foreach($bookings as $b)
            @php
                $urgency      = kitUrgency($b->start_time, $date, $now);
                $countdown    = kitCountdownLabel($b->start_time, $date, $now);
                $countdownCls = kitCountdownCls($urgency);
                $startFmt     = \Carbon\Carbon::parse($b->start_time)->format('h:i A');
                $endFmt       = \Carbon\Carbon::parse($b->end_time)->format('h:i A');
                $evtLabel     = $eventTypes[$b->event_type] ?? ucfirst(str_replace('_',' ',$b->event_type));
                $meals        = array_filter([
                    $b->has_breakfast ? 'Breakfast' : null,
                    $b->has_lunch     ? 'Lunch'     : null,
                    $b->has_dinner    ? 'Dinner'    : null,
                ]);
            @endphp
            <div class="ef-kit-event-card {{ $urgency }}">
                <div class="ef-kit-event-top">
                    {{-- Time column --}}
                    <div class="ef-kit-event-time-col">
                        <div class="ef-kit-event-time-val">{{ $startFmt }}</div>
                        <div class="ef-kit-event-time-end">to {{ $endFmt }}</div>
                    </div>

                    {{-- Main info --}}
                    <div class="ef-kit-event-main">
                        <div class="ef-kit-event-customer">{{ $b->customer_name }}</div>
                        <div class="ef-kit-event-meta">
                            {{ $b->customer_mobile }}
                        </div>
                        <div class="ef-kit-event-chips">
                            <span class="ef-kit-chip evt"><i class="bi bi-calendar-event" style="font-size:.6rem"></i> {{ $evtLabel }}</span>
                            @if($b->hall)
                                <span class="ef-kit-chip hall"><i class="bi bi-building" style="font-size:.6rem"></i> {{ $b->hall->name }}</span>
                            @endif
                            @if($b->mealPlan)
                                <span class="ef-kit-chip mp"><i class="bi bi-grid" style="font-size:.6rem"></i> {{ $b->mealPlan->name }}</span>
                            @endif
                            @if($b->has_breakfast)
                                <span class="ef-kit-chip bf"><i class="bi bi-cup"></i> BF</span>
                            @endif
                            @if($b->has_lunch)
                                <span class="ef-kit-chip ln"><i class="bi bi-sun"></i> LN</span>
                            @endif
                            @if($b->has_dinner)
                                <span class="ef-kit-chip dn"><i class="bi bi-moon-stars"></i> DN</span>
                            @endif
                        </div>
                    </div>

                    {{-- Pax + countdown --}}
                    <div class="ef-kit-event-right">
                        <div class="ef-kit-pax-val">{{ number_format($b->number_of_people) }}</div>
                        <div class="ef-kit-pax-label">Covers</div>
                        <span class="ef-kit-countdown {{ $countdownCls }}">{{ $countdown }}</span>
                    </div>
                </div>

                <div class="ef-kit-event-footer">
                    <div class="ef-kit-event-note">
                        @if($b->notes)
                            <i class="bi bi-chat-left-text" style="font-size:.72rem;margin-right:4px"></i>{{ $b->notes }}
                        @else
                            <span style="opacity:.5">No kitchen notes</span>
                        @endif
                    </div>
                    <div class="ef-kit-event-actions">
                        <a href="{{ route('hall.bookings.show', $b) }}" class="ef-kit-btn ef-kit-btn-view">
                            <i class="bi bi-eye"></i> View
                        </a>
                        <a href="{{ route('hall.bookings.show', $b) }}" class="ef-kit-btn ef-kit-btn-primary">
                            <i class="bi bi-clipboard-check"></i> Details
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@push('scripts')
<script>
// Auto-refresh on today's view every 5 minutes to keep countdowns fresh
@if($isToday)
setTimeout(() => location.reload(), 5 * 60 * 1000);
@endif
</script>
@endpush

</x-admin-layout>
