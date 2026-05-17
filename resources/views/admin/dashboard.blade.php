<x-admin-layout title="Admin Dashboard">
@push('styles')
<style>
/*
 * ADMIN DASHBOARD — ef-ad-* namespace
 * Brand: deep emerald + gold, warm neutrals
 * Mood: premium hospitality operations
 */

/* ── Design tokens ─────────────────────────────────────────────── */
:root {
    --ad-emerald:    #0F7B5F;
    --ad-emerald-hi: #0D9E78;
    --ad-emerald-dk: #0D5C43;
    --ad-gold:       #B8893E;
    --ad-gold-hi:    #D6B97A;
    --ad-amber:      #D89A3D;
    --ad-danger:     #C84B44;
    --ad-info:       #2F6FED;
    --ad-teal:       #0d9488;
    --ad-ink:        #101714;
    --ad-muted:      #6E6A64;
    --ad-faint:      #EDE8DF;
    --ad-surface:    rgba(255,253,250,.88);
    --ad-border:     rgba(15,123,95,.11);
    --ad-border-s:   rgba(15,123,95,.24);
    --ad-shadow:     0 1px 3px rgba(16,23,20,.06),0 4px 12px rgba(16,23,20,.04);
    --ad-shadow-h:   0 8px 30px rgba(16,23,20,.10),0 2px 6px rgba(16,23,20,.06);
    --ad-radius:     14px;
    --ad-ease:       cubic-bezier(.25,.46,.45,.94);
}

/* ── Page scaffold ─────────────────────────────────────────────── */
.ef-ad-page { padding: 0; }

