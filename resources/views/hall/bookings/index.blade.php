<x-admin-layout title="Hall Bookings">
@push('styles')
<style>
/* ── Hall Bookings Dashboard — hb-* ─────────────────────────────── */
:root {
    --hb-gold:       #a0763a;
    --hb-gold-hi:    #b8882a;
    --hb-gold-soft:  #d4b06a;
    --hb-ink:        #131110;
    --hb-sub:        #50473f;
    --hb-muted:      #8a827a;
    --hb-faint:      #bab3aa;
    --hb-border:     rgba(100,82,42,.12);
    --hb-border-s:   rgba(100,82,42,.24);
    --hb-surface:    #ffffff;
    --hb-cream:      #faf8f3;
    --hb-r:          14px;
    --hb-r-sm:       10px;
    --hb-shadow:     0 1px 3px rgba(18,14,8,.06), 0 3px 10px rgba(18,14,8,.04);
    --hb-ease:       cubic-bezier(.25,.46,.45,.94);

    /* Status */
    --hb-confirmed: #16a34a;
    --hb-pending:   #f59e0b;
    --hb-completed: #2563eb;
    --hb-cancelled: #dc2626;
}

*, *::before, *::after { box-sizing: border-box; }

.hb-shell {
    max-width: 1400px;
    margin: 0 auto;
    padding-bottom: 120px;
}

/* ── Flash ───────────────────────────────────────────────────────── */
.hb-flash {
    display: flex;
    align-items: center;
    gap: 9px;
    border-radius: var(--hb-r-sm);
    font-size: .83rem;
    margin-bottom: 14px;
    padding: 11px 14px;
}
.hb-flash.--success { background: rgba(22,163,74,.07); border: 1px solid rgba(22,163,74,.18); color: #0A5C40; }
.hb-flash.--error   { background: rgba(220,38,38,.07); border: 1px solid rgba(220,38,38,.18); color: #8B2020; }

/* ── Stats ───────────────────────────────────────────────────────── */
.hb-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 16px;
}
.hb-stat {
    background: var(--hb-surface);
    border: 1.5px solid var(--hb-border);
    border-radius: var(--hb-r);
    padding: 16px 18px;
    box-shadow: var(--hb-shadow);
    min-width: 0;
}
.hb-stat-icon {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    background: var(--hb-gold);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    margin-bottom: 10px;
    flex-shrink: 0;
}
.hb-stat-val {
    font-size: 1.7rem;
    font-weight: 900;
    color: var(--hb-ink);
    line-height: 1;
    letter-spacing: -.02em;
}
.hb-stat-label {
    font-size: .67rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--hb-faint);
    margin-top: 5px;
}

