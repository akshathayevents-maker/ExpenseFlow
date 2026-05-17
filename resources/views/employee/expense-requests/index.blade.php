<x-admin-layout title="My Requests">
@push('styles')
<style>
/* ════════════════════════════════════════════════════════════
   MY REQUESTS — ef-req-* namespace
   PhonePe / Swiggy order history feel
   ════════════════════════════════════════════════════════════ */
:root {
    --req-green:    #1a6645;
    --req-green-hi: #22845a;
    --req-blue:     #1d4ed8;
    --req-indigo:   #4338ca;
    --req-red:      #dc2626;
    --req-amber:    #d97706;
    --req-cyan:     #0891b2;
    --req-purple:   #7c3aed;
    --req-text:     #111827;
    --req-sub:      #6b7280;
    --req-border:   #e5e7eb;
    --req-surface:  #fff;
    --req-bg:       #f3f4f6;
}

/* ── Page wrapper ───────────────────────────────────────── */
.ef-req-page { max-width: 660px; margin: 0 auto; padding-bottom: 80px; }

/* ── Hero ───────────────────────────────────────────────── */
.ef-req-hero {
    background:
        radial-gradient(ellipse at 110% -10%, rgba(99,102,241,.25) 0%, transparent 50%),
        radial-gradient(ellipse at -10% 120%, rgba(29,78,216,.18) 0%, transparent 55%),
        linear-gradient(135deg, #0d1220 0%, #101827 40%, #1d2d50 100%);
    border-radius: 24px;
    padding: 26px 28px 24px;
    position: relative;
    overflow: hidden;
    margin-bottom: 14px;
    box-shadow: 0 8px 32px rgba(13,18,32,.45), 0 2px 8px rgba(13,18,32,.2);
}
.ef-req-hero::before {
    content: '';
    position: absolute;
    width: 220px; height: 220px;
    background: radial-gradient(circle, rgba(99,102,241,.15) 0%, transparent 70%);
    right: -40px; top: -60px;
    pointer-events: none;
}
.ef-req-hero::after {
    content: '';
    position: absolute;
    width: 140px; height: 140px;
    background: radial-gradient(circle, rgba(29,78,216,.12) 0%, transparent 70%);
    left: 60px; bottom: -40px;
    pointer-events: none;
}
.ef-req-hero-inner {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    position: relative;
    z-index: 1;
}
.ef-req-kicker {
    font-size: .66rem;
    font-weight: 700;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: rgba(165,180,252,.7);
    margin-bottom: 5px;
}
.ef-req-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #f8faff;
    line-height: 1.1;
    margin-bottom: 3px;
}
.ef-req-sub {
    font-size: .78rem;
    color: rgba(255,255,255,.38);
    margin-bottom: 0;
}
.ef-req-hero-stats {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 6px;
    flex-shrink: 0;
}
.ef-req-hero-stat {
    text-align: right;
}
.ef-req-hero-stat-val {
    font-size: 1.3rem;
    font-weight: 800;
    color: #fff;
    line-height: 1;
}
.ef-req-hero-stat-lbl {
    font-size: .62rem;
    font-weight: 600;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: rgba(255,255,255,.35);
}

