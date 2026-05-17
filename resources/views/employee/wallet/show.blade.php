<x-admin-layout title="My Wallet">
@push('styles')
<style>
/* ════════════════════════════════════════════════════════════
   EMPLOYEE WALLET — ef-wallet-* namespace
   Paytm / PhonePe passbook feel
   ════════════════════════════════════════════════════════════ */
:root {
    --wt-green:    #1a6645;
    --wt-green-hi: #22845a;
    --wt-red:      #dc2626;
    --wt-amber:    #d97706;
    --wt-indigo:   #4338ca;
    --wt-cyan:     #0891b2;
    --wt-text:     #111827;
    --wt-sub:      #6b7280;
    --wt-border:   #e5e7eb;
    --wt-surface:  #fff;
}

/* ── Page max-width ─────────────────────────────────────── */
.ef-wallet-page { max-width: 640px; margin: 0 auto; }

/* ── Floating balance pill ──────────────────────────────── */
.ef-wallet-sticky {
    position: fixed;
    top: 72px;
    right: 16px;
    z-index: 900;
    background: var(--wt-green);
    color: #fff;
    font-size: .78rem;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 20px;
    box-shadow: 0 4px 14px rgba(26,102,69,.35);
    display: flex;
    align-items: center;
    gap: 6px;
    opacity: 0;
    transform: translateY(-4px);
    transition: opacity .2s, transform .2s;
    pointer-events: none;
}
.ef-wallet-sticky.visible { opacity: 1; transform: translateY(0); }
.ef-wallet-sticky.warn  { background: var(--wt-amber); }
.ef-wallet-sticky.neg   { background: var(--wt-red); }

/* ── Hero wallet card ───────────────────────────────────── */
.ef-wallet-hero {
    background:
        radial-gradient(ellipse at 110% -10%, rgba(34,180,100,.22) 0%, transparent 55%),
        radial-gradient(ellipse at -10% 110%, rgba(26,102,69,.18) 0%, transparent 55%),
        linear-gradient(135deg, #07130d 0%, #0d1f17 40%, #123524 100%);
    border-radius: 28px;
    padding: 28px 28px 24px;
    position: relative;
    overflow: hidden;
    margin-bottom: 16px;
    box-shadow: 0 8px 32px rgba(7,19,13,.45), 0 2px 8px rgba(7,19,13,.2);
}

/* Mesh noise overlay */
.ef-wallet-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
    border-radius: 28px;
    pointer-events: none;
    opacity: .6;
}
/* Glow blobs */
.ef-wallet-hero::after {
    content: '';
    position: absolute;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(34,132,90,.2) 0%, transparent 70%);
    right: -40px; top: -60px;
    pointer-events: none;
}
.ef-wallet-hero-blob {
    position: absolute;
    width: 140px; height: 140px;
    background: radial-gradient(circle, rgba(26,180,96,.1) 0%, transparent 70%);
    bottom: -30px; left: 40px;
    pointer-events: none;
}

.ef-wallet-hero-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    position: relative;
    z-index: 1;
}
.ef-wallet-hero-label {
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: rgba(255,255,255,.45);
}
.ef-wallet-icon-circle {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.12);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    color: rgba(255,255,255,.7);
    backdrop-filter: blur(8px);
}