/* ── Top bar ─────────────────────────────────────────────────────── */
.hb-topbar {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 10px;
}
.hb-search-wrap {
    flex: 1;
    display: flex;
    gap: 0;
    background: var(--hb-surface);
    border: 1.5px solid var(--hb-border-s);
    border-radius: var(--hb-r-sm);
    overflow: hidden;
}
.hb-search-wrap:focus-within { border-color: var(--hb-gold); }
.hb-search-input {
    flex: 1;
    border: none;
    outline: none;
    padding: 11px 14px;
    font-size: .88rem;
    color: var(--hb-ink);
    background: transparent;
    min-width: 0;
    height: 46px;
}
.hb-search-btn {
    background: none;
    border: none;
    padding: 0 14px;
    color: var(--hb-muted);
    cursor: pointer;
    font-size: 1rem;
    height: 46px;
    display: flex;
    align-items: center;
}
.hb-search-btn:hover { color: var(--hb-gold); }
.hb-topbar-right {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-shrink: 0;
}
.hb-view-toggle {
    display: flex;
    background: var(--hb-cream);
    border: 1.5px solid var(--hb-border);
    border-radius: var(--hb-r-sm);
    overflow: hidden;
}
.hb-vt-btn {
    background: none;
    border: none;
    padding: 0 11px;
    height: 44px;
    min-width: 44px;
    cursor: pointer;
    color: var(--hb-muted);
    font-size: .95rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .14s, color .14s;
}
.hb-vt-btn.--active { background: var(--hb-gold); color: #fff; }
.hb-vt-btn:not(.--active):hover { background: var(--hb-border); color: var(--hb-ink); }
.hb-filter-btn {
    position: relative;
    background: var(--hb-surface);
    border: 1.5px solid var(--hb-border-s);
    border-radius: var(--hb-r-sm);
    height: 46px;
    min-width: 46px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: var(--hb-sub);
    font-size: 1rem;
    padding: 0 14px;
    gap: 6px;
    font-size: .88rem;
    font-weight: 600;
    transition: border-color .14s, color .14s;
}
.hb-filter-btn:hover { border-color: var(--hb-gold); color: var(--hb-gold); }
.hb-filter-btn.--active { border-color: var(--hb-gold); color: var(--hb-gold); background: rgba(160,118,58,.06); }
.hb-filter-dot {
    position: absolute;
    top: 7px;
    right: 7px;
    width: 7px;
    height: 7px;
    background: var(--hb-gold);
    border-radius: 50%;
    border: 1.5px solid #fff;
}
.hb-btn-new {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--hb-ink);
    color: #fff;
    border-radius: var(--hb-r-sm);
    padding: 0 18px;
    height: 46px;
    font-size: .88rem;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    transition: background .14s;
}
.hb-btn-new:hover { background: #2d2820; color: #fff; text-decoration: none; }

/* ── Quick chips ─────────────────────────────────────────────────── */
.hb-chips {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    scrollbar-width: none;
    padding-bottom: 2px;
    margin-bottom: 14px;
}
.hb-chips::-webkit-scrollbar { display: none; }
.hb-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 0 14px;
    height: 36px;
    border-radius: 100px;
    border: 1.5px solid var(--hb-border-s);
    background: var(--hb-surface);
    color: var(--hb-sub);
    font-size: .8rem;
    font-weight: 600;
    white-space: nowrap;
    text-decoration: none;
    cursor: pointer;
    transition: all .14s;
    flex-shrink: 0;
}
.hb-chip:hover { border-color: var(--hb-gold); color: var(--hb-gold); text-decoration: none; }
.hb-chip.--active {
    background: var(--hb-ink);
    border-color: var(--hb-ink);
    color: #fff;
}
.hb-chip.--active:hover { background: #2d2820; border-color: #2d2820; color: #fff; }

/* ── Result bar ──────────────────────────────────────────────────── */
.hb-result-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
    font-size: .78rem;
    color: var(--hb-muted);
    font-weight: 600;
}

/* ── Card grid ───────────────────────────────────────────────────── */
.hb-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 14px;
    align-items: start;
}

/* ── Card ────────────────────────────────────────────────────────── */
.hb-card {
    background: var(--hb-surface);
    border: 1.5px solid var(--hb-border);
    border-radius: var(--hb-r);
    box-shadow: var(--hb-shadow);
    display: flex;
    flex-direction: column;
    height: 218px;
    overflow: hidden;
    position: relative;
    transition: box-shadow .14s, border-color .14s;
}
.hb-card:hover {
    box-shadow: 0 4px 18px rgba(18,14,8,.10), 0 1px 4px rgba(18,14,8,.06);
    border-color: var(--hb-border-s);
}
/* Status stripe — top border */
.hb-card::before {
    content: '';
    display: block;
    height: 3px;
    width: 100%;
    flex-shrink: 0;
}
.hb-card.--confirmed::before { background: var(--hb-confirmed); }
.hb-card.--pending::before   { background: var(--hb-pending); }   /* status pending (not payment) — not used but harmless */
.hb-card.--completed::before { background: var(--hb-completed); }
.hb-card.--cancelled::before { background: var(--hb-cancelled); }

.hb-card-inner {
    display: flex;
    flex-direction: column;
    flex: 1;
    padding: 12px 14px 10px;
    min-height: 0;
    gap: 8px;
}

