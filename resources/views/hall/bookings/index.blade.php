<x-admin-layout title="Hall Bookings">
@push('styles')
<style>
/* ── Hall Bookings — premium redesign — hb-* ──────────────────────── */
:root {
    --hb-gold:      #b07d3b;
    --hb-gold-hi:   #c48f42;
    --hb-gold-soft: #e8d4b0;
    --hb-ink:       #1d1d1d;
    --hb-sub:       #5a5a5a;
    --hb-muted:     #7a7a7a;
    --hb-faint:     #b0b0b0;
    --hb-border:    rgba(0,0,0,.07);
    --hb-surface:   #ffffff;
    --hb-bg:        #f7f6f3;
    --hb-r:         16px;
    --hb-r-sm:      10px;
    --hb-shadow:    0 1px 3px rgba(0,0,0,.05), 0 4px 16px rgba(0,0,0,.07);
    --hb-shadow-sm: 0 1px 3px rgba(0,0,0,.06), 0 2px 8px rgba(0,0,0,.05);
    --hb-ease:      cubic-bezier(.25,.46,.45,.94);

    --hb-confirmed: #16a34a;
    --hb-pending:   #d97706;
    --hb-completed: #2563eb;
    --hb-cancelled: #dc2626;
}
*, *::before, *::after { box-sizing: border-box; }

/* Page background */
#main-content { background: var(--hb-bg); }

.hb-shell {
    max-width: 1400px;
    margin: 0 auto;
    padding-bottom: 140px;
}

