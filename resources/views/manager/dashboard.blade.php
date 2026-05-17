<x-admin-layout title="Manager Dashboard">
@push('styles')
<style>
/*
 * MANAGER DASHBOARD — ef-mg-* namespace
 * Role accent: emerald / teal
 * Mirrors Hall dashboard visual language exactly.
 */

/* ── Design tokens ─────────────────────────────────────────────── */
:root {
    --mg-emerald:   #0F7B5F;
    --mg-emerald-hi:#0D9E78;
    --mg-emerald-dk:#0D5C43;
    --mg-teal:      #0d9488;
    --mg-gold:      #B8893E;
    --mg-amber:     #D89A3D;
    --mg-danger:    #C84B44;
    --mg-ink:       #101714;
    --mg-muted:     #6E6A64;
    --mg-faint:     #EDF5F1;
    --mg-border:    rgba(15,123,95,.12);
    --mg-border-s:  rgba(15,123,95,.26);
    --mg-shadow:    0 1px 3px rgba(16,23,20,.06),0 4px 12px rgba(16,23,20,.05);
    --mg-shadow-h:  0 8px 30px rgba(16,23,20,.10),0 1px 4px rgba(16,23,20,.06);
    --mg-radius:    14px;
    --mg-ease:      cubic-bezier(.25,.46,.45,.94);
}

/* ── Page scaffold (mirrors .ef-dashboard) ─────────────────────── */
.ef-mg-page { padding: 0; }

