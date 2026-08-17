<x-admin-layout title="Payments">
@push('styles')
<style>
/* ── Page shell ────────────────────────────────────────── */
.ef-pay-wrap { max-width: 1360px; margin: 0 auto; }

/* ── Header ────────────────────────────────────────────── */
.ef-pay-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.1rem;
}
.ef-pay-header-text { min-width: 0; }
.ef-pay-eyebrow {
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .09em;
    text-transform: uppercase;
    color: var(--ef-gold);
    margin-bottom: .2rem;
}
.ef-pay-title { font-size: 1.5rem; font-weight: 800; color: var(--ef-ink); letter-spacing: -.01em; line-height: 1.15; }
.ef-pay-sub { color: var(--ef-muted); font-size: .82rem; margin-top: .2rem; }
.ef-pay-header-actions { display: flex; gap: .5rem; flex-wrap: wrap; align-items: center; flex-shrink: 0; }
.ef-pay-btn-ghost {
    background: var(--ef-surface);
    color: var(--ef-ink-2);
    border: 1px solid var(--ef-border);
    border-radius: 9px;
    padding: .5rem .85rem;
    font-size: .8rem;
    font-weight: 600;
    cursor: pointer;
    transition: border-color .15s, background .15s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    white-space: nowrap;
    min-height: 40px;
}
.ef-pay-btn-ghost:hover { border-color: var(--ef-gold); color: var(--ef-gold); background: rgba(184,137,62,.05); }
@media (max-width: 575.98px) {
    .ef-pay-header { flex-direction: column; }
    .ef-pay-header-actions { width: 100%; }
    .ef-pay-header-actions .ef-pay-btn-ghost { flex: 1; justify-content: center; }
}

