<x-admin-layout title="My Workspace">
@push('styles')
<style>
/* ════════════════════════════════════════════════════════════
   EMPLOYEE WORKSPACE — ef-ew-* namespace
   Premium productivity workspace, distinct from admin UI
   ════════════════════════════════════════════════════════════ */
:root {
    --ew-emerald:    #0F7B5F;
    --ew-emerald-hi: #22845a;
    --ew-gold:       #B8893E;
    --ew-gold-hi:    #D6B97A;
    --ew-danger:     #b91c1c;
    --ew-amber:      #d97706;
    --ew-indigo:     #4338ca;
    --ew-surface:    #fff;
    --ew-border:     #e8e3dc;
    --ew-text:       #1c1612;
    --ew-muted:      #9c8e7e;
}

/* ── Personal Hero ────────────────────────────────────────── */
.ef-ew-hero {
    background: linear-gradient(135deg, #0f1c14 0%, #152a1e 45%, #0d1f16 100%);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 24px;
    padding: 36px 40px;
    position: relative;
    overflow: hidden;
    margin-bottom: 20px;
}
.ef-ew-hero::before {
    content: '';
    position: absolute;
    background: radial-gradient(circle, rgba(26,102,69,.32) 0%, transparent 65%);
    height: 520px; width: 520px;
    right: -60px; top: -180px;
    pointer-events: none;
}
.ef-ew-hero::after {
    content: '';
    position: absolute;
    background: radial-gradient(circle, rgba(160,114,56,.12) 0%, transparent 65%);
    height: 300px; width: 300px;
    bottom: -80px; left: 20%;
    pointer-events: none;
}

/* Floating dot accent */
.ef-ew-hero-dot {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
}
.ef-ew-hero-dot-1 {
    width: 6px; height: 6px;
    background: rgba(26,180,96,.5);
    top: 40px; right: 220px;
    animation: ew-float 4s ease-in-out infinite;
}
.ef-ew-hero-dot-2 {
    width: 4px; height: 4px;
    background: rgba(160,114,56,.6);
    top: 80px; right: 180px;
    animation: ew-float 5s ease-in-out infinite reverse;
}
@keyframes ew-float {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-6px); }
}

.ef-ew-hero-inner {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 24px;
    position: relative;
    z-index: 1;
}
.ef-ew-greeting {
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: rgba(26,180,96,.8);
    margin-bottom: 6px;
}
.ef-ew-hero-name {
    font-size: 1.75rem;
    font-weight: 800;
    color: #f0fdf4;
    line-height: 1.1;
    margin-bottom: 4px;
}
.ef-ew-hero-role {
    font-size: .8rem;
    font-weight: 600;
    color: rgba(255,253,250,.4);
    text-transform: capitalize;
    margin-bottom: 20px;
    letter-spacing: .03em;
}