/* ── Summary strip ─────────────────────────────────────── */
.ef-req-strip {
    display: flex;
    gap: 10px;
    margin-bottom: 14px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    padding-bottom: 2px;
}
.ef-req-strip::-webkit-scrollbar { display: none; }
.ef-req-strip-card {
    flex-shrink: 0;
    background: var(--req-surface);
    border: 1px solid var(--req-border);
    border-radius: 18px;
    padding: 14px 16px;
    min-width: 120px;
    position: relative;
    overflow: hidden;
    transition: box-shadow .15s, transform .15s;
    cursor: default;
}
.ef-req-strip-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.08); transform: translateY(-1px); }
.ef-req-strip-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 18px 18px 0 0;
}
.strip-total::before   { background: #94a3b8; }
.strip-pending::before { background: var(--req-amber); }
.strip-approved::before{ background: var(--req-green); }
.strip-paid::before    { background: var(--req-blue); }
.strip-rejected::before{ background: var(--req-red); }

.ef-req-strip-lbl {
    font-size: .62rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--req-sub);
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 5px;
}
/* Pending pulse dot */
.ef-req-pulse {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: var(--req-amber);
    display: inline-block;
    animation: req-pulse 1.8s ease-in-out infinite;
}
@keyframes req-pulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(217,119,6,.5); }
    50%      { box-shadow: 0 0 0 5px rgba(217,119,6,0); }
}
.ef-req-strip-val {
    font-size: 1.2rem;
    font-weight: 800;
    color: var(--req-text);
    line-height: 1;
}
.ef-req-strip-val.amber  { color: var(--req-amber); }
.ef-req-strip-val.green  { color: var(--req-green); }
.ef-req-strip-val.blue   { color: var(--req-blue); }
.ef-req-strip-val.red    { color: var(--req-red); }

