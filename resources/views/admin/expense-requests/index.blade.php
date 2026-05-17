<x-admin-layout title="Expense Requests">
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
.ef-exp-hero {
    background: linear-gradient(135deg, #131210 0%, #211e18 55%, #2e2718 100%);
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
.ef-exp-hero::before {
    content: '';
    position: absolute;
    right: 3rem; top: -2rem;
    width: 120px; height: 120px;
    border-radius: 50%;
    background: rgba(160,114,56,.07);
    pointer-events: none;
}
.ef-exp-hero::after {
    content: '';
    position: absolute;
    right: 6rem; top: 1rem;
    width: 60px; height: 60px;
    border-radius: 50%;
    background: rgba(160,114,56,.05);
    pointer-events: none;
}
.ef-exp-hero-eyebrow {
    font-size: .72rem;
    font-weight: 600;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--ef-gold);
    margin-bottom: .4rem;
}
.ef-exp-hero-title {
    font-size: clamp(1.7rem, 3.5vw, 2.6rem);
    font-weight: 700;
    color: #fff;
    line-height: 1.1;
    letter-spacing: -.02em;
}
.ef-exp-hero-sub  { color: rgba(255,255,255,.45); font-size: .875rem; margin-top: .35rem; }
.ef-exp-hero-date { color: rgba(255,255,255,.25); font-size: .75rem; margin-top: .5rem; letter-spacing: .02em; }
.ef-exp-hero-actions { display: flex; gap: .5rem; flex-wrap: wrap; align-items: center; }
.ef-exp-btn-gold {
    background: var(--ef-gold);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: .52rem 1.1rem;
    font-size: .82rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .2s var(--ef-ease), transform .15s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    white-space: nowrap;
}
.ef-exp-btn-gold:hover { background: var(--ef-gold-hi); color: #fff; transform: translateY(-1px); }
.ef-exp-btn-ghost {
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
.ef-exp-btn-ghost:hover { background: rgba(255,255,255,.16); color: #fff; }
.ef-exp-alert-pill {
    background: rgba(217,119,6,.18);
    border: 1px solid rgba(217,119,6,.35);
    color: #fbbf24;
    border-radius: 20px;
    padding: .38rem .9rem;
    font-size: .8rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    transition: background .2s;
}
.ef-exp-alert-pill:hover { background: rgba(217,119,6,.28); color: #fbbf24; }

/* ── KPI strip ─────────────────────────────────────────── */
.ef-exp-strip {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: .85rem;
    margin-bottom: 1.5rem;
}
@media (max-width: 1399px) { .ef-exp-strip { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 767px)  { .ef-exp-strip { grid-template-columns: repeat(2, 1fr); } }
.ef-exp-kpi {
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
a.ef-exp-kpi:hover { box-shadow: var(--ef-shadow-hover); transform: translateY(-2px); }
.ef-exp-kpi-icon {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem;
    margin-bottom: .65rem;
}
.ef-exp-kpi-icon.--pending  { background: rgba(217,119,6,.12);  color: #b45309; }
.ef-exp-kpi-icon.--approved { background: rgba(22,163,74,.10);  color: #15803d; }
.ef-exp-kpi-icon.--settled  { background: rgba(99,102,241,.10); color: #4338ca; }
.ef-exp-kpi-icon.--rejected { background: rgba(192,57,43,.10);  color: var(--ef-danger); }
.ef-exp-kpi-icon.--total    { background: rgba(160,114,56,.12); color: var(--ef-gold); }
.ef-exp-kpi-icon.--month    { background: rgba(107,114,128,.1); color: #374151; }
.ef-exp-kpi-val { font-size: 1.35rem; font-weight: 800; color: var(--ef-ink); line-height: 1; letter-spacing: -.02em; }
.ef-exp-kpi-val.--warn    { color: #b45309; }
.ef-exp-kpi-val.--danger  { color: var(--ef-danger); }
.ef-exp-kpi-val.--sm      { font-size: 1.05rem; }
.ef-exp-kpi-label { font-size: .75rem; color: var(--ef-muted); margin-top: .22rem; }

/* ── Filter bar ────────────────────────────────────────── */
.ef-exp-filter-bar {
    background: #fff;
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    padding: 1rem 1.2rem;
    box-shadow: var(--ef-shadow);
    margin-bottom: 1.5rem;
}
.ef-exp-chips {
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
.ef-exp-chips::-webkit-scrollbar { display: none; }
.ef-exp-chip {
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
.ef-exp-chip:hover   { border-color: var(--ef-gold); color: var(--ef-gold); background: rgba(160,114,56,.06); }
.ef-exp-chip.--active { background: var(--ef-gold); border-color: var(--ef-gold); color: #fff; }
.ef-exp-chip.--pending  { background: rgba(217,119,6,.08);  border-color: rgba(217,119,6,.3);  color: #92400e; }
.ef-exp-chip.--pending.--active  { background: #b45309; border-color: #b45309; color: #fff; }
.ef-exp-chip.--approved { background: rgba(22,163,74,.08);  border-color: rgba(22,163,74,.3);  color: #15803d; }
.ef-exp-chip.--approved.--active { background: #15803d; border-color: #15803d; color: #fff; }
.ef-exp-chip.--paid     { background: rgba(99,102,241,.08); border-color: rgba(99,102,241,.3); color: #4338ca; }
.ef-exp-chip.--paid.--active     { background: #4338ca; border-color: #4338ca; color: #fff; }
.ef-exp-chip.--rejected { background: rgba(192,57,43,.07);  border-color: rgba(192,57,43,.3);  color: var(--ef-danger); }
.ef-exp-chip.--rejected.--active { background: var(--ef-danger); border-color: var(--ef-danger); color: #fff; }

.ef-exp-filter-row { display: flex; gap: .55rem; align-items: center; flex-wrap: wrap; }
.ef-exp-search-wrap { position: relative; flex: 1; min-width: 200px; }
.ef-exp-search-icon {
    position: absolute; left: .8rem; top: 50%;
    transform: translateY(-50%);
    color: var(--ef-muted); font-size: .85rem; pointer-events: none;
}
.ef-exp-search {
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
.ef-exp-search::placeholder { color: #b5afa8; }
.ef-exp-search:focus { border-color: var(--ef-gold); background: #fff; box-shadow: 0 0 0 3px rgba(160,114,56,.12); }
.ef-exp-select {
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
.ef-exp-select:focus { border-color: var(--ef-gold); background: #fff; }
.ef-exp-adv-toggle {
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
.ef-exp-adv-toggle:hover { border-color: var(--ef-gold); color: var(--ef-gold); background: rgba(160,114,56,.06); }
.ef-exp-adv-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--ef-gold); display: none;
    position: absolute; top: 6px; right: 6px;
}
.ef-exp-adv-toggle.--has-filter .ef-exp-adv-dot { display: block; }
.ef-exp-adv-panel { overflow: hidden; max-height: 0; transition: max-height .35s var(--ef-ease); }
.ef-exp-adv-panel.--open { max-height: 130px; }
.ef-exp-adv-inner {
    padding-top: .75rem;
    border-top: 1px solid var(--ef-border);
    margin-top: .75rem;
    display: flex;
    gap: .55rem;
    flex-wrap: wrap;
    align-items: center;
}
.ef-exp-date-input {
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
.ef-exp-date-input:focus { border-color: var(--ef-gold); background: #fff; }
.ef-exp-btn-apply {
    background: var(--ef-gold); color: #fff; border: none;
    border-radius: 9px; padding: .55rem 1.1rem;
    font-size: .875rem; font-weight: 600; cursor: pointer;
    transition: background .18s;
}
.ef-exp-btn-apply:hover { background: var(--ef-gold-hi); }
.ef-exp-btn-clear {
    background: transparent; color: var(--ef-muted);
    border: 1px solid var(--ef-border); border-radius: 9px;
    padding: .55rem .9rem; font-size: .875rem; cursor: pointer;
    text-decoration: none; display: inline-flex; align-items: center;
    transition: all .18s;
}
.ef-exp-btn-clear:hover { border-color: var(--ef-danger); color: var(--ef-danger); }

/* ── Request list ──────────────────────────────────────── */
.ef-exp-list {
    background: #fff;
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.ef-exp-list-head {
    padding: .9rem 1.4rem;
    border-bottom: 1px solid var(--ef-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}
.ef-exp-list-title { font-size: .82rem; font-weight: 700; color: var(--ef-ink); letter-spacing: .02em; text-transform: uppercase; }
.ef-exp-list-meta  { font-size: .78rem; color: var(--ef-muted); }
.ef-exp-list-meta strong { color: var(--ef-gold); font-weight: 700; }

/* ── Request row ───────────────────────────────────────── */
.ef-exp-row {
    display: grid;
    grid-template-columns: 1fr 180px auto;
    gap: 1.2rem;
    align-items: center;
    padding: 1.1rem 1.4rem 1.1rem 1.55rem;
    border-bottom: 1px solid var(--ef-border);
    transition: background .15s var(--ef-ease);
    position: relative;
}
.ef-exp-row:last-child { border-bottom: none; }
.ef-exp-row:hover { background: rgba(160,114,56,.022); }
/* priority left accent */
.ef-exp-row::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    border-radius: 0 2px 2px 0;
    background: var(--ef-border);
    transition: background .2s;
}
.ef-exp-row.--urgent::before  { background: #dc2626; }
.ef-exp-row.--high::before    { background: #d97706; }
.ef-exp-row.--medium::before  { background: #6366f1; }
.ef-exp-row.--low::before     { background: #9ca3af; }

@media (max-width: 991px) {
    .ef-exp-row { grid-template-columns: 1fr auto; gap: .8rem; }
    .ef-exp-row-center { display: none; }
}
@media (max-width: 639px) {
    .ef-exp-row { grid-template-columns: 1fr; gap: .7rem; padding: 1rem 1.1rem 1rem 1.4rem; }
    .ef-exp-row-right { flex-direction: row; align-items: center; justify-content: space-between; }
}

/* Row left */
.ef-exp-row-left { min-width: 0; }
.ef-exp-row-title {
    font-size: .925rem;
    font-weight: 700;
    color: var(--ef-ink);
    line-height: 1.25;
    margin-bottom: .22rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ef-exp-row-title a { color: inherit; text-decoration: none; transition: color .15s; }
.ef-exp-row-title a:hover { color: var(--ef-gold); }
.ef-exp-row-meta {
    display: flex;
    align-items: center;
    gap: .5rem;
    flex-wrap: wrap;
    font-size: .74rem;
    color: var(--ef-muted);
}
.ef-exp-row-meta .sep { width: 3px; height: 3px; border-radius: 50%; background: rgba(107,101,96,.35); flex-shrink: 0; }

/* Priority dot */
.ef-exp-pri-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
}
.ef-exp-pri-dot.--urgent { background: #dc2626; box-shadow: 0 0 0 2px rgba(220,38,38,.2); }
.ef-exp-pri-dot.--high   { background: #d97706; }
.ef-exp-pri-dot.--medium { background: #6366f1; }
.ef-exp-pri-dot.--low    { background: #9ca3af; }

/* Bills indicator */
.ef-exp-bills-chip {
    display: inline-flex; align-items: center; gap: .25rem;
    font-size: .68rem; font-weight: 600;
    background: rgba(22,163,74,.08); border: 1px solid rgba(22,163,74,.2);
    color: #15803d; border-radius: 5px; padding: .1rem .4rem;
}
.ef-exp-no-bills-chip {
    display: inline-flex; align-items: center; gap: .25rem;
    font-size: .68rem; font-weight: 600;
    background: rgba(107,114,128,.07); border: 1px solid rgba(107,114,128,.18);
    color: var(--ef-muted); border-radius: 5px; padding: .1rem .4rem;
}

/* Row center */
.ef-exp-row-center { display: flex; flex-direction: column; gap: .3rem; }
.ef-exp-row-date { font-size: .74rem; color: var(--ef-muted); }
.ef-exp-row-date strong { color: var(--ef-ink); font-weight: 600; }

/* Row right */
.ef-exp-row-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: .4rem;
    min-width: 120px;
}
.ef-exp-amount {
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--ef-ink);
    letter-spacing: -.02em;
    line-height: 1;
    white-space: nowrap;
}

/* ── Status badges ─────────────────────────────────────── */
.ef-exp-status {
    font-size: .67rem;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    border-radius: 6px;
    padding: .2rem .6rem;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: .3rem;
}
.ef-exp-status.--pending  { background: rgba(217,119,6,.1);  border: 1px solid rgba(217,119,6,.22); color: #92400e; }
.ef-exp-status.--approved { background: rgba(22,163,74,.1);  border: 1px solid rgba(22,163,74,.22); color: #15803d; }
.ef-exp-status.--rejected { background: rgba(192,57,43,.08); border: 1px solid rgba(192,57,43,.2);  color: var(--ef-danger); }
.ef-exp-status.--paid     { background: rgba(99,102,241,.1); border: 1px solid rgba(99,102,241,.2); color: #4338ca; }
.ef-exp-status.--pending-payment  { background: rgba(14,165,233,.1); border: 1px solid rgba(14,165,233,.22); color: #0369a1; }
.ef-exp-status.--reimbursement-pending { background: rgba(139,92,246,.1); border: 1px solid rgba(139,92,246,.2); color: #6d28d9; }
.ef-exp-status.--reimbursed { background: rgba(22,163,74,.1); border: 1px solid rgba(22,163,74,.22); color: #15803d; }
.ef-exp-status.--completed  { background: rgba(107,114,128,.08); border: 1px solid rgba(107,114,128,.18); color: #4b5563; }

/* Review button */
.ef-exp-review-btn {
    font-size: .74rem;
    font-weight: 600;
    color: var(--ef-muted);
    text-decoration: none;
    border: 1px solid var(--ef-border);
    border-radius: 7px;
    padding: .28rem .75rem;
    transition: all .15s var(--ef-ease);
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    white-space: nowrap;
}
.ef-exp-review-btn:hover { border-color: var(--ef-gold); color: var(--ef-gold); background: rgba(160,114,56,.05); }

/* Urgent pulse */
@keyframes ef-pulse {
    0%,100% { opacity: 1; }
    50%      { opacity: .4; }
}
.ef-exp-row.--urgent .ef-exp-pri-dot { animation: ef-pulse 1.8s ease-in-out infinite; }

/* ── Empty state ───────────────────────────────────────── */
.ef-exp-empty {
    padding: 4rem 2rem; text-align: center;
}
.ef-exp-empty-icon {
    width: 64px; height: 64px; border-radius: 16px;
    background: var(--ef-faint); border: 1px solid var(--ef-border);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem; color: var(--ef-border-strong);
    margin: 0 auto 1.2rem;
}
.ef-exp-empty-title { font-size: 1.05rem; font-weight: 700; color: var(--ef-ink); margin-bottom: .4rem; }
.ef-exp-empty-sub   { font-size: .85rem; color: var(--ef-muted); }

/* ── Pagination ────────────────────────────────────────── */
.ef-exp-pagination {
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
.ef-exp-pagination-info { font-size: .78rem; color: var(--ef-muted); }
</style>
@endpush

@php
    $statusFilter   = $filters['status']      ?? '';
    $catFilter      = $filters['category_id'] ?? '';
    $empFilter      = $filters['employee_id'] ?? '';
    $priFilter      = $filters['priority']    ?? '';
    $fromFilter     = $filters['from'] ?? '';
    $toFilter       = $filters['to']   ?? '';
    $search         = $filters['search'] ?? '';
    $hasAdv         = $catFilter || $empFilter || $priFilter || $fromFilter || $toFilter;

    $today     = now()->toDateString();
    $weekStart = now()->startOfWeek()->toDateString();
    $weekEnd   = now()->endOfWeek()->toDateString();
    $monStart  = now()->startOfMonth()->toDateString();
    $monEnd    = now()->endOfMonth()->toDateString();

    $statusCssMap = [
        'pending'               => '--pending',
        'approved'              => '--approved',
        'rejected'              => '--rejected',
        'paid'                  => '--paid',
        'pending_payment'       => '--pending-payment',
        'reimbursement_pending' => '--reimbursement-pending',
        'reimbursed'            => '--reimbursed',
        'completed'             => '--completed',
    ];
    $statusLabelMap = [
        'pending'               => 'Pending Review',
        'approved'              => 'Approved',
        'rejected'              => 'Rejected',
        'paid'                  => 'Paid',
        'pending_payment'       => 'Pending Payment',
        'reimbursement_pending' => 'Reimb. Pending',
        'reimbursed'            => 'Reimbursed',
        'completed'             => 'Completed',
    ];
    $statusIconMap = [
        'pending'               => 'bi-clock',
        'approved'              => 'bi-check-circle',
        'rejected'              => 'bi-x-circle',
        'paid'                  => 'bi-check2-all',
        'pending_payment'       => 'bi-hourglass-split',
        'reimbursement_pending' => 'bi-arrow-return-left',
        'reimbursed'            => 'bi-check-circle-fill',
        'completed'             => 'bi-flag-fill',
    ];

    $fmt = fn(float $v): string =>
        $v >= 10000000 ? '₹' . number_format($v/10000000, 1) . 'Cr'
      : ($v >= 100000  ? '₹' . number_format($v/100000, 1) . 'L'
      :                  '₹' . number_format($v, 0));
@endphp

{{-- ── Hero ──────────────────────────────────────────────────── --}}
<div class="ef-exp-hero">
    <div>
        <div class="ef-exp-hero-eyebrow">Expense Operations</div>
        <div class="ef-exp-hero-title">Expense Requests</div>
        <div class="ef-exp-hero-sub">Employee reimbursements and operational expense tracking</div>
        <div class="ef-exp-hero-date">{{ now()->format('l, j F Y') }}</div>
    </div>
    <div class="ef-exp-hero-actions">
        @if($stats['pending_count'] > 0)
            <a href="{{ route('admin.expense-requests.index', ['status' => 'pending']) }}"
               class="ef-exp-alert-pill">
                <i class="bi bi-clock-fill"></i>
                {{ $stats['pending_count'] }} Awaiting Review
            </a>
        @endif
        <a href="{{ route('admin.expense-requests.index') }}" class="ef-exp-btn-ghost">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </a>
        <a href="{{ route('admin.payments.index') }}" class="ef-exp-btn-ghost">
            <i class="bi bi-credit-card"></i> Payments
        </a>
    </div>
</div>

{{-- ── KPI strip ───────────────────────────────────────────────── --}}
<div class="ef-exp-strip">
    <a href="{{ route('admin.expense-requests.index', ['status' => 'pending']) }}" class="ef-exp-kpi">
        <div class="ef-exp-kpi-icon --pending"><i class="bi bi-clock"></i></div>
        <div class="ef-exp-kpi-val {{ $stats['pending_count'] > 0 ? '--warn' : '' }}">
            {{ number_format($stats['pending_count']) }}
        </div>
        <div class="ef-exp-kpi-label">Pending Review</div>
    </a>
    <a href="{{ route('admin.expense-requests.index', ['status' => 'approved']) }}" class="ef-exp-kpi">
        <div class="ef-exp-kpi-icon --approved"><i class="bi bi-check-circle"></i></div>
        <div class="ef-exp-kpi-val">{{ number_format($stats['approved_count']) }}</div>
        <div class="ef-exp-kpi-label">Approved</div>
    </a>
    <a href="{{ route('admin.expense-requests.index', ['status' => 'paid']) }}" class="ef-exp-kpi">
        <div class="ef-exp-kpi-icon --settled"><i class="bi bi-check2-all"></i></div>
        <div class="ef-exp-kpi-val">{{ number_format($stats['settled_count']) }}</div>
        <div class="ef-exp-kpi-label">Settled</div>
    </a>
    <a href="{{ route('admin.expense-requests.index', ['status' => 'rejected']) }}" class="ef-exp-kpi">
        <div class="ef-exp-kpi-icon --rejected"><i class="bi bi-x-circle"></i></div>
        <div class="ef-exp-kpi-val {{ $stats['rejected_count'] > 0 ? '--danger' : '' }}">
            {{ number_format($stats['rejected_count']) }}
        </div>
        <div class="ef-exp-kpi-label">Rejected</div>
    </a>
    <div class="ef-exp-kpi">
        <div class="ef-exp-kpi-icon --total"><i class="bi bi-currency-rupee"></i></div>
        <div class="ef-exp-kpi-val --sm">{{ $fmt($stats['total_amount']) }}</div>
        <div class="ef-exp-kpi-label">Total Value</div>
    </div>
    <div class="ef-exp-kpi">
        <div class="ef-exp-kpi-icon --month"><i class="bi bi-calendar-month"></i></div>
        <div class="ef-exp-kpi-val --sm">{{ $fmt($stats['monthly_total']) }}</div>
        <div class="ef-exp-kpi-label">This Month</div>
    </div>
</div>

{{-- ── Filter bar ──────────────────────────────────────────────── --}}
<form method="GET" id="expFilterForm" action="{{ route('admin.expense-requests.index') }}">
<div class="ef-exp-filter-bar">

    {{-- Quick chips --}}
    <div class="ef-exp-chips">
        <a href="{{ route('admin.expense-requests.index') }}"
           class="ef-exp-chip {{ !$statusFilter && !$fromFilter && !$toFilter && !$empFilter && !$search && !$catFilter && !$priFilter ? '--active' : '' }}">
            All
        </a>
        <a href="{{ route('admin.expense-requests.index', ['status' => 'pending']) }}"
           class="ef-exp-chip --pending {{ $statusFilter === 'pending' ? '--active' : '' }}">
            <i class="bi bi-clock" style="font-size:.7rem"></i> Pending
            @if($stats['pending_count'] > 0 && $statusFilter !== 'pending')
                <span style="font-size:.68rem;opacity:.8">({{ $stats['pending_count'] }})</span>
            @endif
        </a>
        <a href="{{ route('admin.expense-requests.index', ['status' => 'approved']) }}"
           class="ef-exp-chip --approved {{ $statusFilter === 'approved' ? '--active' : '' }}">
            <i class="bi bi-check-circle" style="font-size:.7rem"></i> Approved
        </a>
        <a href="{{ route('admin.expense-requests.index', ['status' => 'paid']) }}"
           class="ef-exp-chip --paid {{ $statusFilter === 'paid' ? '--active' : '' }}">
            <i class="bi bi-check2-all" style="font-size:.7rem"></i> Paid
        </a>
        <a href="{{ route('admin.expense-requests.index', ['status' => 'rejected']) }}"
           class="ef-exp-chip --rejected {{ $statusFilter === 'rejected' ? '--active' : '' }}">
            <i class="bi bi-x-circle" style="font-size:.7rem"></i> Rejected
        </a>
        <span style="width:1px;height:18px;background:var(--ef-border);flex-shrink:0;margin:0 .2rem"></span>
        <a href="{{ route('admin.expense-requests.index', ['from' => $today, 'to' => $today]) }}"
           class="ef-exp-chip {{ $fromFilter === $today && $toFilter === $today ? '--active' : '' }}">
            Today
        </a>
        <a href="{{ route('admin.expense-requests.index', ['from' => $weekStart, 'to' => $weekEnd]) }}"
           class="ef-exp-chip {{ $fromFilter === $weekStart && $toFilter === $weekEnd ? '--active' : '' }}">
            This Week
        </a>
        <a href="{{ route('admin.expense-requests.index', ['from' => $monStart, 'to' => $monEnd]) }}"
           class="ef-exp-chip {{ $fromFilter === $monStart && $toFilter === $monEnd ? '--active' : '' }}">
            This Month
        </a>
    </div>

    {{-- Search row --}}
    <div class="ef-exp-filter-row">
        <div class="ef-exp-search-wrap">
            <i class="bi bi-search ef-exp-search-icon"></i>
            <input type="text" name="search" class="ef-exp-search"
                   placeholder="Search expense title…" value="{{ $search }}">
        </div>
        @if($statusFilter)<input type="hidden" name="status" value="{{ $statusFilter }}">@endif
        <button type="button"
                class="ef-exp-adv-toggle {{ $hasAdv ? '--has-filter' : '' }}"
                onclick="expToggleAdv(this)">
            <i class="bi bi-sliders2"></i> Filters
            <span class="ef-exp-adv-dot"></span>
        </button>
        <button type="submit" class="ef-exp-btn-apply">Search</button>
    </div>

    {{-- Advanced --}}
    <div class="ef-exp-adv-panel {{ $hasAdv ? '--open' : '' }}" id="expAdvPanel">
        <div class="ef-exp-adv-inner">
            <select name="category_id" class="ef-exp-select">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $catFilter == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="employee_id" class="ef-exp-select">
                <option value="">All Employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ $empFilter == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                @endforeach
            </select>
            <select name="priority" class="ef-exp-select" style="min-width:110px">
                <option value="">All Priority</option>
                @foreach(['urgent','high','medium','low'] as $p)
                    <option value="{{ $p }}" {{ $priFilter === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
            <input type="date" name="from" class="ef-exp-date-input" value="{{ $fromFilter }}" title="From">
            <input type="date" name="to"   class="ef-exp-date-input" value="{{ $toFilter }}"   title="To">
            <a href="{{ route('admin.expense-requests.index') }}" class="ef-exp-btn-clear">
                <i class="bi bi-x-lg me-1"></i> Clear
            </a>
        </div>
    </div>
</div>
</form>

{{-- ── Request list ────────────────────────────────────────────── --}}
<div class="ef-exp-list">
    <div class="ef-exp-list-head">
        <div class="ef-exp-list-title">Request Ledger</div>
        <div class="ef-exp-list-meta">
            @if($requests->total() > 0)
                <strong>{{ number_format($requests->total()) }}</strong>
                {{ Str::plural('request', $requests->total()) }} ·
                <strong>{{ $fmt($stats['total_amount']) }}</strong> total
            @endif
        </div>
    </div>

    @if($requests->isEmpty())
    <div class="ef-exp-empty">
        <div class="ef-exp-empty-icon"><i class="bi bi-file-earmark-text"></i></div>
        <div class="ef-exp-empty-title">No expense requests found</div>
        <div class="ef-exp-empty-sub">
            @if($search || $statusFilter || $catFilter || $empFilter || $priFilter || $fromFilter || $toFilter)
                Try different filters or
                <a href="{{ route('admin.expense-requests.index') }}" style="color:var(--ef-gold)">clear all filters</a>.
            @else
                Employee reimbursement requests and operational expenses will appear here.
            @endif
        </div>
    </div>
    @else

    @foreach($requests as $req)
    @php
        $status    = $req->status;
        $priority  = $req->priority ?? 'low';
        $statusCss = $statusCssMap[$status]    ?? '--pending';
        $statusLbl = $statusLabelMap[$status]  ?? ucfirst(str_replace('_', ' ', $status));
        $statusIco = $statusIconMap[$status]   ?? 'bi-clock';
        $isUrgent  = $priority === 'urgent';
        $hasBills  = ($req->bills_count ?? 0) > 0;
    @endphp
    <div class="ef-exp-row --{{ $priority }}">
        {{-- Left: identity --}}
        <div class="ef-exp-row-left">
            <div class="ef-exp-row-title">
                <a href="{{ route('admin.expense-requests.show', $req) }}">
                    {{ Str::limit($req->title, 55) }}
                </a>
            </div>
            <div class="ef-exp-row-meta">
                <span class="ef-exp-pri-dot --{{ $priority }}"></span>
                <span>{{ ucfirst($priority) }}</span>
                @if($req->requester)
                    <span class="sep"></span>
                    <span><i class="bi bi-person" style="font-size:.65rem"></i> {{ $req->requester->name }}</span>
                @endif
                @if($req->category)
                    <span class="sep"></span>
                    <span>{{ $req->category->name }}</span>
                @endif
                @if($req->vendor)
                    <span class="sep"></span>
                    <span><i class="bi bi-shop" style="font-size:.65rem"></i> {{ Str::limit($req->vendor->name, 20) }}</span>
                @endif
                <span class="sep"></span>
                @if($hasBills)
                    <span class="ef-exp-bills-chip">
                        <i class="bi bi-paperclip" style="font-size:.6rem"></i>
                        {{ $req->bills_count }} {{ Str::plural('bill', $req->bills_count) }}
                    </span>
                @else
                    <span class="ef-exp-no-bills-chip">
                        <i class="bi bi-paperclip" style="font-size:.6rem"></i> No bill
                    </span>
                @endif
            </div>
        </div>

        {{-- Center: date + notes --}}
        <div class="ef-exp-row-center">
            <div class="ef-exp-row-date">
                <strong>{{ $req->created_at->format('j M Y') }}</strong>
            </div>
            <div class="ef-exp-row-date">{{ $req->created_at->format('h:i A') }}</div>
            @if($req->approved_at)
                <div class="ef-exp-row-date" style="color:#15803d;font-size:.69rem">
                    <i class="bi bi-check-circle" style="font-size:.65rem"></i>
                    Approved {{ $req->approved_at->format('j M') }}
                </div>
            @endif
        </div>

        {{-- Right: amount + status + action --}}
        <div class="ef-exp-row-right">
            <div class="ef-exp-amount">₹{{ number_format($req->amount, 0) }}</div>
            <span class="ef-exp-status {{ $statusCss }}">
                <i class="bi {{ $statusIco }}" style="font-size:.6rem"></i>
                {{ $statusLbl }}
            </span>
            <a href="{{ route('admin.expense-requests.show', $req) }}" class="ef-exp-review-btn">
                @if($req->isPending())
                    <i class="bi bi-eye" style="font-size:.65rem"></i> Review
                @else
                    <i class="bi bi-arrow-right" style="font-size:.65rem"></i> View
                @endif
            </a>
        </div>
    </div>
    @endforeach

    @endif
</div>

{{-- ── Pagination ──────────────────────────────────────────────── --}}
@if($requests->hasPages())
<div class="ef-exp-pagination">
    <div class="ef-exp-pagination-info">
        Showing {{ $requests->firstItem() }}–{{ $requests->lastItem() }}
        of {{ number_format($requests->total()) }} requests
    </div>
    {{ $requests->links() }}
</div>
@endif

@push('scripts')
<script>
function expToggleAdv(btn) {
    const panel = document.getElementById('expAdvPanel');
    panel.classList.toggle('--open');
    btn.classList.toggle('--has-filter');
}
</script>
@endpush
</x-admin-layout>
