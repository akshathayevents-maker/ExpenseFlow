<x-admin-layout title="Hall Bookings">
@push('styles')
<style>
/* ── Hall Bookings — hb-* ─────────────────────────────────────────────
   Consumes the shared --ef-* tokens (resources/css/app.css) and mirrors
   the component language of /admin/wallets (.ef-wlt-*): filter bar,
   chips, cards, footer buttons, badges. Hero + KPI strip use the shared
   x-ds.hero / x-ds.kpi-card components — no page-local CSS for those.
   Spacing scale: 4 / 8 / 12 / 16 / 20 / 24px. */

.hb-shell { padding-bottom: 24px; }

/* Keyboard focus — one consistent ring for every interactive hb element */
.hb-chip:focus-visible, .hb-btn:focus-visible, .hb-act:focus-visible,
.hb-search .ef-input:focus-visible, .hb-search-btn:focus-visible,
.hb-vt-btn:focus-visible, .hb-mobile-new a:focus-visible,
.hb-dropdown .dropdown-item:focus-visible {
    outline: 2px solid var(--ef-emerald);
    outline-offset: 2px;
}

/* ── Flash ───────────────────────────────────────────────────────── */
.hb-flash {
    display: flex; align-items: center; gap: 8px;
    border-radius: var(--ef-radius-sm); font-size: .84rem;
    margin-bottom: 12px; padding: 12px 16px;
}
.hb-flash.--success { background: rgba(15,123,95,.07); border: 1px solid rgba(15,123,95,.2); color: var(--ef-emerald-dk); }
.hb-flash.--error   { background: rgba(200,75,68,.07); border: 1px solid rgba(200,75,68,.22); color: var(--ef-danger); }

/* ── Toolbar (same surface as .ef-wlt-filter-bar) ─────────────────── */
.hb-toolbar {
    background: var(--ef-surface); border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius); box-shadow: var(--ef-shadow);
    padding: 16px; margin-bottom: 16px;
}
.hb-topbar { display: flex; gap: 8px; align-items: center; margin: 0 0 12px; }
.hb-search { position: relative; flex: 1 1 auto; min-width: 0; }
.hb-search .ef-input { min-height: 44px; padding: 0 12px 0 40px; font-size: .9rem; }
.hb-search-btn {
    position: absolute; left: 0; top: 0; bottom: 0; width: 40px;
    display: flex; align-items: center; justify-content: center;
    background: none; border: 0; border-radius: 12px 0 0 12px;
    color: var(--ef-muted); font-size: .9rem; cursor: pointer;
    transition: color .18s var(--ef-ease);
}
.hb-search-btn:hover { color: var(--ef-ink); }
.hb-topbar-right { display: flex; gap: 8px; align-items: center; flex: 0 0 auto; }