/* ── Search ────────────────────────────────────────────── */
.ef-req-search-wrap {
    position: relative;
    margin-bottom: 12px;
}
.ef-req-search-icon {
    position: absolute;
    left: 14px; top: 50%;
    transform: translateY(-50%);
    color: var(--req-sub);
    font-size: .9rem;
    pointer-events: none;
}
.ef-req-search-input {
    width: 100%;
    background: var(--req-surface);
    border: 1.5px solid var(--req-border);
    border-radius: 14px;
    padding: 11px 42px 11px 40px;
    font-size: .9rem;
    color: var(--req-text);
    outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.ef-req-search-input:focus {
    border-color: var(--req-indigo);
    box-shadow: 0 0 0 3px rgba(67,56,202,.08);
}
.ef-req-search-input::placeholder { color: #9ca3af; }
.ef-req-search-clear {
    position: absolute;
    right: 12px; top: 50%;
    transform: translateY(-50%);
    background: #f3f4f6;
    border: none;
    border-radius: 50%;
    width: 22px; height: 22px;
    display: flex; align-items: center; justify-content: center;
    font-size: .65rem;
    color: var(--req-sub);
    cursor: pointer;
    transition: background .12s;
}
.ef-req-search-clear:hover { background: #e5e7eb; color: var(--req-text); }

/* ── Filter chips ──────────────────────────────────────── */
.ef-req-filter-bar {
    margin-bottom: 14px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.ef-req-filter-bar::-webkit-scrollbar { display: none; }
.ef-req-filter-inner { display: flex; gap: 7px; white-space: nowrap; }
.ef-req-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 7px 14px;
    border-radius: 20px;
    font-size: .76rem;
    font-weight: 600;
    border: 1.5px solid var(--req-border);
    background: var(--req-surface);
    color: var(--req-sub);
    cursor: pointer;
    text-decoration: none;
    transition: all .12s;
    white-space: nowrap;
    flex-shrink: 0;
}
.ef-req-chip:hover { text-decoration: none; color: var(--req-indigo); border-color: var(--req-indigo); }
.ef-req-chip.active                  { background: var(--req-indigo); border-color: var(--req-indigo); color: #fff; }
.ef-req-chip.chip-pending.active     { background: var(--req-amber);  border-color: var(--req-amber); color: #fff; }
.ef-req-chip.chip-approved.active    { background: var(--req-green);  border-color: var(--req-green); color: #fff; }
.ef-req-chip.chip-paid.active        { background: var(--req-blue);   border-color: var(--req-blue); color: #fff; }
.ef-req-chip.chip-rejected.active    { background: var(--req-red);    border-color: var(--req-red); color: #fff; }
.ef-req-chip.chip-completed.active   { background: var(--req-cyan);   border-color: var(--req-cyan); color: #fff; }
.ef-req-chip-badge {
    background: rgba(0,0,0,.12);
    border-radius: 10px;
    padding: 1px 6px;
    font-size: .65rem;
    font-weight: 700;
}
.ef-req-chip.active .ef-req-chip-badge { background: rgba(255,255,255,.25); }

/* ── Request cards ─────────────────────────────────────── */
.ef-req-list { display: flex; flex-direction: column; gap: 10px; }

.ef-req-card {
    background: var(--req-surface);
    border: 1px solid #f0f0f2;
    border-radius: 20px;
    overflow: hidden;
    transition: box-shadow .18s, transform .15s;
    text-decoration: none;
    display: block;
}
.ef-req-card:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,.08);
    transform: translateY(-1px);
    text-decoration: none;
    border-color: #e5e7eb;
}
.ef-req-card-stripe { height: 3px; }

/* Status stripe colors */
.stripe-pending               { background: var(--req-amber); }
.stripe-pending_payment       { background: var(--req-cyan); }
.stripe-approved              { background: var(--req-green); }
.stripe-paid                  { background: var(--req-blue); }
.stripe-reimbursement_pending { background: var(--req-purple); }
.stripe-reimbursed            { background: var(--req-green); }
.stripe-completed             { background: var(--req-cyan); }
.stripe-rejected              { background: var(--req-red); }

.ef-req-card-body { padding: 16px 18px; }
.ef-req-card-top {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 12px;
}
.ef-req-card-icon {
    width: 44px; height: 44px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.icon-pending               { background: #fef3c7; color: var(--req-amber); }
.icon-pending_payment       { background: #cffafe; color: var(--req-cyan); }
.icon-approved              { background: #dcfce7; color: var(--req-green); }
.icon-paid                  { background: #dbeafe; color: var(--req-blue); }
.icon-reimbursement_pending { background: #ede9fe; color: var(--req-purple); }
.icon-reimbursed            { background: #dcfce7; color: var(--req-green); }
.icon-completed             { background: #cffafe; color: var(--req-cyan); }
.icon-rejected              { background: #fee2e2; color: var(--req-red); }

.ef-req-card-meta { flex: 1; min-width: 0; }
.ef-req-card-id {
    font-size: .62rem;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: var(--req-sub);
    margin-bottom: 3px;
}
.ef-req-card-title {
    font-size: .95rem;
    font-weight: 700;
    color: var(--req-text);
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ef-req-card-category {
    font-size: .72rem;
    color: var(--req-sub);
    display: flex;
    align-items: center;
    gap: 4px;
}
.ef-req-card-right { text-align: right; flex-shrink: 0; }
.ef-req-card-amount {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--req-text);
    margin-bottom: 4px;
}
.ef-req-status-pill {
    font-size: .62rem;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 12px;
    border: 1px solid;
    white-space: nowrap;
    display: inline-block;
}
.spill-pending               { background: #fef3c7; color: #92400e;   border-color: #fde68a; }
.spill-pending_payment       { background: #cffafe; color: #164e63;   border-color: #a5f3fc; }
.spill-approved              { background: #dcfce7; color: #14532d;   border-color: #bbf7d0; }
.spill-paid                  { background: #dbeafe; color: #1e3a8a;   border-color: #bfdbfe; }
.spill-reimbursement_pending { background: #ede9fe; color: #3b0764;   border-color: #ddd6fe; }
.spill-reimbursed            { background: #dcfce7; color: #14532d;   border-color: #bbf7d0; }
.spill-completed             { background: #cffafe; color: #164e63;   border-color: #a5f3fc; }
.spill-rejected              { background: #fee2e2; color: #7f1d1d;   border-color: #fecaca; }

/* ── Timeline strip inside card ─────────────────────────── */
.ef-req-timeline {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 10px 0 0;
    border-top: 1px solid #f5f5f7;
    overflow: hidden;
}
.ef-req-tl-step {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;
}
.ef-req-tl-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.tl-done    { background: var(--req-green); }
.tl-active  { background: var(--req-amber); box-shadow: 0 0 0 3px rgba(217,119,6,.2); }
.tl-pending { background: #d1d5db; }
.tl-paid    { background: var(--req-blue); }
.tl-reject  { background: var(--req-red); }
.ef-req-tl-lbl {
    font-size: .62rem;
    font-weight: 600;
    color: var(--req-sub);
    white-space: nowrap;
}
.ef-req-tl-lbl.done   { color: var(--req-green); }
.ef-req-tl-lbl.active { color: var(--req-amber); font-weight: 700; }
.ef-req-tl-lbl.paid   { color: var(--req-blue); font-weight: 700; }
.ef-req-tl-lbl.reject { color: var(--req-red); }
.ef-req-tl-line {
    flex: 1;
    height: 1.5px;
    background: #e5e7eb;
    min-width: 12px;
    max-width: 40px;
}
.ef-req-tl-line.done { background: var(--req-green); }

/* ── Footer row ─────────────────────────────────────────── */
.ef-req-card-footer {
    padding: 0 18px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.ef-req-card-date {
    font-size: .72rem;
    color: var(--req-sub);
    display: flex;
    align-items: center;
    gap: 4px;
}
.ef-req-view-btn {
    font-size: .74rem;
    font-weight: 700;
    color: var(--req-indigo);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 12px;
    border: 1.5px solid rgba(67,56,202,.25);
    border-radius: 10px;
    transition: all .12s;
}
.ef-req-view-btn:hover {
    background: var(--req-indigo);
    border-color: var(--req-indigo);
    color: #fff;
    text-decoration: none;
}

/* ── Empty state ────────────────────────────────────────── */
.ef-req-empty {
    background: var(--req-surface);
    border: 1px solid var(--req-border);
    border-radius: 20px;
    padding: 60px 24px;
    text-align: center;
}
.ef-req-empty-icon {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: #ede9fe;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 1.8rem;
    color: var(--req-indigo);
}
.ef-req-empty-title { font-size: 1rem; font-weight: 700; color: var(--req-text); margin-bottom: 6px; }
.ef-req-empty-sub   { font-size: .83rem; color: var(--req-sub); margin-bottom: 20px; }
.ef-req-empty-cta {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--req-indigo);
    color: #fff;
    font-size: .85rem;
    font-weight: 700;
    padding: 10px 22px;
    border-radius: 12px;
    text-decoration: none;
    transition: background .15s;
}
.ef-req-empty-cta:hover { background: #3730a3; color: #fff; }

/* ── Floating FAB ───────────────────────────────────────── */
.ef-req-fab {
    display: none;
    position: fixed;
    bottom: 24px; right: 20px;
    z-index: 1030;
    background: var(--req-green);
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 56px; height: 56px;
    font-size: 1.35rem;
    box-shadow: 0 4px 18px rgba(26,102,69,.4);
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: background .15s, transform .12s;
}
.ef-req-fab:hover { background: var(--req-green-hi); color: #fff; transform: scale(1.06); }

/* ── Pagination ─────────────────────────────────────────── */
.ef-req-pager { margin-top: 16px; display: flex; justify-content: center; }
.ef-req-pager-info {
    text-align: center;
    font-size: .75rem;
    color: var(--req-sub);
    margin-bottom: 8px;
}

/* ── Responsive ─────────────────────────────────────────── */
@media (max-width: 575.98px) {
    .ef-req-hero       { padding: 20px 18px; border-radius: 20px; }
    .ef-req-title      { font-size: 1.25rem; }
    .ef-req-card-body  { padding: 14px 14px; }
    .ef-req-card-footer{ padding: 0 14px 12px; }
    .ef-req-fab        { display: flex; }
    .ef-req-hero-stats { display: none; }
}
</style>
@endpush

@php
    $activeStatus = $filters['status'] ?? '';
    $activeSearch = $filters['search'] ?? '';

    $statusIcons = [
        'pending'               => 'bi-hourglass-split',
        'pending_payment'       => 'bi-clock-fill',
        'approved'              => 'bi-check-circle-fill',
        'paid'                  => 'bi-currency-rupee',
        'reimbursement_pending' => 'bi-arrow-return-left',
        'reimbursed'            => 'bi-check-all',
        'completed'             => 'bi-patch-check-fill',
        'rejected'              => 'bi-x-circle-fill',
    ];

    // Timeline steps per status
    $timelines = [
        'pending'               => [['done','Submitted'],['active','Under Review'],['pending','Payment']],
        'pending_payment'       => [['done','Submitted'],['done','Approved'],['active','Payment Due']],
        'approved'              => [['done','Submitted'],['done','Approved'],['pending','Payment']],
        'paid'                  => [['done','Submitted'],['done','Approved'],['paid','Paid']],
        'reimbursement_pending' => [['done','Submitted'],['done','Approved'],['active','Reimb. Pending']],
        'reimbursed'            => [['done','Submitted'],['done','Approved'],['paid','Reimbursed']],
        'completed'             => [['done','Submitted'],['done','Approved'],['paid','Completed']],
        'rejected'              => [['done','Submitted'],['reject','Rejected'],['pending','—']],
    ];
@endphp

<div class="ef-req-page">

    {{-- ── HERO ──────────────────────────────────────────── --}}
    <div class="ef-req-hero">
        <div class="ef-req-hero-inner">
            <div>
                <p class="ef-req-kicker">Expense Center</p>
                <h1 class="ef-req-title">My Requests</h1>
                <p class="ef-req-sub">Track approvals, payments &amp; reimbursements</p>
            </div>
            <div class="ef-req-hero-stats">
                <div class="ef-req-hero-stat">
                    <div class="ef-req-hero-stat-val">{{ $summary['total'] }}</div>
                    <div class="ef-req-hero-stat-lbl">Total</div>
                </div>
                <div class="ef-req-hero-stat">
                    <div class="ef-req-hero-stat-val" style="color:#4ade80">₹{{ number_format($summary['paid_amount'], 0) }}</div>
                    <div class="ef-req-hero-stat-lbl">Paid Out</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SUMMARY STRIP ──────────────────────────────────── --}}
    <div class="ef-req-strip">
        <div class="ef-req-strip-card strip-total">
            <div class="ef-req-strip-lbl">Total</div>
            <div class="ef-req-strip-val">{{ $summary['total'] }}</div>
        </div>
        <div class="ef-req-strip-card strip-pending">
            <div class="ef-req-strip-lbl">
                @if($summary['pending'] > 0)<span class="ef-req-pulse"></span>@endif
                Pending
            </div>
            <div class="ef-req-strip-val {{ $summary['pending'] > 0 ? 'amber' : '' }}">
                {{ $summary['pending'] }}
            </div>
        </div>
        <div class="ef-req-strip-card strip-approved">
            <div class="ef-req-strip-lbl">Approved</div>
            <div class="ef-req-strip-val {{ $summary['approved_amount'] > 0 ? 'green' : '' }}">
                ₹{{ number_format($summary['approved_amount'], 0) }}
            </div>
        </div>
        <div class="ef-req-strip-card strip-paid">
            <div class="ef-req-strip-lbl">Paid</div>
            <div class="ef-req-strip-val {{ $summary['paid_amount'] > 0 ? 'blue' : '' }}">
                ₹{{ number_format($summary['paid_amount'], 0) }}
            </div>
        </div>
        <div class="ef-req-strip-card strip-rejected">
            <div class="ef-req-strip-lbl">Rejected</div>
            <div class="ef-req-strip-val {{ $summary['rejected'] > 0 ? 'red' : '' }}">
                {{ $summary['rejected'] }}
            </div>
        </div>
    </div>

    {{-- ── SEARCH ─────────────────────────────────────────── --}}
    <form method="GET" id="reqForm">
        @if($activeStatus)<input type="hidden" name="status" value="{{ $activeStatus }}">@endif
        <div class="ef-req-search-wrap">
            <i class="bi bi-search ef-req-search-icon"></i>
            <input type="text"
                   name="search"
                   id="searchInput"
                   class="ef-req-search-input"
                   value="{{ $activeSearch }}"
                   placeholder="Search by expense title…"
                   autocomplete="off">
            @if($activeSearch)
            <button type="button" class="ef-req-search-clear" onclick="clearSearch()">
                <i class="bi bi-x"></i>
            </button>
            @endif
        </div>
    </form>

    {{-- ── FILTER CHIPS ────────────────────────────────────── --}}
    <div class="ef-req-filter-bar">
        <div class="ef-req-filter-inner">
            <a href="{{ route('employee.expense-requests.index', $activeSearch ? ['search' => $activeSearch] : []) }}"
               class="ef-req-chip {{ $activeStatus === '' ? 'active' : '' }}">
                All <span class="ef-req-chip-badge">{{ $summary['total'] }}</span>
            </a>
            <a href="{{ route('employee.expense-requests.index', array_filter(['status' => 'pending', 'search' => $activeSearch])) }}"
               class="ef-req-chip chip-pending {{ $activeStatus === 'pending' ? 'active' : '' }}">
                ⏳ Pending <span class="ef-req-chip-badge">{{ $summary['pending'] }}</span>
            </a>
            <a href="{{ route('employee.expense-requests.index', array_filter(['status' => 'approved', 'search' => $activeSearch])) }}"
               class="ef-req-chip chip-approved {{ $activeStatus === 'approved' ? 'active' : '' }}">
                ✓ Approved
            </a>
            <a href="{{ route('employee.expense-requests.index', array_filter(['status' => 'paid', 'search' => $activeSearch])) }}"
               class="ef-req-chip chip-paid {{ $activeStatus === 'paid' ? 'active' : '' }}">
                ₹ Paid
            </a>
            <a href="{{ route('employee.expense-requests.index', array_filter(['status' => 'rejected', 'search' => $activeSearch])) }}"
               class="ef-req-chip chip-rejected {{ $activeStatus === 'rejected' ? 'active' : '' }}">
                ✕ Rejected <span class="ef-req-chip-badge">{{ $summary['rejected'] }}</span>
            </a>
            <a href="{{ route('employee.expense-requests.index', array_filter(['status' => 'completed', 'search' => $activeSearch])) }}"
               class="ef-req-chip chip-completed {{ $activeStatus === 'completed' ? 'active' : '' }}">
                ✓✓ Completed
            </a>
        </div>
    </div>

    {{-- ── REQUEST CARDS ────────────────────────────────────── --}}
    @if($requests->isEmpty())
    <div class="ef-req-empty">
        <div class="ef-req-empty-icon"><i class="bi bi-file-earmark-text"></i></div>
        <p class="ef-req-empty-title">
            @if($activeSearch) No results for "{{ $activeSearch }}"
            @elseif($activeStatus) No {{ $activeStatus }} requests
            @else No expense requests yet @endif
        </p>
        <p class="ef-req-empty-sub">
            @if($activeSearch || $activeStatus) Try a different filter or search term.
            @else Submit your first expense to get started. @endif
        </p>
        @if($activeSearch || $activeStatus)
        <a href="{{ route('employee.expense-requests.index') }}" class="ef-req-empty-cta">
            <i class="bi bi-arrow-left"></i> Clear Filters
        </a>
        @else
        <a href="{{ route('employee.expense-requests.create') }}" class="ef-req-empty-cta">
            <i class="bi bi-plus-lg"></i> Create First Request
        </a>
        @endif
    </div>
    @else

    <div class="ef-req-list">
        @foreach($requests as $req)
        @php
            $st  = $req->status;
            $icon = $statusIcons[$st] ?? 'bi-receipt';
            $iconClass = 'icon-' . $st;
            $stripeClass = 'stripe-' . $st;
            $pillClass = 'spill-' . $st;
            $tl = $timelines[$st] ?? [['done','Submitted'],['pending','Review'],['pending','Payment']];
            $tlDotMap = ['done' => 'tl-done', 'active' => 'tl-active', 'paid' => 'tl-paid', 'reject' => 'tl-reject', 'pending' => 'tl-pending'];
            $tlLblMap = ['done' => 'done', 'active' => 'active', 'paid' => 'paid', 'reject' => 'reject', 'pending' => ''];
        @endphp
        <a href="{{ route('employee.expense-requests.show', $req) }}" class="ef-req-card">
            <div class="ef-req-card-stripe {{ $stripeClass }}"></div>
            <div class="ef-req-card-body">
                <div class="ef-req-card-top">
                    <div class="ef-req-card-icon {{ $iconClass }}">
                        <i class="bi {{ $icon }}"></i>
                    </div>
                    <div class="ef-req-card-meta">
                        <div class="ef-req-card-id">#REQ-{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</div>
                        <div class="ef-req-card-title">{{ $req->title }}</div>
                        @if($req->category)
                        <div class="ef-req-card-category">
                            <i class="bi bi-tag" style="font-size:.65rem"></i>
                            {{ $req->category->name }}
                        </div>
                        @endif
                    </div>
                    <div class="ef-req-card-right">
                        <div class="ef-req-card-amount">₹{{ number_format($req->amount, 0) }}</div>
                        <span class="ef-req-status-pill {{ $pillClass }}">
                            {{ str_replace('_', ' ', $st) }}
                        </span>
                    </div>
                </div>

                {{-- Approval timeline --}}
                <div class="ef-req-timeline">
                    @foreach($tl as $idx => $step)
                        <div class="ef-req-tl-step">
                            <div class="ef-req-tl-dot {{ $tlDotMap[$step[0]] ?? 'tl-pending' }}"></div>
                            <span class="ef-req-tl-lbl {{ $tlLblMap[$step[0]] ?? '' }}">{{ $step[1] }}</span>
                        </div>
                        @if(!$loop->last)
                        <div class="ef-req-tl-line {{ $step[0] === 'done' ? 'done' : '' }}"></div>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="ef-req-card-footer">
                <span class="ef-req-card-date">
                    <i class="bi bi-calendar3" style="font-size:.65rem"></i>
                    {{ $req->created_at->format('d M Y') }}
                    <span style="opacity:.4">·</span>
                    {{ $req->created_at->format('h:i A') }}
                </span>
                <span class="ef-req-view-btn">
                    View <i class="bi bi-arrow-right" style="font-size:.7rem"></i>
                </span>
            </div>
        </a>
        @endforeach
    </div>

    @if($requests->hasPages())
    <p class="ef-req-pager-info mt-4">
        Showing {{ $requests->firstItem() }}–{{ $requests->lastItem() }} of {{ $requests->total() }} requests
    </p>
    <div class="ef-req-pager">{{ $requests->links('pagination::bootstrap-5') }}</div>
    @endif

    @endif

</div>{{-- /page --}}

{{-- Floating FAB (mobile) --}}
<a href="{{ route('employee.expense-requests.create') }}" class="ef-req-fab" title="New Expense Request">
    <i class="bi bi-plus-lg"></i>
</a>

@push('scripts')
<script>
function clearSearch() {
    const input = document.getElementById('searchInput');
    if (input) { input.value = ''; document.getElementById('reqForm').submit(); }
}
// Auto-submit search on Enter (already works), also submit on clear
document.getElementById('searchInput')?.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); document.getElementById('reqForm').submit(); }
});
</script>
@endpush

</x-admin-layout>
