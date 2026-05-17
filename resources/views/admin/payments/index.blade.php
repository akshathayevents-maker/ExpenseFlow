<x-admin-layout title="Payments">
@push('styles')
<style>
/* ── Design tokens ─────────────────────────────────────── */
:root {
    --ef-bg:            #f7f4f0;
    --ef-ink:           #1a1612;
    --ef-gold:          #a07238;
    --ef-gold-hi:       #b8854a;
    --ef-muted:         #6b6560;
    --ef-faint:         #ede9e3;
    --ef-border:        rgba(160,114,56,.15);
    --ef-border-strong: rgba(160,114,56,.30);
    --ef-shadow:        0 1px 3px rgba(26,22,18,.08),0 4px 12px rgba(26,22,18,.06);
    --ef-shadow-hover:  0 4px 16px rgba(26,22,18,.14),0 1px 4px rgba(26,22,18,.08);
    --ef-radius:        14px;
    --ef-ease:          cubic-bezier(.25,.46,.45,.94);
    --ef-danger:        #c0392b;
    --ef-success:       #16a34a;
}

/* ── Hero ──────────────────────────────────────────────── */
.ef-pay-hero {
    background: linear-gradient(135deg, #111210 0%, #1f1d19 55%, #2a2519 100%);
    border-radius: var(--ef-radius);
    padding: 2rem 2.2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
}
.ef-pay-hero::before {
    content: '₹';
    position: absolute;
    right: -1rem;
    top: -1.5rem;
    font-size: 10rem;
    font-weight: 800;
    color: rgba(255,255,255,.025);
    line-height: 1;
    pointer-events: none;
    user-select: none;
}
.ef-pay-hero-eyebrow {
    font-size: .72rem;
    font-weight: 600;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--ef-gold);
    margin-bottom: .4rem;
}
.ef-pay-hero-title {
    font-size: clamp(1.7rem, 3.5vw, 2.6rem);
    font-weight: 700;
    color: #fff;
    line-height: 1.1;
    letter-spacing: -.02em;
}
.ef-pay-hero-sub {
    color: rgba(255,255,255,.45);
    font-size: .875rem;
    margin-top: .35rem;
}
.ef-pay-hero-date {
    color: rgba(255,255,255,.25);
    font-size: .75rem;
    margin-top: .5rem;
    letter-spacing: .02em;
}
.ef-pay-hero-actions { display: flex; gap: .5rem; flex-wrap: wrap; align-items: center; }
.ef-pay-btn-ghost {
    background: rgba(255,255,255,.08);
    color: rgba(255,255,255,.75);
    border: 1px solid rgba(255,255,255,.14);
    border-radius: 8px;
    padding: .48rem .95rem;
    font-size: .82rem;
    font-weight: 500;
    cursor: pointer;
    transition: background .2s var(--ef-ease);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    white-space: nowrap;
}
.ef-pay-btn-ghost:hover { background: rgba(255,255,255,.15); color: #fff; }

/* ── KPI strip ─────────────────────────────────────────── */
.ef-pay-strip {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: .85rem;
    margin-bottom: 1.5rem;
}
@media (max-width: 1399px) { .ef-pay-strip { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 767px)  { .ef-pay-strip { grid-template-columns: repeat(2, 1fr); } }
.ef-pay-kpi {
    background: #fff;
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    padding: 1.15rem 1.2rem;
    box-shadow: var(--ef-shadow);
    display: block;
    text-decoration: none;
    color: inherit;
    transition: box-shadow .2s var(--ef-ease), transform .15s;
}
a.ef-pay-kpi:hover { box-shadow: var(--ef-shadow-hover); transform: translateY(-2px); }
.ef-pay-kpi-icon {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem;
    margin-bottom: .65rem;
}
.ef-pay-kpi-icon.--total  { background: rgba(160,114,56,.12); color: var(--ef-gold); }
.ef-pay-kpi-icon.--cash   { background: rgba(22,163,74,.1);   color: #15803d; }
.ef-pay-kpi-icon.--upi    { background: rgba(99,102,241,.1);  color: #4338ca; }
.ef-pay-kpi-icon.--bank   { background: rgba(14,165,233,.1);  color: #0369a1; }
.ef-pay-kpi-icon.--avg    { background: rgba(107,114,128,.1); color: #374151; }
.ef-pay-kpi-icon.--month  { background: rgba(239,68,68,.08);  color: #b91c1c; }
.ef-pay-kpi-val {
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--ef-ink);
    line-height: 1;
    letter-spacing: -.02em;
}
.ef-pay-kpi-val.--sm { font-size: 1.05rem; }
.ef-pay-kpi-label { font-size: .75rem; color: var(--ef-muted); margin-top: .22rem; }
.ef-pay-kpi-sub   { font-size: .68rem; color: var(--ef-muted); margin-top: .1rem; opacity: .75; }

/* ── Filter bar ────────────────────────────────────────── */
.ef-pay-filter-bar {
    background: #fff;
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    padding: 1rem 1.2rem;
    box-shadow: var(--ef-shadow);
    margin-bottom: 1.5rem;
}
.ef-pay-chips {
    display: flex;
    gap: .45rem;
    overflow-x: auto;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 2px;
    flex-wrap: nowrap;
    align-items: center;
    margin-bottom: .8rem;
}
.ef-pay-chips::-webkit-scrollbar { display: none; }
.ef-pay-chip {
    flex-shrink: 0;
    padding: .35rem .85rem;
    border-radius: 20px;
    font-size: .78rem;
    font-weight: 500;
    border: 1px solid var(--ef-border);
    color: var(--ef-muted);
    background: var(--ef-faint);
    cursor: pointer;
    transition: all .18s var(--ef-ease);
    text-decoration: none;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: .3rem;
}
.ef-pay-chip:hover  { border-color: var(--ef-gold); color: var(--ef-gold); background: rgba(160,114,56,.06); }
.ef-pay-chip.--active { background: var(--ef-gold); border-color: var(--ef-gold); color: #fff; }
.ef-pay-chip.--cash { background: rgba(22,163,74,.08);  border-color: rgba(22,163,74,.25);  color: #15803d; }
.ef-pay-chip.--cash.--active  { background: #15803d; border-color: #15803d; color: #fff; }
.ef-pay-chip.--upi  { background: rgba(99,102,241,.08); border-color: rgba(99,102,241,.25); color: #4338ca; }
.ef-pay-chip.--upi.--active   { background: #4338ca; border-color: #4338ca; color: #fff; }
.ef-pay-chip.--bank { background: rgba(14,165,233,.08); border-color: rgba(14,165,233,.25); color: #0369a1; }
.ef-pay-chip.--bank.--active  { background: #0369a1; border-color: #0369a1; color: #fff; }
.ef-pay-filter-row {
    display: flex;
    gap: .55rem;
    align-items: center;
    flex-wrap: wrap;
}
.ef-pay-search-wrap { position: relative; flex: 1; min-width: 200px; }
.ef-pay-search-icon {
    position: absolute;
    left: .8rem; top: 50%;
    transform: translateY(-50%);
    color: var(--ef-muted);
    font-size: .85rem;
    pointer-events: none;
}
.ef-pay-search {
    width: 100%;
    border: 1px solid var(--ef-border-strong);
    border-radius: 9px;
    padding: .55rem .85rem .55rem 2.2rem;
    font-size: .875rem;
    color: var(--ef-ink);
    background: var(--ef-faint);
    outline: none;
    transition: border-color .18s, background .18s, box-shadow .18s;
}
.ef-pay-search::placeholder { color: #b5afa8; }
.ef-pay-search:focus {
    border-color: var(--ef-gold);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(160,114,56,.12);
}
.ef-pay-select {
    border: 1px solid var(--ef-border-strong);
    border-radius: 9px;
    padding: .55rem .85rem;
    font-size: .875rem;
    color: var(--ef-ink);
    background: var(--ef-faint);
    outline: none;
    transition: border-color .18s;
    cursor: pointer;
    min-width: 130px;
}
.ef-pay-select:focus { border-color: var(--ef-gold); background: #fff; box-shadow: 0 0 0 3px rgba(160,114,56,.12); }
.ef-pay-adv-toggle {
    border: 1px solid var(--ef-border);
    border-radius: 9px;
    padding: .55rem .85rem;
    font-size: .78rem;
    font-weight: 500;
    color: var(--ef-muted);
    background: var(--ef-faint);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    transition: all .18s;
    white-space: nowrap;
    position: relative;
}
.ef-pay-adv-toggle:hover { border-color: var(--ef-gold); color: var(--ef-gold); background: rgba(160,114,56,.06); }
.ef-pay-adv-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--ef-gold);
    display: none;
    position: absolute; top: 6px; right: 6px;
}
.ef-pay-adv-toggle.--has-filter .ef-pay-adv-dot { display: block; }
.ef-pay-adv-panel {
    overflow: hidden;
    max-height: 0;
    transition: max-height .35s var(--ef-ease);
}
.ef-pay-adv-panel.--open { max-height: 100px; }
.ef-pay-adv-inner {
    padding-top: .75rem;
    border-top: 1px solid var(--ef-border);
    margin-top: .75rem;
    display: flex;
    gap: .55rem;
    flex-wrap: wrap;
    align-items: center;
}
.ef-pay-date-input {
    border: 1px solid var(--ef-border-strong);
    border-radius: 9px;
    padding: .52rem .75rem;
    font-size: .82rem;
    color: var(--ef-ink);
    background: var(--ef-faint);
    outline: none;
    transition: border-color .18s;
    cursor: pointer;
}
.ef-pay-date-input:focus { border-color: var(--ef-gold); background: #fff; }
.ef-pay-btn-apply {
    background: var(--ef-gold);
    color: #fff;
    border: none;
    border-radius: 9px;
    padding: .55rem 1.1rem;
    font-size: .875rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .18s;
}
.ef-pay-btn-apply:hover { background: var(--ef-gold-hi); }
.ef-pay-btn-clear {
    background: transparent;
    color: var(--ef-muted);
    border: 1px solid var(--ef-border);
    border-radius: 9px;
    padding: .55rem .9rem;
    font-size: .875rem;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: all .18s;
}
.ef-pay-btn-clear:hover { border-color: var(--ef-danger); color: var(--ef-danger); }

/* ── Transaction list container ────────────────────────── */
.ef-pay-list {
    background: #fff;
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.ef-pay-list-head {
    padding: .9rem 1.4rem;
    border-bottom: 1px solid var(--ef-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}
.ef-pay-list-title {
    font-size: .82rem;
    font-weight: 700;
    color: var(--ef-ink);
    letter-spacing: .02em;
    text-transform: uppercase;
}
.ef-pay-list-meta { font-size: .78rem; color: var(--ef-muted); }
.ef-pay-list-meta strong { color: var(--ef-gold); font-weight: 700; }

/* ── Transaction row ───────────────────────────────────── */
.ef-pay-row {
    display: grid;
    grid-template-columns: 3fr 2fr auto;
    gap: 1rem;
    align-items: center;
    padding: 1.05rem 1.4rem;
    border-bottom: 1px solid var(--ef-border);
    transition: background .15s var(--ef-ease);
    position: relative;
}
.ef-pay-row:last-child { border-bottom: none; }
.ef-pay-row:hover { background: rgba(160,114,56,.025); }
@media (max-width: 767px) {
    .ef-pay-row {
        grid-template-columns: 1fr;
        gap: .65rem;
        padding: 1rem 1.1rem;
    }
}

/* Row left — identity */
.ef-pay-row-left { display: flex; gap: .85rem; align-items: flex-start; min-width: 0; }
.ef-pay-mode-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem;
    flex-shrink: 0;
}
.ef-pay-mode-icon.--cash  { background: rgba(22,163,74,.1);   color: #15803d; }
.ef-pay-mode-icon.--upi   { background: rgba(99,102,241,.1);  color: #4338ca; }
.ef-pay-mode-icon.--bank  { background: rgba(14,165,233,.1);  color: #0369a1; }
.ef-pay-mode-icon.--wallet{ background: rgba(245,158,11,.1);  color: #b45309; }
.ef-pay-row-title {
    font-size: .9rem;
    font-weight: 700;
    color: var(--ef-ink);
    line-height: 1.2;
    margin-bottom: .2rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 280px;
}
.ef-pay-row-title a {
    color: inherit;
    text-decoration: none;
    transition: color .15s;
}
.ef-pay-row-title a:hover { color: var(--ef-gold); }
.ef-pay-row-sub { font-size: .75rem; color: var(--ef-muted); display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
.ef-pay-row-sub .dot { width: 3px; height: 3px; border-radius: 50%; background: var(--ef-border-strong); }

/* Row center — method + ref */
.ef-pay-row-center { display: flex; flex-direction: column; gap: .3rem; }
.ef-pay-mode-badge {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .22rem .65rem;
    border-radius: 6px;
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    width: fit-content;
}
.ef-pay-mode-badge.--cash  { background: rgba(22,163,74,.1);   color: #15803d;  border: 1px solid rgba(22,163,74,.2);  }
.ef-pay-mode-badge.--upi   { background: rgba(99,102,241,.1);  color: #4338ca;  border: 1px solid rgba(99,102,241,.2); }
.ef-pay-mode-badge.--bank  { background: rgba(14,165,233,.1);  color: #0369a1;  border: 1px solid rgba(14,165,233,.2); }
.ef-pay-mode-badge.--wallet{ background: rgba(245,158,11,.1);  color: #92400e;  border: 1px solid rgba(245,158,11,.2); }
.ef-pay-row-ref {
    font-size: .72rem;
    color: var(--ef-muted);
    font-family: monospace;
    letter-spacing: .02em;
}
.ef-pay-row-time {
    font-size: .72rem;
    color: var(--ef-muted);
}

/* Row right — amount + action */
.ef-pay-row-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: .35rem;
}
@media (max-width: 767px) {
    .ef-pay-row-right {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
    .ef-pay-row-center { display: flex; flex-direction: row; gap: .6rem; align-items: center; flex-wrap: wrap; }
}
.ef-pay-amount {
    font-size: 1.2rem;
    font-weight: 800;
    color: var(--ef-ink);
    letter-spacing: -.02em;
    line-height: 1;
}
.ef-pay-settled-badge {
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--ef-success);
    background: rgba(22,163,74,.08);
    border: 1px solid rgba(22,163,74,.2);
    border-radius: 5px;
    padding: .15rem .5rem;
}
.ef-pay-view-btn {
    font-size: .72rem;
    font-weight: 600;
    color: var(--ef-muted);
    text-decoration: none;
    border: 1px solid var(--ef-border);
    border-radius: 6px;
    padding: .22rem .6rem;
    transition: all .15s var(--ef-ease);
    display: inline-flex;
    align-items: center;
    gap: .25rem;
}
.ef-pay-view-btn:hover { border-color: var(--ef-gold); color: var(--ef-gold); background: rgba(160,114,56,.05); }

/* ── Date group header ─────────────────────────────────── */
.ef-pay-date-group {
    padding: .55rem 1.4rem;
    background: var(--ef-faint);
    border-bottom: 1px solid var(--ef-border);
    font-size: .72rem;
    font-weight: 700;
    color: var(--ef-muted);
    letter-spacing: .06em;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.ef-pay-date-group-total { font-size: .75rem; font-weight: 700; color: var(--ef-ink); }

/* ── Empty state ───────────────────────────────────────── */
.ef-pay-empty {
    padding: 4rem 2rem;
    text-align: center;
}
.ef-pay-empty-icon {
    width: 64px; height: 64px;
    border-radius: 16px;
    background: var(--ef-faint);
    border: 1px solid var(--ef-border);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem;
    color: var(--ef-border-strong);
    margin: 0 auto 1.2rem;
}
.ef-pay-empty-title { font-size: 1.05rem; font-weight: 700; color: var(--ef-ink); margin-bottom: .4rem; }
.ef-pay-empty-sub   { font-size: .85rem; color: var(--ef-muted); }

/* ── Pagination ────────────────────────────────────────── */
.ef-pay-pagination {
    background: #fff;
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    padding: .85rem 1.4rem;
    box-shadow: var(--ef-shadow);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .5rem;
    margin-bottom: 1rem;
}
.ef-pay-pagination-info { font-size: .78rem; color: var(--ef-muted); }

/* ── Mobile: row collapsed view ────────────────────────── */
@media (max-width: 767px) {
    .ef-pay-row-left { align-items: center; }
    .ef-pay-row-title { max-width: none; white-space: normal; }
}
</style>
@endpush

@php
    $modeFilter  = $filters['payment_mode'] ?? '';
    $empFilter   = $filters['employee_id']  ?? '';
    $fromFilter  = $filters['from'] ?? '';
    $toFilter    = $filters['to']   ?? '';
    $search      = $filters['search'] ?? '';
    $hasAdv      = $empFilter || $fromFilter || $toFilter;

    $today     = now()->toDateString();
    $weekStart = now()->startOfWeek()->toDateString();
    $weekEnd   = now()->endOfWeek()->toDateString();
    $monStart  = now()->startOfMonth()->toDateString();
    $monEnd    = now()->endOfMonth()->toDateString();

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

{{-- ── Hero ──────────────────────────────────────────────────── --}}
<div class="ef-pay-hero">
    <div>
        <div class="ef-pay-hero-eyebrow">Finance Operations</div>
        <div class="ef-pay-hero-title">Payments</div>
        <div class="ef-pay-hero-sub">Expense settlements and operational transaction tracking</div>
        <div class="ef-pay-hero-date">{{ now()->format('l, j F Y') }}</div>
    </div>
    <div class="ef-pay-hero-actions">
        <a href="{{ route('admin.expense-requests.index') }}" class="ef-pay-btn-ghost">
            <i class="bi bi-receipt"></i> Expense Requests
        </a>
        <a href="{{ route('admin.payments.index') }}" class="ef-pay-btn-ghost">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </a>
    </div>
</div>

{{-- ── KPI strip ───────────────────────────────────────────────── --}}
<div class="ef-pay-strip">
    <div class="ef-pay-kpi">
        <div class="ef-pay-kpi-icon --total"><i class="bi bi-lightning-charge"></i></div>
        <div class="ef-pay-kpi-val">{{ number_format($stats['total_count']) }}</div>
        <div class="ef-pay-kpi-label">Transactions</div>
        <div class="ef-pay-kpi-sub">{{ $fmt($stats['total_amount']) }} total</div>
    </div>
    <div class="ef-pay-kpi">
        <div class="ef-pay-kpi-icon --cash"><i class="bi bi-cash-stack"></i></div>
        <div class="ef-pay-kpi-val --sm">{{ $fmt($stats['cash_total']) }}</div>
        <div class="ef-pay-kpi-label">Cash Payments</div>
    </div>
    <div class="ef-pay-kpi">
        <div class="ef-pay-kpi-icon --upi"><i class="bi bi-phone"></i></div>
        <div class="ef-pay-kpi-val --sm">{{ $fmt($stats['upi_total']) }}</div>
        <div class="ef-pay-kpi-label">UPI Transfers</div>
    </div>
    <div class="ef-pay-kpi">
        <div class="ef-pay-kpi-icon --bank"><i class="bi bi-bank"></i></div>
        <div class="ef-pay-kpi-val --sm">{{ $fmt($stats['bank_total']) }}</div>
        <div class="ef-pay-kpi-label">Bank Transfers</div>
    </div>
    <div class="ef-pay-kpi">
        <div class="ef-pay-kpi-icon --avg"><i class="bi bi-graph-up"></i></div>
        <div class="ef-pay-kpi-val --sm">{{ $fmt($stats['avg_amount']) }}</div>
        <div class="ef-pay-kpi-label">Avg Transaction</div>
    </div>
    <div class="ef-pay-kpi">
        <div class="ef-pay-kpi-icon --month"><i class="bi bi-calendar-month"></i></div>
        <div class="ef-pay-kpi-val --sm">{{ $fmt($stats['monthly_total']) }}</div>
        <div class="ef-pay-kpi-label">This Month</div>
    </div>
</div>

{{-- ── Filter bar ──────────────────────────────────────────────── --}}
<form method="GET" id="payFilterForm" action="{{ route('admin.payments.index') }}">
<div class="ef-pay-filter-bar">

    {{-- Quick chips --}}
    <div class="ef-pay-chips">
        <a href="{{ route('admin.payments.index') }}"
           class="ef-pay-chip {{ !$modeFilter && !$fromFilter && !$toFilter && !$empFilter && !$search ? '--active' : '' }}">
            All Time
        </a>
        <a href="{{ route('admin.payments.index', ['from' => $today, 'to' => $today]) }}"
           class="ef-pay-chip {{ $fromFilter === $today && $toFilter === $today ? '--active' : '' }}">
            Today
        </a>
        <a href="{{ route('admin.payments.index', ['from' => $weekStart, 'to' => $weekEnd]) }}"
           class="ef-pay-chip {{ $fromFilter === $weekStart && $toFilter === $weekEnd ? '--active' : '' }}">
            This Week
        </a>
        <a href="{{ route('admin.payments.index', ['from' => $monStart, 'to' => $monEnd]) }}"
           class="ef-pay-chip {{ $fromFilter === $monStart && $toFilter === $monEnd ? '--active' : '' }}">
            This Month
        </a>
        <span style="width:1px;height:18px;background:var(--ef-border);flex-shrink:0;margin:0 .2rem"></span>
        <a href="{{ route('admin.payments.index', ['payment_mode' => 'cash']) }}"
           class="ef-pay-chip --cash {{ $modeFilter === 'cash' ? '--active' : '' }}">
            <i class="bi bi-cash-stack"></i> Cash
        </a>
        <a href="{{ route('admin.payments.index', ['payment_mode' => 'upi']) }}"
           class="ef-pay-chip --upi {{ $modeFilter === 'upi' ? '--active' : '' }}">
            <i class="bi bi-phone"></i> UPI
        </a>
        <a href="{{ route('admin.payments.index', ['payment_mode' => 'bank_transfer']) }}"
           class="ef-pay-chip --bank {{ $modeFilter === 'bank_transfer' ? '--active' : '' }}">
            <i class="bi bi-bank"></i> Bank
        </a>
    </div>

    {{-- Search + advanced toggle --}}
    <div class="ef-pay-filter-row">
        <div class="ef-pay-search-wrap">
            <i class="bi bi-search ef-pay-search-icon"></i>
            <input type="text" name="search" class="ef-pay-search"
                   placeholder="Search expense title or reference…"
                   value="{{ $search }}">
        </div>
        @if($modeFilter)
            <input type="hidden" name="payment_mode" value="{{ $modeFilter }}">
        @endif
        <button type="button"
                class="ef-pay-adv-toggle {{ $hasAdv ? '--has-filter' : '' }}"
                onclick="payToggleAdv(this)">
            <i class="bi bi-sliders2"></i> Filters
            <span class="ef-pay-adv-dot"></span>
        </button>
        <button type="submit" class="ef-pay-btn-apply">Search</button>
    </div>

    {{-- Advanced --}}
    <div class="ef-pay-adv-panel {{ $hasAdv ? '--open' : '' }}" id="payAdvPanel">
        <div class="ef-pay-adv-inner">
            <select name="employee_id" class="ef-pay-select">
                <option value="">All Employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ $empFilter == $emp->id ? 'selected' : '' }}>
                        {{ $emp->name }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="from" class="ef-pay-date-input"
                   value="{{ $fromFilter }}" title="From date">
            <input type="date" name="to" class="ef-pay-date-input"
                   value="{{ $toFilter }}" title="To date">
            <a href="{{ route('admin.payments.index') }}" class="ef-pay-btn-clear">
                <i class="bi bi-x-lg me-1"></i> Clear
            </a>
        </div>
    </div>

</div>
</form>

{{-- ── Transaction list ────────────────────────────────────────── --}}
<div class="ef-pay-list">
    <div class="ef-pay-list-head">
        <div class="ef-pay-list-title">Transaction Ledger</div>
        <div class="ef-pay-list-meta">
            @if($payments->total() > 0)
                <strong>{{ number_format($payments->total()) }}</strong>
                {{ Str::plural('payment', $payments->total()) }} ·
                <strong>{{ $fmt($stats['total_amount']) }}</strong> total
            @endif
        </div>
    </div>

    @if($payments->isEmpty())
    <div class="ef-pay-empty">
        <div class="ef-pay-empty-icon"><i class="bi bi-credit-card"></i></div>
        <div class="ef-pay-empty-title">No payment records found</div>
        <div class="ef-pay-empty-sub">
            @if($search || $modeFilter || $empFilter || $fromFilter || $toFilter)
                Try different filters or
                <a href="{{ route('admin.payments.index') }}" style="color:var(--ef-gold)">clear all filters</a>.
            @else
                Recorded settlements and transaction history will appear here.
            @endif
        </div>
    </div>
    @else

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
                   : $payment->paid_at->format('l, j F Y'));
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
        {{-- Left: identity --}}
        <div class="ef-pay-row-left">
            <div class="ef-pay-mode-icon {{ $modeCss }}">
                <i class="bi {{ $modeIcon }}"></i>
            </div>
            <div style="min-width:0;flex:1">
                <div class="ef-pay-row-title">
                    @if($req)
                        <a href="{{ route('admin.expense-requests.show', $req) }}">
                            {{ Str::limit($req->title, 45) }}
                        </a>
                    @else
                        <span style="color:var(--ef-muted)">Unlinked Payment</span>
                    @endif
                </div>
                <div class="ef-pay-row-sub">
                    @if($requester)
                        <span><i class="bi bi-person" style="font-size:.65rem"></i> {{ $requester->name }}</span>
                        @if($category)<span class="dot"></span>@endif
                    @endif
                    @if($category)
                        <span>{{ $category->name }}</span>
                    @endif
                    @if($payment->payer)
                        <span class="dot"></span>
                        <span>Paid by {{ $payment->payer->name }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Center: method + ref + time --}}
        <div class="ef-pay-row-center">
            <span class="ef-pay-mode-badge {{ $modeCss }}">
                <i class="bi {{ $modeIcon }}" style="font-size:.65rem"></i>
                {{ $modeLabel }}
            </span>
            @if($payment->transaction_reference)
                <div class="ef-pay-row-ref">
                    <i class="bi bi-hash" style="font-size:.6rem"></i>{{ $payment->transaction_reference }}
                </div>
            @endif
            <div class="ef-pay-row-time">{{ $payment->paid_at->format('h:i A') }}</div>
        </div>

        {{-- Right: amount + status + action --}}
        <div class="ef-pay-row-right">
            <div class="ef-pay-amount">₹{{ number_format($payment->amount, 0) }}</div>
            <span class="ef-pay-settled-badge">Settled</span>
            @if($req)
                <a href="{{ route('admin.expense-requests.show', $req) }}" class="ef-pay-view-btn">
                    <i class="bi bi-arrow-right" style="font-size:.6rem"></i> View
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

@push('scripts')
<script>
function payToggleAdv(btn) {
    const panel = document.getElementById('payAdvPanel');
    panel.classList.toggle('--open');
    btn.classList.toggle('--has-filter');
}
</script>
@endpush
</x-admin-layout>