/* Buttons — .ef-btn geometry, 44px touch height */
.hb-btn {
    min-height: 44px; padding: 0 16px; border-radius: var(--ef-radius-sm);
    background: var(--ef-surface); cursor: pointer; position: relative;
}
.hb-btn.--active { border-color: var(--ef-emerald); color: var(--ef-emerald-dk); background: rgba(15,123,95,.06); }
.hb-btn-count {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 18px; height: 18px; padding: 0 5px; border-radius: 999px;
    background: var(--ef-emerald); color: #fff; font-size: .68rem; font-weight: 750; line-height: 1;
}
.hb-btn-new {
    background: var(--ef-emerald); border-color: var(--ef-emerald); color: #fff;
    white-space: nowrap;
}
.hb-btn-new:hover { background: var(--ef-emerald-hi); border-color: var(--ef-emerald-hi); color: #fff; }

.hb-view-toggle {
    display: inline-flex; padding: 3px; gap: 2px;
    background: var(--ef-surface-2); border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius-sm);
}
.hb-vt-btn {
    min-width: 44px; height: 36px; padding: 0 12px; border: 0; border-radius: 8px;
    background: transparent; color: var(--ef-muted); font-size: .95rem;
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    cursor: pointer; transition: background .18s var(--ef-ease), color .18s var(--ef-ease);
}
.hb-vt-btn:hover { color: var(--ef-ink); }
.hb-vt-btn.--active { background: var(--ef-emerald); color: #fff; }
.hb-vt-label { font-size: .78rem; font-weight: 650; display: none; }

/* ── Quick-filter chips (same as .ef-wlt-chip) ───────────────────── */
.hb-chips {
    display: flex; gap: 8px; align-items: center; flex-wrap: nowrap;
    overflow-x: auto; scrollbar-width: none; -webkit-overflow-scrolling: touch;
    padding: 2px 2px 4px; margin: 0 -2px;
}
.hb-chips::-webkit-scrollbar { display: none; }
.hb-chip {
    flex-shrink: 0; display: inline-flex; align-items: center; gap: 4px;
    min-height: 36px; padding: 0 14px; border-radius: 20px;
    font-size: .8rem; font-weight: 550; white-space: nowrap; text-decoration: none;
    border: 1px solid var(--ef-border); color: var(--ef-muted); background: var(--ef-surface-2);
    transition: background .18s var(--ef-ease), border-color .18s var(--ef-ease), color .18s var(--ef-ease);
}
.hb-chip:hover { border-color: var(--ef-border-strong); color: var(--ef-ink); background: var(--ef-surface); text-decoration: none; }
.hb-chip.--active { background: var(--ef-emerald); border-color: var(--ef-emerald); color: #fff; font-weight: 700; }
.hb-chip.--active:hover { background: var(--ef-emerald-hi); border-color: var(--ef-emerald-hi); color: #fff; }
.hb-chip.--warn { background: rgba(216,154,61,.10); border-color: rgba(216,154,61,.32); color: #7D5218; }
.hb-chip.--warn.--active { background: var(--ef-warning); border-color: var(--ef-warning); color: #fff; }

/* ── Result bar (same as .ef-wlt-section-head) ───────────────────── */
.hb-result-bar { display: flex; align-items: center; gap: 12px; margin: 0 0 12px; }
.hb-result-label {
    font-size: .72rem; font-weight: 700; letter-spacing: .06em;
    text-transform: uppercase; color: var(--ef-muted); white-space: nowrap;
}
.hb-result-label strong { color: var(--ef-ink); font-weight: 700; }
.hb-result-label a { color: var(--ef-emerald); text-decoration: none; letter-spacing: 0; text-transform: none; font-weight: 650; }
.hb-result-line { flex: 1; height: 1px; background: var(--ef-border); }

/* ── Card grid ───────────────────────────────────────────────────── */
.hb-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 320px), 1fr));
    gap: 16px;
    align-items: start;
}

/* ── Card (same surface as .ef-wlt-card) ─────────────────────────── */
/* No overflow:hidden / transform / opacity here: any of them clips or
   re-parents the More menu (see dropdown note in the script). */
.hb-card {
    position: relative;
    background: var(--ef-surface); border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius); box-shadow: var(--ef-shadow);
    display: flex; flex-direction: column; min-width: 0;
    transition: box-shadow .2s var(--ef-ease), border-color .2s var(--ef-ease);
}
.hb-card:hover { box-shadow: var(--ef-shadow-hover); border-color: var(--ef-border-strong); }
.hb-card::before {
    content: ''; position: absolute; top: -1px; left: -1px; right: -1px; height: 3px;
    border-radius: var(--ef-radius) var(--ef-radius) 0 0; background: var(--ef-border-strong);
}
.hb-card.--confirmed::before { background: var(--ef-emerald); }
.hb-card.--completed::before { background: var(--ef-bluegray); }
.hb-card.--cancelled::before { background: var(--ef-danger); }
.hb-card.--pending::before   { background: var(--ef-warning); }
.hb-card.--cancelled .hb-name, .hb-card.--cancelled .hb-amt { color: var(--ef-muted); }
.hb-card.--cancelled .hb-amt { text-decoration: line-through; }