/* Row 1: Name + event type */
.hb-card-r1 {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    min-height: 0;
}
.hb-card-name {
    font-size: .92rem;
    font-weight: 800;
    color: var(--hb-ink);
    flex: 1;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    line-height: 1.3;
}
.hb-card-badges {
    display: flex;
    gap: 4px;
    align-items: center;
    flex-shrink: 0;
}
.hb-status-badge {
    font-size: .67rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .06em;
    padding: 2px 7px;
    border-radius: 6px;
    white-space: nowrap;
}
.hb-status-badge.--confirmed { background: rgba(22,163,74,.1);  color: var(--hb-confirmed); }
.hb-status-badge.--completed { background: rgba(37,99,235,.1);  color: var(--hb-completed); }
.hb-status-badge.--cancelled { background: rgba(220,38,38,.1);  color: var(--hb-cancelled); }

/* Row 2: Amount + payment chip */
.hb-card-r2 {
    display: flex;
    align-items: center;
    gap: 8px;
}
.hb-amount {
    font-size: 1.15rem;
    font-weight: 900;
    color: var(--hb-ink);
    letter-spacing: -.01em;
    flex: 1;
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.hb-pay-chip {
    font-size: .67rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .05em;
    padding: 3px 8px;
    border-radius: 100px;
    white-space: nowrap;
    flex-shrink: 0;
}
.hb-pay-chip.--pending { background: rgba(245,158,11,.12); color: #92400e; border: 1px solid rgba(245,158,11,.25); }
.hb-pay-chip.--partial { background: rgba(37,99,235,.10);  color: #1e3a8a; border: 1px solid rgba(37,99,235,.22); }
.hb-pay-chip.--paid    { background: rgba(22,163,74,.10);  color: #14532d; border: 1px solid rgba(22,163,74,.22); }

/* Row 3: Meta items */
.hb-card-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 6px 10px;
}
.hb-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: .76rem;
    color: var(--hb-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 140px;
}
.hb-meta-item i { font-size: .75rem; color: var(--hb-faint); flex-shrink: 0; }
.hb-evt-tag {
    display: inline-flex;
    align-items: center;
    font-size: .7rem;
    color: var(--hb-muted);
    background: var(--hb-cream);
    border: 1px solid var(--hb-border);
    border-radius: 6px;
    padding: 2px 6px;
    white-space: nowrap;
    max-width: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Row 4: Actions */
.hb-card-foot {
    display: flex;
    gap: 6px;
    align-items: center;
    margin-top: auto;
}
.hb-act {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    min-height: 34px;
    border-radius: 8px;
    font-size: .78rem;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    border: 1.5px solid transparent;
    transition: all .14s;
    padding: 0 12px;
}
.hb-act.--view {
    background: var(--hb-ink);
    color: #fff;
    flex: 1;
}
.hb-act.--view:hover { background: #2d2820; color: #fff; text-decoration: none; }
.hb-act.--ico {
    background: var(--hb-cream);
    border-color: var(--hb-border);
    color: var(--hb-sub);
    min-width: 34px;
    padding: 0 10px;
}
.hb-act.--ico:hover { border-color: var(--hb-gold-soft); color: var(--hb-gold); text-decoration: none; }
.hb-act.--wa { color: #fff; background: #25d366; border-color: #25d366; min-width: 34px; padding: 0 10px; }
.hb-act.--wa:hover { background: #1ebe5c; text-decoration: none; }
.hb-more-btn {
    min-height: 34px;
    min-width: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--hb-cream);
    border: 1.5px solid var(--hb-border);
    border-radius: 8px;
    color: var(--hb-sub);
    cursor: pointer;
    font-size: .88rem;
    transition: all .14s;
    padding: 0;
}
.hb-more-btn:hover { border-color: var(--hb-border-s); color: var(--hb-ink); }

/* ── List view ───────────────────────────────────────────────────── */
.hb-grid.--list {
    grid-template-columns: 1fr;
    gap: 6px;
}
.hb-grid.--list .hb-card {
    height: auto;
    flex-direction: row;
    align-items: stretch;
}
.hb-grid.--list .hb-card::before {
    width: 4px;
    height: auto;
    flex-shrink: 0;
    align-self: stretch;
}
.hb-grid.--list .hb-card-inner {
    flex-direction: row;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    flex-wrap: nowrap;
}
.hb-grid.--list .hb-card-r1 {
    flex: 0 0 200px;
    flex-direction: column;
    gap: 3px;
}
.hb-grid.--list .hb-card-badges { justify-content: flex-start; }
.hb-grid.--list .hb-card-r2 {
    flex: 0 0 140px;
    flex-direction: column;
    align-items: flex-start;
    gap: 3px;
}
.hb-grid.--list .hb-card-meta {
    flex: 1;
    flex-wrap: wrap;
    row-gap: 4px;
}
.hb-grid.--list .hb-card-foot {
    flex: 0 0 auto;
    margin-top: 0;
    gap: 4px;
}
.hb-grid.--list .hb-act.--view { flex: 0 0 auto; }

/* ── Sentinel / loader ───────────────────────────────────────────── */
.hb-sentinel { height: 1px; }
.hb-loader {
    display: none;
    text-align: center;
    padding: 20px;
    grid-column: 1 / -1;
    color: var(--hb-muted);
    font-size: .83rem;
}
.hb-loader.--show { display: block; }
.hb-loader-spin {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid var(--hb-border);
    border-top-color: var(--hb-gold);
    border-radius: 50%;
    animation: hb-spin .7s linear infinite;
    vertical-align: middle;
    margin-right: 6px;
}
@keyframes hb-spin { to { transform: rotate(360deg); } }

/* ── Empty state ─────────────────────────────────────────────────── */
.hb-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: var(--hb-faint);
}
.hb-empty-icon { font-size: 2.4rem; margin-bottom: 12px; opacity: .5; }
.hb-empty-text { font-size: .9rem; }

/* ── FAB (mobile) ────────────────────────────────────────────────── */
.hb-fab {
    position: fixed;
    bottom: 24px;
    right: 20px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: var(--hb-ink);
    color: #fff;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    box-shadow: 0 4px 20px rgba(18,14,8,.25);
    text-decoration: none;
    z-index: 200;
    transition: background .14s, transform .14s;
}
.hb-fab:hover { background: #2d2820; color: #fff; transform: scale(1.06); text-decoration: none; }

/* ── Bottom sheet ────────────────────────────────────────────────── */
.hb-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 1049;
    display: none;
    opacity: 0;
    transition: opacity .25s;
}
.hb-backdrop.--open { display: block; opacity: 1; }
.hb-sheet {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: var(--hb-surface);
    border-radius: 20px 20px 0 0;
    box-shadow: 0 -4px 30px rgba(18,14,8,.14);
    transform: translateY(100%);
    transition: transform .3s var(--hb-ease);
    z-index: 1050;
    max-height: 88vh;
    overflow-y: auto;
    overscroll-behavior: contain;
}
.hb-sheet.--open { transform: translateY(0); }
.hb-sheet-handle-wrap {
    padding: 10px 0 6px;
    display: flex;
    justify-content: center;
}
.hb-sheet-handle {
    width: 36px;
    height: 4px;
    background: var(--hb-border-s);
    border-radius: 2px;
}
.hb-sheet-hdr {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 4px 20px 14px;
    border-bottom: 1px solid var(--hb-border);
}
.hb-sheet-hdr h2 {
    font-size: 1rem;
    font-weight: 800;
    color: var(--hb-ink);
    margin: 0;
}
.hb-sheet-close {
    background: none;
    border: none;
    font-size: 1.1rem;
    color: var(--hb-muted);
    cursor: pointer;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    padding: 0;
}
.hb-sheet-close:hover { background: var(--hb-cream); color: var(--hb-ink); }
.hb-sheet-body {
    padding: 18px 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.hb-sf-label {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--hb-faint);
    margin-bottom: 6px;
    display: block;
}
.hb-sf-select,
.hb-sf-input {
    width: 100%;
    padding: 11px 13px;
    border: 1.5px solid var(--hb-border);
    border-radius: var(--hb-r-sm);
    font-size: .88rem;
    color: var(--hb-ink);
    background: var(--hb-surface);
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    height: 46px;
}
.hb-sf-select:focus, .hb-sf-input:focus { border-color: var(--hb-gold); }
.hb-sf-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.hb-sheet-foot {
    display: flex;
    gap: 10px;
    padding: 14px 20px 24px;
    border-top: 1px solid var(--hb-border);
}
.hb-btn-ghost {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 48px;
    border-radius: var(--hb-r-sm);
    border: 1.5px solid var(--hb-border-s);
    background: none;
    color: var(--hb-sub);
    font-size: .88rem;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    transition: all .14s;
}
.hb-btn-ghost:hover { border-color: var(--hb-ink); color: var(--hb-ink); text-decoration: none; }
.hb-btn-primary {
    flex: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 48px;
    border-radius: var(--hb-r-sm);
    border: none;
    background: var(--hb-ink);
    color: #fff;
    font-size: .88rem;
    font-weight: 700;
    cursor: pointer;
    transition: background .14s;
}
.hb-btn-primary:hover { background: #2d2820; }

/* ── Dropdown menu ───────────────────────────────────────────────── */
.dropdown-menu { border: 1.5px solid var(--hb-border); border-radius: var(--hb-r-sm); box-shadow: var(--hb-shadow); }
.dropdown-item { font-size: .84rem; padding: 9px 16px; color: var(--hb-sub); }
.dropdown-item:hover { background: var(--hb-cream); color: var(--hb-ink); }
.dropdown-divider { border-color: var(--hb-border); }

/* ── Responsive ──────────────────────────────────────────────────── */
@media (max-width: 640px) {
    .hb-stats { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .hb-stat-val { font-size: 1.35rem; }
    .hb-btn-new { display: none; }
    .hb-fab { display: flex; }
    .hb-grid { grid-template-columns: 1fr; }
    .hb-grid.--list .hb-card-r1 { flex: 0 0 140px; }
    .hb-grid.--list .hb-card-r2 { display: none; }
}
@media (min-width: 641px) and (max-width: 1024px) {
    .hb-stats { grid-template-columns: repeat(2, 1fr); }
    .hb-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 1024px) {
    .hb-sheet {
        left: auto;
        right: 0;
        width: 400px;
        max-height: 100vh;
        border-radius: 0;
        top: 0;
        bottom: 0;
    }
}
</style>
@endpush

@php
$today    = now()->toDateString();
$tomorrow = now()->addDay()->toDateString();

$activeFilters = request()->hasAny(['hall_id','status','payment_status','date_from','date_to','booking_type']);

$pendingFs  = strlen('₹'.number_format($stats['pending_collect'])) > 8 ? '1.2rem' : '1.7rem';
$revenueFs  = strlen('₹'.number_format($stats['month_revenue'])) > 8  ? '1.2rem' : '1.7rem';
$scrollPage = $bookings->currentPage() + 1;
$scrollMore = $bookings->hasMorePages() ? 'true' : 'false';

// Chip active states
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

    {{-- Stats --}}
    <div class="hb-stats">
        <div class="hb-stat">
            <div class="hb-stat-icon"><i class="bi bi-calendar-event"></i></div>
            <div class="hb-stat-val">{{ $stats['today'] }}</div>
            <div class="hb-stat-label">Today's Events</div>
        </div>
        <div class="hb-stat">
            <div class="hb-stat-icon" style="background:#2563eb"><i class="bi bi-people-fill"></i></div>
            <div class="hb-stat-val">{{ number_format($stats['upcoming_guests']) }}</div>
            <div class="hb-stat-label">Upcoming Guests</div>
        </div>
        <div class="hb-stat">
            <div class="hb-stat-icon" style="background:#f59e0b"><i class="bi bi-hourglass-split"></i></div>
            @if($isEmployee)
                <div class="hb-stat-val" style="font-size:1rem;color:var(--hb-faint);letter-spacing:0">—</div>
            @else
                <div class="hb-stat-val" style="font-size:{{ $pendingFs }}">₹{{ number_format($stats['pending_collect']) }}</div>
            @endif
            <div class="hb-stat-label">Pending Collection</div>
        </div>
        <div class="hb-stat">
            <div class="hb-stat-icon" style="background:#16a34a"><i class="bi bi-graph-up-arrow"></i></div>
            @if($isEmployee)
                <div class="hb-stat-val" style="font-size:1rem;color:var(--hb-faint);letter-spacing:0">—</div>
            @else
                <div class="hb-stat-val" style="font-size:{{ $revenueFs }}">₹{{ number_format($stats['month_revenue']) }}</div>
            @endif
            <div class="hb-stat-label">Monthly Revenue</div>
        </div>
    </div>

    {{-- Top bar --}}
    <form class="hb-topbar" method="GET" action="{{ route('hall.bookings.index') }}" id="hbSearchForm">
        {{-- Preserve other filters when searching --}}
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
            {{-- View toggle --}}
            <div class="hb-view-toggle" role="group" aria-label="View mode">
                <button type="button" class="hb-vt-btn --active" data-view="card" title="Card view" aria-pressed="true">
                    <i class="bi bi-grid-3x3-gap"></i>
                </button>
                <button type="button" class="hb-vt-btn" data-view="list" title="List view" aria-pressed="false">
                    <i class="bi bi-list-ul"></i>
                </button>
            </div>
            {{-- Filter --}}
            <button type="button" class="hb-filter-btn {{ $activeFilters ? '--active' : '' }}" id="hbOpenSheet" aria-expanded="false" aria-controls="hbSheet">
                <i class="bi bi-sliders"></i>
                <span class="d-none d-sm-inline">Filter</span>
                @if($activeFilters)<span class="hb-filter-dot"></span>@endif
            </button>
            {{-- New (desktop) --}}
            <a href="{{ route('hall.bookings.create') }}" class="hb-btn-new">
                <i class="bi bi-plus-lg"></i> New Booking
            </a>
        </div>
    </form>

    {{-- Quick chips --}}
    <div class="hb-chips" role="list" aria-label="Quick filters">
        <a role="listitem" href="{{ route('hall.bookings.index') }}"
           class="hb-chip {{ $chipAll ? '--active' : '' }}">
            <i class="bi bi-collection"></i> All
        </a>
        <a role="listitem" href="{{ route('hall.bookings.index', array_merge(request()->except(['date_from','date_to','page']), ['date_from' => $today, 'date_to' => $today])) }}"
           class="hb-chip {{ $chipToday ? '--active' : '' }}">
            <i class="bi bi-sun"></i> Today
        </a>
        <a role="listitem" href="{{ route('hall.bookings.index', array_merge(request()->except(['date_from','date_to','page']), ['date_from' => $tomorrow, 'date_to' => $tomorrow])) }}"
           class="hb-chip {{ $chipTomorrow ? '--active' : '' }}">
            <i class="bi bi-arrow-right-circle"></i> Tomorrow
        </a>
        <a role="listitem" href="{{ route('hall.bookings.index', ['payment_status' => 'pending']) }}"
           class="hb-chip {{ $chipPending ? '--active' : '' }}">
            <i class="bi bi-hourglass"></i> Pending Pay
        </a>
        <a role="listitem" href="{{ route('hall.bookings.index', ['booking_type' => 'food_only']) }}"
           class="hb-chip {{ $chipFood ? '--active' : '' }}">
            <i class="bi bi-egg-fried"></i> Food Only
        </a>
        <a role="listitem" href="{{ route('hall.bookings.index', array_merge(request()->except(['date_from','date_to','page']), ['date_from' => $today])) }}"
           class="hb-chip {{ $chipUpcoming ? '--active' : '' }}">
            <i class="bi bi-calendar-range"></i> Upcoming
        </a>
    </div>

    {{-- Result bar --}}
    <div class="hb-result-bar">
        <span>{{ number_format($bookings->total()) }} booking{{ $bookings->total() !== 1 ? 's' : '' }}
        @if($activeFilters || request('search'))
            &nbsp;·&nbsp;<a href="{{ route('hall.bookings.index') }}" style="color:var(--hb-gold);text-decoration:none">Clear filters</a>
        @endif
        </span>
    </div>

    {{-- Booking grid --}}
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
    var baseUrl   = '{{ $bookings->url(1) }}';

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