.ef-wallet-balance-area {
    position: relative;
    z-index: 1;
    margin-bottom: 6px;
}
.ef-wallet-balance {
    font-size: 2.6rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: -.02em;
    line-height: 1;
}
.ef-wallet-balance.is-low { color: #fbbf24; }
.ef-wallet-balance.is-neg { color: #f87171; }

.ef-wallet-balance-sub {
    font-size: .78rem;
    font-weight: 500;
    color: rgba(255,255,255,.45);
    margin-top: 6px;
    position: relative;
    z-index: 1;
    margin-bottom: 24px;
}

/* Bottom stat row */
.ef-wallet-hero-stats {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
}
.ef-wallet-stat-chip {
    flex: 1;
    min-width: 90px;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 14px;
    padding: 10px 12px;
    backdrop-filter: blur(8px);
}
.ef-wallet-stat-lbl {
    font-size: .62rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: rgba(255,255,255,.4);
    margin-bottom: 4px;
}
.ef-wallet-stat-val {
    font-size: .95rem;
    font-weight: 800;
    color: #fff;
    line-height: 1;
}
.ef-wallet-stat-val.credited { color: #4ade80; }
.ef-wallet-stat-val.debited  { color: #f87171; }
.ef-wallet-stat-val.pending  { color: #fbbf24; }

/* Health badge */
.ef-wallet-health {
    position: absolute;
    top: 28px; right: 72px;
    z-index: 1;
    font-size: .62rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 12px;
    backdrop-filter: blur(8px);
}
.health-ok  { background: rgba(74,222,128,.15); color: #4ade80; border: 1px solid rgba(74,222,128,.25); }
.health-low { background: rgba(251,191,36,.15);  color: #fbbf24; border: 1px solid rgba(251,191,36,.25); }
.health-neg { background: rgba(248,113,113,.15); color: #f87171; border: 1px solid rgba(248,113,113,.25); }

/* ── Quick actions ──────────────────────────────────────── */
.ef-wallet-actions {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-bottom: 16px;
}
.ef-wallet-action {
    background: var(--wt-surface);
    border: 1px solid var(--wt-border);
    border-radius: 16px;
    padding: 14px 8px;
    text-align: center;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 7px;
    transition: all .15s;
    cursor: pointer;
}
.ef-wallet-action:hover {
    box-shadow: 0 4px 16px rgba(26,102,69,.1);
    border-color: rgba(26,102,69,.2);
    transform: translateY(-1px);
    text-decoration: none;
}
.ef-wallet-action-icon {
    width: 38px; height: 38px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}
.waction-submit .ef-wallet-action-icon { background: #dcfce7; color: var(--wt-green); }
.waction-requests .ef-wallet-action-icon{ background: #ede9fe; color: var(--wt-indigo); }
.waction-history .ef-wallet-action-icon { background: #cffafe; color: var(--wt-cyan); }
.waction-pending .ef-wallet-action-icon { background: #fef3c7; color: var(--wt-amber); }
.ef-wallet-action-lbl {
    font-size: .68rem;
    font-weight: 700;
    color: var(--wt-text);
    line-height: 1.2;
    letter-spacing: .01em;
}

/* ── Insight cards ──────────────────────────────────────── */
.ef-wallet-insights {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 16px;
}
.ef-wallet-insight {
    background: var(--wt-surface);
    border: 1px solid var(--wt-border);
    border-radius: 18px;
    padding: 16px 16px;
    position: relative;
    overflow: hidden;
    transition: box-shadow .15s, transform .15s;
}
.ef-wallet-insight:hover { box-shadow: 0 4px 14px rgba(0,0,0,.06); transform: translateY(-1px); }
.ef-wallet-insight::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 18px 18px 0 0;
}
.insight-credit::before   { background: linear-gradient(90deg, var(--wt-green), var(--wt-green-hi)); }
.insight-debit::before    { background: linear-gradient(90deg, var(--wt-red), #ef4444); }
.insight-pending::before  { background: linear-gradient(90deg, var(--wt-amber), #f59e0b); }
.insight-lastcredit::before { background: linear-gradient(90deg, var(--wt-cyan), #06b6d4); }

.ef-wallet-insight-lbl {
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--wt-sub);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 5px;
}
.ef-wallet-insight-val {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--wt-text);
    line-height: 1;
    margin-bottom: 4px;
}
.ef-wallet-insight-val.green  { color: var(--wt-green); }
.ef-wallet-insight-val.red    { color: var(--wt-red); }
.ef-wallet-insight-val.amber  { color: var(--wt-amber); }
.ef-wallet-insight-sub {
    font-size: .72rem;
    color: var(--wt-sub);
    margin-top: 2px;
}

/* ── Filter chips ───────────────────────────────────────── */
.ef-wallet-filter-bar {
    margin-bottom: 14px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.ef-wallet-filter-bar::-webkit-scrollbar { display: none; }
.ef-wallet-filter-inner {
    display: flex;
    gap: 8px;
    padding-bottom: 2px;
    white-space: nowrap;
}
.ef-wallet-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 7px 16px;
    border-radius: 20px;
    font-size: .78rem;
    font-weight: 600;
    border: 1.5px solid var(--wt-border);
    background: var(--wt-surface);
    color: var(--wt-sub);
    cursor: pointer;
    text-decoration: none;
    transition: all .15s;
    white-space: nowrap;
    flex-shrink: 0;
}
.ef-wallet-chip:hover { border-color: var(--wt-green); color: var(--wt-green); text-decoration: none; }
.ef-wallet-chip.active {
    background: var(--wt-green);
    border-color: var(--wt-green);
    color: #fff;
    box-shadow: 0 2px 8px rgba(26,102,69,.25);
}
.ef-wallet-chip.chip-credit.active  { background: var(--wt-green);  border-color: var(--wt-green); }
.ef-wallet-chip.chip-debit.active   { background: var(--wt-red);    border-color: var(--wt-red); }
.ef-wallet-chip.chip-reimb.active   { background: var(--wt-indigo); border-color: var(--wt-indigo); }
.ef-wallet-chip.chip-adjust.active  { background: var(--wt-cyan);   border-color: var(--wt-cyan); }

/* Date range filter (compact) */
.ef-wallet-date-row {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-bottom: 14px;
}
.ef-wallet-date-input {
    flex: 1;
    border: 1.5px solid var(--wt-border);
    border-radius: 10px;
    padding: 7px 12px;
    font-size: .8rem;
    color: var(--wt-text);
    background: var(--wt-surface);
    outline: none;
    transition: border-color .15s;
}
.ef-wallet-date-input:focus { border-color: var(--wt-green); }
.ef-wallet-date-btn {
    background: var(--wt-green);
    border: none;
    color: #fff;
    font-size: .78rem;
    font-weight: 700;
    padding: 7px 16px;
    border-radius: 10px;
    cursor: pointer;
    white-space: nowrap;
    transition: background .12s;
}
.ef-wallet-date-btn:hover { background: var(--wt-green-hi); }
.ef-wallet-date-reset {
    font-size: .78rem;
    font-weight: 600;
    color: var(--wt-sub);
    text-decoration: none;
    padding: 7px 10px;
    white-space: nowrap;
}
.ef-wallet-date-reset:hover { color: var(--wt-text); }

/* ── Transaction section header ─────────────────────────── */
.ef-wallet-txn-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
    padding: 0 2px;
}
.ef-wallet-txn-title {
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--wt-sub);
}
.ef-wallet-txn-count {
    font-size: .72rem;
    font-weight: 600;
    color: var(--wt-sub);
    background: #f3f4f6;
    padding: 3px 10px;
    border-radius: 12px;
}

/* ── Transaction timeline ───────────────────────────────── */
.ef-wallet-txn-list { display: flex; flex-direction: column; gap: 8px; }

.ef-wallet-txn {
    background: var(--wt-surface);
    border: 1px solid #f0f0f0;
    border-radius: 18px;
    padding: 16px 18px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    transition: box-shadow .15s, transform .12s;
    text-decoration: none;
}
.ef-wallet-txn:hover {
    box-shadow: 0 3px 14px rgba(0,0,0,.07);
    transform: translateY(-1px);
    text-decoration: none;
}
.ef-wallet-txn-icon {
    width: 42px; height: 42px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}
.txn-credit      .ef-wallet-txn-icon { background: #dcfce7; color: var(--wt-green); }
.txn-debit       .ef-wallet-txn-icon { background: #fee2e2; color: var(--wt-red); }
.txn-reimbursement .ef-wallet-txn-icon { background: #ede9fe; color: var(--wt-indigo); }
.txn-adjustment  .ef-wallet-txn-icon { background: #cffafe; color: var(--wt-cyan); }

.ef-wallet-txn-body { flex: 1; min-width: 0; }
.ef-wallet-txn-type {
    font-size: .62rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-bottom: 2px;
}
.txn-credit      .ef-wallet-txn-type { color: var(--wt-green); }
.txn-debit       .ef-wallet-txn-type { color: var(--wt-red); }
.txn-reimbursement .ef-wallet-txn-type { color: var(--wt-indigo); }
.txn-adjustment  .ef-wallet-txn-type { color: var(--wt-cyan); }

.ef-wallet-txn-desc {
    font-size: .88rem;
    font-weight: 600;
    color: var(--wt-text);
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ef-wallet-txn-meta {
    font-size: .72rem;
    color: var(--wt-sub);
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.ef-wallet-txn-sep { opacity: .4; }

.ef-wallet-txn-right { text-align: right; flex-shrink: 0; }
.ef-wallet-txn-amount {
    font-size: 1rem;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 4px;
}
.txn-credit      .ef-wallet-txn-amount,
.txn-reimbursement .ef-wallet-txn-amount { color: var(--wt-green); }
.txn-debit       .ef-wallet-txn-amount { color: var(--wt-red); }
.txn-adjustment  .ef-wallet-txn-amount { color: var(--wt-cyan); }

.ef-wallet-txn-bal {
    font-size: .7rem;
    color: var(--wt-sub);
    font-weight: 500;
    white-space: nowrap;
}

/* ── Empty state ────────────────────────────────────────── */
.ef-wallet-empty {
    background: var(--wt-surface);
    border: 1px solid var(--wt-border);
    border-radius: 20px;
    padding: 56px 24px;
    text-align: center;
}
.ef-wallet-empty-icon {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: #f0fdf4;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 1.8rem;
    color: var(--wt-green);
}
.ef-wallet-empty-title { font-size: 1rem; font-weight: 700; color: var(--wt-text); margin-bottom: 6px; }
.ef-wallet-empty-sub   { font-size: .83rem; color: var(--wt-sub); margin-bottom: 20px; }
.ef-wallet-empty-cta {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--wt-green);
    color: #fff;
    font-size: .85rem;
    font-weight: 700;
    padding: 10px 22px;
    border-radius: 12px;
    text-decoration: none;
    transition: background .15s;
}
.ef-wallet-empty-cta:hover { background: var(--wt-green-hi); color: #fff; }

/* ── Pagination ─────────────────────────────────────────── */
.ef-wallet-pagination {
    margin-top: 16px;
    display: flex;
    justify-content: center;
}

/* ── Responsive ─────────────────────────────────────────── */
@media (max-width: 575.98px) {
    .ef-wallet-hero    { padding: 22px 20px 20px; border-radius: 22px; }
    .ef-wallet-balance { font-size: 2.2rem; }
    .ef-wallet-actions { grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .ef-wallet-action  { padding: 12px 6px; }
    .ef-wallet-action-icon { width: 34px; height: 34px; font-size: .9rem; }
    .ef-wallet-action-lbl  { font-size: .62rem; }
    .ef-wallet-insights { grid-template-columns: 1fr 1fr; }
    .ef-wallet-txn      { padding: 14px 14px; gap: 10px; }
}
@media (max-width: 359.98px) {
    .ef-wallet-actions { grid-template-columns: repeat(2, 1fr); }
}
</style>
@endpush

@php
    $activeType = request('type', '');
    $hasDateFilter = request('from') || request('to');
    $walletClass = $wallet->isNegative() ? 'is-neg' : ($wallet->isLow() ? 'is-low' : '');
    $stickyClass  = $wallet->isNegative() ? 'neg' : ($wallet->isLow() ? 'warn' : '');
    $healthClass  = $wallet->isNegative() ? 'health-neg' : ($wallet->isLow() ? 'health-low' : 'health-ok');
    $healthLabel  = $wallet->isNegative() ? 'Overdrawn' : ($wallet->isLow() ? 'Low' : 'Healthy');
@endphp

{{-- Floating sticky balance pill --}}
<div class="ef-wallet-sticky {{ $stickyClass }}" id="walletSticky">
    <i class="bi bi-wallet2" style="font-size:.75rem"></i>
    ₹{{ number_format($wallet->balance, 0) }}
</div>

<div class="ef-wallet-page">

    {{-- ── WALLET HERO CARD ─────────────────────────────── --}}
    <div class="ef-wallet-hero" id="walletHero">
        <div class="ef-wallet-hero-blob"></div>

        {{-- Health badge --}}
        <span class="ef-wallet-health {{ $healthClass }}">{{ $healthLabel }}</span>

        {{-- Top row --}}
        <div class="ef-wallet-hero-top">
            <span class="ef-wallet-hero-label">My Wallet</span>
            <div class="ef-wallet-icon-circle"><i class="bi bi-wallet2"></i></div>
        </div>

        {{-- Balance --}}
        <div class="ef-wallet-balance-area">
            <div class="ef-wallet-balance {{ $walletClass }}" id="walletBalanceEl">
                ₹{{ number_format($wallet->balance, 2) }}
            </div>
        </div>
        <p class="ef-wallet-balance-sub">Available for expenses</p>

        {{-- Stat chips --}}
        <div class="ef-wallet-hero-stats">
            <div class="ef-wallet-stat-chip">
                <div class="ef-wallet-stat-lbl">Received</div>
                <div class="ef-wallet-stat-val credited">₹{{ number_format($stats['month_credited'], 0) }}</div>
            </div>
            <div class="ef-wallet-stat-chip">
                <div class="ef-wallet-stat-lbl">Spent</div>
                <div class="ef-wallet-stat-val debited">₹{{ number_format($stats['month_debited'], 0) }}</div>
            </div>
            <div class="ef-wallet-stat-chip">
                <div class="ef-wallet-stat-lbl">Pending</div>
                <div class="ef-wallet-stat-val {{ $stats['pending_requests'] > 0 ? 'pending' : '' }}">
                    {{ $stats['pending_requests'] }}
                </div>
            </div>
        </div>
    </div>

    {{-- ── QUICK ACTIONS ─────────────────────────────────── --}}
    <div class="ef-wallet-actions">
        <a href="{{ route('employee.expense-requests.create') }}" class="ef-wallet-action waction-submit">
            <div class="ef-wallet-action-icon"><i class="bi bi-plus-circle-fill"></i></div>
            <span class="ef-wallet-action-lbl">Submit Expense</span>
        </a>
        <a href="{{ route('employee.expense-requests.index') }}" class="ef-wallet-action waction-requests">
            <div class="ef-wallet-action-icon"><i class="bi bi-list-ul"></i></div>
            <span class="ef-wallet-action-lbl">My Requests</span>
        </a>
        <a href="{{ route('employee.wallet.show', ['type' => 'credit']) }}" class="ef-wallet-action waction-history">
            <div class="ef-wallet-action-icon"><i class="bi bi-arrow-down-circle-fill"></i></div>
            <span class="ef-wallet-action-lbl">Credits</span>
        </a>
        <a href="{{ route('employee.expense-requests.index', ['status' => 'pending']) }}" class="ef-wallet-action waction-pending">
            <div class="ef-wallet-action-icon"><i class="bi bi-hourglass-split"></i></div>
            <span class="ef-wallet-action-lbl">Pending</span>
        </a>
    </div>

    {{-- ── INSIGHT CARDS ─────────────────────────────────── --}}
    <div class="ef-wallet-insights">
        <div class="ef-wallet-insight insight-credit">
            <div class="ef-wallet-insight-lbl">
                <i class="bi bi-arrow-down-circle" style="color:var(--wt-green)"></i>
                {{ now()->format('M') }} Received
            </div>
            <div class="ef-wallet-insight-val green">₹{{ number_format($stats['month_credited'], 0) }}</div>
            <div class="ef-wallet-insight-sub">Credits &amp; reimbursements</div>
        </div>
        <div class="ef-wallet-insight insight-debit">
            <div class="ef-wallet-insight-lbl">
                <i class="bi bi-arrow-up-circle" style="color:var(--wt-red)"></i>
                {{ now()->format('M') }} Spent
            </div>
            <div class="ef-wallet-insight-val red">₹{{ number_format($stats['month_debited'], 0) }}</div>
            <div class="ef-wallet-insight-sub">Expenses debited</div>
        </div>
        <div class="ef-wallet-insight insight-pending">
            <div class="ef-wallet-insight-lbl">
                <i class="bi bi-hourglass-split" style="color:var(--wt-amber)"></i>
                Pending
            </div>
            <div class="ef-wallet-insight-val {{ $stats['pending_requests'] > 0 ? 'amber' : '' }}">
                {{ $stats['pending_requests'] }}
            </div>
            <div class="ef-wallet-insight-sub">Awaiting approval</div>
        </div>
        <div class="ef-wallet-insight insight-lastcredit">
            <div class="ef-wallet-insight-lbl">
                <i class="bi bi-lightning-charge-fill" style="color:var(--wt-cyan)"></i>
                Last Credit
            </div>
            @if($stats['last_credit'])
            <div class="ef-wallet-insight-val" style="font-size:1.1rem">
                ₹{{ number_format($stats['last_credit']->amount, 0) }}
            </div>
            <div class="ef-wallet-insight-sub">
                {{ $stats['last_credit']->created_at->diffForHumans(['short' => true, 'parts' => 1]) }}
            </div>
            @else
            <div class="ef-wallet-insight-val" style="font-size:1rem;color:var(--wt-sub)">—</div>
            <div class="ef-wallet-insight-sub">No credits yet</div>
            @endif
        </div>
    </div>

    {{-- ── FILTER CHIPS ──────────────────────────────────── --}}
    <form method="GET" id="filterForm">
        <div class="ef-wallet-filter-bar">
            <div class="ef-wallet-filter-inner">
                <a href="{{ route('employee.wallet.show') }}"
                   class="ef-wallet-chip {{ $activeType === '' && !$hasDateFilter ? 'active' : '' }}">
                    All
                </a>
                <a href="{{ route('employee.wallet.show', ['type' => 'credit']) }}"
                   class="ef-wallet-chip chip-credit {{ $activeType === 'credit' ? 'active' : '' }}">
                    <i class="bi bi-arrow-down-short"></i> Credits
                </a>
                <a href="{{ route('employee.wallet.show', ['type' => 'debit']) }}"
                   class="ef-wallet-chip chip-debit {{ $activeType === 'debit' ? 'active' : '' }}">
                    <i class="bi bi-arrow-up-short"></i> Debits
                </a>
                <a href="{{ route('employee.wallet.show', ['type' => 'reimbursement']) }}"
                   class="ef-wallet-chip chip-reimb {{ $activeType === 'reimbursement' ? 'active' : '' }}">
                    <i class="bi bi-arrow-return-left"></i> Reimbursements
                </a>
                <a href="{{ route('employee.wallet.show', ['type' => 'adjustment']) }}"
                   class="ef-wallet-chip chip-adjust {{ $activeType === 'adjustment' ? 'active' : '' }}">
                    <i class="bi bi-sliders2"></i> Adjustments
                </a>
            </div>
        </div>

        {{-- Date range (compact) --}}
        <div class="ef-wallet-date-row">
            <input type="date" name="from" class="ef-wallet-date-input"
                   value="{{ request('from') }}" placeholder="From">
            <input type="date" name="to"   class="ef-wallet-date-input"
                   value="{{ request('to') }}" placeholder="To">
            @if($activeType) <input type="hidden" name="type" value="{{ $activeType }}"> @endif
            <button type="submit" class="ef-wallet-date-btn">Go</button>
            @if($hasDateFilter)
            <a href="{{ route('employee.wallet.show', $activeType ? ['type' => $activeType] : []) }}"
               class="ef-wallet-date-reset">Clear</a>
            @endif
        </div>
    </form>

    {{-- ── TRANSACTIONS ──────────────────────────────────── --}}
    <div class="ef-wallet-txn-header">
        <span class="ef-wallet-txn-title">Transaction History</span>
        <span class="ef-wallet-txn-count">{{ $transactions->total() }} entries</span>
    </div>

    @if($transactions->isEmpty())
    <div class="ef-wallet-empty">
        <div class="ef-wallet-empty-icon"><i class="bi bi-wallet2"></i></div>
        <p class="ef-wallet-empty-title">No wallet activity yet</p>
        <p class="ef-wallet-empty-sub">
            @if($activeType || $hasDateFilter)
                No transactions match the selected filter.
            @else
                Your transaction history will appear here once your wallet is active.
            @endif
        </p>
        @if(!$activeType && !$hasDateFilter)
        <a href="{{ route('employee.expense-requests.create') }}" class="ef-wallet-empty-cta">
            <i class="bi bi-plus-lg"></i> Submit your first expense
        </a>
        @else
        <a href="{{ route('employee.wallet.show') }}" class="ef-wallet-empty-cta">
            <i class="bi bi-arrow-left"></i> Clear filters
        </a>
        @endif
    </div>
    @else

    <div class="ef-wallet-txn-list">
        @foreach($transactions as $txn)
        @php
            $typeClass = 'txn-' . $txn->type;
            $icons = [
                'credit'        => 'bi-arrow-down-circle-fill',
                'debit'         => 'bi-arrow-up-circle-fill',
                'reimbursement' => 'bi-arrow-return-left',
                'adjustment'    => 'bi-sliders2',
            ];
            $icon = $icons[$txn->type] ?? 'bi-circle-fill';
            $prefix = $txn->isCredit() ? '+' : '−';
            $desc = $txn->notes
                ?? ($txn->expenseRequest?->title ?? ucfirst($txn->type));
        @endphp
        @if($txn->expenseRequest)
        <a href="{{ route('employee.expense-requests.show', $txn->expenseRequest) }}"
           class="ef-wallet-txn {{ $typeClass }}">
        @else
        <div class="ef-wallet-txn {{ $typeClass }}">
        @endif
            <div class="ef-wallet-txn-icon">
                <i class="bi {{ $icon }}"></i>
            </div>
            <div class="ef-wallet-txn-body">
                <div class="ef-wallet-txn-type">{{ ucfirst($txn->type) }}</div>
                <div class="ef-wallet-txn-desc">{{ $desc }}</div>
                <div class="ef-wallet-txn-meta">
                    <span>{{ $txn->created_at->format('d M Y') }}</span>
                    <span class="ef-wallet-txn-sep">·</span>
                    <span>{{ $txn->created_at->format('h:i A') }}</span>
                    @if($txn->expenseRequest)
                    <span class="ef-wallet-txn-sep">·</span>
                    <span>Req #{{ $txn->expenseRequest->id }}</span>
                    @endif
                </div>
            </div>
            <div class="ef-wallet-txn-right">
                <div class="ef-wallet-txn-amount">{{ $prefix }}₹{{ number_format($txn->amount, 0) }}</div>
                <div class="ef-wallet-txn-bal">₹{{ number_format($txn->balance_after, 0) }}</div>
            </div>
        @if($txn->expenseRequest) </a> @else </div> @endif
        @endforeach
    </div>

    @if($transactions->hasPages())
    <div class="ef-wallet-pagination">{{ $transactions->links('pagination::bootstrap-5') }}</div>
    @endif

    @endif

</div>{{-- /page --}}

@push('scripts')
<script>
(function () {
    'use strict';
    const hero   = document.getElementById('walletHero');
    const sticky = document.getElementById('walletSticky');
    if (!hero || !sticky) return;

    const observer = new IntersectionObserver(
        ([entry]) => sticky.classList.toggle('visible', !entry.isIntersecting),
        { threshold: 0.2 }
    );
    observer.observe(hero);
})();
</script>
@endpush

</x-admin-layout>