/* ── Flash ───────────────────────────────────────────────────────── */
.hb-flash {
    display: flex; align-items: center; gap: 9px;
    border-radius: 12px; font-size: .84rem;
    margin-bottom: 14px; padding: 12px 16px;
}
.hb-flash.--success { background: rgba(22,163,74,.07); border: 1px solid rgba(22,163,74,.15); color: #0a5c40; }
.hb-flash.--error   { background: rgba(220,38,38,.07); border: 1px solid rgba(220,38,38,.15); color: #8b2020; }

/* ── Stats ───────────────────────────────────────────────────────── */
.hb-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-bottom: 14px;
}
.hb-stat {
    background: var(--hb-surface);
    border-radius: var(--hb-r);
    padding: 16px 18px 14px;
    box-shadow: var(--hb-shadow-sm);
    min-width: 0;
    position: relative;
    overflow: hidden;
}
.hb-stat::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--hb-gold-soft), transparent);
    opacity: .6;
}
.hb-stat.--blue::after  { background: linear-gradient(90deg, #93c5fd, transparent); }
.hb-stat.--amber::after { background: linear-gradient(90deg, #fcd34d, transparent); }
.hb-stat.--green::after { background: linear-gradient(90deg, #86efac, transparent); }

.hb-stat-val {
    font-size: 1.65rem;
    font-weight: 900;
    color: var(--hb-ink);
    line-height: 1;
    letter-spacing: -.03em;
    margin-bottom: 6px;
    font-variant-numeric: tabular-nums;
}
.hb-stat-label {
    font-size: .7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--hb-muted);
}

/* ── Sticky search toolbar ───────────────────────────────────────── */
.hb-sticky {
    position: sticky;
    top: 0;
    z-index: 100;
    background: var(--hb-bg);
    padding: 10px 0 8px;
    margin: 0 -16px;
    padding-left: 16px;
    padding-right: 16px;
}
.hb-topbar {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-bottom: 8px;
}
.hb-search-wrap {
    flex: 1;
    display: flex;
    background: var(--hb-surface);
    border-radius: 12px;
    box-shadow: var(--hb-shadow-sm);
    overflow: hidden;
}
.hb-search-input {
    flex: 1; border: none; outline: none;
    padding: 12px 14px;
    font-size: .9rem; color: var(--hb-ink);
    background: transparent; min-width: 0;
    height: 46px;
}
.hb-search-btn {
    background: none; border: none; padding: 0 14px;
    color: var(--hb-muted); cursor: pointer;
    font-size: 1rem; height: 46px;
    display: flex; align-items: center;
    transition: color .14s;
}
.hb-search-btn:hover { color: var(--hb-gold); }

.hb-topbar-right { display: flex; gap: 7px; align-items: center; flex-shrink: 0; }

.hb-view-toggle {
    display: flex;
    background: var(--hb-surface);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: var(--hb-shadow-sm);
}
.hb-vt-btn {
    background: none; border: none; padding: 0 10px;
    height: 44px; min-width: 44px;
    cursor: pointer; color: var(--hb-muted);
    font-size: .95rem;
    display: flex; align-items: center; justify-content: center;
    transition: background .14s, color .14s;
}
.hb-vt-btn.--active { background: var(--hb-gold); color: #fff; }
.hb-vt-btn:not(.--active):hover { background: rgba(0,0,0,.04); color: var(--hb-ink); }

.hb-filter-btn {
    position: relative;
    background: var(--hb-surface);
    border-radius: 10px;
    height: 44px; min-width: 44px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: var(--hb-sub);
    font-size: .88rem; font-weight: 600;
    padding: 0 14px; gap: 6px;
    box-shadow: var(--hb-shadow-sm);
    border: none;
    transition: color .14s, background .14s;
}
.hb-filter-btn:hover,
.hb-filter-btn.--active { color: var(--hb-gold); background: #fff8ef; }
.hb-filter-dot {
    position: absolute; top: 8px; right: 8px;
    width: 6px; height: 6px;
    background: var(--hb-gold); border-radius: 50%;
    border: 1.5px solid var(--hb-bg);
}
.hb-btn-new {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--hb-gold); color: #fff;
    border-radius: 10px; padding: 0 18px; height: 44px;
    font-size: .88rem; font-weight: 700;
    text-decoration: none; white-space: nowrap;
    transition: background .14s; box-shadow: 0 2px 8px rgba(176,125,59,.35);
}
.hb-btn-new:hover { background: var(--hb-gold-hi); color: #fff; text-decoration: none; }

/* ── Quick chips ─────────────────────────────────────────────────── */
.hb-chips {
    display: flex; gap: 6px;
    overflow-x: auto; scrollbar-width: none;
    padding-bottom: 2px;
}
.hb-chips::-webkit-scrollbar { display: none; }
.hb-chip {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 0 14px; height: 34px; border-radius: 100px;
    background: var(--hb-surface);
    color: var(--hb-sub); font-size: .8rem; font-weight: 600;
    white-space: nowrap; text-decoration: none;
    cursor: pointer; flex-shrink: 0;
    box-shadow: var(--hb-shadow-sm);
    transition: all .14s;
}
.hb-chip:hover { color: var(--hb-gold); text-decoration: none; }
.hb-chip.--active {
    background: var(--hb-gold); color: #fff;
    box-shadow: 0 2px 8px rgba(176,125,59,.3);
}
.hb-chip.--active:hover { background: var(--hb-gold-hi); color: #fff; }

/* ── Result bar ──────────────────────────────────────────────────── */
.hb-result-bar {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 12px; font-size: .78rem;
    color: var(--hb-muted); font-weight: 600;
}

/* ── Card grid ───────────────────────────────────────────────────── */
.hb-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 12px;
    align-items: start;
}

/* ── Card ────────────────────────────────────────────────────────── */
.hb-card {
    background: var(--hb-surface);
    border-radius: var(--hb-r);
    box-shadow: var(--hb-shadow);
    display: flex;
    flex-direction: column;
    min-height: 178px;
    overflow: hidden;
    transition: box-shadow .18s, transform .18s;
}
.hb-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,.08), 0 12px 32px rgba(0,0,0,.09);
    transform: translateY(-1px);
}
.hb-card.--cancelled { opacity: .72; }

.hb-card-body {
    flex: 1;
    padding: 16px 16px 12px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-height: 0;
}

/* Row 1: Name + Amount */
.hb-card-top {
    display: flex;
    align-items: flex-start;
    gap: 8px;
}
.hb-name {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--hb-ink);
    flex: 1;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    line-height: 1.3;
}
.hb-amt {
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--hb-ink);
    letter-spacing: -.03em;
    white-space: nowrap;
    flex-shrink: 0;
    font-variant-numeric: tabular-nums;
    line-height: 1.3;
}

/* Row 2: Event tag + Pills */
.hb-card-sub {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.hb-evt-tag {
    font-size: .72rem;
    color: var(--hb-muted);
    background: var(--hb-bg);
    border-radius: 6px;
    padding: 2px 8px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 110px;
    font-weight: 500;
}
.hb-pills { display: flex; gap: 5px; flex-wrap: wrap; }
.hb-pill {
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    padding: 2px 8px;
    border-radius: 100px;
    white-space: nowrap;
}
.hb-pill.--confirmed { background: rgba(22,163,74,.1);  color: #15803d; }
.hb-pill.--completed { background: rgba(37,99,235,.1);  color: #1d4ed8; }
.hb-pill.--pending   { background: rgba(217,119,6,.1);  color: #b45309; }
.hb-pill.--cancelled { background: rgba(220,38,38,.1);  color: #b91c1c; }
.hb-pill.--paid      { background: rgba(22,163,74,.1);  color: #15803d; }
.hb-pill.--partial   { background: rgba(234,88,12,.1);  color: #c2410c; }
.hb-pill.--unpaid    { background: rgba(220,38,38,.08); color: #b91c1c; }

/* Row 3: Meta items */
.hb-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 4px 12px;
    margin-top: 2px;
}
.hb-mi {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: .78rem; color: var(--hb-sub);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.hb-mi i { font-size: .72rem; color: var(--hb-faint); flex-shrink: 0; }

/* Card footer */
.hb-card-footer {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 14px 12px;
    border-top: 1px solid var(--hb-border);
    background: rgba(247,246,243,.5);
}
.hb-act {
    display: inline-flex; align-items: center; justify-content: center;
    gap: 5px; border-radius: 9px;
    font-size: .8rem; font-weight: 700;
    text-decoration: none; cursor: pointer;
    border: none; transition: all .15s;
    white-space: nowrap;
}
.hb-act.--view {
    flex: 1;
    height: 36px;
    background: var(--hb-gold);
    color: #fff;
    box-shadow: 0 2px 6px rgba(176,125,59,.3);
    padding: 0 14px;
}
.hb-act.--view:hover { background: var(--hb-gold-hi); color: #fff; text-decoration: none; }
.hb-act.--wa {
    width: 36px; height: 36px;
    background: #f0fdf4;
    color: #16a34a;
    border-radius: 9px;
    flex-shrink: 0;
    font-size: .95rem;
    border: 1px solid rgba(22,163,74,.15);
}
.hb-act.--wa:hover { background: #dcfce7; color: #15803d; text-decoration: none; }
.hb-act.--more {
    width: 36px; height: 36px;
    background: rgba(0,0,0,.04);
    color: var(--hb-muted);
    border-radius: 9px;
    flex-shrink: 0;
    font-size: .95rem;
}
.hb-act.--more:hover { background: rgba(0,0,0,.08); color: var(--hb-ink); }

/* ── List view ───────────────────────────────────────────────────── */
.hb-grid.--list {
    grid-template-columns: 1fr;
    gap: 7px;
}
.hb-grid.--list .hb-card {
    min-height: unset;
    flex-direction: row;
    align-items: stretch;
}
.hb-grid.--list .hb-card-body {
    flex-direction: row;
    align-items: center;
    flex: 1;
    gap: 12px;
    padding: 12px 14px;
    flex-wrap: nowrap;
}
.hb-grid.--list .hb-card-top {
    flex: 0 0 190px;
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
}
.hb-grid.--list .hb-card-sub { flex: 0 0 auto; flex-direction: column; align-items: flex-start; }
.hb-grid.--list .hb-meta { flex: 1; row-gap: 2px; }
.hb-grid.--list .hb-card-footer {
    flex: 0 0 auto;
    flex-direction: column;
    border-top: none;
    border-left: 1px solid var(--hb-border);
    padding: 10px 12px;
    background: transparent;
    gap: 5px;
}
.hb-grid.--list .hb-act.--view { width: 80px; flex: unset; }

/* ── Dropdown ────────────────────────────────────────────────────── */
.hb-dropdown {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,.06), 0 10px 30px rgba(0,0,0,.12);
    padding: 6px;
}
.hb-dropdown .dropdown-item {
    font-size: .84rem; padding: 9px 12px;
    color: var(--hb-sub); border-radius: 8px;
}
.hb-dropdown .dropdown-item:hover { background: var(--hb-bg); color: var(--hb-ink); }
.hb-dropdown .dropdown-divider { border-color: var(--hb-border); margin: 4px 0; }

/* ── Sentinel / loader ───────────────────────────────────────────── */
.hb-sentinel { height: 1px; }
.hb-loader { display: none; text-align: center; padding: 24px; grid-column: 1/-1; color: var(--hb-muted); font-size: .83rem; }
.hb-loader.--show { display: block; }
.hb-loader-spin {
    display: inline-block;
    width: 16px; height: 16px;
    border: 2px solid var(--hb-border);
    border-top-color: var(--hb-gold);
    border-radius: 50%;
    animation: hb-spin .7s linear infinite;
    vertical-align: middle; margin-right: 6px;
}
@keyframes hb-spin { to { transform: rotate(360deg); } }

/* ── Empty ───────────────────────────────────────────────────────── */
.hb-empty { grid-column: 1/-1; text-align: center; padding: 60px 20px; color: var(--hb-faint); }
.hb-empty-icon { font-size: 2.5rem; margin-bottom: 14px; opacity: .4; }
.hb-empty-text { font-size: .92rem; }

/* ── FAB ─────────────────────────────────────────────────────────── */
.hb-fab {
    position: fixed; bottom: 90px; right: 20px;
    width: 56px; height: 56px; border-radius: 50%;
    background: var(--hb-gold); color: #fff;
    display: none; align-items: center; justify-content: center;
    font-size: 1.4rem;
    box-shadow: 0 4px 14px rgba(176,125,59,.45), 0 1px 3px rgba(0,0,0,.12);
    text-decoration: none; z-index: 200;
    transition: background .15s, transform .15s;
}
.hb-fab:hover { background: var(--hb-gold-hi); color: #fff; transform: scale(1.06); text-decoration: none; }

/* ── Bottom sheet ────────────────────────────────────────────────── */
.hb-backdrop {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.4);
    backdrop-filter: blur(2px);
    z-index: 1049;
    display: none; opacity: 0;
    transition: opacity .25s;
}
.hb-backdrop.--open { display: block; opacity: 1; }
.hb-sheet {
    position: fixed; bottom: 0; left: 0; right: 0;
    background: var(--hb-surface);
    border-radius: 20px 20px 0 0;
    box-shadow: 0 -4px 40px rgba(0,0,0,.15);
    transform: translateY(100%);
    transition: transform .3s var(--hb-ease);
    z-index: 1050; max-height: 88vh;
    overflow-y: auto; overscroll-behavior: contain;
}
.hb-sheet.--open { transform: translateY(0); }
.hb-sheet-handle-wrap { padding: 10px 0 6px; display: flex; justify-content: center; }
.hb-sheet-handle { width: 36px; height: 4px; background: rgba(0,0,0,.1); border-radius: 2px; }
.hb-sheet-hdr {
    display: flex; align-items: center; justify-content: space-between;
    padding: 4px 20px 14px;
    border-bottom: 1px solid var(--hb-border);
}
.hb-sheet-hdr h2 { font-size: 1rem; font-weight: 800; color: var(--hb-ink); margin: 0; }
.hb-sheet-close {
    background: rgba(0,0,0,.05); border: none; font-size: 1rem; color: var(--hb-muted);
    cursor: pointer; width: 32px; height: 32px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 8px; padding: 0; transition: background .14s;
}
.hb-sheet-close:hover { background: rgba(0,0,0,.1); color: var(--hb-ink); }
.hb-sheet-body { padding: 18px 20px; display: flex; flex-direction: column; gap: 14px; }
.hb-sf-label {
    font-size: .68rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: var(--hb-muted); margin-bottom: 6px; display: block;
}
.hb-sf-select, .hb-sf-input {
    width: 100%; padding: 11px 13px;
    border: 1.5px solid rgba(0,0,0,.1);
    border-radius: 10px; font-size: .88rem;
    color: var(--hb-ink); background: var(--hb-surface);
    outline: none; appearance: none; -webkit-appearance: none; height: 46px;
    transition: border-color .14s;
}
.hb-sf-select:focus, .hb-sf-input:focus { border-color: var(--hb-gold); }
.hb-sf-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.hb-sheet-foot {
    display: flex; gap: 10px;
    padding: 14px 20px 28px;
    border-top: 1px solid var(--hb-border);
}
.hb-btn-ghost {
    flex: 1; display: flex; align-items: center; justify-content: center;
    height: 48px; border-radius: 12px;
    border: 1.5px solid rgba(0,0,0,.12);
    background: none; color: var(--hb-sub);
    font-size: .88rem; font-weight: 700; cursor: pointer;
    transition: all .14s;
}
.hb-btn-ghost:hover { border-color: var(--hb-ink); color: var(--hb-ink); }
.hb-btn-primary {
    flex: 2; display: flex; align-items: center; justify-content: center;
    height: 48px; border-radius: 12px; border: none;
    background: var(--hb-gold); color: #fff;
    font-size: .88rem; font-weight: 700; cursor: pointer;
    box-shadow: 0 2px 8px rgba(176,125,59,.35);
    transition: background .14s;
}
.hb-btn-primary:hover { background: var(--hb-gold-hi); }

/* ── Responsive ──────────────────────────────────────────────────── */
@media (max-width: 640px) {
    .hb-stats { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .hb-stat-val { font-size: 1.4rem; }
    .hb-btn-new { display: none; }
    .hb-fab { display: flex; }
    .hb-grid { grid-template-columns: 1fr; }
    .hb-name { font-size: .95rem; }
    .hb-amt  { font-size: 1.15rem; }
    .hb-grid.--list .hb-card-top { flex: 0 0 130px; }
    .hb-grid.--list .hb-card-sub { display: none; }
}
@media (min-width: 641px) and (max-width: 1024px) {
    .hb-stats { grid-template-columns: repeat(2, 1fr); }
    .hb-grid  { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 1025px) {
    .hb-sheet {
        left: auto; right: 0; width: 420px;
        max-height: 100vh; border-radius: 0; top: 0; bottom: 0;
    }
    .hb-sticky { margin: 0; }
}
</style>
@endpush

@php
$today    = now()->toDateString();
$tomorrow = now()->addDay()->toDateString();

$activeFilters = request()->hasAny(['hall_id','status','payment_status','date_from','date_to','booking_type']);

// Abbreviate large numbers for KPI display
$fmtKpi = function($n) {
    if ($n >= 10000000) return '₹' . number_format($n/10000000, 1) . 'Cr';
    if ($n >= 100000)   return '₹' . number_format($n/100000, 1) . 'L';
    if ($n >= 1000)     return '₹' . round($n/1000) . 'K';
    return '₹' . number_format($n);
};

$scrollPage = $bookings->currentPage() + 1;
$scrollMore = $bookings->hasMorePages() ? 'true' : 'false';

$chipToday    = request('date_from') === $today && request('date_to') === $today;
$chipTomorrow = request('date_from') === $tomorrow && request('date_to') === $tomorrow;
$chipPending  = request('payment_status') === 'pending' && !request()->hasAny(['date_from','date_to','status','booking_type','hall_id']);
$chipFood     = request('booking_type') === 'food_only' && !request()->hasAny(['date_from','date_to','status','payment_status','hall_id']);
$chipUpcoming = request('date_from') === $today && !request('date_to') && !request()->hasAny(['status','payment_status','booking_type','hall_id']);
$chipAll      = !$activeFilters && !request('search');
@endphp

<div class="hb-shell">

    {{-- Flash --}}
    @if(session('success'))
        <div class="hb-flash --success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
    @endif
    @if(session('error') || $errors->any())
        <div class="hb-flash --error"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') ?? $errors->first() }}</div>
    @endif

    {{-- KPI Stats --}}
    <div class="hb-stats">
        <div class="hb-stat">
            <div class="hb-stat-val">{{ $stats['today'] }}</div>
            <div class="hb-stat-label">Today's Events</div>
        </div>
        <div class="hb-stat --blue">
            <div class="hb-stat-val">{{ number_format($stats['upcoming_guests']) }}</div>
            <div class="hb-stat-label">Upcoming Guests</div>
        </div>
        <div class="hb-stat --amber">
            @if($isEmployee)
                <div class="hb-stat-val" style="color:var(--hb-faint)">—</div>
            @else
                <div class="hb-stat-val">{{ $fmtKpi($stats['pending_collect']) }}</div>
            @endif
            <div class="hb-stat-label">Pending Collection</div>
        </div>
        <div class="hb-stat --green">
            @if($isEmployee)
                <div class="hb-stat-val" style="color:var(--hb-faint)">—</div>
            @else
                <div class="hb-stat-val">{{ $fmtKpi($stats['month_revenue']) }}</div>
            @endif
            <div class="hb-stat-label">Monthly Revenue</div>
        </div>
    </div>

    {{-- Sticky search + filters --}}
    <div class="hb-sticky">
        <form class="hb-topbar" method="GET" action="{{ route('hall.bookings.index') }}" id="hbSearchForm">
            @foreach(request()->except('search','page') as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach
            <div class="hb-search-wrap">
                <input type="search" name="search" class="hb-search-input"
                       placeholder="Search name or mobile…"
                       value="{{ request('search') }}"
                       autocomplete="off">
                <button type="submit" class="hb-search-btn" aria-label="Search">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            <div class="hb-topbar-right">
                <div class="hb-view-toggle" role="group" aria-label="View mode">
                    <button type="button" class="hb-vt-btn --active" data-view="card" title="Card view" aria-pressed="true">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </button>
                    <button type="button" class="hb-vt-btn" data-view="list" title="List view" aria-pressed="false">
                        <i class="bi bi-list-ul"></i>
                    </button>
                </div>
                <button type="button" class="hb-filter-btn {{ $activeFilters ? '--active' : '' }}" id="hbOpenSheet" aria-expanded="false" aria-controls="hbSheet">
                    <i class="bi bi-sliders"></i>
                    <span class="d-none d-sm-inline">Filter</span>
                    @if($activeFilters)<span class="hb-filter-dot"></span>@endif
                </button>
                <a href="{{ route('hall.bookings.create') }}" class="hb-btn-new">
                    <i class="bi bi-plus-lg"></i> New Booking
                </a>
            </div>
        </form>

        <div class="hb-chips" role="list" aria-label="Quick filters">
            <a role="listitem" href="{{ route('hall.bookings.index') }}"
               class="hb-chip {{ $chipAll ? '--active' : '' }}">All</a>
            <a role="listitem" href="{{ route('hall.bookings.index', array_merge(request()->except(['date_from','date_to','page']), ['date_from' => $today, 'date_to' => $today])) }}"
               class="hb-chip {{ $chipToday ? '--active' : '' }}">Today</a>
            <a role="listitem" href="{{ route('hall.bookings.index', array_merge(request()->except(['date_from','date_to','page']), ['date_from' => $tomorrow, 'date_to' => $tomorrow])) }}"
               class="hb-chip {{ $chipTomorrow ? '--active' : '' }}">Tomorrow</a>
            <a role="listitem" href="{{ route('hall.bookings.index', ['payment_status' => 'pending']) }}"
               class="hb-chip {{ $chipPending ? '--active' : '' }}">Pending Pay</a>
            <a role="listitem" href="{{ route('hall.bookings.index', ['booking_type' => 'food_only']) }}"
               class="hb-chip {{ $chipFood ? '--active' : '' }}">Food Only</a>
            <a role="listitem" href="{{ route('hall.bookings.index', array_merge(request()->except(['date_from','date_to','page']), ['date_from' => $today])) }}"
               class="hb-chip {{ $chipUpcoming ? '--active' : '' }}">Upcoming</a>
        </div>
    </div>

    {{-- Result bar --}}
    <div class="hb-result-bar">
        <span>{{ number_format($bookings->total()) }} booking{{ $bookings->total() !== 1 ? 's' : '' }}
        @if($activeFilters || request('search'))
            &nbsp;·&nbsp;<a href="{{ route('hall.bookings.index') }}" style="color:var(--hb-gold);text-decoration:none">Clear filters</a>
        @endif
        </span>
    </div>

    {{-- Card grid --}}
    <div class="hb-grid" id="hbGrid">
        @include('hall.bookings._booking_cards', ['bookings' => $bookings, 'today' => $today, 'isEmployee' => $isEmployee])
        @if($bookings->isEmpty())
            <div class="hb-empty">
                <div class="hb-empty-icon"><i class="bi bi-calendar-x"></i></div>
                <div class="hb-empty-text">No bookings found. <a href="{{ route('hall.bookings.index') }}" style="color:var(--hb-gold)">Clear filters</a> or <a href="{{ route('hall.bookings.create') }}" style="color:var(--hb-gold)">create one</a>.</div>
            </div>
        @endif
        <div class="hb-sentinel" id="hbSentinel" style="grid-column:1/-1"></div>
        <div class="hb-loader" id="hbLoader" style="grid-column:1/-1">
            <span class="hb-loader-spin"></span> Loading more…
        </div>
    </div>

</div>

{{-- FAB (mobile) --}}
<a href="{{ route('hall.bookings.create') }}" class="hb-fab" aria-label="New Booking">
    <i class="bi bi-plus-lg"></i>
</a>

{{-- Backdrop --}}
<div class="hb-backdrop" id="hbBackdrop" role="presentation"></div>

{{-- Bottom / side sheet filter --}}
<div class="hb-sheet" id="hbSheet" role="dialog" aria-modal="true" aria-label="Filter bookings">
    <div class="hb-sheet-handle-wrap" aria-hidden="true"><div class="hb-sheet-handle"></div></div>
    <div class="hb-sheet-hdr">
        <h2>Filter Bookings</h2>
        <button type="button" class="hb-sheet-close" id="hbCloseSheet" aria-label="Close filters">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <form method="GET" action="{{ route('hall.bookings.index') }}" id="hbFilterForm">
        @if(request('search'))
            <input type="hidden" name="search" value="{{ request('search') }}">
        @endif
        <div class="hb-sheet-body">
            <div>
                <label class="hb-sf-label">Hall / Venue</label>
                <select name="hall_id" class="hb-sf-select">
                    <option value="">All halls</option>
                    @foreach($halls as $h)
                        <option value="{{ $h->id }}" {{ request('hall_id') == $h->id ? 'selected' : '' }}>{{ $h->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="hb-sf-label">Booking Type</label>
                <select name="booking_type" class="hb-sf-select">
                    <option value="">All types</option>
                    <option value="hall_only" {{ request('booking_type') === 'hall_only' ? 'selected' : '' }}>Hall Only</option>
                    <option value="hall_food" {{ request('booking_type') === 'hall_food' ? 'selected' : '' }}>Hall + Food</option>
                    <option value="food_only" {{ request('booking_type') === 'food_only' ? 'selected' : '' }}>Food Only</option>
                </select>
            </div>
            <div>
                <label class="hb-sf-label">Event Status</label>
                <select name="status" class="hb-sf-select">
                    <option value="">All statuses</option>
                    <option value="confirmed"  {{ request('status') === 'confirmed'  ? 'selected' : '' }}>Confirmed</option>
                    <option value="completed"  {{ request('status') === 'completed'  ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled"  {{ request('status') === 'cancelled'  ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <label class="hb-sf-label">Payment Status</label>
                <select name="payment_status" class="hb-sf-select">
                    <option value="">All payments</option>
                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid"    {{ request('payment_status') === 'paid'    ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div>
                <label class="hb-sf-label">Date Range</label>
                <div class="hb-sf-row">
                    <input type="date" name="date_from" class="hb-sf-input" value="{{ request('date_from') }}" placeholder="From">
                    <input type="date" name="date_to"   class="hb-sf-input" value="{{ request('date_to') }}"   placeholder="To">
                </div>
            </div>
        </div>
        <div class="hb-sheet-foot">
            <a href="{{ route('hall.bookings.index', request('search') ? ['search' => request('search')] : []) }}"
               class="hb-btn-ghost">Reset</a>
            <button type="submit" class="hb-btn-primary">Apply Filters</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    /* ── View toggle ──────────────────────────────────────────────── */
    var grid = document.getElementById('hbGrid');
    var vtBtns = document.querySelectorAll('.hb-vt-btn');
    var storedView = localStorage.getItem('hb_view') || 'card';

    function setView(mode) {
        storedView = mode;
        localStorage.setItem('hb_view', mode);
        grid.classList.toggle('--list', mode === 'list');
        vtBtns.forEach(function(btn) {
            var active = btn.dataset.view === mode;
            btn.classList.toggle('--active', active);
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }
    setView(storedView);
    vtBtns.forEach(function(btn) {
        btn.addEventListener('click', function() { setView(btn.dataset.view); });
    });

    /* ── Bottom sheet ─────────────────────────────────────────────── */
    var sheet    = document.getElementById('hbSheet');
    var backdrop = document.getElementById('hbBackdrop');
    var openBtn  = document.getElementById('hbOpenSheet');
    var closeBtn = document.getElementById('hbCloseSheet');

    function openSheet() {
        sheet.classList.add('--open');
        backdrop.classList.add('--open');
        openBtn && openBtn.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }
    function closeSheet() {
        sheet.classList.remove('--open');
        backdrop.classList.remove('--open');
        openBtn && openBtn.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    openBtn  && openBtn.addEventListener('click', openSheet);
    closeBtn && closeBtn.addEventListener('click', closeSheet);
    backdrop && backdrop.addEventListener('click', closeSheet);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sheet.classList.contains('--open')) closeSheet();
    });

    /* Touch-swipe down to close sheet */
    var touchStartY = 0;
    sheet.addEventListener('touchstart', function(e) { touchStartY = e.touches[0].clientY; }, { passive: true });
    sheet.addEventListener('touchend', function(e) {
        if (e.changedTouches[0].clientY - touchStartY > 80 && sheet.scrollTop === 0) closeSheet();
    }, { passive: true });

    /* ── Infinite scroll ──────────────────────────────────────────── */
    var sentinel  = document.getElementById('hbSentinel');
    var loader    = document.getElementById('hbLoader');
    var nextPage  = {{ $scrollPage }};
    var hasMore   = {{ $scrollMore }};
    var loading   = false;

    function buildUrl(page) {
        var params = new URLSearchParams(window.location.search);
        params.set('page', page);
        return window.location.pathname + '?' + params.toString();
    }

    function fetchMore() {
        if (loading || !hasMore) return;
        loading = true;
        loader.classList.add('--show');

        fetch(buildUrl(nextPage), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            loading  = false;
            hasMore  = data.hasMore;
            nextPage = data.nextPage;
            loader.classList.remove('--show');

            var tmp = document.createElement('div');
            tmp.innerHTML = data.html;
            while (tmp.firstChild) {
                grid.insertBefore(tmp.firstChild, sentinel);
            }
            if (!hasMore && sentinel) sentinel.remove();
        })
        .catch(function() { loading = false; loader.classList.remove('--show'); });
    }

    if (sentinel && hasMore) {
        var obs = new IntersectionObserver(function(entries) {
            if (entries[0].isIntersecting) fetchMore();
        }, { rootMargin: '200px' });
        obs.observe(sentinel);
    }
})();
</script>
@endpush
</x-admin-layout>