/* ── Hero ──────────────────────────────────────────────────────── */
.ef-mg-hero {
    background: linear-gradient(135deg, #081a0f 0%, #0e2b1c 50%, #0a1f14 100%) !important;
    border: 1px solid rgba(255,255,255,.07) !important;
    border-radius: 20px;
    margin-bottom: 20px;
    overflow: hidden;
    padding: 32px 28px;
    position: relative;
    display: flex;
    align-items: stretch;
    gap: 0;
}
.ef-mg-hero::before {
    background: radial-gradient(circle, rgba(16,185,129,.16) 0%, transparent 68%);
    border-radius: 50%;
    content: "";
    height: 480px;
    pointer-events: none;
    position: absolute;
    right: -100px;
    top: -160px;
    width: 480px;
}
.ef-mg-hero::after {
    background: radial-gradient(circle, rgba(5,150,105,.10) 0%, transparent 68%);
    bottom: -110px;
    content: "";
    height: 300px;
    left: 20%;
    pointer-events: none;
    position: absolute;
    width: 300px;
    border-radius: 50%;
}
.ef-mg-hero-main {
    flex: 1;
    position: relative;
    z-index: 1;
}
.ef-mg-eyebrow {
    color: rgba(16,185,129,.85);
    font-size: .67rem;
    font-weight: 760;
    letter-spacing: .18em;
    margin-bottom: 10px;
    text-transform: uppercase;
}
.ef-mg-hero-title {
    color: #f0fdf8;
    font-size: 1.65rem;
    font-weight: 800;
    letter-spacing: -.02em;
    line-height: 1.2;
    margin-bottom: 10px;
}
.ef-mg-hero-summary {
    color: rgba(240,253,248,.52);
    font-size: .86rem;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
    margin-bottom: 0;
}
.ef-mg-hero-summary span { color: rgba(240,253,248,.52); }
.ef-mg-hero-summary b   { color: rgba(240,253,248,.88); font-weight: 700; }
.ef-mg-hero-summary .dot { opacity: .3; }

.ef-mg-hero-side {
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
.ef-mg-side-label {
    color: rgba(240,253,248,.3);
    font-size: .72rem;
    font-weight: 660;
    letter-spacing: .06em;
    text-transform: uppercase;
}
.ef-mg-side-value {
    color: #f0fdf8;
    font-size: 1.1rem;
    font-weight: 760;
    margin-top: 2px;
}
.ef-mg-hero-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.ef-mg-btn {
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.14);
    border-radius: 10px;
    color: rgba(240,253,248,.85);
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
.ef-mg-btn:hover { background: rgba(255,255,255,.14); color: #f0fdf8; }
.ef-mg-btn-primary {
    background: var(--mg-emerald);
    border-color: var(--mg-emerald);
    color: #fff;
}
.ef-mg-btn-primary:hover { background: var(--mg-emerald-hi); border-color: var(--mg-emerald-hi); color: #fff; }

/* ── KPI Metrics Strip ─────────────────────────────────────────── */
.ef-mg-metrics {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}
.ef-mg-metric {
    background: #fff;
    border: 1px solid var(--mg-border);
    border-radius: var(--mg-radius);
    box-shadow: var(--mg-shadow);
    padding: 16px 18px;
    position: relative;
    transition: box-shadow .18s var(--mg-ease), transform .18s var(--mg-ease);
}
a.ef-mg-metric { text-decoration: none; }
a.ef-mg-metric:hover {
    border-color: var(--mg-border-s);
    box-shadow: var(--mg-shadow-h);
    transform: translateY(-2px);
}
.ef-mg-metric-icon {
    color: var(--mg-emerald);
    float: right;
    font-size: 1rem;
    opacity: .55;
}
.ef-mg-metric-label {
    color: var(--mg-muted);
    font-size: .68rem;
    font-weight: 720;
    letter-spacing: .05em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
.ef-mg-metric-value {
    color: var(--mg-ink);
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -.02em;
    line-height: 1;
}
.ef-mg-metric-note {
    color: var(--mg-muted);
    font-size: .72rem;
    margin-top: 4px;
}
.ef-mg-metric-value.c-emerald { color: var(--mg-emerald); }
.ef-mg-metric-value.c-amber   { color: var(--mg-amber); }
.ef-mg-metric-value.c-danger  { color: var(--mg-danger); }
.ef-mg-metric-value.c-teal    { color: var(--mg-teal); }

/* ── Command Grid ──────────────────────────────────────────────── */
.ef-mg-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 20px;
    align-items: start;
}

/* ── Content card ──────────────────────────────────────────────── */
.ef-mg-card {
    background: #fff;
    border: 1px solid var(--mg-border);
    border-radius: 16px;
    box-shadow: var(--mg-shadow);
    overflow: hidden;
}
.ef-mg-card-head {
    align-items: center;
    border-bottom: 1px solid var(--mg-border);
    display: flex;
    gap: 10px;
    justify-content: space-between;
    padding: 16px 22px;
}
.ef-mg-card-title {
    color: var(--mg-ink);
    font-size: .88rem;
    font-weight: 760;
}
.ef-mg-card-aside {
    color: var(--mg-muted);
    font-size: .82rem;
    font-weight: 660;
    text-decoration: none;
}
a.ef-mg-card-aside { color: var(--mg-emerald); }
a.ef-mg-card-aside:hover { color: var(--mg-teal); }
.ef-mg-card-body { padding: 18px 22px; }
.ef-mg-card-body.flush { padding: 0; }

/* ── Approval queue cards ──────────────────────────────────────── */
.ef-mg-req-list { display: flex; flex-direction: column; }
.ef-mg-req-item {
    align-items: center;
    border-bottom: 1px solid var(--mg-border);
    display: flex;
    gap: 14px;
    padding: 14px 22px;
    text-decoration: none;
    transition: background .14s;
}
.ef-mg-req-item:last-child { border-bottom: none; }
.ef-mg-req-item:hover { background: #f8fffe; }
.ef-mg-req-avatar {
    align-items: center;
    background: var(--mg-faint);
    border-radius: 50%;
    color: var(--mg-emerald);
    display: flex;
    flex-shrink: 0;
    font-size: .75rem;
    font-weight: 800;
    height: 36px;
    justify-content: center;
    letter-spacing: .04em;
    text-transform: uppercase;
    width: 36px;
}
.ef-mg-req-main { flex: 1; min-width: 0; }
.ef-mg-req-title {
    color: var(--mg-ink);
    font-size: .86rem;
    font-weight: 700;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ef-mg-req-meta {
    color: var(--mg-muted);
    font-size: .75rem;
    margin-top: 2px;
}
.ef-mg-req-right { flex-shrink: 0; text-align: right; }
.ef-mg-req-amount {
    color: var(--mg-ink);
    font-size: .95rem;
    font-weight: 800;
}
.ef-mg-req-time {
    color: var(--mg-muted);
    font-size: .72rem;
    margin-top: 2px;
}

/* ── Priority badge ────────────────────────────────────────────── */
.ef-mg-priority {
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    font-size: .67rem;
    font-weight: 760;
    letter-spacing: .05em;
    padding: 2px 7px;
    text-transform: uppercase;
    gap: 4px;
}
.ef-mg-priority.urgent { background: #fef2f2; color: var(--mg-danger); }
.ef-mg-priority.high   { background: #fffbeb; color: var(--mg-amber); }
.ef-mg-priority.medium { background: #ecfdf5; color: var(--mg-emerald); }
.ef-mg-priority.low    { background: #f8fafc; color: #94a3b8; }

/* ── Decision feed ─────────────────────────────────────────────── */
.ef-mg-feed-item {
    align-items: center;
    border-bottom: 1px solid var(--mg-border);
    display: flex;
    gap: 14px;
    padding: 12px 22px;
    text-decoration: none;
}
.ef-mg-feed-item:last-child { border-bottom: none; }
.ef-mg-feed-item:hover { background: #f8fffe; }
.ef-mg-feed-icon {
    align-items: center;
    border-radius: 50%;
    display: flex;
    flex-shrink: 0;
    font-size: .9rem;
    height: 30px;
    justify-content: center;
    width: 30px;
}
.ef-mg-feed-icon.approved { background: #ecfdf5; color: var(--mg-emerald); }
.ef-mg-feed-icon.rejected { background: #fef2f2; color: var(--mg-danger); }
.ef-mg-feed-body  { flex: 1; min-width: 0; }
.ef-mg-feed-title { color: var(--mg-ink); font-size: .83rem; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ef-mg-feed-meta  { color: var(--mg-muted); font-size: .73rem; margin-top: 2px; }
.ef-mg-feed-amt   { color: var(--mg-ink); font-size: .86rem; font-weight: 800; flex-shrink: 0; }

/* ── Attention items ───────────────────────────────────────────── */
.ef-mg-alert-item {
    align-items: flex-start;
    border-bottom: 1px solid var(--mg-border);
    display: flex;
    gap: 12px;
    padding: 12px 22px;
    text-decoration: none;
    transition: background .14s;
}
.ef-mg-alert-item:last-child { border-bottom: none; }
.ef-mg-alert-item:hover { background: #fffcf5; }
.ef-mg-alert-rail {
    border-radius: 2px;
    flex-shrink: 0;
    margin-top: 4px;
    width: 3px;
    height: 32px;
}
.ef-mg-alert-item[data-tone="danger"]  .ef-mg-alert-rail { background: var(--mg-danger); }
.ef-mg-alert-item[data-tone="warning"] .ef-mg-alert-rail { background: var(--mg-amber); }
.ef-mg-alert-title { color: var(--mg-ink); font-size: .83rem; font-weight: 700; }
.ef-mg-alert-body  { color: var(--mg-muted); font-size: .75rem; margin-top: 2px; }

/* ── Action hub tiles ──────────────────────────────────────────── */
.ef-mg-action-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.ef-mg-action-tile {
    align-items: center;
    background: var(--mg-faint);
    border: 1px solid var(--mg-border);
    border-radius: 12px;
    color: var(--mg-ink);
    display: flex;
    flex-direction: column;
    font-size: .78rem;
    font-weight: 700;
    gap: 6px;
    padding: 14px 10px;
    text-align: center;
    text-decoration: none;
    transition: background .18s var(--mg-ease), transform .14s var(--mg-ease), box-shadow .18s;
}
.ef-mg-action-tile:hover {
    background: #d1fae5;
    border-color: var(--mg-border-s);
    box-shadow: var(--mg-shadow-h);
    color: var(--mg-ink);
    transform: translateY(-2px);
}
.ef-mg-action-tile i {
    color: var(--mg-emerald);
    font-size: 1.2rem;
}
.ef-mg-action-tile.primary {
    background: var(--mg-emerald);
    border-color: var(--mg-emerald);
    color: #fff;
    grid-column: span 2;
}
.ef-mg-action-tile.primary:hover { background: var(--mg-teal); border-color: var(--mg-teal); }
.ef-mg-action-tile.primary i { color: #fff; }

/* ── Pipeline ──────────────────────────────────────────────────── */
.ef-mg-pipeline {
    display: flex;
    align-items: center;
    gap: 0;
    padding: 0 4px;
}
.ef-mg-pipe-step {
    flex: 1;
    text-align: center;
    position: relative;
}
.ef-mg-pipe-step + .ef-mg-pipe-step::before {
    background: var(--mg-border);
    content: "";
    height: 2px;
    left: -50%;
    position: absolute;
    top: 16px;
    width: 100%;
}
.ef-mg-pipe-dot {
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
.ef-mg-pipe-dot.done    { background: var(--mg-emerald); color: #fff; }
.ef-mg-pipe-dot.active  { background: var(--mg-amber); color: #fff; box-shadow: 0 0 0 4px rgba(217,119,6,.18); }
.ef-mg-pipe-dot.pending { background: var(--mg-faint); color: var(--mg-muted); }
.ef-mg-pipe-label {
    color: var(--mg-muted);
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .03em;
}
.ef-mg-pipe-count {
    color: var(--mg-ink);
    font-size: 1.1rem;
    font-weight: 800;
}

/* ── Empty state ───────────────────────────────────────────────── */
.ef-mg-empty {
    padding: 40px 22px;
    text-align: center;
}
.ef-mg-empty-orb {
    align-items: center;
    background: var(--mg-faint);
    border: 1px solid var(--mg-border);
    border-radius: 50%;
    color: var(--mg-emerald);
    display: inline-flex;
    font-size: 1.5rem;
    height: 60px;
    justify-content: center;
    margin-bottom: 14px;
    width: 60px;
}
.ef-mg-empty p { color: var(--mg-muted); font-size: .86rem; margin-bottom: 16px; }

/* ── Mobile bottom bar ─────────────────────────────────────────── */
.ef-mg-mobile-bar {
    display: none;
    background: rgba(8,26,15,.95) !important;
    border-top: 1px solid rgba(255,255,255,.07) !important;
    bottom: 0; left: 0; right: 0;
    gap: 8px;
    padding: 10px 16px;
    position: fixed;
    z-index: 999;
}

/* ── Responsive ────────────────────────────────────────────────── */
@media (max-width: 1199.98px) {
    .ef-mg-metrics { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 991.98px) {
    .ef-mg-grid { grid-template-columns: 1fr; }
    .ef-mg-hero { flex-direction: column; gap: 20px; }
    .ef-mg-hero-side {
        border-left: none !important;
        border-top: 1px solid rgba(255,255,255,.07) !important;
        flex-direction: row;
        margin-left: 0;
        padding-left: 0;
        padding-top: 20px;
    }
    .ef-mg-hero-actions { flex-direction: row; flex-wrap: wrap; }
}
@media (max-width: 767.98px) {
    .ef-mg-hero { padding: 22px 18px; border-radius: 16px; }
    .ef-mg-hero-title { font-size: 1.35rem; }
    .ef-mg-metrics { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .ef-mg-mobile-bar { display: flex; }
    .ef-mg-page { padding-bottom: 70px; }
}
@media (max-width: 575.98px) {
    .ef-mg-hero-side { flex-direction: column; }
}
</style>
@endpush

@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $name     = explode(' ', auth()->user()->name)[0];

    $priorityMeta = [
        'urgent' => ['dot' => '🔴', 'cls' => 'urgent'],
        'high'   => ['dot' => '🟡', 'cls' => 'high'],
        'medium' => ['dot' => '🟢', 'cls' => 'medium'],
        'low'    => ['dot' => '⚪', 'cls' => 'low'],
    ];
@endphp

<div class="ef-mg-page">

{{-- ── Hero ────────────────────────────────────────────────────── --}}
<section class="ef-mg-hero mb-4">
    <div class="ef-mg-hero-main">
        <div class="ef-mg-eyebrow">Expense Approval Operations</div>
        <h1 class="ef-mg-hero-title">{{ $greeting }}, {{ $name }}</h1>
        <div class="ef-mg-hero-summary">
            <span><b>{{ $stats['pending'] }}</b> pending review</span>
            <span class="dot">·</span>
            <span><b>{{ $stats['approved_today'] }}</b> approved today</span>
            <span class="dot">·</span>
            <span><b>₹{{ number_format($stats['monthly_amount'], 0) }}</b> this month</span>
            <span class="dot">·</span>
            <span>{{ now()->format('d F Y') }}</span>
        </div>
    </div>
    <div class="ef-mg-hero-side">
        <div>
            <div class="ef-mg-side-label">Approval Queue</div>
            <div class="ef-mg-side-value">
                @if($stats['pending'] === 0)
                    All caught up
                @else
                    {{ $stats['pending'] }} waiting
                @endif
            </div>
        </div>
        <div class="ef-mg-hero-actions">
            <a href="{{ route('manager.expense-requests.index', ['status' => 'pending']) }}"
               class="ef-mg-btn ef-mg-btn-primary">
                <i class="bi bi-check2-square"></i> Review Queue
            </a>
            <a href="{{ route('manager.expense-requests.index') }}" class="ef-mg-btn">
                <i class="bi bi-list-ul"></i> All Requests
            </a>
        </div>
    </div>
</section>

{{-- ── KPI Strip ───────────────────────────────────────────────── --}}
<div class="ef-mg-metrics mb-4">
    <a href="{{ route('manager.expense-requests.index', ['status' => 'pending']) }}" class="ef-mg-metric">
        <div class="ef-mg-metric-icon"><i class="bi bi-hourglass-split"></i></div>
        <div class="ef-mg-metric-label">Pending</div>
        <div class="ef-mg-metric-value {{ $stats['pending'] > 0 ? 'c-amber' : 'c-emerald' }}">{{ $stats['pending'] }}</div>
        <div class="ef-mg-metric-note">awaiting review</div>
    </a>
    <div class="ef-mg-metric">
        <div class="ef-mg-metric-icon"><i class="bi bi-check-circle-fill"></i></div>
        <div class="ef-mg-metric-label">Approved Today</div>
        <div class="ef-mg-metric-value c-emerald">{{ $stats['approved_today'] }}</div>
        <div class="ef-mg-metric-note">decisions today</div>
    </div>
    <a href="{{ route('manager.expense-requests.index', ['status' => 'rejected']) }}" class="ef-mg-metric">
        <div class="ef-mg-metric-icon"><i class="bi bi-x-circle-fill"></i></div>
        <div class="ef-mg-metric-label">Rejected</div>
        <div class="ef-mg-metric-value c-danger">{{ $stats['rejected'] }}</div>
        <div class="ef-mg-metric-note">all time</div>
    </a>
    <div class="ef-mg-metric">
        <div class="ef-mg-metric-icon"><i class="bi bi-bar-chart-line"></i></div>
        <div class="ef-mg-metric-label">Total Processed</div>
        <div class="ef-mg-metric-value">{{ $stats['total_processed'] }}</div>
        <div class="ef-mg-metric-note">approved + rejected</div>
    </div>
    <div class="ef-mg-metric">
        <div class="ef-mg-metric-icon"><i class="bi bi-currency-rupee"></i></div>
        <div class="ef-mg-metric-label">Month Approved</div>
        <div class="ef-mg-metric-value c-teal" style="font-size:1.2rem">₹{{ number_format($stats['monthly_amount'], 0) }}</div>
        <div class="ef-mg-metric-note">{{ now()->format('M Y') }}</div>
    </div>
    <div class="ef-mg-metric">
        <div class="ef-mg-metric-icon"><i class="bi bi-people"></i></div>
        <div class="ef-mg-metric-label">Team Size</div>
        <div class="ef-mg-metric-value">{{ $stats['total_employees'] }}</div>
        <div class="ef-mg-metric-note">employees</div>
    </div>
</div>

{{-- ── Approval Pipeline ───────────────────────────────────────── --}}
<div class="ef-mg-card mb-4">
    <div class="ef-mg-card-head">
        <span class="ef-mg-card-title"><i class="bi bi-diagram-3 me-2" style="color:var(--mg-emerald)"></i>Approval Pipeline</span>
        <span class="ef-mg-card-aside">live flow</span>
    </div>
    <div class="ef-mg-card-body">
        <div class="ef-mg-pipeline">
            <div class="ef-mg-pipe-step">
                <div class="ef-mg-pipe-dot done"><i class="bi bi-upload"></i></div>
                <div class="ef-mg-pipe-count">{{ $stats['pending'] + $stats['total_processed'] }}</div>
                <div class="ef-mg-pipe-label">Submitted</div>
            </div>
            <div class="ef-mg-pipe-step">
                <div class="ef-mg-pipe-dot {{ $stats['pending'] > 0 ? 'active' : 'done' }}"><i class="bi bi-eye"></i></div>
                <div class="ef-mg-pipe-count">{{ $stats['pending'] }}</div>
                <div class="ef-mg-pipe-label">Under Review</div>
            </div>
            <div class="ef-mg-pipe-step">
                <div class="ef-mg-pipe-dot done"><i class="bi bi-check-lg"></i></div>
                <div class="ef-mg-pipe-count">{{ $stats['approved_total'] }}</div>
                <div class="ef-mg-pipe-label">Approved</div>
            </div>
            <div class="ef-mg-pipe-step">
                <div class="ef-mg-pipe-dot {{ $stats['paid_total'] > 0 ? 'done' : 'pending' }}">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="ef-mg-pipe-count">{{ $stats['paid_total'] }}</div>
                <div class="ef-mg-pipe-label">Paid</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Command Grid ─────────────────────────────────────────────── --}}
<div class="ef-mg-grid">
    <div class="d-flex flex-column gap-4">

        {{-- Pending Approval Queue --}}
        <div class="ef-mg-card">
            <div class="ef-mg-card-head">
                <span class="ef-mg-card-title">
                    <i class="bi bi-hourglass-split me-2" style="color:var(--mg-amber)"></i>
                    Pending Review
                    @if($stats['pending'] > 0)
                        <span class="ms-2 badge rounded-pill"
                              style="background:rgba(217,119,6,.12);color:var(--mg-amber);font-size:.7rem">
                            {{ $stats['pending'] }}
                        </span>
                    @endif
                </span>
                <a href="{{ route('manager.expense-requests.index', ['status' => 'pending']) }}"
                   class="ef-mg-card-aside">View all →</a>
            </div>
            <div class="ef-mg-req-list">
                @forelse($pendingRequests as $req)
                    @php
                        $initials = collect(explode(' ', $req->requester->name ?? 'UN'))
                                        ->take(2)->map(fn($w) => strtoupper($w[0]))->join('');
                        $pm = $priorityMeta[$req->priority ?? 'low'];
                    @endphp
                    <a href="{{ route('manager.expense-requests.show', $req) }}" class="ef-mg-req-item">
                        <div class="ef-mg-req-avatar">{{ $initials }}</div>
                        <div class="ef-mg-req-main">
                            <div class="ef-mg-req-title">{{ $req->title }}</div>
                            <div class="ef-mg-req-meta">
                                {{ $req->requester->name ?? '—' }}
                                @if($req->category)
                                    · {{ $req->category->name }}
                                @endif
                                <span class="ms-2 ef-mg-priority {{ $pm['cls'] }}">
                                    {{ ucfirst($req->priority ?? 'low') }}
                                </span>
                            </div>
                        </div>
                        <div class="ef-mg-req-right">
                            <div class="ef-mg-req-amount">₹{{ number_format($req->amount, 0) }}</div>
                            <div class="ef-mg-req-time">{{ $req->created_at->diffForHumans() }}</div>
                        </div>
                    </a>
                @empty
                    <div class="ef-mg-empty">
                        <div class="ef-mg-empty-orb"><i class="bi bi-check2-all"></i></div>
                        <p>All caught up — no pending requests.</p>
                        <a href="{{ route('manager.expense-requests.index') }}"
                           class="ef-mg-btn ef-mg-btn-primary" style="display:inline-flex">
                            View History
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Decisions --}}
        <div class="ef-mg-card">
            <div class="ef-mg-card-head">
                <span class="ef-mg-card-title">
                    <i class="bi bi-clock-history me-2" style="color:var(--mg-teal)"></i>Recent Decisions
                </span>
                <a href="{{ route('manager.expense-requests.index') }}" class="ef-mg-card-aside">All →</a>
            </div>
            <div class="ef-mg-req-list flush">
                @forelse($recentDecisions as $req)
                    <a href="{{ route('manager.expense-requests.show', $req) }}" class="ef-mg-feed-item">
                        <div class="ef-mg-feed-icon {{ $req->status }}">
                            <i class="bi bi-{{ $req->status === 'approved' ? 'check-lg' : 'x-lg' }}"></i>
                        </div>
                        <div class="ef-mg-feed-body">
                            <div class="ef-mg-feed-title">{{ $req->title }}</div>
                            <div class="ef-mg-feed-meta">{{ $req->requester->name ?? '—' }} · {{ $req->updated_at->diffForHumans() }}</div>
                        </div>
                        <div class="ef-mg-feed-amt"
                             style="color:{{ $req->status === 'approved' ? 'var(--mg-emerald)' : 'var(--mg-danger)' }}">
                            ₹{{ number_format($req->amount, 0) }}
                        </div>
                    </a>
                @empty
                    <p class="py-4 text-center" style="color:var(--mg-muted);font-size:.86rem">No decisions recorded yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Sidebar ─────────────────────────────────────────────── --}}
    <div class="d-flex flex-column gap-4">

        {{-- Attention Required --}}
        <div class="ef-mg-card">
            <div class="ef-mg-card-head">
                <span class="ef-mg-card-title">Attention Required</span>
                @php $urgentCount = $attentionItems->where('tone','danger')->count(); @endphp
                @if($urgentCount > 0)
                    <span style="color:var(--mg-danger);font-size:.8rem;font-weight:760">
                        <i class="bi bi-exclamation-circle-fill"></i> {{ $urgentCount }} high-value
                    </span>
                @endif
            </div>
            @if($attentionItems->isNotEmpty())
                <div class="flush">
                    @foreach($attentionItems as $item)
                        <a href="{{ $item['url'] }}" class="ef-mg-alert-item" data-tone="{{ $item['tone'] }}">
                            <span class="ef-mg-alert-rail"></span>
                            <span>
                                <span class="ef-mg-alert-title d-block">{{ $item['title'] }}</span>
                                <span class="ef-mg-alert-body">{{ $item['body'] }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="ef-mg-card-body">
                    <p class="mb-0" style="color:var(--mg-muted);font-size:.84rem">No alerts — everything looks normal.</p>
                </div>
            @endif
        </div>

        {{-- Action Hub --}}
        <div class="ef-mg-card">
            <div class="ef-mg-card-head">
                <span class="ef-mg-card-title">Action Hub</span>
            </div>
            <div class="ef-mg-card-body">
                <div class="ef-mg-action-grid">
                    <a href="{{ route('manager.expense-requests.index', ['status' => 'pending']) }}"
                       class="ef-mg-action-tile primary">
                        <i class="bi bi-check2-square"></i>
                        Review Pending ({{ $stats['pending'] }})
                    </a>
                    <a href="{{ route('manager.expense-requests.index', ['priority' => 'urgent']) }}"
                       class="ef-mg-action-tile">
                        <i class="bi bi-fire"></i>
                        Urgent
                    </a>
                    <a href="{{ route('manager.expense-requests.index', ['priority' => 'high']) }}"
                       class="ef-mg-action-tile">
                        <i class="bi bi-arrow-up-circle-fill"></i>
                        High Priority
                    </a>
                    <a href="{{ route('manager.expense-requests.index', ['status' => 'approved']) }}"
                       class="ef-mg-action-tile">
                        <i class="bi bi-wallet2"></i>
                        Payment Follow-up
                    </a>
                    <a href="{{ route('manager.expense-requests.index') }}"
                       class="ef-mg-action-tile">
                        <i class="bi bi-list-ul"></i>
                        All Requests
                    </a>
                    <a href="{{ route('manager.expense-requests.index', ['status' => 'rejected']) }}"
                       class="ef-mg-action-tile">
                        <i class="bi bi-x-circle"></i>
                        Rejected
                    </a>
                </div>
            </div>
        </div>

        {{-- Approval Health --}}
        <div class="ef-mg-card">
            <div class="ef-mg-card-head">
                <span class="ef-mg-card-title">Approval Health</span>
            </div>
            <div class="ef-mg-card-body">
                @php
                    $total  = $stats['total_processed'] ?: 1;
                    $approved_count = $stats['approved_total'];
                    $approvalRate   = round(($approved_count / $total) * 100);
                    $rateColor = $approvalRate >= 70 ? 'var(--mg-emerald)' : ($approvalRate >= 40 ? 'var(--mg-amber)' : 'var(--mg-danger)');
                @endphp
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span style="font-size:.78rem;font-weight:700;color:var(--mg-muted);text-transform:uppercase;letter-spacing:.05em">Approval Rate</span>
                    <span style="font-size:1.1rem;font-weight:800;color:{{ $rateColor }}">{{ $approvalRate }}%</span>
                </div>
                <div style="background:var(--mg-faint);border-radius:6px;height:8px;overflow:hidden">
                    <div style="background:{{ $rateColor }};border-radius:6px;height:8px;width:{{ $approvalRate }}%;transition:width .5s"></div>
                </div>
                <div class="mt-3 d-flex gap-3">
                    <div style="flex:1;text-align:center">
                        <div style="font-size:1.1rem;font-weight:800;color:var(--mg-emerald)">{{ $approved_count }}</div>
                        <div style="font-size:.72rem;color:var(--mg-muted);font-weight:700">Approved</div>
                    </div>
                    <div style="width:1px;background:var(--mg-border)"></div>
                    <div style="flex:1;text-align:center">
                        <div style="font-size:1.1rem;font-weight:800;color:var(--mg-danger)">{{ $stats['rejected'] }}</div>
                        <div style="font-size:.72rem;color:var(--mg-muted);font-weight:700">Rejected</div>
                    </div>
                    <div style="width:1px;background:var(--mg-border)"></div>
                    <div style="flex:1;text-align:center">
                        <div style="font-size:1.1rem;font-weight:800;color:var(--mg-amber)">{{ $stats['pending'] }}</div>
                        <div style="font-size:.72rem;color:var(--mg-muted);font-weight:700">Pending</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

{{-- Mobile bottom bar --}}
<div class="ef-mg-mobile-bar">
    <a href="{{ route('manager.expense-requests.index', ['status' => 'pending']) }}"
       class="ef-mg-btn ef-mg-btn-primary flex-fill justify-content-center">
        <i class="bi bi-check2-square"></i> Review ({{ $stats['pending'] }})
    </a>
    <a href="{{ route('manager.expense-requests.index') }}"
       class="ef-mg-btn flex-fill justify-content-center">
        <i class="bi bi-list-ul"></i> All
    </a>
</div>

</x-admin-layout>
