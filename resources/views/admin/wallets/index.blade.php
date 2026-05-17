<x-admin-layout title="Wallets">
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
.ef-wlt-hero {
    background: linear-gradient(135deg, #0e0f0d 0%, #1a1e15 55%, #242b18 100%);
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
.ef-wlt-hero::before {
    content: '';
    position: absolute;
    right: 4rem; top: -3rem;
    width: 150px; height: 150px;
    border-radius: 50%;
    background: rgba(22,163,74,.05);
    pointer-events: none;
}
.ef-wlt-hero::after {
    content: '';
    position: absolute;
    right: 1rem; top: 1rem;
    width: 70px; height: 70px;
    border-radius: 50%;
    background: rgba(22,163,74,.04);
    pointer-events: none;
}
.ef-wlt-hero-eyebrow {
    font-size: .72rem; font-weight: 600;
    letter-spacing: .1em; text-transform: uppercase;
    color: #4ade80; margin-bottom: .4rem;
    opacity: .75;
}
.ef-wlt-hero-title {
    font-size: clamp(1.7rem, 3.5vw, 2.6rem);
    font-weight: 700; color: #fff;
    line-height: 1.1; letter-spacing: -.02em;
}
.ef-wlt-hero-sub  { color: rgba(255,255,255,.45); font-size: .875rem; margin-top: .35rem; }
.ef-wlt-hero-date { color: rgba(255,255,255,.22); font-size: .75rem; margin-top: .5rem; letter-spacing: .02em; }
.ef-wlt-hero-actions { display: flex; gap: .5rem; flex-wrap: wrap; align-items: center; }
.ef-wlt-btn-gold {
    background: var(--ef-gold); color: #fff; border: none;
    border-radius: 8px; padding: .52rem 1.1rem;
    font-size: .82rem; font-weight: 600; cursor: pointer;
    transition: background .2s var(--ef-ease), transform .15s;
    text-decoration: none; display: inline-flex; align-items: center;
    gap: .35rem; white-space: nowrap;
}
.ef-wlt-btn-gold:hover { background: var(--ef-gold-hi); color: #fff; transform: translateY(-1px); }
.ef-wlt-btn-ghost {
    background: rgba(255,255,255,.08); color: rgba(255,255,255,.75);
    border: 1px solid rgba(255,255,255,.14); border-radius: 8px;
    padding: .48rem .95rem; font-size: .82rem; font-weight: 500;
    cursor: pointer; transition: background .2s var(--ef-ease);
    text-decoration: none; display: inline-flex; align-items: center;
    gap: .35rem; white-space: nowrap;
}
.ef-wlt-btn-ghost:hover { background: rgba(255,255,255,.16); color: #fff; }
.ef-wlt-alert-pill {
    background: rgba(239,68,68,.18); border: 1px solid rgba(239,68,68,.32);
    color: #fca5a5; border-radius: 20px; padding: .38rem .9rem;
    font-size: .8rem; font-weight: 600; text-decoration: none;
    display: inline-flex; align-items: center; gap: .4rem;
    transition: background .2s;
}
.ef-wlt-alert-pill:hover { background: rgba(239,68,68,.28); color: #fca5a5; }
.ef-wlt-reimb-pill {
    background: rgba(139,92,246,.18); border: 1px solid rgba(139,92,246,.32);
    color: #c4b5fd; border-radius: 20px; padding: .38rem .9rem;
    font-size: .8rem; font-weight: 600; text-decoration: none;
    display: inline-flex; align-items: center; gap: .4rem;
    transition: background .2s;
}
.ef-wlt-reimb-pill:hover { background: rgba(139,92,246,.28); color: #c4b5fd; }

/* ── KPI strip ─────────────────────────────────────────── */
.ef-wlt-strip {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: .85rem;
    margin-bottom: 1.5rem;
}
@media (max-width: 1399px) { .ef-wlt-strip { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 767px)  { .ef-wlt-strip { grid-template-columns: repeat(2, 1fr); } }
.ef-wlt-kpi {
    background: #fff; border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius); padding: 1.15rem 1.2rem;
    box-shadow: var(--ef-shadow); display: block; text-decoration: none;
    color: inherit; transition: box-shadow .2s var(--ef-ease), transform .15s;
}
a.ef-wlt-kpi:hover { box-shadow: var(--ef-shadow-hover); transform: translateY(-2px); }
.ef-wlt-kpi-icon {
    width: 32px; height: 32px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; margin-bottom: .65rem;
}
.ef-wlt-kpi-icon.--green  { background: rgba(22,163,74,.1);   color: #15803d; }
.ef-wlt-kpi-icon.--gold   { background: rgba(160,114,56,.12); color: var(--ef-gold); }
.ef-wlt-kpi-icon.--warn   { background: rgba(217,119,6,.12);  color: #b45309; }
.ef-wlt-kpi-icon.--danger { background: rgba(192,57,43,.10);  color: var(--ef-danger); }
.ef-wlt-kpi-icon.--indigo { background: rgba(99,102,241,.1);  color: #4338ca; }
.ef-wlt-kpi-icon.--grey   { background: rgba(107,114,128,.1); color: #374151; }
.ef-wlt-kpi-val { font-size: 1.35rem; font-weight: 800; color: var(--ef-ink); line-height: 1; letter-spacing: -.02em; }
.ef-wlt-kpi-val.--sm     { font-size: 1.05rem; }
.ef-wlt-kpi-val.--danger { color: var(--ef-danger); }
.ef-wlt-kpi-val.--warn   { color: #b45309; }
.ef-wlt-kpi-label { font-size: .75rem; color: var(--ef-muted); margin-top: .22rem; }

/* ── Filter bar ────────────────────────────────────────── */
.ef-wlt-filter-bar {
    background: #fff; border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius); padding: 1rem 1.2rem;
    box-shadow: var(--ef-shadow); margin-bottom: 1.5rem;
}
.ef-wlt-filter-row { display: flex; gap: .55rem; align-items: center; flex-wrap: wrap; }
.ef-wlt-chips {
    display: flex; gap: .45rem; overflow-x: auto;
    scrollbar-width: none; -webkit-overflow-scrolling: touch;
    padding-bottom: 2px; flex-wrap: nowrap; align-items: center;
    margin-bottom: .8rem;
}
.ef-wlt-chips::-webkit-scrollbar { display: none; }
.ef-wlt-chip {
    flex-shrink: 0; padding: .35rem .85rem; border-radius: 20px;
    font-size: .78rem; font-weight: 500;
    border: 1px solid var(--ef-border); color: var(--ef-muted);
    background: var(--ef-faint); cursor: pointer;
    transition: all .18s var(--ef-ease); text-decoration: none;
    white-space: nowrap; display: inline-flex; align-items: center; gap: .3rem;
}
.ef-wlt-chip:hover { border-color: var(--ef-gold); color: var(--ef-gold); background: rgba(160,114,56,.06); }
.ef-wlt-chip.--active  { background: var(--ef-gold); border-color: var(--ef-gold); color: #fff; }
.ef-wlt-chip.--warn    { background: rgba(217,119,6,.08);  border-color: rgba(217,119,6,.3);  color: #92400e; }
.ef-wlt-chip.--warn.--active   { background: #b45309; border-color: #b45309; color: #fff; }
.ef-wlt-chip.--danger  { background: rgba(192,57,43,.07);  border-color: rgba(192,57,43,.3);  color: var(--ef-danger); }
.ef-wlt-chip.--danger.--active { background: var(--ef-danger); border-color: var(--ef-danger); color: #fff; }
.ef-wlt-chip.--healthy { background: rgba(22,163,74,.08);  border-color: rgba(22,163,74,.25); color: #15803d; }
.ef-wlt-chip.--healthy.--active { background: #15803d; border-color: #15803d; color: #fff; }
.ef-wlt-search-wrap { position: relative; flex: 1; min-width: 200px; }
.ef-wlt-search-icon {
    position: absolute; left: .8rem; top: 50%;
    transform: translateY(-50%); color: var(--ef-muted);
    font-size: .85rem; pointer-events: none;
}
.ef-wlt-search {
    width: 100%; border: 1px solid var(--ef-border-strong);
    border-radius: 9px; padding: .55rem .85rem .55rem 2.2rem;
    font-size: .875rem; color: var(--ef-ink); background: var(--ef-faint);
    outline: none; transition: border-color .18s, background .18s, box-shadow .18s;
}
.ef-wlt-search::placeholder { color: #b5afa8; }
.ef-wlt-search:focus { border-color: var(--ef-gold); background: #fff; box-shadow: 0 0 0 3px rgba(160,114,56,.12); }
.ef-wlt-btn-search {
    background: var(--ef-gold); color: #fff; border: none;
    border-radius: 9px; padding: .55rem 1.1rem;
    font-size: .875rem; font-weight: 600; cursor: pointer;
    transition: background .18s;
}
.ef-wlt-btn-search:hover { background: var(--ef-gold-hi); }
.ef-wlt-btn-clear {
    background: transparent; color: var(--ef-muted);
    border: 1px solid var(--ef-border); border-radius: 9px;
    padding: .55rem .9rem; font-size: .875rem; cursor: pointer;
    text-decoration: none; display: inline-flex; align-items: center;
    transition: all .18s;
}
.ef-wlt-btn-clear:hover { border-color: var(--ef-danger); color: var(--ef-danger); }

/* ── Wallet cards grid ─────────────────────────────────── */
.ef-wlt-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
@media (max-width: 1199px) { .ef-wlt-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 639px)  { .ef-wlt-grid { grid-template-columns: 1fr; } }

.ef-wlt-card {
    background: #fff; border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius); box-shadow: var(--ef-shadow);
    overflow: hidden; transition: box-shadow .2s var(--ef-ease), transform .15s;
    display: flex; flex-direction: column;
}
.ef-wlt-card:hover { box-shadow: var(--ef-shadow-hover); transform: translateY(-2px); }

/* Card top accent */
.ef-wlt-card-accent { height: 3px; background: var(--ef-border); }
.ef-wlt-card.--healthy .ef-wlt-card-accent  { background: #16a34a; }
.ef-wlt-card.--good .ef-wlt-card-accent     { background: var(--ef-gold); }
.ef-wlt-card.--low .ef-wlt-card-accent      { background: #d97706; }
.ef-wlt-card.--critical .ef-wlt-card-accent { background: #ea580c; }
.ef-wlt-card.--negative .ef-wlt-card-accent { background: var(--ef-danger); }

/* Card head */
.ef-wlt-card-head {
    padding: 1rem 1.1rem .6rem;
    display: flex; align-items: flex-start;
    justify-content: space-between; gap: .6rem;
}
.ef-wlt-avatar-wrap { display: flex; gap: .7rem; align-items: center; min-width: 0; }
.ef-wlt-avatar {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: .88rem; font-weight: 700; color: #fff;
    flex-shrink: 0; letter-spacing: .02em;
}
.ef-wlt-emp-name {
    font-size: .9rem; font-weight: 700; color: var(--ef-ink);
    line-height: 1.2; white-space: nowrap; overflow: hidden;
    text-overflow: ellipsis; max-width: 140px;
}
.ef-wlt-emp-email {
    font-size: .7rem; color: var(--ef-muted);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    max-width: 140px;
}
.ef-wlt-role-badge {
    font-size: .65rem; font-weight: 700; letter-spacing: .04em;
    text-transform: uppercase; border-radius: 5px;
    padding: .15rem .5rem; white-space: nowrap; flex-shrink: 0;
}
.ef-wlt-role-badge.--employee { background: rgba(37,99,235,.09);  color: #1d4ed8; border: 1px solid rgba(37,99,235,.18);  }
.ef-wlt-role-badge.--manager  { background: rgba(160,114,56,.1);  color: var(--ef-gold); border: 1px solid rgba(160,114,56,.2); }
.ef-wlt-role-badge.--admin    { background: rgba(107,114,128,.1); color: #374151; border: 1px solid rgba(107,114,128,.18); }

/* Card body — balance */
.ef-wlt-card-body { padding: .2rem 1.1rem .8rem; flex: 1; }
.ef-wlt-balance-label { font-size: .68rem; font-weight: 600; color: var(--ef-muted); letter-spacing: .05em; text-transform: uppercase; margin-bottom: .2rem; }
.ef-wlt-balance {
    font-size: 1.6rem; font-weight: 800; color: var(--ef-ink);
    letter-spacing: -.03em; line-height: 1; margin-bottom: .7rem;
}
.ef-wlt-balance.--low      { color: #b45309; }
.ef-wlt-balance.--critical { color: #ea580c; }
.ef-wlt-balance.--negative { color: var(--ef-danger); }

/* Balance bar */
.ef-wlt-bar-wrap {
    height: 5px; background: var(--ef-faint);
    border-radius: 10px; overflow: hidden; margin-bottom: .35rem;
}
.ef-wlt-bar-fill {
    height: 100%; border-radius: 10px;
    background: #16a34a;
    transition: width .5s var(--ef-ease);
}
.ef-wlt-bar-fill.--low      { background: #d97706; }
.ef-wlt-bar-fill.--critical { background: #ea580c; }
.ef-wlt-bar-fill.--negative { background: var(--ef-danger); width: 3px !important; }
.ef-wlt-bar-caption {
    display: flex; justify-content: space-between;
    font-size: .67rem; color: var(--ef-muted);
}

/* Health chip */
.ef-wlt-health-chip {
    font-size: .65rem; font-weight: 700; letter-spacing: .05em;
    text-transform: uppercase; border-radius: 5px;
    padding: .18rem .55rem; display: inline-flex; align-items: center; gap: .25rem;
}
.ef-wlt-health-chip.--healthy  { background: rgba(22,163,74,.1);  color: #15803d; border: 1px solid rgba(22,163,74,.2);  }
.ef-wlt-health-chip.--good     { background: rgba(160,114,56,.1); color: var(--ef-gold); border: 1px solid rgba(160,114,56,.2); }
.ef-wlt-health-chip.--low      { background: rgba(217,119,6,.1);  color: #92400e; border: 1px solid rgba(217,119,6,.2);  }
.ef-wlt-health-chip.--critical { background: rgba(234,88,12,.1);  color: #c2410c; border: 1px solid rgba(234,88,12,.2);  }
.ef-wlt-health-chip.--negative { background: rgba(192,57,43,.08); color: var(--ef-danger); border: 1px solid rgba(192,57,43,.18); }

/* Card meta */
.ef-wlt-card-meta {
    padding: 0 1.1rem .75rem;
    font-size: .72rem; color: var(--ef-muted);
    display: flex; align-items: center; gap: .5rem;
}
.ef-wlt-meta-dot { width: 3px; height: 3px; border-radius: 50%; background: var(--ef-border-strong); }

/* Card foot */
.ef-wlt-card-foot {
    padding: .65rem 1.1rem;
    border-top: 1px solid var(--ef-border);
    display: flex; gap: .5rem; align-items: center;
}
.ef-wlt-foot-btn {
    flex: 1; text-align: center;
    padding: .45rem .4rem; border-radius: 7px;
    font-size: .76rem; font-weight: 600; cursor: pointer;
    transition: all .18s var(--ef-ease); text-decoration: none;
    display: inline-flex; align-items: center; justify-content: center;
    gap: .3rem; border: none;
}
.ef-wlt-foot-btn.--gold    { background: var(--ef-gold); color: #fff; }
.ef-wlt-foot-btn.--gold:hover { background: var(--ef-gold-hi); color: #fff; }
.ef-wlt-foot-btn.--outline { background: transparent; color: var(--ef-muted); border: 1px solid var(--ef-border); }
.ef-wlt-foot-btn.--outline:hover { border-color: var(--ef-gold); color: var(--ef-gold); background: rgba(160,114,56,.05); }
.ef-wlt-foot-menu {
    width: 32px; height: 32px; border-radius: 7px;
    background: var(--ef-faint); border: 1px solid var(--ef-border);
    color: var(--ef-muted); display: flex; align-items: center;
    justify-content: center; cursor: pointer; transition: all .18s; flex-shrink: 0;
}
.ef-wlt-foot-menu:hover { border-color: var(--ef-gold); color: var(--ef-gold); background: rgba(160,114,56,.05); }

/* Needs Attention section header */
.ef-wlt-section-head {
    display: flex; align-items: center; gap: .6rem;
    margin-bottom: .75rem; margin-top: .25rem;
}
.ef-wlt-section-label {
    font-size: .72rem; font-weight: 700; letter-spacing: .06em;
    text-transform: uppercase; color: var(--ef-muted);
}
.ef-wlt-section-line { flex: 1; height: 1px; background: var(--ef-border); }
.ef-wlt-section-head.--alert .ef-wlt-section-label { color: var(--ef-danger); }

/* ── Empty state ───────────────────────────────────────── */
.ef-wlt-empty {
    background: #fff; border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius); box-shadow: var(--ef-shadow);
    padding: 4rem 2rem; text-align: center; margin-bottom: 1.5rem;
}
.ef-wlt-empty-icon {
    width: 64px; height: 64px; border-radius: 16px;
    background: var(--ef-faint); border: 1px solid var(--ef-border);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem; color: var(--ef-border-strong);
    margin: 0 auto 1.2rem;
}
.ef-wlt-empty-title { font-size: 1.05rem; font-weight: 700; color: var(--ef-ink); margin-bottom: .4rem; }
.ef-wlt-empty-sub   { font-size: .85rem; color: var(--ef-muted); }

/* ── Pagination ────────────────────────────────────────── */
.ef-wlt-pagination {
    background: #fff; border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius); padding: .85rem 1.4rem;
    box-shadow: var(--ef-shadow);
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: .5rem; margin-bottom: 1rem;
}
.ef-wlt-pagination-info { font-size: .78rem; color: var(--ef-muted); }

/* Pulse for critical/negative */
@keyframes ef-warn-pulse {
    0%,100% { opacity: 1; } 50% { opacity: .5; }
}
.ef-wlt-card.--negative .ef-wlt-card-accent,
.ef-wlt-card.--critical .ef-wlt-card-accent {
    animation: ef-warn-pulse 2s ease-in-out infinite;
}
</style>
@endpush

@php
    $avatarTones = ['#a07238','#4e7a96','#3e8a60','#6a5e8c','#807050','#5a7a64','#7a5a4e'];

    $fmt = fn(float $v): string =>
        $v >= 100000 ? '₹' . number_format($v/100000, 1) . 'L'
      :               '₹' . number_format($v, 0);

    $maxBar = 5000; // ₹5000 = full bar

    $healthState = function (\App\Models\Wallet $w): string {
        if ($w->isNegative()) return 'negative';
        if ($w->balance < 200)  return 'critical';
        if ($w->isLow())        return 'low';
        if ($w->balance >= 2000) return 'good';
        return 'healthy';
    };
    $healthLabel = ['negative' => 'Negative', 'critical' => 'Critical', 'low' => 'Low Balance', 'good' => 'Good', 'healthy' => 'Healthy'];
    $healthIcon  = ['negative' => 'bi-x-circle-fill', 'critical' => 'bi-exclamation-triangle-fill', 'low' => 'bi-arrow-down-circle', 'good' => 'bi-check-circle', 'healthy' => 'bi-check-circle'];

    $needsAttention = ($stats['low_balance_count'] + $stats['negative_count']) > 0 && !$health;
@endphp

{{-- ── Hero ──────────────────────────────────────────────────── --}}
<div class="ef-wlt-hero">
    <div>
        <div class="ef-wlt-hero-eyebrow">Wallet Operations</div>
        <div class="ef-wlt-hero-title">Wallets</div>
        <div class="ef-wlt-hero-sub">Employee advance balances and reimbursement monitoring</div>
        <div class="ef-wlt-hero-date">{{ now()->format('l, j F Y') }}</div>
    </div>
    <div class="ef-wlt-hero-actions">
        @if($stats['negative_count'] > 0)
            <a href="{{ route('admin.wallets.index', ['health' => 'critical']) }}"
               class="ef-wlt-alert-pill">
                <i class="bi bi-x-circle-fill"></i>
                {{ $stats['negative_count'] }} Negative
            </a>
        @endif
        @if($stats['pending_reimb_count'] > 0)
            <a href="{{ route('admin.expense-requests.index', ['status' => 'reimbursement_pending']) }}"
               class="ef-wlt-reimb-pill">
                <i class="bi bi-arrow-return-left"></i>
                {{ $stats['pending_reimb_count'] }} Pending Reimb.
            </a>
        @endif
        <a href="{{ route('admin.expense-requests.index') }}" class="ef-wlt-btn-ghost">
            <i class="bi bi-receipt"></i> Expenses
        </a>
        <a href="{{ route('admin.wallets.index') }}" class="ef-wlt-btn-ghost">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </a>
    </div>
</div>

{{-- ── KPI strip ───────────────────────────────────────────────── --}}
<div class="ef-wlt-strip">
    <div class="ef-wlt-kpi">
        <div class="ef-wlt-kpi-icon --green"><i class="bi bi-wallet2"></i></div>
        <div class="ef-wlt-kpi-val --sm">{{ $fmt($stats['total_balance']) }}</div>
        <div class="ef-wlt-kpi-label">Total Balance</div>
    </div>
    <div class="ef-wlt-kpi">
        <div class="ef-wlt-kpi-icon --gold"><i class="bi bi-people"></i></div>
        <div class="ef-wlt-kpi-val">{{ number_format($stats['total_wallets']) }}</div>
        <div class="ef-wlt-kpi-label">Active Wallets</div>
    </div>
    <a href="{{ route('admin.wallets.index', ['health' => 'low']) }}" class="ef-wlt-kpi">
        <div class="ef-wlt-kpi-icon --warn"><i class="bi bi-arrow-down-circle"></i></div>
        <div class="ef-wlt-kpi-val {{ $stats['low_balance_count'] > 0 ? '--warn' : '' }}">
            {{ number_format($stats['low_balance_count']) }}
        </div>
        <div class="ef-wlt-kpi-label">Low Balance</div>
    </a>
    <a href="{{ route('admin.wallets.index', ['health' => 'critical']) }}" class="ef-wlt-kpi">
        <div class="ef-wlt-kpi-icon --danger"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="ef-wlt-kpi-val {{ $stats['negative_count'] > 0 ? '--danger' : '' }}">
            {{ number_format($stats['negative_count']) }}
        </div>
        <div class="ef-wlt-kpi-label">Negative Balances</div>
    </a>
    <a href="{{ route('admin.expense-requests.index', ['status' => 'reimbursement_pending']) }}" class="ef-wlt-kpi">
        <div class="ef-wlt-kpi-icon --indigo"><i class="bi bi-arrow-return-left"></i></div>
        <div class="ef-wlt-kpi-val {{ $stats['pending_reimb_count'] > 0 ? '' : '' }}">
            {{ number_format($stats['pending_reimb_count']) }}
        </div>
        <div class="ef-wlt-kpi-label">Pending Reimb.</div>
    </a>
    <div class="ef-wlt-kpi">
        <div class="ef-wlt-kpi-icon --grey"><i class="bi bi-graph-up"></i></div>
        <div class="ef-wlt-kpi-val --sm">{{ $fmt($stats['avg_balance']) }}</div>
        <div class="ef-wlt-kpi-label">Avg Balance</div>
    </div>
</div>

{{-- ── Filter bar ──────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('admin.wallets.index') }}" id="wltFilterForm">
<div class="ef-wlt-filter-bar">
    <div class="ef-wlt-chips">
        <a href="{{ route('admin.wallets.index') }}"
           class="ef-wlt-chip {{ !$health ? '--active' : '' }}">
            All Wallets
        </a>
        <a href="{{ route('admin.wallets.index', ['health' => 'critical']) }}"
           class="ef-wlt-chip --danger {{ $health === 'critical' ? '--active' : '' }}">
            <i class="bi bi-exclamation-triangle" style="font-size:.7rem"></i> Critical
            @if($stats['negative_count'] > 0)<span style="font-size:.68rem;opacity:.8">({{ $stats['negative_count'] }})</span>@endif
        </a>
        <a href="{{ route('admin.wallets.index', ['health' => 'low']) }}"
           class="ef-wlt-chip --warn {{ $health === 'low' ? '--active' : '' }}">
            <i class="bi bi-arrow-down-circle" style="font-size:.7rem"></i> Low Balance
            @if($stats['low_balance_count'] > 0)<span style="font-size:.68rem;opacity:.8">({{ $stats['low_balance_count'] }})</span>@endif
        </a>
        <a href="{{ route('admin.wallets.index', ['health' => 'healthy']) }}"
           class="ef-wlt-chip --healthy {{ $health === 'healthy' ? '--active' : '' }}">
            <i class="bi bi-check-circle" style="font-size:.7rem"></i> Healthy
        </a>
    </div>
    <div class="ef-wlt-filter-row">
        <div class="ef-wlt-search-wrap">
            <i class="bi bi-search ef-wlt-search-icon"></i>
            <input type="text" name="search" class="ef-wlt-search"
                   placeholder="Search employee name or email…"
                   value="{{ $search }}">
        </div>
        @if($health)<input type="hidden" name="health" value="{{ $health }}">@endif
        <button type="submit" class="ef-wlt-btn-search">Search</button>
        @if($search || $health)
            <a href="{{ route('admin.wallets.index') }}" class="ef-wlt-btn-clear">
                <i class="bi bi-x-lg me-1"></i> Clear
            </a>
        @endif
    </div>
</div>
</form>

{{-- ── Wallet cards ────────────────────────────────────────────── --}}
@if($wallets->isEmpty())
<div class="ef-wlt-empty">
    <div class="ef-wlt-empty-icon"><i class="bi bi-wallet2"></i></div>
    <div class="ef-wlt-empty-title">No wallets found</div>
    <div class="ef-wlt-empty-sub">
        @if($search || $health)
            Try different filters or
            <a href="{{ route('admin.wallets.index') }}" style="color:var(--ef-gold)">clear all filters</a>.
        @else
            Employee advance balances will appear here once created.
        @endif
    </div>
</div>
@else

{{-- Attention section header --}}
@if($needsAttention)
<div class="ef-wlt-section-head --alert">
    <div class="ef-wlt-section-label">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>Needs Attention
    </div>
    <div class="ef-wlt-section-line"></div>
</div>
@endif

<div class="ef-wlt-grid">
    @foreach($wallets as $wallet)
    @php
        $user    = $wallet->user;
        $state   = $healthState($wallet);
        $initials = collect(explode(' ', $user->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->implode('');
        $avatarColor = $avatarTones[ord(strtoupper($user->name[0] ?? 'A')) % count($avatarTones)];
        $role    = $user->role ?? 'employee';
        $barPct  = $wallet->isNegative() ? 0 : min(100, round(max(0, (float)$wallet->balance) / $maxBar * 100));
        $barMod  = match($state) { 'negative' => '--negative', 'critical' => '--critical', 'low' => '--low', default => '' };
        $balMod  = match($state) { 'negative', 'critical' => '--' . $state, 'low' => '--low', default => '' };
    @endphp
    <div class="ef-wlt-card --{{ $state }}">
        <div class="ef-wlt-card-accent"></div>

        <div class="ef-wlt-card-head">
            <div class="ef-wlt-avatar-wrap">
                <div class="ef-wlt-avatar" style="background:{{ $avatarColor }}">{{ $initials }}</div>
                <div style="min-width:0">
                    <div class="ef-wlt-emp-name" title="{{ $user->name }}">{{ $user->name }}</div>
                    <div class="ef-wlt-emp-email" title="{{ $user->email }}">{{ $user->email }}</div>
                </div>
            </div>
            <span class="ef-wlt-role-badge --{{ $role }}">{{ ucfirst($role) }}</span>
        </div>

        <div class="ef-wlt-card-body">
            <div class="ef-wlt-balance-label">Current Balance</div>
            <div class="ef-wlt-balance {{ $balMod }}">
                ₹{{ number_format($wallet->balance, 0) }}
            </div>

            <div class="ef-wlt-bar-wrap">
                <div class="ef-wlt-bar-fill {{ $barMod }}"
                     style="{{ $wallet->isNegative() ? '' : 'width:' . $barPct . '%' }}"></div>
            </div>
            <div class="ef-wlt-bar-caption">
                <span class="ef-wlt-health-chip --{{ $state }}">
                    <i class="bi {{ $healthIcon[$state] }}" style="font-size:.6rem"></i>
                    {{ $healthLabel[$state] }}
                </span>
                <span>{{ $barPct }}%</span>
            </div>
        </div>

        <div class="ef-wlt-card-meta">
            <i class="bi bi-clock" style="font-size:.65rem"></i>
            <span>{{ $wallet->updated_at->diffForHumans() }}</span>
        </div>

        <div class="ef-wlt-card-foot">
            <a href="{{ route('admin.wallets.show', $user) }}"
               class="ef-wlt-foot-btn --outline">
                <i class="bi bi-clock-history"></i> History
            </a>
            <button type="button"
                    class="ef-wlt-foot-btn --gold"
                    data-bs-toggle="modal"
                    data-bs-target="#fundModal"
                    data-user-id="{{ $user->id }}"
                    data-user-name="{{ $user->name }}"
                    data-balance="{{ number_format((float)$wallet->balance, 2) }}"
                    onclick="wltSetModal(this)">
                <i class="bi bi-plus-lg"></i> Add Funds
            </button>
        </div>
    </div>
    @endforeach
</div>

@if($wallets->hasPages())
<div class="ef-wlt-pagination">
    <div class="ef-wlt-pagination-info">
        Showing {{ $wallets->firstItem() }}–{{ $wallets->lastItem() }}
        of {{ number_format($wallets->total()) }} wallets
    </div>
    {{ $wallets->links() }}
</div>
@endif
@endif

{{-- ── Add Funds modal ────────────────────────────────────────── --}}
<div class="modal fade" id="fundModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
        <div class="modal-content" style="border:1px solid var(--ef-border);border-radius:var(--ef-radius);box-shadow:0 20px 60px rgba(0,0,0,.18)">
            <div class="modal-header" style="border-bottom:1px solid var(--ef-border);padding:1.2rem 1.5rem .9rem">
                <div>
                    <h5 class="modal-title mb-0" style="font-weight:700;font-size:.95rem;color:var(--ef-ink)">
                        Wallet Transaction
                    </h5>
                    <div class="modal-subtitle" id="fundModalSubtitle"
                         style="font-size:.78rem;color:var(--ef-muted);margin-top:.15rem"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="opacity:.5"></button>
            </div>
            <form method="POST" id="fundModalForm">
                @csrf
                <div class="modal-body" style="padding:1.4rem 1.5rem">
                    <div style="display:flex;flex-direction:column;gap:1rem">
                        <div>
                            <label style="font-size:.78rem;font-weight:600;color:var(--ef-ink);display:block;margin-bottom:.4rem">
                                Transaction Type
                            </label>
                            <select name="type" class="form-select" style="border:1px solid var(--ef-border-strong);border-radius:9px;font-size:.875rem;background:var(--ef-faint);padding:.55rem .85rem;outline:none">
                                <option value="credit">Credit — Add Funds</option>
                                <option value="debit">Debit — Deduct Funds</option>
                                <option value="adjustment">Adjustment — Set Balance</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:.78rem;font-weight:600;color:var(--ef-ink);display:block;margin-bottom:.4rem">
                                Amount (₹) <span style="color:var(--ef-danger)">*</span>
                            </label>
                            <input type="number" name="amount" step="0.01" min="0.01"
                                   class="form-control"
                                   style="border:1px solid var(--ef-border-strong);border-radius:9px;font-size:.9rem;font-weight:700;background:var(--ef-faint);padding:.6rem .85rem;outline:none"
                                   placeholder="0.00" required>
                        </div>
                        <div>
                            <label style="font-size:.78rem;font-weight:600;color:var(--ef-ink);display:block;margin-bottom:.4rem">
                                Notes
                            </label>
                            <textarea name="notes" rows="2"
                                      class="form-control"
                                      style="border:1px solid var(--ef-border-strong);border-radius:9px;font-size:.85rem;background:var(--ef-faint);padding:.55rem .85rem;outline:none;resize:none"
                                      placeholder="Optional — reason for transaction"></textarea>
                        </div>
                        <div style="background:var(--ef-faint);border:1px solid var(--ef-border);border-radius:9px;padding:.65rem .9rem;font-size:.78rem;color:var(--ef-muted)">
                            <i class="bi bi-wallet2 me-1"></i> Current balance:
                            <strong id="fundModalBalance" style="color:var(--ef-ink)">—</strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--ef-border);padding:.9rem 1.5rem;gap:.5rem">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"
                            style="border-radius:8px;font-size:.82rem;border-color:var(--ef-border-strong)">
                        Cancel
                    </button>
                    <button type="submit"
                            style="background:var(--ef-gold);color:#fff;border:none;border-radius:8px;padding:.5rem 1.3rem;font-size:.875rem;font-weight:700;cursor:pointer;transition:background .18s">
                        Record Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function wltSetModal(btn) {
    const userId  = btn.dataset.userId;
    const name    = btn.dataset.userName;
    const balance = btn.dataset.balance;
    document.getElementById('fundModalSubtitle').textContent = name;
    document.getElementById('fundModalBalance').textContent  = '₹' + balance;
    document.getElementById('fundModalForm').action =
        '{{ url('admin/wallets') }}/' + userId + '/transact';
}
</script>
@endpush
</x-admin-layout>