/* ── Hero ──────────────────────────────────────────────────────── */
.ef-ad-hero {
    background: linear-gradient(135deg, #041b14 0%, #052e21 45%, #02110c 100%) !important;
    border: 1px solid rgba(255,255,255,.06) !important;
    border-radius: 20px;
    display: flex;
    align-items: stretch;
    gap: 0;
    margin-bottom: 20px;
    overflow: hidden;
    padding: 32px 28px;
    position: relative;
}
.ef-ad-hero::before {
    background: radial-gradient(circle, rgba(15,123,95,.18) 0%, transparent 68%);
    border-radius: 50%;
    content: "";
    height: 480px;
    pointer-events: none;
    position: absolute;
    right: -100px;
    top: -160px;
    width: 480px;
}
.ef-ad-hero::after {
    background: radial-gradient(circle, rgba(184,137,62,.10) 0%, transparent 68%);
    bottom: -110px;
    border-radius: 50%;
    content: "";
    height: 300px;
    left: 20%;
    pointer-events: none;
    position: absolute;
    width: 300px;
}
.ef-ad-hero-main {
    flex: 1;
    position: relative;
    z-index: 1;
}
.ef-ad-eyebrow {
    color: rgba(184,137,62,.88);
    font-size: .67rem;
    font-weight: 760;
    letter-spacing: .18em;
    margin-bottom: 10px;
    text-transform: uppercase;
}
.ef-ad-hero-title {
    color: #f0fdf8;
    font-size: 1.65rem;
    font-weight: 800;
    letter-spacing: -.02em;
    line-height: 1.2;
    margin-bottom: 10px;
}
.ef-ad-hero-summary {
    color: rgba(240,253,248,.50);
    font-size: .86rem;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
    margin-bottom: 0;
}
.ef-ad-hero-summary b   { color: rgba(240,253,248,.88); font-weight: 700; }
.ef-ad-hero-summary .dot { opacity: .3; }

.ef-ad-hero-side {
    background: rgba(255,255,255,.03) !important;
    border-left: 1px solid rgba(255,255,255,.07) !important;
    display: flex;
    flex-direction: column;
    gap: 18px;
    justify-content: space-between;
    margin-left: 28px;
    min-width: 200px;
    padding-left: 28px;
    position: relative;
    z-index: 1;
}
.ef-ad-side-label {
    color: rgba(240,253,248,.3);
    font-size: .72rem;
    font-weight: 660;
    letter-spacing: .06em;
    text-transform: uppercase;
}
.ef-ad-side-value {
    color: #f0fdf8;
    font-size: 1.1rem;
    font-weight: 760;
    margin-top: 2px;
}
.ef-ad-hero-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.ef-ad-btn {
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.13);
    border-radius: 10px;
    color: rgba(240,253,248,.82);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: .82rem;
    font-weight: 660;
    padding: 8px 14px;
    text-decoration: none;
    transition: background .18s, color .18s;
    white-space: nowrap;
}
.ef-ad-btn:hover { background: rgba(255,255,255,.14); color: #f0fdf8; }
.ef-ad-btn-primary {
    background: var(--ad-emerald);
    border-color: var(--ad-emerald);
    color: #fff;
}
.ef-ad-btn-primary:hover { background: var(--ad-emerald-hi); border-color: var(--ad-emerald-hi); color: #fff; }

/* ── KPI Metrics Strip ─────────────────────────────────────────── */
.ef-ad-metrics {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 16px;
}
.ef-ad-metric {
    background: var(--ad-surface);
    border: 1px solid var(--ad-border);
    border-top: 3px solid rgba(15,123,95,.15);
    border-radius: var(--ad-radius);
    box-shadow: var(--ad-shadow);
    padding: 18px 18px 16px;
    position: relative;
    transition: box-shadow .18s var(--ad-ease), transform .18s var(--ad-ease);
}
a.ef-ad-metric { text-decoration: none; }
a.ef-ad-metric:hover {
    border-color: var(--ad-border-s);
    border-top-color: var(--ad-emerald);
    box-shadow: var(--ad-shadow-h);
    transform: translateY(-2px);
}
.ef-ad-metric-icon {
    color: var(--ad-emerald);
    float: right;
    font-size: 1rem;
    opacity: .5;
}
.ef-ad-metric-label {
    color: var(--ad-muted);
    font-size: .68rem;
    font-weight: 720;
    letter-spacing: .05em;
    margin-bottom: 6px;
    text-transform: uppercase;
}
.ef-ad-metric-value {
    color: var(--ad-ink);
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -.02em;
    line-height: 1;
}
.ef-ad-metric-note {
    color: var(--ad-muted);
    font-size: .72rem;
    margin-top: 5px;
}
.ef-ad-metric-value.c-emerald { color: var(--ad-emerald); }
.ef-ad-metric-value.c-amber   { color: var(--ad-amber); }
.ef-ad-metric-value.c-danger  { color: var(--ad-danger); }
.ef-ad-metric-value.c-gold    { color: var(--ad-gold); }
.ef-ad-metric-value.c-teal    { color: var(--ad-teal); }
.ef-ad-metric-value.c-muted   { color: var(--ad-muted); }

/* accent top border per metric type */
.ef-ad-metric[data-accent="emerald"] { border-top-color: var(--ad-emerald); }
.ef-ad-metric[data-accent="amber"]   { border-top-color: var(--ad-amber); }
.ef-ad-metric[data-accent="danger"]  { border-top-color: var(--ad-danger); }
.ef-ad-metric[data-accent="gold"]    { border-top-color: var(--ad-gold); }
.ef-ad-metric[data-accent="teal"]    { border-top-color: var(--ad-teal); }
.ef-ad-metric[data-accent="muted"]   { border-top-color: rgba(110,106,100,.2); }

/* ── Command Grid ──────────────────────────────────────────────── */
.ef-ad-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 20px;
    align-items: start;
}

/* ── Content card ──────────────────────────────────────────────── */
.ef-ad-card {
    background: var(--ad-surface);
    border: 1px solid var(--ad-border);
    border-radius: 16px;
    box-shadow: var(--ad-shadow);
    overflow: hidden;
}
.ef-ad-card-head {
    align-items: center;
    border-bottom: 1px solid var(--ad-border);
    display: flex;
    gap: 10px;
    justify-content: space-between;
    padding: 16px 22px;
}
.ef-ad-card-title {
    color: var(--ad-ink);
    font-size: .88rem;
    font-weight: 760;
}
.ef-ad-card-aside {
    color: var(--ad-muted);
    font-size: .82rem;
    font-weight: 660;
    text-decoration: none;
}
a.ef-ad-card-aside { color: var(--ad-emerald); }
a.ef-ad-card-aside:hover { color: var(--ad-emerald-dk); }
.ef-ad-card-body { padding: 18px 22px; }

/* ── Request list items ────────────────────────────────────────── */
.ef-ad-req-list { display: flex; flex-direction: column; }
.ef-ad-req-item {
    align-items: center;
    border-bottom: 1px solid var(--ad-border);
    display: flex;
    gap: 14px;
    padding: 16px 22px;
    text-decoration: none;
    transition: background .14s;
}
.ef-ad-req-item:last-child { border-bottom: none; }
.ef-ad-req-item:hover { background: rgba(15,123,95,.04); }
.ef-ad-req-avatar {
    align-items: center;
    background: rgba(15,123,95,.10);
    border-radius: 50%;
    color: var(--ad-emerald-dk);
    display: flex;
    flex-shrink: 0;
    font-size: .75rem;
    font-weight: 800;
    height: 38px;
    justify-content: center;
    letter-spacing: .04em;
    text-transform: uppercase;
    width: 38px;
}
.ef-ad-req-main { flex: 1; min-width: 0; }
.ef-ad-req-title {
    color: var(--ad-ink);
    font-size: .88rem;
    font-weight: 700;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ef-ad-req-meta {
    color: var(--ad-muted);
    font-size: .76rem;
    margin-top: 3px;
}
.ef-ad-req-right { flex-shrink: 0; text-align: right; }
.ef-ad-req-amount {
    color: var(--ad-ink);
    font-size: 1rem;
    font-weight: 800;
}
.ef-ad-req-time {
    margin-top: 4px;
}

/* ── Priority badge ────────────────────────────────────────────── */
.ef-ad-priority {
    border-radius: 5px;
    display: inline-flex;
    align-items: center;
    font-size: .64rem;
    font-weight: 760;
    letter-spacing: .04em;
    padding: 2px 6px;
    text-transform: uppercase;
}
.ef-ad-priority.urgent { background: rgba(200,75,68,.10); color: #9B2C2C; }
.ef-ad-priority.high   { background: rgba(216,154,61,.12); color: #7D5218; }
.ef-ad-priority.medium { background: rgba(15,123,95,.10); color: var(--ad-emerald-dk); }
.ef-ad-priority.low    { background: rgba(110,106,100,.07); color: #9A9690; }

/* ── Pipeline ──────────────────────────────────────────────────── */
.ef-ad-pipeline {
    display: flex;
    align-items: center;
    gap: 0;
    padding: 4px 4px 8px;
}
.ef-ad-pipe-step {
    flex: 1;
    text-align: center;
    position: relative;
}
.ef-ad-pipe-step + .ef-ad-pipe-step::before {
    background: var(--ad-border);
    content: "";
    height: 2px;
    left: -50%;
    position: absolute;
    top: 16px;
    width: 100%;
}
.ef-ad-pipe-dot {
    align-items: center;
    border-radius: 50%;
    display: inline-flex;
    font-size: .7rem;
    height: 32px;
    justify-content: center;
    margin: 0 auto 6px;
    position: relative;
    width: 32px;
    z-index: 1;
}
.ef-ad-pipe-dot.done    { background: var(--ad-emerald); color: #fff; }
.ef-ad-pipe-dot.active  { background: var(--ad-amber); color: #fff; box-shadow: 0 0 0 4px rgba(216,154,61,.18); }
.ef-ad-pipe-dot.pending { background: var(--ad-faint); color: var(--ad-muted); }
.ef-ad-pipe-label {
    color: var(--ad-muted);
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .03em;
}
.ef-ad-pipe-count {
    color: var(--ad-ink);
    font-size: 1.1rem;
    font-weight: 800;
}

/* ── Wallet alert card ─────────────────────────────────────────── */
.ef-ad-wallet-alert {
    background: linear-gradient(135deg, #1c0505 0%, #2d0b0b 100%);
    border: 1px solid rgba(200,75,68,.22) !important;
    border-radius: 16px;
    overflow: hidden;
    position: relative;
}
.ef-ad-wallet-alert::before {
    background: radial-gradient(circle, rgba(200,75,68,.16) 0%, transparent 68%);
    border-radius: 50%;
    content: "";
    height: 200px;
    pointer-events: none;
    position: absolute;
    right: -40px;
    top: -60px;
    width: 200px;
}

/* ── Action hub tiles ──────────────────────────────────────────── */
.ef-ad-action-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.ef-ad-action-tile {
    align-items: center;
    background: rgba(15,123,95,.06);
    border: 1px solid var(--ad-border);
    border-radius: 12px;
    color: var(--ad-ink);
    display: flex;
    flex-direction: column;
    font-size: .78rem;
    font-weight: 700;
    gap: 6px;
    padding: 14px 10px;
    text-align: center;
    text-decoration: none;
    transition: background .18s var(--ad-ease), transform .14s var(--ad-ease), box-shadow .18s;
}
.ef-ad-action-tile:hover {
    background: rgba(15,123,95,.12);
    border-color: var(--ad-border-s);
    box-shadow: var(--ad-shadow-h);
    color: var(--ad-ink);
    transform: translateY(-2px);
}
.ef-ad-action-tile i { color: var(--ad-emerald); font-size: 1.2rem; }
.ef-ad-action-tile.primary {
    background: var(--ad-emerald);
    border-color: var(--ad-emerald);
    color: #fff;
    grid-column: span 2;
}
.ef-ad-action-tile.primary:hover { background: var(--ad-emerald-hi); border-color: var(--ad-emerald-hi); }
.ef-ad-action-tile.primary i { color: #fff; }

/* ── Summary rows ──────────────────────────────────────────────── */
.ef-ad-summary-row {
    align-items: center;
    border-bottom: 1px solid var(--ad-border);
    display: flex;
    justify-content: space-between;
    padding: 11px 0;
}
.ef-ad-summary-row:last-child { border-bottom: none; }
.ef-ad-summary-label { color: var(--ad-muted); font-size: .8rem; font-weight: 660; }
.ef-ad-summary-val   { color: var(--ad-ink); font-size: .88rem; font-weight: 760; }

/* ── Approval health bar ───────────────────────────────────────── */
.ef-ad-health-bar-track {
    background: var(--ad-faint);
    border-radius: 6px;
    height: 8px;
    overflow: hidden;
}
.ef-ad-health-bar-fill {
    border-radius: 6px;
    height: 8px;
    transition: width .5s var(--ad-ease);
}

/* ── Empty state ───────────────────────────────────────────────── */
.ef-ad-empty { padding: 40px 22px; text-align: center; }
.ef-ad-empty-orb {
    align-items: center;
    background: rgba(15,123,95,.08);
    border: 1px solid var(--ad-border);
    border-radius: 50%;
    color: var(--ad-emerald);
    display: inline-flex;
    font-size: 1.5rem;
    height: 60px;
    justify-content: center;
    margin-bottom: 14px;
    width: 60px;
}
.ef-ad-empty p { color: var(--ad-muted); font-size: .86rem; margin-bottom: 16px; }

/* ── Mobile bottom bar ─────────────────────────────────────────── */
.ef-ad-mobile-bar {
    display: none;
    background: linear-gradient(135deg, rgba(11,34,26,.97) 0%, rgba(18,49,38,.97) 100%) !important;
    border-top: 1px solid rgba(255,255,255,.07) !important;
    bottom: 0; left: 0; right: 0;
    gap: 8px;
    padding: 10px 16px calc(10px + env(safe-area-inset-bottom, 0px));
    position: fixed;
    z-index: 999;
}

/* ── Responsive ────────────────────────────────────────────────── */
@media (max-width: 1199.98px) {
    .ef-ad-metrics { grid-template-columns: repeat(4, 1fr); }
}
@media (max-width: 991.98px) {
    .ef-ad-grid { grid-template-columns: 1fr; }
    .ef-ad-hero { flex-direction: column; gap: 20px; }
    .ef-ad-hero-side {
        border-left: none !important;
        border-top: 1px solid rgba(255,255,255,.07) !important;
        flex-direction: row;
        margin-left: 0;
        padding-left: 0;
        padding-top: 20px;
    }
    .ef-ad-hero-actions { flex-direction: row; flex-wrap: wrap; }
}
@media (max-width: 767.98px) {
    .ef-ad-hero { padding: 22px 18px; border-radius: 16px; }
    .ef-ad-hero-title { font-size: 1.35rem; }
    .ef-ad-metrics { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .ef-ad-mobile-bar { display: flex; }
    .ef-ad-page { padding-bottom: 76px; }
}
@media (max-width: 575.98px) {
    .ef-ad-hero-side { flex-direction: column; }
}
</style>
@endpush

@php
    $hour     = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $name     = explode(' ', auth()->user()->name)[0];

    $priorityMeta = [
        'urgent' => ['cls' => 'urgent'],
        'high'   => ['cls' => 'high'],
        'medium' => ['cls' => 'medium'],
        'low'    => ['cls' => 'low'],
    ];
@endphp

<div class="ef-ad-page">

{{-- ── Hero ────────────────────────────────────────────────────── --}}
<section class="ef-ad-hero mb-4">
    <div class="ef-ad-hero-main">
        <div class="ef-ad-eyebrow">Admin Operations Center</div>
        <h1 class="ef-ad-hero-title">{{ $greeting }}, {{ $name }}</h1>
        <div class="ef-ad-hero-summary">
            <span><b>{{ $stats['pending_approvals'] }}</b> pending approval</span>
            <span class="dot">·</span>
            <span><b>{{ $stats['approved_today'] }}</b> approved today</span>
            <span class="dot">·</span>
            <span><b>₹{{ number_format($stats['total_expenses_month'], 0) }}</b> this month</span>
            @if($stats['low_balance_count'] > 0)
                <span class="dot">·</span>
                <span style="color:rgba(200,75,68,.80)"><b>{{ $stats['low_balance_count'] }}</b> low wallets</span>
            @endif
            <span class="dot">·</span>
            <span>{{ now()->format('d F Y') }}</span>
        </div>
    </div>
    <div class="ef-ad-hero-side">
        <div>
            <div class="ef-ad-side-label">Pending Review</div>
            <div class="ef-ad-side-value">
                @if($stats['pending_approvals'] === 0) All clear
                @else {{ $stats['pending_approvals'] }} waiting
                @endif
            </div>
        </div>
        <div class="ef-ad-hero-actions">
            <a href="{{ route('admin.expense-requests.index', ['status' => 'pending']) }}"
               class="ef-ad-btn ef-ad-btn-primary">
                <i class="bi bi-check2-square"></i> Review Queue
            </a>
            <a href="{{ route('admin.expense-requests.index') }}" class="ef-ad-btn">
                <i class="bi bi-list-ul"></i> All Requests
            </a>
        </div>
    </div>
</section>

{{-- ── KPI Row 1 ───────────────────────────────────────────────── --}}
<div class="ef-ad-metrics mb-3">
    <a href="{{ route('admin.expense-requests.index', ['status' => 'pending']) }}"
       class="ef-ad-metric" data-accent="{{ $stats['pending_approvals'] > 0 ? 'amber' : 'emerald' }}">
        <div class="ef-ad-metric-icon"><i class="bi bi-hourglass-split"></i></div>
        <div class="ef-ad-metric-label">Pending</div>
        <div class="ef-ad-metric-value {{ $stats['pending_approvals'] > 0 ? 'c-amber' : 'c-emerald' }}">{{ $stats['pending_approvals'] }}</div>
        <div class="ef-ad-metric-note">awaiting approval</div>
    </a>
    <div class="ef-ad-metric" data-accent="emerald">
        <div class="ef-ad-metric-icon"><i class="bi bi-check-circle-fill"></i></div>
        <div class="ef-ad-metric-label">Approved Today</div>
        <div class="ef-ad-metric-value c-emerald">{{ $stats['approved_today'] }}</div>
        <div class="ef-ad-metric-note">₹{{ number_format($stats['approved_today_amount'], 0) }}</div>
    </div>
    <a href="{{ route('admin.expense-requests.index', ['status' => 'rejected']) }}"
       class="ef-ad-metric" data-accent="{{ $stats['rejected'] > 0 ? 'danger' : 'muted' }}">
        <div class="ef-ad-metric-icon"><i class="bi bi-x-circle-fill"></i></div>
        <div class="ef-ad-metric-label">Rejected</div>
        <div class="ef-ad-metric-value {{ $stats['rejected'] > 0 ? 'c-danger' : 'c-muted' }}">{{ $stats['rejected'] }}</div>
        <div class="ef-ad-metric-note">all time</div>
    </a>
    <div class="ef-ad-metric" data-accent="teal">
        <div class="ef-ad-metric-icon"><i class="bi bi-currency-rupee"></i></div>
        <div class="ef-ad-metric-label">Month Expenses</div>
        <div class="ef-ad-metric-value c-teal" style="font-size:1.15rem">₹{{ number_format($stats['total_expenses_month'], 0) }}</div>
        <div class="ef-ad-metric-note">{{ now()->format('M Y') }}</div>
    </div>
</div>

{{-- ── KPI Row 2 ───────────────────────────────────────────────── --}}
<div class="ef-ad-metrics mb-4">
    <a href="{{ route('admin.wallets.index') }}"
       class="ef-ad-metric" data-accent="{{ $stats['low_balance_count'] > 0 ? 'danger' : 'emerald' }}">
        <div class="ef-ad-metric-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div class="ef-ad-metric-label">Low Wallet Alerts</div>
        <div class="ef-ad-metric-value {{ $stats['low_balance_count'] > 0 ? 'c-danger' : 'c-emerald' }}">{{ $stats['low_balance_count'] }}</div>
        <div class="ef-ad-metric-note">balance &lt; ₹500</div>
    </a>
    <a href="{{ route('admin.employees.index') }}" class="ef-ad-metric" data-accent="gold">
        <div class="ef-ad-metric-icon"><i class="bi bi-people-fill"></i></div>
        <div class="ef-ad-metric-label">Team Size</div>
        <div class="ef-ad-metric-value">{{ $stats['total_employees'] }}</div>
        <div class="ef-ad-metric-note">{{ $stats['total_managers'] }} managers</div>
    </a>
    <a href="{{ route('admin.wallets.index') }}" class="ef-ad-metric" data-accent="emerald">
        <div class="ef-ad-metric-icon"><i class="bi bi-wallet2"></i></div>
        <div class="ef-ad-metric-label">Total Wallet</div>
        <div class="ef-ad-metric-value c-emerald" style="font-size:1.15rem">₹{{ number_format($stats['total_wallet_balance'], 0) }}</div>
        <div class="ef-ad-metric-note">across all employees</div>
    </a>
    @if($stats['pending_reimb_count'] > 0)
    <a href="{{ route('admin.reports.reimbursement') }}" class="ef-ad-metric" data-accent="gold">
        <div class="ef-ad-metric-icon"><i class="bi bi-arrow-return-left"></i></div>
        <div class="ef-ad-metric-label">Reimb. Pending</div>
        <div class="ef-ad-metric-value c-gold">{{ $stats['pending_reimb_count'] }}</div>
        <div class="ef-ad-metric-note">₹{{ number_format($stats['pending_reimb_amount'], 0) }}</div>
    </a>
    @else
    <div class="ef-ad-metric" data-accent="muted">
        <div class="ef-ad-metric-icon"><i class="bi bi-arrow-return-left"></i></div>
        <div class="ef-ad-metric-label">Reimb. Pending</div>
        <div class="ef-ad-metric-value c-emerald">0</div>
        <div class="ef-ad-metric-note">all settled</div>
    </div>
    @endif
</div>

{{-- ── Approval Pipeline ───────────────────────────────────────── --}}
<div class="ef-ad-card mb-4">
    <div class="ef-ad-card-head">
        <span class="ef-ad-card-title">
            <i class="bi bi-diagram-3 me-2" style="color:var(--ad-emerald)"></i>Approval Pipeline
        </span>
        <span class="ef-ad-card-aside">live flow</span>
    </div>
    <div class="ef-ad-card-body">
        <div class="ef-ad-pipeline">
            <div class="ef-ad-pipe-step">
                <div class="ef-ad-pipe-dot done"><i class="bi bi-upload"></i></div>
                <div class="ef-ad-pipe-count">{{ $stats['total_submitted'] }}</div>
                <div class="ef-ad-pipe-label">Submitted</div>
            </div>
            <div class="ef-ad-pipe-step">
                <div class="ef-ad-pipe-dot {{ $stats['pending_approvals'] > 0 ? 'active' : 'done' }}">
                    <i class="bi bi-eye"></i>
                </div>
                <div class="ef-ad-pipe-count">{{ $stats['pending_approvals'] }}</div>
                <div class="ef-ad-pipe-label">Manager Review</div>
            </div>
            <div class="ef-ad-pipe-step">
                <div class="ef-ad-pipe-dot done"><i class="bi bi-check-lg"></i></div>
                <div class="ef-ad-pipe-count">{{ $stats['approved_total'] }}</div>
                <div class="ef-ad-pipe-label">Approved</div>
            </div>
            <div class="ef-ad-pipe-step">
                <div class="ef-ad-pipe-dot {{ $stats['paid_total'] > 0 ? 'done' : 'pending' }}">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="ef-ad-pipe-count">{{ $stats['paid_total'] }}</div>
                <div class="ef-ad-pipe-label">Paid</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Command Grid ─────────────────────────────────────────────── --}}
<div class="ef-ad-grid">
    <div class="d-flex flex-column gap-4">

        {{-- Recent Requests --}}
        <div class="ef-ad-card">
            <div class="ef-ad-card-head">
                <span class="ef-ad-card-title">
                    <i class="bi bi-clock-history me-2" style="color:var(--ad-gold)"></i>
                    Recent Requests
                    @if($stats['pending_approvals'] > 0)
                        <span class="ms-2" style="background:rgba(216,154,61,.12);color:var(--ad-amber);font-size:.68rem;font-weight:760;border-radius:6px;padding:2px 8px">
                            {{ $stats['pending_approvals'] }} pending
                        </span>
                    @endif
                </span>
                <a href="{{ route('admin.expense-requests.index') }}" class="ef-ad-card-aside">View all →</a>
            </div>
            <div class="ef-ad-req-list">
                @forelse($recentRequests as $req)
                    @php
                        $initials = collect(explode(' ', $req->requester->name ?? 'UN'))
                                        ->take(2)->map(fn($w) => strtoupper($w[0]))->join('');
                        $pm = $priorityMeta[$req->priority ?? 'low'];
                    @endphp
                    <a href="{{ route('admin.expense-requests.show', $req) }}" class="ef-ad-req-item">
                        <div class="ef-ad-req-avatar">{{ $initials }}</div>
                        <div class="ef-ad-req-main">
                            <div class="ef-ad-req-title">{{ $req->title }}</div>
                            <div class="ef-ad-req-meta">
                                {{ $req->requester->name ?? '—' }}
                                @if($req->category)· {{ $req->category->name }}@endif
                                <span class="ms-2 ef-ad-priority {{ $pm['cls'] }}">{{ ucfirst($req->priority ?? 'low') }}</span>
                            </div>
                        </div>
                        <div class="ef-ad-req-right">
                            <div class="ef-ad-req-amount">₹{{ number_format($req->amount, 0) }}</div>
                            <div class="ef-ad-req-time">
                                <x-status-badge :status="$req->status" />
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="ef-ad-empty">
                        <div class="ef-ad-empty-orb"><i class="bi bi-inbox"></i></div>
                        <p>No requests yet.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Approval Health --}}
        <div class="ef-ad-card">
            <div class="ef-ad-card-head">
                <span class="ef-ad-card-title">Approval Health</span>
            </div>
            <div class="ef-ad-card-body">
                @php
                    $total        = $stats['total_processed'] ?: 1;
                    $approvalRate = round(($stats['approved_total'] / $total) * 100);
                    $rateColor    = $approvalRate >= 70
                        ? 'var(--ad-emerald)'
                        : ($approvalRate >= 40 ? 'var(--ad-amber)' : 'var(--ad-danger)');
                @endphp
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span style="font-size:.75rem;font-weight:720;color:var(--ad-muted);text-transform:uppercase;letter-spacing:.05em">Approval Rate</span>
                    <span style="font-size:1.15rem;font-weight:800;color:{{ $rateColor }}">{{ $approvalRate }}%</span>
                </div>
                <div class="ef-ad-health-bar-track">
                    <div class="ef-ad-health-bar-fill" style="background:{{ $rateColor }};width:{{ $approvalRate }}%"></div>
                </div>
                <div class="mt-3 d-flex gap-3">
                    <div style="flex:1;text-align:center">
                        <div style="font-size:1.1rem;font-weight:800;color:var(--ad-emerald)">{{ $stats['approved_total'] }}</div>
                        <div style="font-size:.72rem;color:var(--ad-muted);font-weight:700;text-transform:uppercase;letter-spacing:.04em">Approved</div>
                    </div>
                    <div style="width:1px;background:var(--ad-border)"></div>
                    <div style="flex:1;text-align:center">
                        <div style="font-size:1.1rem;font-weight:800;color:var(--ad-danger)">{{ $stats['rejected'] }}</div>
                        <div style="font-size:.72rem;color:var(--ad-muted);font-weight:700;text-transform:uppercase;letter-spacing:.04em">Rejected</div>
                    </div>
                    <div style="width:1px;background:var(--ad-border)"></div>
                    <div style="flex:1;text-align:center">
                        <div style="font-size:1.1rem;font-weight:800;color:var(--ad-amber)">{{ $stats['pending_approvals'] }}</div>
                        <div style="font-size:.72rem;color:var(--ad-muted);font-weight:700;text-transform:uppercase;letter-spacing:.04em">Pending</div>
                    </div>
                    <div style="width:1px;background:var(--ad-border)"></div>
                    <div style="flex:1;text-align:center">
                        <div style="font-size:1.1rem;font-weight:800;color:var(--ad-teal)">{{ $stats['paid_total'] }}</div>
                        <div style="font-size:.72rem;color:var(--ad-muted);font-weight:700;text-transform:uppercase;letter-spacing:.04em">Paid</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Sidebar ─────────────────────────────────────────────── --}}
    <div class="d-flex flex-column gap-4">

        {{-- Wallet Alert (premium dark card when alerts exist) --}}
        @if($stats['low_balance_count'] > 0)
        <div class="ef-ad-wallet-alert">
            <div class="ef-ad-card-head" style="border-color:rgba(200,75,68,.18)">
                <span class="ef-ad-card-title" style="color:#fca5a5">
                    <i class="bi bi-exclamation-triangle-fill me-2" style="color:#f87171"></i>Wallet Alerts
                </span>
                <span style="color:#f87171;font-size:.78rem;font-weight:760">{{ $stats['low_balance_count'] }} critical</span>
            </div>
            <div class="ef-ad-card-body" style="position:relative;z-index:1">
                <p style="color:rgba(252,165,165,.78);font-size:.83rem;margin-bottom:14px">
                    {{ $stats['low_balance_count'] }} wallet(s) below ₹500. Top up to prevent payment failures.
                </p>
                @if($stats['pending_reimb_count'] > 0)
                <p style="color:rgba(252,165,165,.65);font-size:.78rem;margin-bottom:14px">
                    <i class="bi bi-arrow-return-left me-1"></i>
                    {{ $stats['pending_reimb_count'] }} reimbursement(s) — ₹{{ number_format($stats['pending_reimb_amount'], 0) }}
                </p>
                @endif
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.wallets.index') }}"
                       style="background:rgba(200,75,68,.75);border:1px solid rgba(200,75,68,.4);border-radius:9px;color:#fff;font-size:.78rem;font-weight:700;padding:7px 14px;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
                        <i class="bi bi-wallet2"></i> Manage Wallets
                    </a>
                    @if($stats['pending_reimb_count'] > 0)
                    <a href="{{ route('admin.reports.reimbursement') }}"
                       style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.13);border-radius:9px;color:rgba(255,255,255,.78);font-size:.78rem;font-weight:700;padding:7px 14px;text-decoration:none">
                        Reimbursements
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Action Hub --}}
        <div class="ef-ad-card">
            <div class="ef-ad-card-head">
                <span class="ef-ad-card-title">Action Hub</span>
            </div>
            <div class="ef-ad-card-body">
                <div class="ef-ad-action-grid">
                    <a href="{{ route('admin.expense-requests.index', ['status' => 'pending']) }}"
                       class="ef-ad-action-tile primary">
                        <i class="bi bi-check2-square"></i>
                        Review Pending ({{ $stats['pending_approvals'] }})
                    </a>
                    <a href="{{ route('admin.employees.create') }}" class="ef-ad-action-tile">
                        <i class="bi bi-person-plus-fill"></i>
                        Add Employee
                    </a>
                    <a href="{{ route('admin.wallets.index') }}" class="ef-ad-action-tile">
                        <i class="bi bi-wallet2"></i>
                        Wallets
                    </a>
                    <a href="{{ route('admin.categories.create') }}" class="ef-ad-action-tile">
                        <i class="bi bi-tag-fill"></i>
                        Add Category
                    </a>
                    <a href="{{ route('admin.vendors.create') }}" class="ef-ad-action-tile">
                        <i class="bi bi-shop"></i>
                        Add Vendor
                    </a>
                    <a href="{{ route('admin.expense-requests.index', ['status' => 'rejected']) }}"
                       class="ef-ad-action-tile">
                        <i class="bi bi-x-circle"></i>
                        Rejected
                    </a>
                </div>
            </div>
        </div>

        {{-- System Summary --}}
        <div class="ef-ad-card">
            <div class="ef-ad-card-head">
                <span class="ef-ad-card-title">
                    <i class="bi bi-info-circle me-2" style="color:var(--ad-gold)"></i>System Summary
                </span>
            </div>
            <div class="ef-ad-card-body">
                <div class="ef-ad-summary-row">
                    <span class="ef-ad-summary-label">Active Users</span>
                    <span class="ef-ad-summary-val">{{ $stats['active_users'] }}</span>
                </div>
                <div class="ef-ad-summary-row">
                    <span class="ef-ad-summary-label">Inactive Users</span>
                    <span class="ef-ad-summary-val" style="color:var(--ad-muted)">{{ $stats['inactive_users'] }}</span>
                </div>
                <div class="ef-ad-summary-row">
                    <span class="ef-ad-summary-label">Managers</span>
                    <span class="ef-ad-summary-val">{{ $stats['total_managers'] }}</span>
                </div>
                <div class="ef-ad-summary-row">
                    <span class="ef-ad-summary-label">Employees</span>
                    <span class="ef-ad-summary-val">{{ $stats['total_employees'] }}</span>
                </div>
                <div class="ef-ad-summary-row">
                    <span class="ef-ad-summary-label">Total Requests</span>
                    <span class="ef-ad-summary-val">{{ $stats['total_submitted'] }}</span>
                </div>
                <div class="ef-ad-summary-row">
                    <span class="ef-ad-summary-label">Wallet Balance</span>
                    <span class="ef-ad-summary-val" style="color:var(--ad-emerald)">₹{{ number_format($stats['total_wallet_balance'], 0) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

{{-- Mobile bottom bar --}}
<div class="ef-ad-mobile-bar">
    <a href="{{ route('admin.expense-requests.index', ['status' => 'pending']) }}"
       class="ef-ad-btn ef-ad-btn-primary flex-fill justify-content-center">
        <i class="bi bi-check2-square"></i> Review ({{ $stats['pending_approvals'] }})
    </a>
    <a href="{{ route('admin.wallets.index') }}"
       class="ef-ad-btn flex-fill justify-content-center">
        <i class="bi bi-wallet2"></i> Wallets
    </a>
    <a href="{{ route('admin.employees.index') }}"
       class="ef-ad-btn flex-fill justify-content-center">
        <i class="bi bi-people"></i> Team
    </a>
</div>

</x-admin-layout>