.hb-card-body { flex: 1; padding: 16px 16px 12px; display: flex; flex-direction: column; gap: 12px; min-width: 0; }
.hb-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
.hb-title { min-width: 0; flex: 1; }
.hb-name {
    font-size: .95rem; font-weight: 700; color: var(--ef-ink); line-height: 1.25;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.hb-evt { font-size: .75rem; color: var(--ef-muted); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.hb-amt {
    font-size: 1.25rem; font-weight: 800; color: var(--ef-ink); letter-spacing: -.03em;
    line-height: 1.1; white-space: nowrap; flex-shrink: 0; font-variant-numeric: tabular-nums;
}

/* Badges (same geometry as .ef-wlt-health-chip, sentence case for legibility) */
.hb-badges { display: flex; flex-wrap: wrap; gap: 8px; }
.hb-badge {
    display: inline-flex; align-items: center; gap: 4px;
    min-height: 24px; padding: 0 8px; border-radius: 6px;
    font-size: .72rem; font-weight: 700; line-height: 1; white-space: nowrap;
    border: 1px solid transparent;
}
.hb-badge i { font-size: .72rem; }
.hb-badge.--confirmed, .hb-badge.--paid { background: rgba(15,123,95,.10); color: var(--ef-emerald-dk); border-color: rgba(15,123,95,.2); }
.hb-badge.--completed { background: rgba(47,111,237,.08); color: #2350b8; border-color: rgba(47,111,237,.2); }
.hb-badge.--pending, .hb-badge.--partial, .hb-badge.--unpaid { background: rgba(216,154,61,.10); color: #7D5218; border-color: rgba(216,154,61,.28); }
.hb-badge.--cancelled { background: rgba(200,75,68,.08); color: var(--ef-danger); border-color: rgba(200,75,68,.22); }

/* Meta — 2-column grid so values align across cards */
.hb-meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px 12px; }
.hb-mi {
    display: flex; align-items: center; gap: 8px; min-width: 0;
    font-size: .78rem; color: var(--ef-ink-2);
}
.hb-mi i { font-size: .8rem; color: var(--ef-muted); flex-shrink: 0; }
.hb-mi span { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.hb-mi.--wide { grid-column: 1 / -1; }

/* Footer (same as .ef-wlt-card-foot) */
.hb-card-foot { padding: 12px 16px; border-top: 1px solid var(--ef-border); display: flex; gap: 8px; align-items: center; }
.hb-act {
    display: inline-flex; align-items: center; justify-content: center; gap: 4px;
    height: 40px; border-radius: var(--ef-radius-sm); font-size: .8rem; font-weight: 650;
    text-decoration: none; cursor: pointer; white-space: nowrap; flex-shrink: 0;
    border: 1px solid var(--ef-border); background: transparent; color: var(--ef-muted);
    transition: background .18s var(--ef-ease), border-color .18s var(--ef-ease), color .18s var(--ef-ease);
}
.hb-act:hover { border-color: var(--ef-border-strong); color: var(--ef-ink); background: var(--ef-surface-2); text-decoration: none; }
.hb-act:active { background: var(--ef-border); }
.hb-act.--view { flex: 1; min-width: 0; padding: 0 12px; background: var(--ef-emerald); border-color: var(--ef-emerald); color: #fff; }
.hb-act.--view:hover { background: var(--ef-emerald-hi); border-color: var(--ef-emerald-hi); color: #fff; }
.hb-act.--view:active { background: var(--ef-emerald-dk); }
.hb-act.--wa, .hb-act.--more { width: 40px; padding: 0; font-size: 1rem; }
.hb-act.--wa { color: #1c9c56; }
.hb-act.--wa:hover { color: #15803d; }
.hb-act.--more[aria-expanded="true"] { background: var(--ef-surface-2); border-color: var(--ef-border-strong); color: var(--ef-ink); }
.hb-card-foot .dropdown { flex-shrink: 0; }

/* ── List view ───────────────────────────────────────────────────── */
.hb-list-col-head { display: none; }
.hb-grid.--list { grid-template-columns: 1fr; gap: 8px; }
.hb-grid.--list .hb-card-body { gap: 8px; padding: 12px 16px 8px; }
.hb-grid.--list .hb-card-foot { padding: 8px 16px 12px; }

@media (min-width: 1025px) {
    /* One grid per row; body/top dissolve so every cell aligns with the column header */
    .hb-list-col-head.--show,
    .hb-grid.--list .hb-card {
        display: grid;
        grid-template-columns: 200px 150px minmax(0, 1fr) 100px 240px;
        column-gap: 16px; align-items: center;
    }
    .hb-list-col-head.--show {
        padding: 0 17px; margin-bottom: 8px;
        font-size: .68rem; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; color: var(--ef-muted);
    }
    .hb-list-col-head .c4 { text-align: right; }
    .hb-grid.--list .hb-card { padding: 12px 16px; }
    .hb-grid.--list .hb-card::before { right: auto; bottom: -1px; width: 3px; height: auto; border-radius: var(--ef-radius) 0 0 var(--ef-radius); }
    .hb-grid.--list .hb-card-body, .hb-grid.--list .hb-card-top { display: contents; }
    .hb-grid.--list .hb-title  { grid-column: 1; grid-row: 1; }
    .hb-grid.--list .hb-badges { grid-column: 2; grid-row: 1; }
    .hb-grid.--list .hb-meta   { grid-column: 3; grid-row: 1; display: flex; flex-wrap: wrap; gap: 4px 16px; }
    .hb-grid.--list .hb-mi.--wide { grid-column: auto; }
    .hb-grid.--list .hb-amt    { grid-column: 4; grid-row: 1; text-align: right; font-size: 1.05rem; }
    .hb-grid.--list .hb-card-foot { grid-column: 5; grid-row: 1; padding: 0; border-top: 0; }
}

/* ── More menu ───────────────────────────────────────────────────── */
.hb-dropdown {
    min-width: 200px; max-height: min(70vh, 360px); overflow-y: auto;
    border: 1px solid var(--ef-border); border-radius: var(--ef-radius-sm);
    background: var(--ef-surface); box-shadow: var(--ef-shadow-hover); padding: 4px;
}
.hb-dropdown .dropdown-item {
    display: flex; align-items: center; gap: 8px; min-height: 40px;
    font-size: .84rem; padding: 0 12px; color: var(--ef-ink-2); border-radius: 8px;
}
.hb-dropdown .dropdown-item:hover, .hb-dropdown .dropdown-item:focus { background: var(--ef-surface-2); color: var(--ef-ink); }
.hb-dropdown .dropdown-item i { color: var(--ef-muted); width: 16px; text-align: center; }
.hb-dropdown .dropdown-divider { border-color: var(--ef-border); margin: 4px 0; }

/* ── Sentinel / loader ───────────────────────────────────────────── */
.hb-sentinel { height: 1px; grid-column: 1 / -1; }
.hb-loader { display: none; text-align: center; padding: 24px; grid-column: 1 / -1; color: var(--ef-muted); font-size: .83rem; }
.hb-loader.--show { display: block; }
.hb-loader-spin {
    display: inline-block; width: 16px; height: 16px;
    border: 2px solid var(--ef-border); border-top-color: var(--ef-emerald);
    border-radius: 50%; animation: hb-spin .7s linear infinite; vertical-align: middle; margin-right: 8px;
}
@keyframes hb-spin { to { transform: rotate(360deg); } }

/* ── Empty (same as .ef-wlt-empty) ───────────────────────────────── */
.hb-empty {
    grid-column: 1 / -1; background: var(--ef-surface); border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius); box-shadow: var(--ef-shadow);
    padding: 48px 24px; text-align: center;
}
.hb-empty-icon {
    width: 64px; height: 64px; border-radius: 16px; margin: 0 auto 16px;
    background: var(--ef-surface-2); border: 1px solid var(--ef-border);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem; color: var(--ef-border-strong);
}
.hb-empty-title { font-size: 1.05rem; font-weight: 700; color: var(--ef-ink); margin-bottom: 4px; }
.hb-empty-text { font-size: .85rem; color: var(--ef-muted); }
.hb-empty .hb-btn { margin-top: 16px; }

/* ── Mobile sticky "New Booking" bar ─────────────────────────────── */
.hb-mobile-new {
    display: none; position: fixed; left: 0; right: 0; bottom: 0; z-index: 200;
    padding: 12px 16px calc(12px + env(safe-area-inset-bottom, 0px));
    background: var(--ef-surface); border-top: 1px solid var(--ef-border);
    box-shadow: 0 -8px 24px rgba(24,22,18,.06);
}
.hb-mobile-new a { width: 100%; }

/* ── Filter sheet ────────────────────────────────────────────────── */
.hb-backdrop {
    position: fixed; inset: 0; background: rgba(20,20,18,.4);
    z-index: 1049; display: none; opacity: 0; transition: opacity .25s;
}
.hb-backdrop.--open { display: block; opacity: 1; }
.hb-sheet {
    position: fixed; bottom: 0; left: 0; right: 0;
    background: var(--ef-surface); border-radius: 20px 20px 0 0;
    box-shadow: 0 -8px 40px rgba(24,22,18,.18);
    transform: translateY(100%); transition: transform .3s var(--ef-ease);
    z-index: 1050; max-height: 88vh; overflow-y: auto; overscroll-behavior: contain;
}
.hb-sheet.--open { transform: translateY(0); }
.hb-sheet-handle-wrap { padding: 8px 0 4px; display: flex; justify-content: center; }
.hb-sheet-handle { width: 36px; height: 4px; background: var(--ef-border-strong); border-radius: 2px; }
.hb-sheet-hdr { display: flex; align-items: center; justify-content: space-between; padding: 4px 20px 12px; border-bottom: 1px solid var(--ef-border); }
.hb-sheet-hdr h2 { font-size: 1rem; font-weight: 800; color: var(--ef-ink); margin: 0; }
.hb-sheet-close {
    width: 40px; height: 40px; padding: 0; border: 1px solid var(--ef-border); border-radius: var(--ef-radius-sm);
    background: transparent; color: var(--ef-muted); cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .18s var(--ef-ease), color .18s var(--ef-ease);
}
.hb-sheet-close:hover { background: var(--ef-surface-2); color: var(--ef-ink); }
.hb-sheet-body { padding: 16px 20px; display: flex; flex-direction: column; gap: 16px; }
.hb-sf-label { display: block; margin-bottom: 4px; font-size: .68rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--ef-muted); }
.hb-sf-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.hb-sheet-foot { display: flex; gap: 8px; padding: 12px 20px calc(16px + env(safe-area-inset-bottom, 0px)); border-top: 1px solid var(--ef-border); }
.hb-sheet-foot .hb-btn { flex: 1; }
.hb-sheet-foot .hb-btn-new { flex: 2; }

/* ── Responsive ──────────────────────────────────────────────────── */
@media (min-width: 641px) {
    .hb-vt-label { display: inline; }
}
@media (max-width: 640px) {
    .hb-toolbar { padding: 12px; }
    .hb-topbar { flex-wrap: wrap; margin-bottom: 8px; }
    .hb-search { flex: 1 1 100%; }
    .hb-topbar-right { flex: 1 1 100%; }
    .hb-topbar-right .hb-view-toggle { flex: 1; }
    .hb-topbar-right .hb-vt-btn { flex: 1; }
    .hb-topbar-right .hb-btn { flex: 1; }
    .hb-topbar-right .hb-btn-new { display: none; }
    .hb-mobile-new { display: block; }
    .hb-shell { padding-bottom: calc(80px + env(safe-area-inset-bottom, 0px)); }
    .hb-grid { gap: 12px; }
}
@media (max-width: 380px) {
    .hb-card-body { padding: 12px 12px 8px; }
    .hb-card-foot { padding: 8px 12px 12px; }
    .hb-amt { font-size: 1.1rem; }
}
</style>
@endpush

@php
$today    = now()->toDateString();
$tomorrow = now()->addDay()->toDateString();

$activeFilters = request()->hasAny(['hall_id','status','payment_status','date_from','date_to','booking_type']);
$activeFilterCount = collect(request()->only(['hall_id','status','payment_status','date_from','date_to','booking_type']))->filter()->count();

$monthStart = now()->startOfMonth()->toDateString();
$monthEnd   = now()->endOfMonth()->toDateString();

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

$contextLabel = $chipToday ? 'Today'
    : ($chipTomorrow ? 'Tomorrow'
    : ($chipPending ? 'Pending Payment'
    : ($chipFood ? 'Food Only'
    : ($chipUpcoming ? 'Upcoming'
    : null))));
@endphp

<div class="hb-shell">

    {{-- Hero — shared x-ds.hero --}}
    <x-ds.hero
        eyebrow="Hall Operations"
        title="Hall Bookings"
        :meta="[
            ['icon' => 'bi-calendar3', 'text' => now()->format('l, j F Y')],
            ['icon' => 'bi-journal-check', 'text' => number_format($bookings->total()) . ' booking' . ($bookings->total() !== 1 ? 's' : '')],
        ]"
    />

    {{-- Flash --}}
    @if(session('success'))
        <div class="hb-flash --success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
    @endif
    @if(session('error') || $errors->any())
        <div class="hb-flash --error"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') ?? $errors->first() }}</div>
    @endif

    {{-- KPI strip — shared x-ds.kpi-card; each meaningful one links into the filtered list --}}
    <div class="ef-ds-kpi-wrap">
        <div class="ef-ds-kpi-grid" style="--kpi-cols:4">
            <x-ds.kpi-card
                icon="bi-calendar-event"
                label="Today's Events"
                :value="number_format($stats['today'])"
                note="scheduled today"
                accent="emerald"
                value-color="c-emerald"
                href="{{ route('hall.bookings.index', ['date_from' => $today, 'date_to' => $today]) }}"
            />
            <x-ds.kpi-card
                icon="bi-people"
                label="Upcoming Guests"
                :value="number_format($stats['upcoming_guests'])"
                note="from today onward"
                accent="bluegray"
                href="{{ route('hall.bookings.index', ['date_from' => $today]) }}"
            />
            @if($isEmployee)
                <x-ds.kpi-card icon="bi-hourglass-split" label="Pending Collection" value="—" accent="muted" value-color="c-muted" />
                <x-ds.kpi-card icon="bi-graph-up" label="Monthly Revenue" value="—" accent="muted" value-color="c-muted" />
            @else
                <x-ds.kpi-card
                    icon="bi-hourglass-split"
                    label="Pending Collection"
                    :value="$fmtKpi($stats['pending_collect'])"
                    note="awaiting payment"
                    :accent="$stats['pending_collect'] > 0 ? 'amber' : 'muted'"
                    :value-color="$stats['pending_collect'] > 0 ? 'c-amber' : ''"
                    href="{{ route('hall.bookings.index', ['payment_status' => 'pending']) }}"
                />
                <x-ds.kpi-card
                    icon="bi-graph-up"
                    label="Monthly Revenue"
                    :value="$fmtKpi($stats['month_revenue'])"
                    note="this month"
                    accent="gold"
                    href="{{ route('hall.bookings.index', ['date_from' => $monthStart, 'date_to' => $monthEnd]) }}"
                />
            @endif
        </div>
    </div>

    {{-- Search + view + filters + quick chips --}}
    <div class="hb-toolbar">
        <form class="hb-topbar" method="GET" action="{{ route('hall.bookings.index') }}" id="hbSearchForm">
            @foreach(request()->except('search','page') as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach
            <div class="hb-search">
                <button type="submit" class="hb-search-btn" aria-label="Search bookings">
                    <i class="bi bi-search"></i>
                </button>
                <input type="search" name="search" class="ef-input"
                       placeholder="Search name or mobile…"
                       aria-label="Search name or mobile"
                       value="{{ request('search') }}"
                       autocomplete="off">
            </div>
            <div class="hb-topbar-right">
                <div class="hb-view-toggle" role="group" aria-label="View mode">
                    <button type="button" class="hb-vt-btn --active" data-view="card" aria-label="Grid view" aria-pressed="true">
                        <i class="bi bi-grid-3x3-gap"></i><span class="hb-vt-label">Grid</span>
                    </button>
                    <button type="button" class="hb-vt-btn" data-view="list" aria-label="List view" aria-pressed="false">
                        <i class="bi bi-list-ul"></i><span class="hb-vt-label">List</span>
                    </button>
                </div>
                <button type="button" class="ef-btn hb-btn {{ $activeFilters ? '--active' : '' }}" id="hbOpenSheet" aria-expanded="false" aria-controls="hbSheet">
                    <i class="bi bi-sliders"></i>
                    <span>Filters</span>
                    @if($activeFilterCount)<span class="hb-btn-count">{{ $activeFilterCount }}</span>@endif
                </button>
                <a href="{{ route('hall.bookings.create') }}" class="ef-btn hb-btn hb-btn-new">
                    <i class="bi bi-plus-lg"></i> New Booking
                </a>
            </div>
        </form>

        <div class="hb-chips" role="list" aria-label="Quick filters">
            <a role="listitem" href="{{ route('hall.bookings.index') }}"
               class="hb-chip {{ $chipAll ? '--active' : '' }}" @if($chipAll) aria-current="true" @endif>All</a>
            <a role="listitem" href="{{ route('hall.bookings.index', array_merge(request()->except(['date_from','date_to','page']), ['date_from' => $today, 'date_to' => $today])) }}"
               class="hb-chip {{ $chipToday ? '--active' : '' }}" @if($chipToday) aria-current="true" @endif>Today</a>
            <a role="listitem" href="{{ route('hall.bookings.index', array_merge(request()->except(['date_from','date_to','page']), ['date_from' => $tomorrow, 'date_to' => $tomorrow])) }}"
               class="hb-chip {{ $chipTomorrow ? '--active' : '' }}" @if($chipTomorrow) aria-current="true" @endif>Tomorrow</a>
            <a role="listitem" href="{{ route('hall.bookings.index', ['payment_status' => 'pending']) }}"
               class="hb-chip --warn {{ $chipPending ? '--active' : '' }}" @if($chipPending) aria-current="true" @endif>
                <i class="bi bi-hourglass-split" style="font-size:.7rem"></i> Pending Pay</a>
            <a role="listitem" href="{{ route('hall.bookings.index', ['booking_type' => 'food_only']) }}"
               class="hb-chip {{ $chipFood ? '--active' : '' }}" @if($chipFood) aria-current="true" @endif>Food Only</a>
            <a role="listitem" href="{{ route('hall.bookings.index', array_merge(request()->except(['date_from','date_to','page']), ['date_from' => $today])) }}"
               class="hb-chip {{ $chipUpcoming ? '--active' : '' }}" @if($chipUpcoming) aria-current="true" @endif>Upcoming</a>
        </div>
    </div>

    {{-- Result bar --}}
    <div class="hb-result-bar">
        <div class="hb-result-label">
            {{ number_format($bookings->total()) }} booking{{ $bookings->total() !== 1 ? 's' : '' }}
            @if($contextLabel)
                &nbsp;·&nbsp;<strong>{{ $contextLabel }}</strong>
            @endif
            @if($activeFilters || request('search'))
                &nbsp;·&nbsp;<a href="{{ route('hall.bookings.index') }}">Clear filters</a>
            @endif
        </div>
        <div class="hb-result-line"></div>
    </div>

    {{-- Desktop list-view column header (shown only in list mode via JS) --}}
    <div class="hb-list-col-head" id="hbListColHead">
        <span class="c1">Customer</span>
        <span class="c2">Status</span>
        <span class="c3">Details</span>
        <span class="c4">Amount</span>
        <span class="c5">Actions</span>
    </div>

    {{-- Card grid --}}
    <div class="hb-grid" id="hbGrid">
        @include('hall.bookings._booking_cards', ['bookings' => $bookings, 'today' => $today, 'isEmployee' => $isEmployee])
        @if($bookings->isEmpty())
            <div class="hb-empty">
                <div class="hb-empty-icon"><i class="bi bi-calendar-x"></i></div>
                <div class="hb-empty-title">No bookings found</div>
                <div class="hb-empty-text">Try changing your filters or search criteria.</div>
                <a href="{{ route('hall.bookings.index') }}" class="ef-btn hb-btn">Clear Filters</a>
            </div>
        @endif
        <div class="hb-sentinel" id="hbSentinel"></div>
        <div class="hb-loader" id="hbLoader">
            <span class="hb-loader-spin"></span> Loading more…
        </div>
    </div>

</div>

{{-- Sticky "New Booking" bar (mobile) — content gets bottom padding so it never covers a card --}}
<div class="hb-mobile-new">
    <a href="{{ route('hall.bookings.create') }}" class="ef-btn hb-btn hb-btn-new"><i class="bi bi-plus-lg"></i> New Booking</a>
</div>

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
                <label class="hb-sf-label" for="hbFHall">Hall / Venue</label>
                <select id="hbFHall" name="hall_id" class="ef-select">
                    <option value="">All halls</option>
                    @foreach($halls as $h)
                        <option value="{{ $h->id }}" {{ request('hall_id') == $h->id ? 'selected' : '' }}>{{ $h->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="hb-sf-label" for="hbFType">Booking Type</label>
                <select id="hbFType" name="booking_type" class="ef-select">
                    <option value="">All types</option>
                    <option value="hall_only" {{ request('booking_type') === 'hall_only' ? 'selected' : '' }}>Hall Only</option>
                    <option value="hall_food" {{ request('booking_type') === 'hall_food' ? 'selected' : '' }}>Hall + Food</option>
                    <option value="food_only" {{ request('booking_type') === 'food_only' ? 'selected' : '' }}>Food Only</option>
                </select>
            </div>
            <div>
                <label class="hb-sf-label" for="hbFStatus">Event Status</label>
                <select id="hbFStatus" name="status" class="ef-select">
                    <option value="">All statuses</option>
                    <option value="confirmed"  {{ request('status') === 'confirmed'  ? 'selected' : '' }}>Confirmed</option>
                    <option value="completed"  {{ request('status') === 'completed'  ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled"  {{ request('status') === 'cancelled'  ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <label class="hb-sf-label" for="hbFPay">Payment Status</label>
                <select id="hbFPay" name="payment_status" class="ef-select">
                    <option value="">All payments</option>
                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid"    {{ request('payment_status') === 'paid'    ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div>
                <span class="hb-sf-label">Date Range</span>
                <div class="hb-sf-row">
                    <input type="date" name="date_from" class="ef-input" aria-label="From date" value="{{ request('date_from') }}">
                    <input type="date" name="date_to"   class="ef-input" aria-label="To date"   value="{{ request('date_to') }}">
                </div>
            </div>
        </div>
        <div class="hb-sheet-foot">
            <a href="{{ route('hall.bookings.index', request('search') ? ['search' => request('search')] : []) }}"
               class="ef-btn hb-btn">Reset</a>
            <button type="submit" class="ef-btn hb-btn hb-btn-new">Apply Filters</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    /* ── View toggle ──────────────────────────────────────────────── */
    var grid = document.getElementById('hbGrid');
    var listColHead = document.getElementById('hbListColHead');
    var vtBtns = document.querySelectorAll('.hb-vt-btn');
    var storedView = localStorage.getItem('hb_view') || 'card';

    function setView(mode) {
        storedView = mode;
        localStorage.setItem('hb_view', mode);
        grid.classList.toggle('--list', mode === 'list');
        if (listColHead) listColHead.classList.toggle('--show', mode === 'list');
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

    /* ── More menu: Popper positioning ────────────────────────────
       Root cause of the clipped menu: Bootstrap's default absolute
       strategy renders the menu inside the card, and Popper's default
       boundary ("clippingParents") makes flip/preventOverflow measure
       against the card / scroll container instead of the screen. Use
       position:fixed + the viewport as boundary, and reserve room for
       the topbar and the sticky mobile "New Booking" bar so the menu
       flips/shifts instead of opening under them. */
    function menuPadding() {
        var tb  = document.getElementById('topbar');
        var bar = document.querySelector('.hb-mobile-new');
        var barShown = bar && getComputedStyle(bar).display !== 'none';
        return {
            top:    (tb ? tb.getBoundingClientRect().bottom : 0) + 8,
            bottom: (barShown ? bar.offsetHeight : 0) + 8,
            left: 8, right: 8
        };
    }
    /* window capture: Bootstrap's own delegated handler is a document capture
       listener, so this must run before it to supply the config. */
    window.addEventListener('click', function(e) {
        var btn = e.target.closest('.hb-act.--more');
        if (!btn || !window.bootstrap || bootstrap.Dropdown.getInstance(btn)) return;
        new bootstrap.Dropdown(btn, {
            popperConfig: function(cfg) {
                var opts = { boundary: document.documentElement, rootBoundary: 'viewport', padding: menuPadding() };
                return Object.assign({}, cfg, {
                    strategy: 'fixed',
                    modifiers: cfg.modifiers.concat([
                        { name: 'preventOverflow', options: Object.assign({ altAxis: true }, opts) },
                        { name: 'flip', options: Object.assign({ fallbackPlacements: ['top-end', 'bottom-start', 'top-start'] }, opts) }
                    ])
                });
            }
        });
    }, true);

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
