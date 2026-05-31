<x-admin-layout title="Kitchen Summary">
@push('styles')
<style>
/* ── Kitchen Command Center — ef-kit-* ───────────────────────────
   Mobile-first redesign: operational density over decoration.
   Primary users: kitchen manager, catering staff, ops team.
   ──────────────────────────────────────────────────────────────── */

:root {
    --kit-amber:    #d97706;
    --kit-amber-hi: #f59e0b;
    --kit-copper:   #b45309;
    --kit-orange:   #ea580c;
    --kit-green:    #059669;
    --kit-danger:   #dc2626;
    --kit-ink:      #1c1007;
    --kit-muted:    #78716c;
    --kit-faint:    #fffbf5;
    --kit-border:   rgba(217,119,6,.13);
    --kit-shadow:   0 1px 3px rgba(28,16,7,.06),0 2px 8px rgba(28,16,7,.05);
    --kit-radius:   14px;
    --kit-ease:     cubic-bezier(.25,.46,.45,.94);
}

/* ── Command bar (replaces hero) ────────────────────────────────
   Max 68px. Dark background. Left: title + date. Right: stats.
   When empty → single-line "Kitchen clear" state.
   ──────────────────────────────────────────────────────────────── */
.ef-kit-bar {
    align-items: center;
    background: linear-gradient(135deg, #1a1208 0%, #2e1a0e 100%);
    border: 1px solid rgba(245,158,11,.12);
    border-radius: 16px;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    margin-bottom: 12px;
    padding: 14px 18px;
    position: relative;
    overflow: hidden;
}
.ef-kit-bar::after {
    background: radial-gradient(circle, rgba(245,158,11,.10) 0%, transparent 65%);
    border-radius: 50%;
    content: '';
    height: 200px;
    pointer-events: none;
    position: absolute;
    right: -50px;
    top: -80px;
    width: 200px;
}
.ef-kit-bar-left { position: relative; z-index: 1; }
.ef-kit-bar-eyebrow {
    color: rgba(245,158,11,.7);
    font-size: .58rem;
    font-weight: 760;
    letter-spacing: .18em;
    text-transform: uppercase;
    margin-bottom: 3px;
}
.ef-kit-bar-title {
    color: #fef9f0;
    font-size: 1.1rem;
    font-weight: 800;
    letter-spacing: -.02em;
    line-height: 1.1;
}
.ef-kit-bar-right {
    align-items: center;
    display: flex;
    gap: 10px;
    position: relative;
    z-index: 1;
    flex-shrink: 0;
}
/* Stats when bookings exist */
.ef-kit-stat {
    text-align: center;
    min-width: 44px;
}
.ef-kit-stat-val {
    color: #fbbf24;
    font-size: 1.3rem;
    font-weight: 900;
    letter-spacing: -.04em;
    line-height: 1;
}
.ef-kit-stat-lbl {
    color: rgba(255,255,255,.45);
    font-size: .58rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    margin-top: 2px;
}
.ef-kit-stat-div {
    background: rgba(255,255,255,.1);
    height: 32px;
    width: 1px;
}
/* Empty state inside bar */
.ef-kit-bar-clear {
    align-items: center;
    display: flex;
    gap: 8px;
    color: rgba(245,158,11,.7);
    font-size: .82rem;
    font-weight: 700;
}
.ef-kit-bar-clear i { font-size: 1rem; }

/* ── Date navigator — single compact row ────────────────────────
   [←] 31 May 2026, Saturday [📅] [→]
   Today chip appears inline when not today.
   ──────────────────────────────────────────────────────────────── */
.ef-kit-date-row {
    align-items: center;
    background: #fff;
    border: 1px solid var(--kit-border);
    border-radius: 12px;
    box-shadow: var(--kit-shadow);
    display: flex;
    gap: 0;
    margin-bottom: 12px;
    overflow: hidden;
    height: 44px;
}
.ef-kit-date-arrow {
    align-items: center;
    background: var(--kit-faint);
    border: none;
    color: var(--kit-amber);
    cursor: pointer;
    display: flex;
    flex-shrink: 0;
    font-size: 1rem;
    font-weight: 700;
    height: 44px;
    justify-content: center;
    text-decoration: none;
    transition: background .14s;
    width: 44px;
}
.ef-kit-date-arrow:hover { background: #fef0d0; color: var(--kit-copper); }
.ef-kit-date-arrow:first-child { border-right: 1px solid var(--kit-border); }
.ef-kit-date-arrow:last-child  { border-left:  1px solid var(--kit-border); }
.ef-kit-date-center {
    align-items: center;
    display: flex;
    flex: 1;
    gap: 8px;
    justify-content: center;
    padding: 0 10px;
    overflow: hidden;
}
.ef-kit-date-text {
    color: var(--kit-ink);
    font-size: .88rem;
    font-weight: 800;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ef-kit-date-today-chip {
    align-items: center;
    background: var(--kit-amber);
    border-radius: 6px;
    color: #fff;
    display: inline-flex;
    font-size: .6rem;
    font-weight: 800;
    gap: 3px;
    letter-spacing: .04em;
    padding: 2px 7px;
    text-decoration: none;
    text-transform: uppercase;
    white-space: nowrap;
    transition: background .14s;
    flex-shrink: 0;
}
.ef-kit-date-today-chip:hover { background: var(--kit-copper); color: #fff; }
.ef-kit-date-picker-btn {
    align-items: center;
    background: none;
    border: none;
    border-left: 1px solid var(--kit-border);
    color: var(--kit-muted);
    cursor: pointer;
    display: flex;
    flex-shrink: 0;
    font-size: .82rem;
    height: 44px;
    justify-content: center;
    transition: color .14s;
    width: 36px;
}
.ef-kit-date-picker-btn:hover { color: var(--kit-amber); }
.ef-kit-date-hidden { position: absolute; opacity: 0; pointer-events: none; width: 1px; height: 1px; }

/* ── Meal strip — compact 3-column ─────────────────────────────
   Replaced large gradient cards with a tight strip.
   Hidden when all counts are zero (no point showing 0 / 0 / 0).
   ──────────────────────────────────────────────────────────────── */
.ef-kit-meal-strip {
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(3, 1fr);
    margin-bottom: 14px;
}
.ef-kit-meal-pill {
    align-items: center;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    gap: 2px;
    justify-content: center;
    padding: 10px 6px;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.ef-kit-meal-pill.bf { background: #fff7ed; border: 1.5px solid #fed7aa; }
.ef-kit-meal-pill.ln { background: #fefce8; border: 1.5px solid #fde68a; }
.ef-kit-meal-pill.dn { background: #eef2ff; border: 1.5px solid #c7d2fe; }
/* Zero-state dim */
.ef-kit-meal-pill.zero { opacity: .5; }
.ef-kit-meal-pill-icon { font-size: .95rem; line-height: 1; }
.ef-kit-meal-pill.bf .ef-kit-meal-pill-icon { color: #c2410c; }
.ef-kit-meal-pill.ln .ef-kit-meal-pill-icon { color: #a16207; }
.ef-kit-meal-pill.dn .ef-kit-meal-pill-icon { color: #4338ca; }
.ef-kit-meal-pill-count {
    font-size: 1.15rem;
    font-weight: 900;
    letter-spacing: -.04em;
    line-height: 1;
    margin-top: 2px;
}
.ef-kit-meal-pill.bf .ef-kit-meal-pill-count { color: #9a3412; }
.ef-kit-meal-pill.ln .ef-kit-meal-pill-count { color: #78350f; }
.ef-kit-meal-pill.dn .ef-kit-meal-pill-count { color: #312e81; }
.ef-kit-meal-pill-label {
    font-size: .6rem;
    font-weight: 760;
    letter-spacing: .06em;
    text-transform: uppercase;
    opacity: .6;
}
.ef-kit-meal-pill.bf .ef-kit-meal-pill-label { color: #c2410c; }
.ef-kit-meal-pill.ln .ef-kit-meal-pill-label { color: #a16207; }
.ef-kit-meal-pill.dn .ef-kit-meal-pill-label { color: #4338ca; }
/* Mini bar at bottom of pill */
.ef-kit-meal-bar {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 0 0 12px 12px;
    overflow: hidden;
}
.ef-kit-meal-pill.bf .ef-kit-meal-bar { background: #fed7aa; }
.ef-kit-meal-pill.ln .ef-kit-meal-bar { background: #fde68a; }
.ef-kit-meal-pill.dn .ef-kit-meal-bar { background: #c7d2fe; }
.ef-kit-meal-bar-fill {
    height: 3px;
    transition: width .5s var(--kit-ease);
}
.ef-kit-meal-pill.bf .ef-kit-meal-bar-fill { background: #c2410c; }
.ef-kit-meal-pill.ln .ef-kit-meal-bar-fill { background: #a16207; }
.ef-kit-meal-pill.dn .ef-kit-meal-bar-fill { background: #4338ca; }

/* ── Section header ──────────────────────────────────────────── */
.ef-kit-section {
    align-items: center;
    color: var(--kit-muted);
    display: flex;
    font-size: .68rem;
    font-weight: 720;
    gap: 8px;
    letter-spacing: .05em;
    margin-bottom: 10px;
    text-transform: uppercase;
}
.ef-kit-section::after { background: var(--kit-border); content: ''; flex: 1; height: 1px; }

/* ── Event cards ────────────────────────────────────────────── */
.ef-kit-cards { display: flex; flex-direction: column; gap: 10px; }

.ef-kit-card {
    background: #fff;
    border: 1px solid var(--kit-border);
    border-left: 3px solid var(--kit-amber);
    border-radius: var(--kit-radius);
    box-shadow: var(--kit-shadow);
    overflow: hidden;
}
.ef-kit-card.urgent  { border-left-color: var(--kit-danger); }
.ef-kit-card.warning { border-left-color: var(--kit-orange); }
.ef-kit-card.normal  { border-left-color: var(--kit-green); }
.ef-kit-card.active  { border-left-color: var(--kit-amber); }
.ef-kit-card.past    { border-left-color: #94a3b8; opacity: .82; }

.ef-kit-card-top {
    display: grid;
    /* time | info | pax */
    grid-template-columns: 64px 1fr auto;
    gap: 12px;
    align-items: start;
    padding: 14px 16px 12px;
}
.ef-kit-time {
    background: var(--kit-faint);
    border: 1px solid var(--kit-border);
    border-radius: 9px;
    padding: 6px 6px;
    text-align: center;
}
.ef-kit-time-val {
    color: var(--kit-copper);
    font-size: .84rem;
    font-weight: 800;
    line-height: 1.2;
}
.ef-kit-time-end {
    color: var(--kit-muted);
    font-size: .6rem;
    font-weight: 600;
    margin-top: 1px;
}
.ef-kit-info { min-width: 0; }
.ef-kit-customer {
    color: var(--kit-ink);
    font-size: .92rem;
    font-weight: 800;
    margin-bottom: 2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ef-kit-evt-type {
    color: var(--kit-muted);
    font-size: .72rem;
    font-weight: 600;
    margin-bottom: 6px;
}
.ef-kit-chips { display: flex; gap: 5px; flex-wrap: wrap; }
.ef-kit-chip {
    border-radius: 5px;
    display: inline-flex;
    align-items: center;
    font-size: .62rem;
    font-weight: 760;
    gap: 3px;
    padding: 2px 6px;
}
.ef-kit-chip.bf { background: #fff7ed; color: #c2410c; }
.ef-kit-chip.ln { background: #fefce8; color: #a16207; }
.ef-kit-chip.dn { background: #eef2ff; color: #4338ca; }
.ef-kit-chip.hall { background: #fef9f0; color: var(--kit-copper); }
.ef-kit-chip.mp   { background: #f0fdf4; color: #166534; }
.ef-kit-pax {
    text-align: right;
    flex-shrink: 0;
}
.ef-kit-pax-num {
    color: var(--kit-ink);
    font-size: 1.4rem;
    font-weight: 900;
    letter-spacing: -.04em;
    line-height: 1;
}
.ef-kit-pax-lbl {
    color: var(--kit-muted);
    font-size: .58rem;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    margin-top: 1px;
}
.ef-kit-countdown {
    border-radius: 7px;
    display: inline-block;
    font-size: .65rem;
    font-weight: 760;
    margin-top: 5px;
    padding: 2px 8px;
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

.ef-kit-card-foot {
    align-items: center;
    background: #fafaf8;
    border-top: 1px solid #f5f0ea;
    display: flex;
    gap: 8px;
    justify-content: space-between;
    padding: 8px 16px;
}
.ef-kit-note {
    color: var(--kit-muted);
    font-size: .72rem;
    font-style: italic;
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ef-kit-actions { display: flex; gap: 6px; flex-shrink: 0; }
.ef-kit-btn {
    align-items: center;
    border-radius: 7px;
    cursor: pointer;
    display: inline-flex;
    font-size: .72rem;
    font-weight: 700;
    gap: 4px;
    padding: 5px 10px;
    text-decoration: none;
    transition: all .13s;
    border: 1.5px solid;
}
.ef-kit-btn-view    { background: #f5f0e8; border-color: #e8d8c0; color: var(--kit-copper); }
.ef-kit-btn-view:hover { background: #efe4d0; color: var(--kit-copper); }
.ef-kit-btn-primary { background: var(--kit-amber); border-color: var(--kit-amber); color: #fff; }
.ef-kit-btn-primary:hover { background: var(--kit-copper); border-color: var(--kit-copper); color: #fff; }
.ef-kit-btn-outline { background: #fff; border-color: var(--kit-border); color: var(--kit-ink); }
.ef-kit-btn-outline:hover { border-color: var(--kit-amber); color: var(--kit-amber); }

/* ── Empty state — compact + actionable ──────────────────────── */
.ef-kit-empty {
    background: #fff;
    border: 1px solid var(--kit-border);
    border-radius: 16px;
    box-shadow: var(--kit-shadow);
    padding: 32px 20px 28px;
    text-align: center;
}
.ef-kit-empty-icon {
    font-size: 2rem;
    margin-bottom: 10px;
    opacity: .45;
}
.ef-kit-empty-title {
    color: var(--kit-ink);
    font-size: .92rem;
    font-weight: 800;
    margin-bottom: 4px;
}
.ef-kit-empty-sub {
    color: var(--kit-muted);
    font-size: .8rem;
    margin-bottom: 18px;
    line-height: 1.5;
}
.ef-kit-empty-actions { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; }

/* ── Mobile: already mobile-first; add breakpoint tweaks ─────── */
@media (max-width: 440px) {
    .ef-kit-card-top { grid-template-columns: 56px 1fr auto; gap: 10px; padding: 12px 12px 10px; }
    .ef-kit-card-foot { padding: 8px 12px; }
    .ef-kit-pax-num { font-size: 1.2rem; }
    .ef-kit-bar { padding: 12px 14px; }
    .ef-kit-bar-title { font-size: 1rem; }
    .ef-kit-stat-val { font-size: 1.1rem; }
    .ef-kit-date-text { font-size: .82rem; }
}

/* ── Desktop: add a bit more breathing room ─────────────────── */
@media (min-width: 768px) {
    .ef-kit-bar { padding: 16px 24px; }
    .ef-kit-meal-strip { gap: 12px; }
    .ef-kit-card-top { grid-template-columns: 76px 1fr auto; gap: 16px; padding: 16px 20px 14px; }
    .ef-kit-card-foot { padding: 10px 20px; }
    .ef-kit-cards { gap: 12px; }
}
</style>
@endpush

@php
    $eventTypes = \App\Models\HallBooking::eventTypes();
    $dateObj    = \Carbon\Carbon::parse($date);
    $isToday    = $dateObj->isToday();
    $prevDate   = $dateObj->copy()->subDay()->toDateString();
    $nextDate   = $dateObj->copy()->addDay()->toDateString();
    $todayDate  = today()->toDateString();

    // Meal tallies
    $bfCount     = $bookings->where('has_breakfast', true)->sum('number_of_people');
    $lnCount     = $bookings->where('has_lunch',     true)->sum('number_of_people');
    $dnCount     = $bookings->where('has_dinner',    true)->sum('number_of_people');
    $totalCovers = $bfCount + $lnCount + $dnCount;
    $maxCovers   = max($bfCount, $lnCount, $dnCount, 1);
    $hasMeals    = $totalCovers > 0;

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
        } catch (\Throwable $e) { return 'normal'; }
    }

    function kitCountdownLabel($startTime, $date, $now): string {
        try {
            $start = \Carbon\Carbon::parse($date . ' ' . $startTime);
            $mins  = $now->diffInMinutes($start, false);
            if ($start->isPast() && abs($mins) < 120) return 'In Progress';
            if ($start->isPast()) return 'Completed';
            if ($mins < 60) return "{$mins}m away";
            $hrs = floor($mins / 60); $rem = $mins % 60;
            return $rem > 0 ? "{$hrs}h {$rem}m" : "{$hrs}h away";
        } catch (\Throwable $e) { return '—'; }
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

{{-- ═══════════════════════════════════════════════════════════════
     COMMAND BAR — replaces hero; stays compact on mobile
═══════════════════════════════════════════════════════════════════ --}}
<div class="ef-kit-bar">
    <div class="ef-kit-bar-left">
        <div class="ef-kit-bar-eyebrow">Kitchen</div>
        <div class="ef-kit-bar-title">
            @if($isToday)
                Today's Operations
            @else
                {{ $dateObj->format('d M Y') }}
            @endif
        </div>
    </div>

    <div class="ef-kit-bar-right">
        @if($bookings->isNotEmpty())
            <div class="ef-kit-stat">
                <div class="ef-kit-stat-val">{{ $bookings->count() }}</div>
                <div class="ef-kit-stat-lbl">Events</div>
            </div>
            <div class="ef-kit-stat-div"></div>
            <div class="ef-kit-stat">
                <div class="ef-kit-stat-val">{{ number_format($bookings->sum('number_of_people')) }}</div>
                <div class="ef-kit-stat-lbl">Guests</div>
            </div>
            @if($hasMeals)
                <div class="ef-kit-stat-div"></div>
                <div class="ef-kit-stat">
                    <div class="ef-kit-stat-val">{{ number_format($totalCovers) }}</div>
                    <div class="ef-kit-stat-lbl">Covers</div>
                </div>
            @endif
        @else
            <div class="ef-kit-bar-clear">
                <i class="bi bi-check-circle"></i>
                Kitchen Clear
            </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     DATE NAVIGATOR — single 44px row
═══════════════════════════════════════════════════════════════════ --}}
<div class="ef-kit-date-row">
    <a href="{{ route('hall.bookings.kitchen', ['date' => $prevDate]) }}"
       class="ef-kit-date-arrow" aria-label="Previous day">
        <i class="bi bi-chevron-left"></i>
    </a>

    <div class="ef-kit-date-center">
        <span class="ef-kit-date-text">
            {{ $dateObj->format('d M') }}, {{ $dateObj->format('l') }}
        </span>
        @if(!$isToday)
            <a href="{{ route('hall.bookings.kitchen', ['date' => $todayDate]) }}"
               class="ef-kit-date-today-chip">
                <i class="bi bi-calendar-check" style="font-size:.65rem"></i> Today
            </a>
        @endif
    </div>

    <form method="GET" id="datePickerForm" style="position:relative;display:flex">
        <input type="date" name="date" id="datePicker"
               class="ef-kit-date-hidden"
               value="{{ $date }}"
               onchange="document.getElementById('datePickerForm').submit()">
        <button type="button" class="ef-kit-date-picker-btn"
                onclick="document.getElementById('datePicker').showPicker()"
                aria-label="Pick date">
            <i class="bi bi-calendar3"></i>
        </button>
    </form>

    <a href="{{ route('hall.bookings.kitchen', ['date' => $nextDate]) }}"
       class="ef-kit-date-arrow" aria-label="Next day">
        <i class="bi bi-chevron-right"></i>
    </a>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     MEAL STRIP — compact 3-column, hidden when all zero + no events
═══════════════════════════════════════════════════════════════════ --}}
@if($bookings->isNotEmpty() || $hasMeals)
<div class="ef-kit-meal-strip">
    @php
        $bfPct = $maxCovers > 0 ? round(($bfCount / $maxCovers) * 100) : 0;
        $lnPct = $maxCovers > 0 ? round(($lnCount / $maxCovers) * 100) : 0;
        $dnPct = $maxCovers > 0 ? round(($dnCount / $maxCovers) * 100) : 0;
    @endphp
    <div class="ef-kit-meal-pill bf {{ $bfCount === 0 ? 'zero' : '' }}">
        <span class="ef-kit-meal-pill-icon"><i class="bi bi-cup-hot"></i></span>
        <div class="ef-kit-meal-pill-count">{{ $bfCount }}</div>
        <div class="ef-kit-meal-pill-label">Breakfast</div>
        <div class="ef-kit-meal-bar">
            <div class="ef-kit-meal-bar-fill" style="width:{{ $bfPct }}%"></div>
        </div>
    </div>
    <div class="ef-kit-meal-pill ln {{ $lnCount === 0 ? 'zero' : '' }}">
        <span class="ef-kit-meal-pill-icon"><i class="bi bi-brightness-high"></i></span>
        <div class="ef-kit-meal-pill-count">{{ $lnCount }}</div>
        <div class="ef-kit-meal-pill-label">Lunch</div>
        <div class="ef-kit-meal-bar">
            <div class="ef-kit-meal-bar-fill" style="width:{{ $lnPct }}%"></div>
        </div>
    </div>
    <div class="ef-kit-meal-pill dn {{ $dnCount === 0 ? 'zero' : '' }}">
        <span class="ef-kit-meal-pill-icon"><i class="bi bi-moon-stars"></i></span>
        <div class="ef-kit-meal-pill-count">{{ $dnCount }}</div>
        <div class="ef-kit-meal-pill-label">Dinner</div>
        <div class="ef-kit-meal-bar">
            <div class="ef-kit-meal-bar-fill" style="width:{{ $dnPct }}%"></div>
        </div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     EVENT CARDS / EMPTY STATE
═══════════════════════════════════════════════════════════════════ --}}
@if($bookings->isEmpty())

    {{-- Compact actionable empty state --}}
    <div class="ef-kit-empty">
        <div class="ef-kit-empty-icon">🍽</div>
        <div class="ef-kit-empty-title">Kitchen Schedule Clear</div>
        <div class="ef-kit-empty-sub">
            No events on {{ $dateObj->format('d F Y') }}.
        </div>
        <div class="ef-kit-empty-actions">
            <a href="{{ route('hall.bookings.index') }}" class="ef-kit-btn ef-kit-btn-outline">
                <i class="bi bi-calendar3"></i> View Bookings
            </a>
            <a href="{{ route('hall.bookings.create') }}" class="ef-kit-btn ef-kit-btn-primary">
                <i class="bi bi-plus-lg"></i> Create Booking
            </a>
        </div>
    </div>

@else

    <div class="ef-kit-section">
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
            @endphp
            <div class="ef-kit-card {{ $urgency }}">

                <div class="ef-kit-card-top">
                    {{-- Time column --}}
                    <div class="ef-kit-time">
                        <div class="ef-kit-time-val">{{ $startFmt }}</div>
                        <div class="ef-kit-time-end">→ {{ $endFmt }}</div>
                    </div>

                    {{-- Info --}}
                    <div class="ef-kit-info">
                        <div class="ef-kit-customer">{{ $b->customer_name }}</div>
                        <div class="ef-kit-evt-type">{{ $evtLabel }}@if($b->hall) · {{ $b->hall->name }}@endif</div>
                        <div class="ef-kit-chips">
                            @if($b->has_breakfast)
                                <span class="ef-kit-chip bf"><i class="bi bi-cup-hot"></i> BF</span>
                            @endif
                            @if($b->has_lunch)
                                <span class="ef-kit-chip ln"><i class="bi bi-brightness-high"></i> LN</span>
                            @endif
                            @if($b->has_dinner)
                                <span class="ef-kit-chip dn"><i class="bi bi-moon-stars"></i> DN</span>
                            @endif
                            @if($b->mealPlan)
                                <span class="ef-kit-chip mp"><i class="bi bi-grid-3x3-gap" style="font-size:.6rem"></i> {{ $b->mealPlan->name }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Pax + countdown --}}
                    <div class="ef-kit-pax">
                        <div class="ef-kit-pax-num">{{ number_format($b->number_of_people) }}</div>
                        <div class="ef-kit-pax-lbl">Covers</div>
                        <span class="ef-kit-countdown {{ $countdownCls }}">{{ $countdown }}</span>
                    </div>
                </div>

                <div class="ef-kit-card-foot">
                    <div class="ef-kit-note">
                        @if($b->notes)
                            <i class="bi bi-chat-left-text" style="font-size:.68rem;margin-right:3px"></i>{{ $b->notes }}
                        @else
                            <span style="opacity:.45">No kitchen notes</span>
                        @endif
                    </div>
                    <div class="ef-kit-actions">
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
@if($isToday)
// Auto-refresh today's view every 5 min so countdowns stay current
setTimeout(() => location.reload(), 5 * 60 * 1000);
@endif
</script>
@endpush

</x-admin-layout>
