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

/* ── Expandable booking card (details/summary) ── */
details.ef-kit-card > summary { list-style: none; cursor: pointer; -webkit-tap-highlight-color: transparent; }
details.ef-kit-card > summary::-webkit-details-marker { display: none; }
details.ef-kit-card > summary:focus-visible { outline: 2px solid var(--ef-primary, #c8902a); outline-offset: -2px; border-radius: 12px; }
.ef-kit-caret { display: block; margin: 6px auto 0; font-size: .8rem; color: #8a827a; transition: transform .2s; }
details.ef-kit-card[open] .ef-kit-caret { transform: rotate(180deg); }
details.ef-kit-card[open] { box-shadow: 0 6px 22px rgba(19,17,16,.10); }
.ef-kit-body { border-top: 1px dashed rgba(100,82,42,.18); padding: 12px 16px 14px; display: grid; gap: 10px; }
.ef-kit-plan { font-size: .74rem; font-weight: 650; color: #5b5047; }
.ef-kit-guests { display: flex; flex-wrap: wrap; gap: 6px; }
.ef-kit-guests span { font-size: .7rem; padding: 3px 9px; border-radius: 20px; background: rgba(100,82,42,.07); color: #5b5047; }
.ef-kit-dish-head { display: flex; align-items: center; gap: 6px; font-size: .7rem; font-weight: 720; text-transform: uppercase; letter-spacing: .04em; color: #5b5047; margin-bottom: 4px; }
.ef-kit-dish-head span { margin-left: auto; font-weight: 650; color: #8a827a; }
.ef-kit-dishes { list-style: none; margin: 0; padding: 0; border: 1px solid rgba(100,82,42,.12); border-radius: 10px; overflow: hidden; }
.ef-kit-dishes li { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 9px 12px; font-size: .82rem; min-height: 44px; }
.ef-kit-dishes li + li { border-top: 1px solid rgba(100,82,42,.09); }
.ef-kit-dish-name { min-width: 0; overflow-wrap: anywhere; color: #131110; font-weight: 560; }
.ef-kit-dish-name small { display: block; color: #8a827a; font-weight: 400; font-size: .7rem; }
.ef-kit-corp-list { display: flex; flex-direction: column; gap: 10px; }
.ef-kit-corp { border: 1px solid rgba(100,82,42,.14); border-left: 4px solid #2c5784; border-radius: 14px; background: #fff; padding: 12px 14px; display: flex; flex-direction: column; gap: 10px; }
.ef-kit-corp-head { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; }
.ef-kit-corp-meals { display: flex; flex-wrap: wrap; gap: 6px; }
.ef-kit-corp-meals b { margin-left: 2px; }
.ef-kit-corp-acts { display: flex; gap: 8px; flex-wrap: wrap; }
.ef-kit-corp-acts .ef-kit-btn { flex: 1 1 140px; justify-content: center; }
.ef-kit-custom-chip { background: #fdebd9 !important; color: #a3480f !important; font-weight: 750; }
.ef-kit-custom { margin: 8px 0 10px; padding: 9px 12px; border-radius: 10px; border: 1px solid rgba(214,110,40,.35); background: #fff6ec; font-size: .8rem; color: #5b3a1a; }
.ef-kit-custom b { display: block; color: #a3480f; font-size: .72rem; letter-spacing: .05em; }
.ef-kit-custom ul { margin: 4px 0 0; padding-left: 18px; }
.ef-kit-dish-tag { font-style: normal; font-size: .6rem; text-transform: uppercase; margin-left: 4px; padding: 1px 6px; border-radius: 8px; background: #fdf0d8; color: #9a6a10; }
.ef-kit-recipe { flex-shrink: 0; font-size: .68rem; font-weight: 650; padding: 4px 9px; border-radius: 14px; text-decoration: none; white-space: nowrap; }
.ef-kit-recipe.ok { background: #e6f4ea; color: #1e7a3c; }
.ef-kit-recipe.none { background: rgba(100,82,42,.06); color: #a39a90; }
.ef-kit-nodish { font-size: .78rem; color: #8a827a; }
.ef-kit-body .ef-kit-actions { display: flex; flex-wrap: wrap; gap: 8px; }
@media (max-width: 420px) {
    .ef-kit-body { padding: 10px 12px 12px; }
    .ef-kit-body .ef-kit-actions .ef-kit-btn { flex: 1 1 auto; justify-content: center; }
}
</style>
@endpush

@php
    $eventTypes = \App\Models\HallBooking::eventTypes();
    $dateObj    = \Carbon\Carbon::parse($date);
    $isToday    = $dateObj->isToday();
    // staff (admin/manager) vs employee read-only view of the same schedule
    $kStaff = ! request()->routeIs('employee.kitchen.schedule');
    $kRoute = $kStaff ? 'hall.bookings.kitchen' : 'employee.kitchen.schedule';
    $prevDate   = $dateObj->copy()->subDay()->toDateString();
    $nextDate   = $dateObj->copy()->addDay()->toDateString();
    $todayDate  = today()->toDateString();

    // Meal tallies
    $mealEntries = $mealEntries ?? collect();
    $corpMeal    = fn ($type) => (int) $mealEntries->sum(fn ($e) => $e->items->where('meal_type', $type)->sum(fn ($i) => $i->planned_count ?? 0));
    $bfCount     = $bookings->where('has_breakfast', true)->sum('number_of_people') + $corpMeal('breakfast');
    $lnCount     = $bookings->where('has_lunch',     true)->sum('number_of_people') + $corpMeal('lunch');
    $dnCount     = $bookings->where('has_dinner',    true)->sum('number_of_people') + $corpMeal('dinner');
    $hasAny      = $bookings->isNotEmpty() || $mealEntries->isNotEmpty();
    $totalCovers = $bfCount + $lnCount + $dnCount;
    $maxCovers   = max($bfCount, $lnCount, $dnCount, 1);
    $hasMeals    = $totalCovers > 0;

    $now = now();

    $kitUrgency = function($startTime, $date, $now): string {
        try {
            $start = \Carbon\Carbon::parse($date . ' ' . $startTime);
            $mins  = $now->diffInMinutes($start, false);
            if ($start->isPast() && abs($mins) < 120) return 'active';
            if ($start->isPast()) return 'past';
            if ($mins <= 90)  return 'urgent';
            if ($mins <= 360) return 'warning';
            return 'normal';
        } catch (\Throwable $e) { return 'normal'; }
    };

    $kitCountdownLabel = function($startTime, $date, $now): string {
        try {
            $start = \Carbon\Carbon::parse($date . ' ' . $startTime);
            $mins  = $now->diffInMinutes($start, false);
            if ($start->isPast() && abs($mins) < 120) return 'In Progress';
            if ($start->isPast()) return 'Completed';
            if ($mins < 60) return "{$mins}m away";
            $hrs = floor($mins / 60); $rem = $mins % 60;
            return $rem > 0 ? "{$hrs}h {$rem}m" : "{$hrs}h away";
        } catch (\Throwable $e) { return '—'; }
    };

    $kitCountdownCls = function($urgency): string {
        return match($urgency) {
            'urgent'  => 'soon',
            'warning' => 'prep',
            'normal'  => 'ahead',
            'active'  => 'active',
            'past'    => 'past',
            default   => 'ahead',
        };
    };
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
        @if($hasAny)
            <div class="ef-kit-stat">
                <div class="ef-kit-stat-val">{{ $bookings->count() + $mealEntries->count() }}</div>
                <div class="ef-kit-stat-lbl">Orders</div>
            </div>
            @if($bookings->isNotEmpty())
            <div class="ef-kit-stat-div"></div>
            <div class="ef-kit-stat">
                <div class="ef-kit-stat-val">{{ number_format($bookings->sum('number_of_people')) }}</div>
                <div class="ef-kit-stat-lbl">Guests</div>
            </div>
            @endif
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
    <a href="{{ route($kRoute, ['date' => $prevDate]) }}"
       class="ef-kit-date-arrow" aria-label="Previous day">
        <i class="bi bi-chevron-left"></i>
    </a>

    <div class="ef-kit-date-center">
        <span class="ef-kit-date-text">
            {{ $dateObj->format('d M') }}, {{ $dateObj->format('l') }}
        </span>
        @if(!$isToday)
            <a href="{{ route($kRoute, ['date' => $todayDate]) }}"
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

    <a href="{{ route($kRoute, ['date' => $nextDate]) }}"
       class="ef-kit-date-arrow" aria-label="Next day">
        <i class="bi bi-chevron-right"></i>
    </a>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     CATERING TYPE FILTER
═══════════════════════════════════════════════════════════════════ --}}
@php $cateringFilter = request('catering_type', 'all'); @endphp
<div style="display:flex;gap:6px;overflow-x:auto;scrollbar-width:none;margin-bottom:12px;padding-bottom:2px">
    @foreach(['all' => 'All Catering', 'hall' => 'Hall Catering', 'external' => 'External Catering', 'corporate' => 'Corporate Meals'] as $val => $label)
        <a href="{{ route($kRoute, ['date' => $date, 'catering_type' => $val === 'all' ? null : $val]) }}"
           style="display:inline-block;flex-shrink:0;padding:5px 13px;border-radius:20px;font-size:.72rem;font-weight:660;text-decoration:none;border:1.5px solid {{ $cateringFilter === $val ? '#131110' : 'rgba(100,82,42,.18)' }};background:{{ $cateringFilter === $val ? '#131110' : 'transparent' }};color:{{ $cateringFilter === $val ? '#fdfaf5' : '#8a827a' }};white-space:nowrap">
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- ═══════════════════════════════════════════════════════════════
     MEAL STRIP — compact 3-column, hidden when all zero + no events
═══════════════════════════════════════════════════════════════════ --}}
@if($hasAny || $hasMeals)
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
@if(! $hasAny)

    {{-- Compact actionable empty state --}}
    <div class="ef-kit-empty">
        <div class="ef-kit-empty-icon">🍽</div>
        <div class="ef-kit-empty-title">Kitchen Schedule Clear</div>
        <div class="ef-kit-empty-sub">
            No events or corporate meal entries on {{ $dateObj->format('d F Y') }}.
        </div>
        <div class="ef-kit-empty-actions">
@if($kStaff)
            <a href="{{ route('hall.bookings.index') }}" class="ef-kit-btn ef-kit-btn-outline">
                <i class="bi bi-calendar3"></i> View Bookings
            </a>
            <a href="{{ route('hall.bookings.create') }}" class="ef-kit-btn ef-kit-btn-primary">
                <i class="bi bi-plus-lg"></i> Create Booking
            </a>
        @endif
            </div>
    </div>

@else

    @if($bookings->isNotEmpty())
    <div class="ef-kit-section">
        <i class="bi bi-list-ul"></i>
        {{ $bookings->count() }} event{{ $bookings->count() === 1 ? '' : 's' }} · sorted by start time
    </div>

    <div class="ef-kit-cards">
        @foreach($bookings as $b)
            @php
                $urgency      = $kitUrgency($b->start_time, $date, $now);
                $countdown    = $kitCountdownLabel($b->start_time, $date, $now);
                $countdownCls = $kitCountdownCls($urgency);
                $startFmt     = \Carbon\Carbon::parse($b->start_time)->format('h:i A');
                $endFmt       = \Carbon\Carbon::parse($b->end_time)->format('h:i A');
                $evtLabel     = $eventTypes[$b->event_type] ?? ucfirst(str_replace('_',' ',$b->event_type));
            @endphp
            @php
                $groups   = $kitchenItems[$b->id] ?? [];
                $custom   = $kitchenCustom[$b->id] ?? [];
                $dishCnt  = collect($groups)->flatten(1)->count();
                $mealMeta = ['breakfast' => ['Breakfast','bi-cup-hot','bf'], 'lunch' => ['Lunch','bi-brightness-high','ln'], 'dinner' => ['Dinner','bi-moon-stars','dn'], 'menu' => ['Menu','bi-list-ul','mp']];
            @endphp
            <details class="ef-kit-card {{ $urgency }}" data-kit-card>
                <summary class="ef-kit-card-top" aria-label="Show items for {{ $b->customer_name }}">
                    <div class="ef-kit-time">
                        <div class="ef-kit-time-val">{{ $startFmt }}</div>
                        <div class="ef-kit-time-end">→ {{ $endFmt }}</div>
                    </div>
                    <div class="ef-kit-info">
                        <div class="ef-kit-customer">
                            {{ $b->customer_name }}
                            <x-booking-type-badge :type="$b->booking_type" size="xs" style="margin-left:4px;vertical-align:middle" />
                        </div>
                        <div class="ef-kit-evt-type">{{ $evtLabel }} · {{ $b->location_label }}</div>
                        <div class="ef-kit-chips">
                            @if($b->has_breakfast)<span class="ef-kit-chip bf"><i class="bi bi-cup-hot"></i> BF</span>@endif
                            @if($b->has_lunch)<span class="ef-kit-chip ln"><i class="bi bi-brightness-high"></i> LN</span>@endif
                            @if($b->has_dinner)<span class="ef-kit-chip dn"><i class="bi bi-moon-stars"></i> DN</span>@endif
                            <span class="ef-kit-chip mp"><i class="bi bi-egg-fried" style="font-size:.6rem"></i> {{ $dishCnt }} item{{ $dishCnt === 1 ? '' : 's' }}</span>
                            @if($custom)<span class="ef-kit-chip ef-kit-custom-chip" data-customized><i class="bi bi-exclamation-triangle-fill"></i> Customized menu</span>@endif
                        </div>
                    </div>
                    <div class="ef-kit-pax">
                        <div class="ef-kit-pax-num">{{ number_format($b->number_of_people) }}</div>
                        <div class="ef-kit-pax-lbl">Covers</div>
                        <span class="ef-kit-countdown {{ $countdownCls }}">{{ $countdown }}</span>
                        <i class="bi bi-chevron-down ef-kit-caret" aria-hidden="true"></i>
                    </div>
                </summary>

                <div class="ef-kit-body">
                    @if($b->mealPlan)
                        <div class="ef-kit-plan"><i class="bi bi-grid-3x3-gap"></i> Base menu: {{ $b->mealPlan->name }}</div>
                    @endif
                    @if($custom)
                        <div class="ef-kit-custom" role="note">
                            <b><i class="bi bi-exclamation-triangle-fill"></i> CUSTOMIZATION</b>
                            <ul>@foreach($custom as $c)<li>{{ $c }}</li>@endforeach</ul>
                        </div>
                    @endif
                    @if($b->meals->isNotEmpty())
                        <div class="ef-kit-guests">
                            @foreach($b->meals as $m)
                                <span><b>{{ number_format($m->guest_count) }}</b> {{ ucfirst($m->meal_type) }}</span>
                            @endforeach
                        </div>
                    @endif

                    @forelse($groups as $meal => $lines)
                        @php [$mLabel, $mIcon, $mCls] = $mealMeta[$meal] ?? [ucfirst($meal), 'bi-list-ul', 'mp']; @endphp
                        <div class="ef-kit-dish-group">
                            <div class="ef-kit-dish-head {{ $mCls }}"><i class="bi {{ $mIcon }}"></i> {{ $mLabel }} <span>{{ count($lines) }}</span></div>
                            <ul class="ef-kit-dishes">
                                @foreach($lines as $line)
                                    <li>
                                        <span class="ef-kit-dish-name">
                                            {{ $line['name'] }}
                                            @php $ct = $line['change_type'] ?? 'plan'; @endphp
                                            @if($ct === 'added')<em class="ef-kit-dish-tag">extra</em>
                                            @elseif($ct === 'replaced')<em class="ef-kit-dish-tag">replaced</em>
                                            @elseif($ct === 'modified')<em class="ef-kit-dish-tag">changed</em>@endif
                                            @if($ct === 'replaced' && ! empty($line['replaces']))<small>instead of {{ $line['replaces'] }}</small>@endif
                                            @if(! empty($line['servings']))<small>{{ number_format($line['servings']) }} servings</small>@endif
                                            @if(! empty($line['notes']))<small>{{ $line['notes'] }}</small>@endif
                                        </span>
                                        @if($line['recipe'])
                                            <a href="{{ $kStaff ? route('kitchen.recipes.show', $line['recipe']) : route('employee.kitchen.calculator', ['recipe' => $line['recipe']->id, 'people' => $b->number_of_people]) }}" class="ef-kit-recipe ok"><i class="bi bi-journal-check"></i> Recipe</a>
                                        @else
                                            <span class="ef-kit-recipe none">No recipe</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @empty
                        <div class="ef-kit-nodish"><i class="bi bi-info-circle"></i> No menu items yet — set the final menu.</div>
                    @endforelse

                    @if($b->notes)
                        <div class="ef-kit-note"><i class="bi bi-chat-left-text" style="font-size:.68rem;margin-right:3px"></i>{{ $b->notes }}</div>
                    @endif

                    <div class="ef-kit-actions">
                        @if($kStaff)
                        <a href="{{ route('hall.bookings.show', $b) }}" class="ef-kit-btn ef-kit-btn-outline"><i class="bi bi-clipboard-check"></i> Booking</a>
                        <a href="{{ route('hall.bookings.menu.edit', $b) }}" class="ef-kit-btn ef-kit-btn-outline"><i class="bi bi-pencil-square"></i> Final menu</a>
                        @endif
                        @if(Route::has('inventory.material-requests.create'))
                            <a href="{{ route('inventory.material-requests.create', ['hall_booking_id' => $b->id]) }}" class="ef-kit-btn ef-kit-btn-primary"><i class="bi bi-box-seam"></i> Request materials</a>
                        @endif
                    </div>
                </div>
            </details>
        @endforeach
    </div>
    @endif

    @if($mealEntries->isNotEmpty())
    <div class="ef-kit-section" style="margin-top:14px">
        <i class="bi bi-building"></i>
        {{ $mealEntries->count() }} corporate meal {{ Str::plural('entry', $mealEntries->count()) }} · from the Daily Meal Register
    </div>
    <div class="ef-kit-corp-list">
        @foreach($mealEntries as $e)
            @php $its = $e->items->filter(fn ($i) => ($i->planned_count ?? 0) > 0 || ($i->actual_count ?? 0) > 0); @endphp
            <div class="ef-kit-corp" data-meal-entry="{{ $e->id }}">
                <div class="ef-kit-corp-head">
                    <div style="min-width:0">
                        <div class="ef-kit-customer">{{ $e->client?->name ?? 'Client' }}</div>
                        <div class="ef-kit-evt-type">Corporate meals @if($e->remarks)· {{ Str::limit($e->remarks, 60) }}@endif</div>
                    </div>
                    <div class="ef-kit-pax">
                        <div class="ef-kit-pax-num">{{ number_format($its->sum(fn ($i) => $i->planned_count ?? 0)) }}</div>
                        <div class="ef-kit-pax-lbl">Planned</div>
                    </div>
                </div>
                <div class="ef-kit-corp-meals">
                    @foreach($its as $i)
                        @php [$ml, $mi, $mc] = ['breakfast' => ['Breakfast', 'bi-cup-hot', 'bf'], 'lunch' => ['Lunch', 'bi-brightness-high', 'ln'], 'dinner' => ['Dinner', 'bi-moon-stars', 'dn']][$i->meal_type] ?? [ucfirst($i->meal_type), 'bi-egg-fried', 'mp']; @endphp
                        <span class="ef-kit-chip {{ $mc }}"><i class="bi {{ $mi }}"></i> {{ $ml }} <b>{{ number_format($i->planned_count ?? 0) }}</b>@if($i->actual_count !== null) · served {{ number_format($i->actual_count) }}@endif</span>
                    @endforeach
                </div>
                <div class="ef-kit-corp-acts">
                    <a href="{{ route('meal-register.entries.show', $e) }}" class="ef-kit-btn ef-kit-btn-outline"><i class="bi bi-journal-text"></i> Entry</a>
                    <a href="{{ route('inventory.material-requests.create', ['meal_entry_id' => $e->id]) }}" class="ef-kit-btn ef-kit-btn-primary"><i class="bi bi-basket"></i> Request materials</a>
                </div>
            </div>
        @endforeach
    </div>
    @endif

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