/* ── Compact KPI strip ─────────────────────────────────── */
.ef-pay-strip {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: .65rem;
    margin-bottom: 1rem;
}
@media (max-width: 767px) { .ef-pay-strip { grid-template-columns: repeat(2, 1fr); gap: .55rem; } }
.ef-pay-kpi {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius-sm);
    padding: .7rem .85rem;
    display: flex;
    align-items: center;
    gap: .65rem;
    min-width: 0;
}
.ef-pay-kpi-icon {
    width: 30px; height: 30px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: .8rem;
    flex-shrink: 0;
}
.ef-pay-kpi-icon.--total { background: rgba(184,137,62,.12); color: var(--ef-gold); }
.ef-pay-kpi-icon.--count { background: rgba(99,102,241,.1);  color: #4338ca; }
.ef-pay-kpi-icon.--avg   { background: rgba(107,114,128,.1); color: #374151; }
.ef-pay-kpi-icon.--month { background: rgba(22,163,74,.1);   color: #15803d; }
.ef-pay-kpi-text { min-width: 0; }
.ef-pay-kpi-val { font-size: 1.02rem; font-weight: 800; color: var(--ef-ink); line-height: 1.15; letter-spacing: -.01em; white-space: nowrap; }
.ef-pay-kpi-label { font-size: .68rem; color: var(--ef-muted); margin-top: .05rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ── Toolbar ───────────────────────────────────────────── */
.ef-pay-toolbar {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius-sm);
    padding: .7rem .85rem;
    margin-bottom: .85rem;
}
.ef-pay-toolbar-row { display: flex; gap: .55rem; align-items: center; }
.ef-pay-search-wrap { position: relative; flex: 1; min-width: 0; }
.ef-pay-search {
    width: 100%;
    border: 1px solid var(--ef-border-strong);
    border-radius: 9px;
    padding: .5rem .85rem .5rem 2.15rem;
    font-size: .85rem;
    color: var(--ef-ink);
    background: var(--ef-surface-2);
    outline: none;
    min-height: 40px;
    transition: border-color .15s, box-shadow .15s;
}
.ef-pay-search::placeholder { color: var(--ef-faint); }
.ef-pay-search:focus { border-color: var(--ef-gold); background: var(--ef-surface); box-shadow: 0 0 0 3px rgba(184,137,62,.12); }
.ef-pay-search-submit {
    position: absolute; left: 0; top: 0; bottom: 0; width: 2.15rem;
    display: flex; align-items: center; justify-content: center;
    background: none; border: none; color: var(--ef-muted); padding: 0; cursor: pointer;
}
.ef-pay-search-submit:hover { color: var(--ef-gold); }
.ef-pay-filters-btn {
    flex-shrink: 0; position: relative;
    display: inline-flex; align-items: center; gap: .35rem;
    border: 1px solid var(--ef-border-strong);
    border-radius: 9px;
    padding: 0 .85rem;
    min-height: 40px;
    font-size: .82rem; font-weight: 600; color: var(--ef-ink-2);
    background: var(--ef-surface-2);
    cursor: pointer; white-space: nowrap;
}
.ef-pay-filters-btn:hover { border-color: var(--ef-gold); color: var(--ef-gold); }
.ef-pay-filters-btn .count {
    background: var(--ef-gold); color: #fff; font-size: .68rem; font-weight: 700;
    border-radius: 999px; padding: 0 .4rem; min-width: 17px; text-align: center; line-height: 1.5;
}

.ef-pay-chip-scroll {
    display: flex; gap: .4rem; overflow-x: auto; -webkit-overflow-scrolling: touch;
    scrollbar-width: none; margin-top: .55rem; padding-bottom: 1px;
}
.ef-pay-chip-scroll::-webkit-scrollbar { display: none; }
.ef-pay-chip {
    flex-shrink: 0;
    padding: .38rem .8rem;
    border-radius: 999px;
    font-size: .78rem;
    font-weight: 600;
    border: 1px solid var(--ef-border);
    color: var(--ef-muted);
    background: var(--ef-surface-2);
    cursor: pointer;
    transition: all .15s var(--ef-ease);
    text-decoration: none;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    min-height: 32px;
}
.ef-pay-chip:hover { border-color: var(--ef-gold); color: var(--ef-gold); }
.ef-pay-chip.--active { background: var(--ef-gold); border-color: var(--ef-gold); color: #fff; }

@media (max-width: 767px) {
    .ef-pay-toolbar-row { flex-wrap: nowrap; }
    .ef-pay-filters-btn span.label { display: none; }
    .ef-pay-filters-btn { padding: 0 .65rem; }
}

/* ── Filters modal (bottom sheet on mobile) ───────────────── */
.ef-pay-filters-modal .modal-dialog { max-width: 420px; }
@media (max-width: 575.98px) {
    .ef-pay-filters-modal .modal-dialog {
        margin: 0; max-width: 100%; width: 100%;
        position: fixed; bottom: 0; left: 0;
    }
    .ef-pay-filters-modal .modal-content { border-radius: 16px 16px 0 0; }
}
.ef-pay-filter-group { margin-bottom: 1rem; }
.ef-pay-filter-group:last-child { margin-bottom: 0; }
.ef-pay-filter-label { font-size: .74rem; font-weight: 700; color: var(--ef-ink-2); margin-bottom: .4rem; text-transform: uppercase; letter-spacing: .03em; }
.ef-pay-filter-select, .ef-pay-filter-date {
    width: 100%;
    border: 1px solid var(--ef-border-strong);
    border-radius: 9px;
    padding: .55rem .75rem;
    font-size: .85rem;
    color: var(--ef-ink);
    background: var(--ef-surface-2);
    min-height: 42px;
}
.ef-pay-filter-date-row { display: flex; gap: .5rem; }
.ef-pay-filter-date-row > * { flex: 1; min-width: 0; }

/* ── Transaction list container ───────────────────────────── */
.ef-pay-list {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius-sm);
    overflow: hidden;
    margin-bottom: 1rem;
}
.ef-pay-list-head {
    padding: .7rem .95rem;
    border-bottom: 1px solid var(--ef-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}
.ef-pay-list-title { font-size: .72rem; font-weight: 750; color: var(--ef-ink); letter-spacing: .05em; text-transform: uppercase; }
.ef-pay-list-meta { font-size: .78rem; color: var(--ef-muted); }
.ef-pay-list-meta strong { color: var(--ef-gold); font-weight: 700; }

/* Desktop column header row — purely visual labels above the grid rows */
.ef-pay-col-head {
    display: none;
}
@media (min-width: 900px) {
    .ef-pay-col-head {
        display: grid;
        grid-template-columns: 2.4fr 1.3fr 1fr .9fr .8fr auto;
        gap: .75rem;
        padding: .5rem .95rem;
        background: var(--ef-surface-2);
        border-bottom: 1px solid var(--ef-border);
        font-size: .64rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--ef-muted);
    }
    .ef-pay-col-head .amt { text-align: right; }
}

/* ── Date group divider ───────────────────────────────────── */
.ef-pay-date-group {
    padding: .4rem .95rem;
    border-bottom: 1px solid var(--ef-border);
    font-size: .68rem;
    font-weight: 700;
    color: var(--ef-muted);
    letter-spacing: .05em;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: transparent;
}
.ef-pay-date-group-total { font-size: .7rem; font-weight: 700; color: var(--ef-ink-2); }

/* ── Transaction row ──────────────────────────────────────── */
.ef-pay-row {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: .35rem .75rem;
    align-items: center;
    padding: .6rem .95rem;
    border-bottom: 1px solid var(--ef-border);
    transition: background .12s;
    position: relative;
}
.ef-pay-row:last-child { border-bottom: none; }
.ef-pay-row:hover { background: var(--ef-surface-2); }
.ef-pay-row:focus-within { background: var(--ef-surface-2); }

@media (min-width: 900px) {
    .ef-pay-row { grid-template-columns: 2.4fr 1.3fr 1fr .9fr .8fr auto; padding: .55rem .95rem; }
}

.ef-pay-row-left { display: flex; gap: .6rem; align-items: center; min-width: 0; }
.ef-pay-mode-icon {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: .82rem;
    flex-shrink: 0;
}
.ef-pay-mode-icon.--cash  { background: rgba(22,163,74,.1);   color: #15803d; }
.ef-pay-mode-icon.--upi   { background: rgba(99,102,241,.1);  color: #4338ca; }
.ef-pay-mode-icon.--bank  { background: rgba(14,165,233,.1);  color: #0369a1; }
.ef-pay-mode-icon.--wallet{ background: rgba(245,158,11,.1);  color: #b45309; }
.ef-pay-row-title { font-size: .87rem; font-weight: 700; color: var(--ef-ink); line-height: 1.25; overflow-wrap: anywhere; }
.ef-pay-row-title a { color: inherit; text-decoration: none; }
.ef-pay-row-title a:hover { color: var(--ef-gold); }
.ef-pay-row-sub { font-size: .72rem; color: var(--ef-muted); display: flex; align-items: center; gap: .4rem; flex-wrap: wrap; margin-top: .1rem; }
.ef-pay-row-sub .dot { width: 3px; height: 3px; border-radius: 50%; background: var(--ef-border-strong); flex-shrink: 0; }

.ef-pay-row-category { font-size: .78rem; color: var(--ef-ink-2); }

.ef-pay-mode-badge {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .18rem .55rem;
    border-radius: 6px;
    font-size: .66rem;
    font-weight: 700;
    letter-spacing: .03em;
    text-transform: uppercase;
    width: fit-content;
}
.ef-pay-mode-badge.--cash  { background: rgba(22,163,74,.1);   color: #15803d;  border: 1px solid rgba(22,163,74,.2);  }
.ef-pay-mode-badge.--upi   { background: rgba(99,102,241,.1);  color: #4338ca;  border: 1px solid rgba(99,102,241,.2); }
.ef-pay-mode-badge.--bank  { background: rgba(14,165,233,.1);  color: #0369a1;  border: 1px solid rgba(14,165,233,.2); }
.ef-pay-mode-badge.--wallet{ background: rgba(245,158,11,.1);  color: #92400e;  border: 1px solid rgba(245,158,11,.2); }
.ef-pay-row-time { font-size: .72rem; color: var(--ef-muted); }

.ef-pay-status-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .64rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase;
    color: var(--ef-success); background: rgba(22,163,74,.08); border: 1px solid rgba(22,163,74,.2);
    border-radius: 5px; padding: .14rem .5rem; width: fit-content;
}
.ef-pay-status-badge::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

.ef-pay-row-right { display: flex; flex-direction: column; align-items: flex-end; gap: .25rem; justify-self: end; }
.ef-pay-amount { font-size: 1.05rem; font-weight: 800; color: var(--ef-ink); letter-spacing: -.01em; line-height: 1; font-variant-numeric: tabular-nums; }
.ef-pay-view-btn {
    font-size: .74rem; font-weight: 600; color: var(--ef-muted); text-decoration: none;
    display: inline-flex; align-items: center; gap: .25rem; min-height: 28px;
}
.ef-pay-view-btn:hover { color: var(--ef-gold); }

/* Mobile stacked layout: identity full width, then a meta row (method/time/status), amount+action inline at end */
@media (max-width: 899.98px) {
    .ef-pay-row { grid-template-columns: 1fr; }
    .ef-pay-row-category { display: none; } /* folded into sub-line instead */
    .ef-pay-row-meta {
        grid-column: 1 / -1;
        display: flex; align-items: center; justify-content: space-between; gap: .5rem; flex-wrap: wrap;
        margin-top: .3rem;
    }
    .ef-pay-row-meta-left { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
    .ef-pay-row-right { flex-direction: row; align-items: center; gap: .6rem; }
}
@media (min-width: 900px) {
    .ef-pay-row-meta { display: contents; }
    .ef-pay-row-meta-left { display: contents; }
    .ef-pay-row-time { text-align: left; }
}

/* Whole row clickable on mobile via an overlay anchor; explicit "View" link stays for accessibility/desktop */
.ef-pay-row-hit { position: absolute; inset: 0; z-index: 1; border-radius: inherit; }
.ef-pay-row-hit:focus-visible { outline: 2px solid var(--ef-gold); outline-offset: -2px; }
.ef-pay-row-right, .ef-pay-row-title a { position: relative; z-index: 2; }

/* ── Empty state ───────────────────────────────────────────── */
.ef-pay-empty { padding: 3rem 1.5rem; text-align: center; }
.ef-pay-empty-icon {
    width: 56px; height: 56px; border-radius: 14px;
    background: var(--ef-surface-2); border: 1px solid var(--ef-border);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; color: var(--ef-border-strong); margin: 0 auto 1rem;
}
.ef-pay-empty-title { font-size: 1rem; font-weight: 700; color: var(--ef-ink); margin-bottom: .35rem; }
.ef-pay-empty-sub { font-size: .84rem; color: var(--ef-muted); margin-bottom: 1rem; }

/* ── Pagination ────────────────────────────────────────────── */
.ef-pay-pagination {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius-sm);
    padding: .65rem .95rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .5rem;
    margin-bottom: 1rem;
}
.ef-pay-pagination-info { font-size: .76rem; color: var(--ef-muted); }
@media (max-width: 575.98px) {
    .ef-pay-pagination { flex-direction: column; align-items: stretch; text-align: center; }
}
</style>
@endpush

@php
    $modeFilter  = $filters['payment_mode'] ?? '';
    $empFilter   = $filters['employee_id']  ?? '';
    $fromFilter  = $filters['from'] ?? '';
    $toFilter    = $filters['to']   ?? '';
    $search      = $filters['search'] ?? '';

    $today     = now()->toDateString();
    $weekStart = now()->startOfWeek()->toDateString();
    $weekEnd   = now()->endOfWeek()->toDateString();
    $monStart  = now()->startOfMonth()->toDateString();
    $monEnd    = now()->endOfMonth()->toDateString();

    $isAllTime = !$fromFilter && !$toFilter;
    $isToday   = $fromFilter === $today && $toFilter === $today;
    $isWeek    = $fromFilter === $weekStart && $toFilter === $weekEnd;
    $isMonth   = $fromFilter === $monStart && $toFilter === $monEnd;
    $isCustomRange = ($fromFilter || $toFilter) && !$isToday && !$isWeek && !$isMonth;

    // "Filters" modal covers method + paid-by + a manual date range —
    // count active ones so the button can show "Filters · N".
    $advFilterCount = ($modeFilter ? 1 : 0) + ($empFilter ? 1 : 0) + ($isCustomRange ? 1 : 0);

    $modeIconMap  = ['cash' => 'bi-cash-stack', 'upi' => 'bi-phone', 'bank_transfer' => 'bi-bank', 'wallet' => 'bi-wallet2'];
    $modeCssMap   = ['cash' => '--cash', 'upi' => '--upi', 'bank_transfer' => '--bank', 'wallet' => '--wallet'];
    $modeLabelMap = \App\Models\ExpensePayment::modeLabels();

    $fmt = fn(float $v): string =>
        $v >= 10000000 ? '₹' . number_format($v/10000000, 1) . 'Cr'
      : ($v >= 100000  ? '₹' . number_format($v/100000, 1) . 'L'
      :                  '₹' . number_format($v, 0));

    // Group paginated payments by date for the timeline display
    $grouped = $payments->getCollection()->groupBy(fn($p) => $p->paid_at->toDateString());
@endphp

<div class="ef-pay-wrap">

{{-- ── Header ────────────────────────────────────────────────── --}}
<div class="ef-pay-header">
    <div class="ef-pay-header-text">
        <div class="ef-pay-eyebrow d-none d-md-block">Finance Operations</div>
        <div class="ef-pay-title">Payments</div>
        <div class="ef-pay-sub">
            <span class="d-md-none">Manage and track all payment transactions</span>
            <span class="d-none d-md-inline">Manage incoming and outgoing payment transactions</span>
        </div>
    </div>
    <div class="ef-pay-header-actions">
        <a href="{{ route('admin.expense-requests.index') }}" class="ef-pay-btn-ghost">
            <i class="bi bi-receipt"></i> Expense Requests
        </a>
        <a href="{{ route('admin.payments.index') }}" class="ef-pay-btn-ghost">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </a>
    </div>
</div>

{{-- ── Compact KPI strip (real backend metrics only) ───────────── --}}
<div class="ef-pay-strip">
    <div class="ef-pay-kpi">
        <div class="ef-pay-kpi-icon --total"><i class="bi bi-currency-rupee"></i></div>
        <div class="ef-pay-kpi-text">
            <div class="ef-pay-kpi-val">{{ $fmt($stats['total_amount']) }}</div>
            <div class="ef-pay-kpi-label">Total collected</div>
        </div>
    </div>
    <div class="ef-pay-kpi">
        <div class="ef-pay-kpi-icon --count"><i class="bi bi-receipt-cutoff"></i></div>
        <div class="ef-pay-kpi-text">
            <div class="ef-pay-kpi-val">{{ number_format($stats['total_count']) }}</div>
            <div class="ef-pay-kpi-label">{{ Str::plural('Transaction', $stats['total_count']) }}</div>
        </div>
    </div>
    <div class="ef-pay-kpi">
        <div class="ef-pay-kpi-icon --avg"><i class="bi bi-graph-up"></i></div>
        <div class="ef-pay-kpi-text">
            <div class="ef-pay-kpi-val">{{ $fmt($stats['avg_amount']) }}</div>
            <div class="ef-pay-kpi-label">Avg transaction</div>
        </div>
    </div>
    <div class="ef-pay-kpi">
        <div class="ef-pay-kpi-icon --month"><i class="bi bi-calendar-month"></i></div>
        <div class="ef-pay-kpi-text">
            <div class="ef-pay-kpi-val">{{ $fmt($stats['monthly_total']) }}</div>
            <div class="ef-pay-kpi-label">This month</div>
        </div>
    </div>
</div>

{{-- ── Toolbar: search + date chips + Filters ──────────────────── --}}
<form method="GET" id="payFilterForm" action="{{ route('admin.payments.index') }}">
<div class="ef-pay-toolbar">
    <div class="ef-pay-toolbar-row">
        <div class="ef-pay-search-wrap">
            <button type="submit" class="ef-pay-search-submit" aria-label="Search payments"><i class="bi bi-search"></i></button>
            <input type="text" name="search" class="ef-pay-search"
                   placeholder="Search payments…"
                   value="{{ $search }}">
        </div>
        @if($modeFilter)<input type="hidden" name="payment_mode" value="{{ $modeFilter }}">@endif
        @if($empFilter)<input type="hidden" name="employee_id" value="{{ $empFilter }}">@endif
        @if($isCustomRange)
            <input type="hidden" name="from" value="{{ $fromFilter }}">
            <input type="hidden" name="to" value="{{ $toFilter }}">
        @endif
        <button type="button" class="ef-pay-filters-btn" data-bs-toggle="modal" data-bs-target="#payFiltersModal">
            <i class="bi bi-sliders2"></i><span class="label">Filters</span>
            @if($advFilterCount)<span class="count">{{ $advFilterCount }}</span>@endif
        </button>
    </div>

    <div class="ef-pay-chip-scroll" role="group" aria-label="Filter by date range">
        <a href="{{ route('admin.payments.index', array_filter(['search' => $search, 'payment_mode' => $modeFilter, 'employee_id' => $empFilter])) }}"
           class="ef-pay-chip {{ $isAllTime ? '--active' : '' }}">All Time</a>
        <a href="{{ route('admin.payments.index', array_filter(['from' => $today, 'to' => $today, 'search' => $search, 'payment_mode' => $modeFilter, 'employee_id' => $empFilter])) }}"
           class="ef-pay-chip {{ $isToday ? '--active' : '' }}">Today</a>
        <a href="{{ route('admin.payments.index', array_filter(['from' => $weekStart, 'to' => $weekEnd, 'search' => $search, 'payment_mode' => $modeFilter, 'employee_id' => $empFilter])) }}"
           class="ef-pay-chip {{ $isWeek ? '--active' : '' }}">This Week</a>
        <a href="{{ route('admin.payments.index', array_filter(['from' => $monStart, 'to' => $monEnd, 'search' => $search, 'payment_mode' => $modeFilter, 'employee_id' => $empFilter])) }}"
           class="ef-pay-chip {{ $isMonth ? '--active' : '' }}">This Month</a>
    </div>
</div>

{{-- ── Filters modal (method / paid by / manual date range) ─────── --}}
<div class="modal fade ef-pay-filters-modal" id="payFiltersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filters</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="ef-pay-filter-group">
                    <div class="ef-pay-filter-label">Payment Method</div>
                    <select name="payment_mode" class="ef-pay-filter-select">
                        <option value="">All methods</option>
                        @foreach($modeLabelMap as $value => $label)
                            <option value="{{ $value }}" {{ $modeFilter === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ef-pay-filter-group">
                    <div class="ef-pay-filter-label">Paid By</div>
                    <select name="employee_id" class="ef-pay-filter-select">
                        <option value="">All employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ (string) $empFilter === (string) $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ef-pay-filter-group">
                    <div class="ef-pay-filter-label">Date Range</div>
                    <div class="ef-pay-filter-date-row">
                        <input type="date" name="from" class="ef-pay-filter-date" value="{{ $fromFilter }}" aria-label="From date">
                        <input type="date" name="to" class="ef-pay-filter-date" value="{{ $toFilter }}" aria-label="To date">
                    </div>
                </div>
                @if($search)<input type="hidden" name="search" value="{{ $search }}">@endif
            </div>
            <div class="modal-footer">
                <a href="{{ route('admin.payments.index', array_filter(['search' => $search])) }}" class="btn btn-outline-dark">Clear</a>
                <button type="submit" class="btn btn-dark">Apply Filters</button>
            </div>
        </div>
    </div>
</div>
</form>

{{-- ── Transaction list ─────────────────────────────────────────── --}}
<div class="ef-pay-list">
    <div class="ef-pay-list-head">
        <div class="ef-pay-list-title">Transactions</div>
        <div class="ef-pay-list-meta">
            @if($payments->total() > 0)
                <strong>{{ number_format($payments->total()) }}</strong>
                {{ Str::plural('payment', $payments->total()) }} ·
                <strong>{{ $fmt($stats['total_amount']) }}</strong>
            @endif
        </div>
    </div>

    @if($payments->isEmpty())
    <div class="ef-pay-empty">
        <div class="ef-pay-empty-icon"><i class="bi bi-credit-card"></i></div>
        <div class="ef-pay-empty-title">No payments found</div>
        <div class="ef-pay-empty-sub">
            @if($search || $modeFilter || $empFilter || $fromFilter || $toFilter)
                Try changing your filters or search criteria.
            @else
                Recorded settlements and transaction history will appear here.
            @endif
        </div>
        @if($search || $modeFilter || $empFilter || $fromFilter || $toFilter)
            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-dark btn-sm">Clear Filters</a>
        @endif
    </div>
    @else

    <div class="ef-pay-col-head">
        <span>Payment</span>
        <span>Category</span>
        <span>Method</span>
        <span>Time</span>
        <span>Status</span>
        <span class="amt">Amount</span>
    </div>

    @php $prevDate = null; @endphp
    @foreach($payments as $payment)
    @php
        $dateKey  = $payment->paid_at->toDateString();
        $mode     = $payment->payment_mode;
        $modeCss  = $modeCssMap[$mode]  ?? '--cash';
        $modeIcon = $modeIconMap[$mode] ?? 'bi-credit-card';
        $modeLabel= $modeLabelMap[$mode] ?? ucfirst($mode);
        $req      = $payment->expenseRequest;
        $requester= $req?->requester;
        $category = $req?->category;
        $isToday  = $payment->paid_at->isToday();
        $isYest   = $payment->paid_at->isYesterday();
        $dateLabel = $isToday ? 'Today'
                   : ($isYest ? 'Yesterday'
                   : $payment->paid_at->format('D, j M Y'));
    @endphp

    {{-- Date group separator --}}
    @if($dateKey !== $prevDate)
    @php
        $dayTotal = $payments->getCollection()
            ->filter(fn($p) => $p->paid_at->toDateString() === $dateKey)
            ->sum('amount');
        $prevDate = $dateKey;
    @endphp
    <div class="ef-pay-date-group">
        <span>{{ $dateLabel }}</span>
        <span class="ef-pay-date-group-total">₹{{ number_format($dayTotal, 0) }}</span>
    </div>
    @endif

    <div class="ef-pay-row">
        @if($req)
            <a href="{{ route('admin.expense-requests.show', $req) }}" class="ef-pay-row-hit" aria-label="View payment: {{ $req->title }}, ₹{{ number_format($payment->amount, 0) }}"></a>
        @endif

        {{-- Identity --}}
        <div class="ef-pay-row-left">
            <div class="ef-pay-mode-icon {{ $modeCss }}">
                <i class="bi {{ $modeIcon }}"></i>
            </div>
            <div style="min-width:0;flex:1">
                <div class="ef-pay-row-title">
                    @if($req)
                        <a href="{{ route('admin.expense-requests.show', $req) }}">{{ Str::limit($req->title, 45) }}</a>
                    @else
                        <span style="color:var(--ef-muted)">Unlinked Payment</span>
                    @endif
                </div>
                <div class="ef-pay-row-sub">
                    @if($requester)
                        <span>{{ $requester->name }}</span>
                    @endif
                    @if($category)
                        <span class="dot"></span><span>{{ $category->name }}</span>
                    @endif
                    @if($payment->payer)
                        <span class="dot"></span><span>Paid by {{ $payment->payer->name }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Category (desktop column only) --}}
        <div class="ef-pay-row-category">{{ $category->name ?? '—' }}</div>

        {{-- Meta: method + time + status (wraps as one row on mobile, separate grid cells on desktop) --}}
        <div class="ef-pay-row-meta">
            <div class="ef-pay-row-meta-left">
                <span class="ef-pay-mode-badge {{ $modeCss }}">{{ $modeLabel }}</span>
                <span class="ef-pay-row-time">{{ $payment->paid_at->format('h:i A') }}</span>
            </div>
            <span class="ef-pay-status-badge">Settled</span>
        </div>

        {{-- Amount + action --}}
        <div class="ef-pay-row-right">
            <div class="ef-pay-amount">₹{{ number_format($payment->amount, 0) }}</div>
            @if($req)
                <a href="{{ route('admin.expense-requests.show', $req) }}" class="ef-pay-view-btn">
                    View <i class="bi bi-arrow-right" style="font-size:.65rem"></i>
                </a>
            @endif
        </div>
    </div>
    @endforeach

    @endif
</div>

{{-- ── Pagination ──────────────────────────────────────────────── --}}
@if($payments->hasPages())
<div class="ef-pay-pagination">
    <div class="ef-pay-pagination-info">
        Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }}
        of {{ number_format($payments->total()) }} payments
    </div>
    {{ $payments->links() }}
</div>
@endif

</div>
</x-admin-layout>