/* Wallet pod in hero */
.ef-ew-wallet-pod {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 16px;
    padding: 14px 20px;
}
.ef-ew-wallet-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.wallet-healthy { background: rgba(26,102,69,.4); color: #4ade80; }
.wallet-low     { background: rgba(217,119,6,.3);  color: #fbbf24; }
.wallet-neg     { background: rgba(185,28,28,.3);  color: #f87171; }
.ef-ew-wallet-lbl {
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: rgba(255,253,250,.45);
    margin-bottom: 2px;
}
.ef-ew-wallet-amt {
    font-size: 1.4rem;
    font-weight: 800;
    line-height: 1;
}
.wallet-healthy .ef-ew-wallet-amt, .wallet-text-ok   { color: #4ade80; }
.wallet-low     .ef-ew-wallet-amt, .wallet-text-low  { color: #fbbf24; }
.wallet-neg     .ef-ew-wallet-amt, .wallet-text-neg  { color: #f87171; }

/* Hero status pills */
.ef-ew-hero-pills {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 16px;
}
.ef-ew-hero-pill {
    font-size: .72rem;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.pill-pending  { background: rgba(217,119,6,.18);  color: #fbbf24; border: 1px solid rgba(217,119,6,.25); }
.pill-approved { background: rgba(26,180,96,.15);  color: #4ade80; border: 1px solid rgba(26,180,96,.2); }
.pill-reimb    { background: rgba(99,102,241,.18); color: #a5b4fc; border: 1px solid rgba(99,102,241,.25); }

/* Hero CTA */
.ef-ew-hero-cta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 12px;
    flex-shrink: 0;
}
.ef-ew-btn-primary {
    background: var(--ew-emerald);
    border: 1px solid var(--ew-emerald-hi);
    color: #fff;
    font-size: .9rem;
    font-weight: 700;
    padding: 14px 24px;
    border-radius: 14px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    letter-spacing: .02em;
    box-shadow: 0 4px 16px rgba(26,102,69,.35);
    transition: background .15s, transform .12s, box-shadow .15s;
}
.ef-ew-btn-primary:hover {
    background: var(--ew-emerald-hi);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(26,102,69,.45);
}
.ef-ew-btn-ghost {
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.12);
    color: rgba(255,253,250,.7);
    font-size: .78rem;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 10px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    transition: background .15s, color .15s;
}
.ef-ew-btn-ghost:hover {
    background: rgba(255,255,255,.12);
    color: #fff;
}

/* ── KPI Strip ─────────────────────────────────────────────── */
.ef-ew-kpi {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}
.ef-ew-kpi-card {
    background: var(--ew-surface);
    border: 1px solid var(--ew-border);
    border-radius: 16px;
    padding: 18px 16px;
    position: relative;
    overflow: hidden;
    cursor: pointer;
    text-decoration: none;
    display: block;
    transition: box-shadow .15s, transform .15s;
}
a.ef-ew-kpi-card:hover, .ef-ew-kpi-card:hover {
    box-shadow: 0 4px 20px rgba(26,102,69,.1);
    transform: translateY(-2px);
    text-decoration: none;
}
.ef-ew-kpi-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 16px 16px 0 0;
}
.kpi-total::before   { background: #94a3b8; }
.kpi-pending::before { background: var(--ew-amber); }
.kpi-approved::before{ background: var(--ew-emerald); }
.kpi-wallet::before  { background: #0891b2; }
.kpi-month::before   { background: var(--ew-indigo); }
.kpi-reimb::before   { background: #7c3aed; }

.ef-ew-kpi-icon {
    width: 34px; height: 34px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .9rem;
    margin-bottom: 12px;
}
.icon-total   { background: #f1f5f9; color: #64748b; }
.icon-pending { background: #fef3c7; color: var(--ew-amber); }
.icon-approved{ background: #dcfce7; color: var(--ew-emerald); }
.icon-wallet  { background: #cffafe; color: #0891b2; }
.icon-month   { background: #ede9fe; color: var(--ew-indigo); }
.icon-reimb   { background: #f3e8ff; color: #7c3aed; }

.ef-ew-kpi-val {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--ew-text);
    line-height: 1;
    margin-bottom: 4px;
}
.ef-ew-kpi-val.is-alert { color: var(--ew-danger); }
.ef-ew-kpi-val.is-warn  { color: var(--ew-amber); }
.ef-ew-kpi-lbl {
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: var(--ew-muted);
    margin-bottom: 0;
}

/* ── Main layout ──────────────────────────────────────────── */
.ef-ew-main {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 20px;
    align-items: start;
}

/* ── Activity feed ─────────────────────────────────────────── */
.ef-ew-panel {
    background: var(--ew-surface);
    border: 1px solid var(--ew-border);
    border-radius: 20px;
    overflow: hidden;
}
.ef-ew-panel-head {
    padding: 18px 22px 14px;
    border-bottom: 1px solid #f5f1eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.ef-ew-panel-title {
    font-size: .8rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--ew-muted);
}
.ef-ew-panel-link {
    font-size: .78rem;
    font-weight: 600;
    color: var(--ew-emerald);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: color .12s;
}
.ef-ew-panel-link:hover { color: var(--ew-emerald-hi); }

/* Activity cards */
.ef-ew-activity-item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 16px 22px;
    border-bottom: 1px solid #faf7f4;
    text-decoration: none;
    transition: background .12s;
}
.ef-ew-activity-item:last-child { border-bottom: none; }
.ef-ew-activity-item:hover { background: #faf7f4; text-decoration: none; }

.ef-ew-activity-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    margin-top: 6px;
    flex-shrink: 0;
}
.dot-pending  { background: var(--ew-amber); box-shadow: 0 0 0 3px rgba(217,119,6,.15); }
.dot-approved { background: #22c55e;         box-shadow: 0 0 0 3px rgba(34,197,94,.15); }
.dot-rejected { background: var(--ew-danger);box-shadow: 0 0 0 3px rgba(185,28,28,.12); }
.dot-paid     { background: #0891b2;         box-shadow: 0 0 0 3px rgba(8,145,178,.15); }
.dot-reimb    { background: #7c3aed;         box-shadow: 0 0 0 3px rgba(124,58,237,.15); }
.dot-other    { background: #94a3b8; }

.ef-ew-activity-title {
    font-size: .88rem;
    font-weight: 700;
    color: var(--ew-text);
    margin-bottom: 3px;
    line-height: 1.3;
}
.ef-ew-activity-meta {
    font-size: .73rem;
    color: var(--ew-muted);
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.ef-ew-activity-meta .sep { opacity: .4; }
.ef-ew-activity-amount {
    font-size: .92rem;
    font-weight: 800;
    color: var(--ew-text);
    white-space: nowrap;
    margin-left: auto;
    flex-shrink: 0;
}
.ef-ew-activity-status {
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    padding: 2px 8px;
    border-radius: 12px;
    border: 1px solid;
}
.stat-pending   { background: #fef3c7; color: #b45309; border-color: #fde68a; }
.stat-approved  { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
.stat-rejected  { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
.stat-paid      { background: #cffafe; color: #0e7490; border-color: #a5f3fc; }
.stat-reimbursement_pending { background: #ede9fe; color: #5b21b6; border-color: #ddd6fe; }
.stat-reimbursed{ background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
.stat-completed { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
.stat-pending_payment { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }

/* Empty state */
.ef-ew-empty {
    text-align: center;
    padding: 56px 24px;
    color: var(--ew-muted);
}
.ef-ew-empty-icon {
    width: 60px; height: 60px;
    border-radius: 50%;
    background: #f5f1eb;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
    font-size: 1.4rem;
    color: var(--ew-emerald);
}

/* ── Right sidebar ─────────────────────────────────────────── */
.ef-ew-sidebar { display: flex; flex-direction: column; gap: 16px; }

/* Action workspace */
.ef-ew-actions-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    padding: 18px;
}
.ef-ew-action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 18px 12px;
    border-radius: 14px;
    border: 1px solid var(--ew-border);
    background: #faf8f5;
    text-decoration: none;
    text-align: center;
    transition: all .15s;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}
.ef-ew-action-card:hover {
    box-shadow: 0 4px 16px rgba(26,102,69,.1);
    border-color: rgba(26,102,69,.2);
    transform: translateY(-1px);
    text-decoration: none;
}
.ef-ew-action-card.primary-action {
    grid-column: 1 / -1;
    flex-direction: row;
    justify-content: center;
    gap: 10px;
    padding: 16px;
    background: var(--ew-emerald);
    border-color: var(--ew-emerald);
}
.ef-ew-action-card.primary-action:hover {
    background: var(--ew-emerald-hi);
    border-color: var(--ew-emerald-hi);
    box-shadow: 0 4px 16px rgba(26,102,69,.3);
}
.ef-ew-action-card.primary-action .ef-ew-action-lbl,
.ef-ew-action-card.primary-action .ef-ew-action-icon { color: #fff; }

.ef-ew-action-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}
.action-submit  .ef-ew-action-icon { background: transparent; font-size: 1.2rem; }
.action-pending .ef-ew-action-icon { background: #fef3c7; color: var(--ew-amber); }
.action-approved.ef-ew-action-card .ef-ew-action-icon { background: #dcfce7; color: var(--ew-emerald); }
.action-wallet  .ef-ew-action-icon { background: #cffafe; color: #0891b2; }
.ef-ew-action-lbl {
    font-size: .78rem;
    font-weight: 700;
    color: var(--ew-text);
    line-height: 1.2;
}
.ef-ew-action-count {
    font-size: .7rem;
    font-weight: 600;
    color: var(--ew-muted);
    margin-top: 1px;
}
.ef-ew-action-badge {
    position: absolute;
    top: 8px; right: 8px;
    background: var(--ew-amber);
    color: #fff;
    font-size: .6rem;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 10px;
    line-height: 1.2;
}

/* Wallet card */
.ef-ew-wallet-card {
    padding: 22px;
}
.ef-ew-wallet-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}
.ef-ew-wallet-balance {
    font-size: 2rem;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 4px;
}
.color-ok  { color: var(--ew-emerald); }
.color-low { color: var(--ew-amber); }
.color-neg { color: var(--ew-danger); }
.ef-ew-wallet-status-pill {
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 12px;
    border: 1px solid;
}
.wstatus-ok  { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
.wstatus-low { background: #fef3c7; color: #b45309; border-color: #fde68a; }
.wstatus-neg { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
.ef-ew-wallet-sub { font-size: .75rem; color: var(--ew-muted); margin-bottom: 16px; }
.ef-ew-wallet-cta {
    display: block;
    text-align: center;
    font-size: .8rem;
    font-weight: 600;
    color: #0891b2;
    text-decoration: none;
    padding: 10px;
    border: 1px solid #a5f3fc;
    border-radius: 10px;
    background: #f0fdfe;
    transition: all .15s;
}
.ef-ew-wallet-cta:hover {
    background: #cffafe;
    border-color: #67e8f9;
    color: #0e7490;
}

/* ── Alert banner ─────────────────────────────────────────── */
.ef-ew-alert {
    border-radius: 14px;
    padding: 14px 18px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 16px;
    font-size: .84rem;
    font-weight: 500;
}
.ef-ew-alert-danger {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
}
.ef-ew-alert-warning {
    background: #fffbeb;
    border: 1px solid #fde68a;
    color: #92400e;
}
.ef-ew-alert i { flex-shrink: 0; margin-top: 1px; }
.ef-ew-alert-close {
    margin-left: auto;
    background: none;
    border: none;
    color: inherit;
    opacity: .5;
    cursor: pointer;
    padding: 0;
    flex-shrink: 0;
    font-size: 1rem;
    transition: opacity .12s;
}
.ef-ew-alert-close:hover { opacity: 1; }

/* ── Floating FAB (mobile only) ────────────────────────────── */
.ef-ew-fab {
    display: none;
    position: fixed;
    bottom: 24px;
    right: 20px;
    z-index: 1030;
    background: var(--ew-emerald);
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 58px; height: 58px;
    font-size: 1.4rem;
    box-shadow: 0 4px 20px rgba(26,102,69,.45);
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: background .15s, transform .15s;
}
.ef-ew-fab:hover { background: var(--ew-emerald-hi); color: #fff; transform: scale(1.05); }

/* ── Responsive ────────────────────────────────────────────── */
@media (max-width: 1199.98px) {
    .ef-ew-kpi      { grid-template-columns: repeat(3, 1fr); }
    .ef-ew-main     { grid-template-columns: 1fr; }
    .ef-ew-sidebar  { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .ef-ew-hero     { padding: 28px 28px; }
}
@media (max-width: 767.98px) {
    .ef-ew-hero         { padding: 22px 20px; border-radius: 18px; }
    .ef-ew-hero-inner   { flex-direction: column; gap: 20px; }
    .ef-ew-hero-cta     { align-items: flex-start; flex-direction: row; flex-wrap: wrap; }
    .ef-ew-hero-name    { font-size: 1.4rem; }
    .ef-ew-wallet-pod   { width: 100%; }
    .ef-ew-kpi          { grid-template-columns: repeat(2, 1fr); }
    .ef-ew-kpi-val      { font-size: 1.25rem; }
    .ef-ew-main         { grid-template-columns: 1fr; }
    .ef-ew-sidebar      { grid-template-columns: 1fr; }
    .ef-ew-fab          { display: flex; }
    /* FAB positioned above mobile nav — see mobile.css .ef-mobile-fab */
    .ef-ew-fab          { bottom: calc(var(--ef-mobile-nav-height, 0px) + 16px + env(safe-area-inset-bottom, 0px)); z-index: 1050; }
    /* Last content section clears FAB (58px) + gap (16px) + nav (--ef-mobile-nav-height) */
    .ef-ew-main         { padding-bottom: calc(var(--ef-mobile-nav-height, 0px) + 90px + env(safe-area-inset-bottom, 0px)); }
}
@media (max-width: 479.98px) {
    .ef-ew-kpi { grid-template-columns: repeat(2, 1fr); }
    .ef-ew-hero-pills { gap: 6px; }
}
</style>
@endpush

{{-- ── Wallet alerts ──────────────────────────────────────────── --}}
@if($stats['wallet_negative'])
<div class="ef-ew-alert ef-ew-alert-danger" role="alert">
    <i class="bi bi-exclamation-circle-fill"></i>
    <div>Wallet balance is <strong>negative (₹{{ number_format($stats['wallet_balance'], 2) }})</strong>. Contact admin to resolve.</div>
    <button class="ef-ew-alert-close" onclick="this.closest('.ef-ew-alert').remove()"><i class="bi bi-x"></i></button>
</div>
@elseif($stats['wallet_low'])
<div class="ef-ew-alert ef-ew-alert-warning" role="alert">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <div>Wallet balance is low (<strong>₹{{ number_format($stats['wallet_balance'], 2) }}</strong>). Contact admin to top up.</div>
    <button class="ef-ew-alert-close" onclick="this.closest('.ef-ew-alert').remove()"><i class="bi bi-x"></i></button>
</div>
@endif

{{-- ── Personal Hero ───────────────────────────────────────────── --}}
<div class="ef-ew-hero">
    <div class="ef-ew-hero-dot ef-ew-hero-dot-1"></div>
    <div class="ef-ew-hero-dot ef-ew-hero-dot-2"></div>

    <div class="ef-ew-hero-inner">
        <div>
            @php
                $hour = now()->hour;
                $greet = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
            @endphp
            <p class="ef-ew-greeting">{{ $greet }}</p>
            <h1 class="ef-ew-hero-name">{{ auth()->user()->name }}</h1>
            <p class="ef-ew-hero-role">{{ ucfirst(auth()->user()->role) }}</p>

            {{-- Wallet pod --}}
            @php
                $walletClass = $stats['wallet_negative'] ? 'wallet-neg' : ($stats['wallet_low'] ? 'wallet-low' : 'wallet-healthy');
                $walletTextClass = $stats['wallet_negative'] ? 'wallet-text-neg' : ($stats['wallet_low'] ? 'wallet-text-low' : 'wallet-text-ok');
            @endphp
            <div class="ef-ew-wallet-pod">
                <div class="ef-ew-wallet-icon {{ $walletClass }}">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div>
                    <div class="ef-ew-wallet-lbl">Wallet Balance</div>
                    <div class="ef-ew-wallet-amt {{ $walletClass }}">
                        ₹{{ number_format($stats['wallet_balance'], 2) }}
                    </div>
                </div>
            </div>

            {{-- Status pills --}}
            <div class="ef-ew-hero-pills">
                @if($stats['pending_requests'] > 0)
                <span class="ef-ew-hero-pill pill-pending">
                    <i class="bi bi-hourglass-split" style="font-size:.65rem"></i>
                    {{ $stats['pending_requests'] }} pending
                </span>
                @endif
                @if($stats['approved_requests'] > 0)
                <span class="ef-ew-hero-pill pill-approved">
                    <i class="bi bi-check-circle" style="font-size:.65rem"></i>
                    {{ $stats['approved_requests'] }} approved
                </span>
                @endif
                @if($stats['reimbursement_pending'] > 0)
                <span class="ef-ew-hero-pill pill-reimb">
                    <i class="bi bi-arrow-return-left" style="font-size:.65rem"></i>
                    ₹{{ number_format($stats['reimbursement_pending'], 0) }} reimbursement due
                </span>
                @endif
            </div>
        </div>

        <div class="ef-ew-hero-cta">
            <a href="{{ route('employee.expense-requests.create') }}" class="ef-ew-btn-primary">
                <i class="bi bi-plus-lg"></i> New Expense
            </a>
            <a href="{{ route('employee.expense-requests.index') }}" class="ef-ew-btn-ghost">
                <i class="bi bi-list-ul"></i> All Requests
            </a>
        </div>
    </div>
</div>

{{-- ── KPI Strip ───────────────────────────────────────────────── --}}
<div class="ef-ew-kpi">
    <div class="ef-ew-kpi-card kpi-total">
        <div class="ef-ew-kpi-icon icon-total"><i class="bi bi-files"></i></div>
        <div class="ef-ew-kpi-val">{{ $stats['my_requests'] }}</div>
        <p class="ef-ew-kpi-lbl">Total Requests</p>
    </div>
    <a href="{{ route('employee.expense-requests.index', ['status' => 'pending']) }}"
       class="ef-ew-kpi-card kpi-pending">
        <div class="ef-ew-kpi-icon icon-pending"><i class="bi bi-hourglass-split"></i></div>
        <div class="ef-ew-kpi-val {{ $stats['pending_requests'] > 0 ? 'is-warn' : '' }}">
            {{ $stats['pending_requests'] }}
        </div>
        <p class="ef-ew-kpi-lbl">Awaiting Approval</p>
    </a>
    <a href="{{ route('employee.expense-requests.index', ['status' => 'approved']) }}"
       class="ef-ew-kpi-card kpi-approved">
        <div class="ef-ew-kpi-icon icon-approved"><i class="bi bi-check-circle-fill"></i></div>
        <div class="ef-ew-kpi-val">₹{{ number_format($stats['approved_amount'], 0) }}</div>
        <p class="ef-ew-kpi-lbl">Approved Amount</p>
    </a>
    <a href="{{ route('employee.wallet.show') }}" class="ef-ew-kpi-card kpi-wallet">
        <div class="ef-ew-kpi-icon icon-wallet"><i class="bi bi-wallet2"></i></div>
        <div class="ef-ew-kpi-val
            {{ $stats['wallet_negative'] ? 'is-alert' : ($stats['wallet_low'] ? 'is-warn' : '') }}">
            ₹{{ number_format($stats['wallet_balance'], 0) }}
        </div>
        <p class="ef-ew-kpi-lbl">Wallet Balance</p>
    </a>
    <div class="ef-ew-kpi-card kpi-month">
        <div class="ef-ew-kpi-icon icon-month"><i class="bi bi-calendar-month"></i></div>
        <div class="ef-ew-kpi-val">₹{{ number_format($stats['monthly_expense'], 0) }}</div>
        <p class="ef-ew-kpi-lbl">{{ now()->format('M') }} Spend</p>
    </div>
    <div class="ef-ew-kpi-card kpi-reimb">
        <div class="ef-ew-kpi-icon icon-reimb"><i class="bi bi-arrow-return-left"></i></div>
        <div class="ef-ew-kpi-val {{ $stats['reimbursement_pending'] > 0 ? 'is-alert' : '' }}">
            ₹{{ number_format($stats['reimbursement_pending'], 0) }}
        </div>
        <p class="ef-ew-kpi-lbl">Reimb. Pending</p>
    </div>
</div>

{{-- ── Main content + sidebar ──────────────────────────────────── --}}
<div class="ef-ew-main">

    {{-- Activity feed --}}
    <div class="ef-ew-panel">
        <div class="ef-ew-panel-head">
            <span class="ef-ew-panel-title">Recent Activity</span>
            <a href="{{ route('employee.expense-requests.index') }}" class="ef-ew-panel-link">
                View all <i class="bi bi-arrow-right" style="font-size:.7rem"></i>
            </a>
        </div>

        @if($recentRequests->isEmpty())
        <div class="ef-ew-empty">
            <div class="ef-ew-empty-icon"><i class="bi bi-file-earmark-plus"></i></div>
            <p class="fw-semibold mb-1" style="color:#3d3528">No expense requests yet</p>
            <p class="small mb-3">Submit your first expense to get started.</p>
            <a href="{{ route('employee.expense-requests.create') }}" class="ef-ew-btn-primary" style="display:inline-flex">
                <i class="bi bi-plus-lg"></i> Submit Request
            </a>
        </div>
        @else
        @foreach($recentRequests as $req)
        @php
            $dotMap = [
                'pending'               => 'dot-pending',
                'approved'              => 'dot-approved',
                'rejected'              => 'dot-rejected',
                'paid'                  => 'dot-paid',
                'reimbursement_pending' => 'dot-reimb',
                'reimbursed'            => 'dot-approved',
                'completed'             => 'dot-approved',
            ];
            $dot = $dotMap[$req->status] ?? 'dot-other';
        @endphp
        <a href="{{ route('employee.expense-requests.show', $req) }}" class="ef-ew-activity-item">
            <div class="ef-ew-activity-dot {{ $dot }}"></div>
            <div style="flex:1;min-width:0">
                <div class="ef-ew-activity-title">{{ $req->title }}</div>
                <div class="ef-ew-activity-meta">
                    @if($req->category)<span>{{ $req->category->name }}</span><span class="sep">·</span>@endif
                    <span>{{ $req->created_at->diffForHumans(['short' => true, 'parts' => 1]) }}</span>
                    <span class="ef-ew-activity-status stat-{{ $req->status }}">
                        {{ str_replace('_', ' ', $req->status) }}
                    </span>
                </div>
            </div>
            <div class="ef-ew-activity-amount">₹{{ number_format($req->amount, 0) }}</div>
        </a>
        @endforeach
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="ef-ew-sidebar">

        {{-- Action workspace --}}
        <div class="ef-ew-panel">
            <div class="ef-ew-panel-head">
                <span class="ef-ew-panel-title">Quick Actions</span>
            </div>
            <div class="ef-ew-actions-grid">
                <a href="{{ route('employee.expense-requests.create') }}" class="ef-ew-action-card primary-action action-submit">
                    <div class="ef-ew-action-icon"><i class="bi bi-plus-circle-fill"></i></div>
                    <span class="ef-ew-action-lbl">Submit New Expense</span>
                </a>
                <a href="{{ route('employee.expense-requests.index', ['status' => 'pending']) }}"
                   class="ef-ew-action-card action-pending">
                    @if($stats['pending_requests'] > 0)
                    <span class="ef-ew-action-badge">{{ $stats['pending_requests'] }}</span>
                    @endif
                    <div class="ef-ew-action-icon"><i class="bi bi-hourglass-split"></i></div>
                    <span class="ef-ew-action-lbl">Pending</span>
                    <span class="ef-ew-action-count">{{ $stats['pending_requests'] }} requests</span>
                </a>
                <a href="{{ route('employee.expense-requests.index', ['status' => 'approved']) }}"
                   class="ef-ew-action-card action-approved">
                    <div class="ef-ew-action-icon"><i class="bi bi-check-circle-fill"></i></div>
                    <span class="ef-ew-action-lbl">Approved</span>
                    <span class="ef-ew-action-count">{{ $stats['approved_requests'] }} requests</span>
                </a>
                <a href="{{ route('employee.wallet.show') }}" class="ef-ew-action-card action-wallet">
                    <div class="ef-ew-action-icon"><i class="bi bi-wallet2"></i></div>
                    <span class="ef-ew-action-lbl">My Wallet</span>
                    <span class="ef-ew-action-count">View ledger</span>
                </a>
            </div>
        </div>

        {{-- Wallet card --}}
        <div class="ef-ew-panel">
            <div class="ef-ew-wallet-card">
                <div class="ef-ew-wallet-header">
                    <span class="ef-ew-panel-title">Wallet</span>
                    <span class="ef-ew-wallet-status-pill {{ $stats['wallet_negative'] ? 'wstatus-neg' : ($stats['wallet_low'] ? 'wstatus-low' : 'wstatus-ok') }}">
                        {{ $stats['wallet_negative'] ? 'Overdrawn' : ($stats['wallet_low'] ? 'Low' : 'Healthy') }}
                    </span>
                </div>
                <div class="ef-ew-wallet-balance {{ $stats['wallet_negative'] ? 'color-neg' : ($stats['wallet_low'] ? 'color-low' : 'color-ok') }}">
                    ₹{{ number_format($stats['wallet_balance'], 2) }}
                </div>
                <p class="ef-ew-wallet-sub">Available for expenses</p>
                <a href="{{ route('employee.wallet.show') }}" class="ef-ew-wallet-cta">
                    <i class="bi bi-clock-history me-1"></i> View Transactions
                </a>
            </div>
        </div>

    </div>{{-- /sidebar --}}
</div>{{-- /main --}}

{{-- Floating FAB (mobile only) --}}
<a href="{{ route('employee.expense-requests.create') }}" class="ef-ew-fab ef-mobile-fab" title="New Expense Request">
    <i class="bi bi-plus-lg"></i>
</a>

</x-admin-layout>
