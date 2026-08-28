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
.ef-ad-page { padding: 0; max-width: 1400px; margin: 0 auto; padding: 24px 32px; }
@media (max-width: 991.98px) { .ef-ad-page { padding: 16px 20px; } }
@media (max-width: 575.98px) { .ef-ad-page { padding: 12px 14px; } }

/* ── Compact hero ──────────────────────────────────────────────── */
.ef-ad-hero {
    background: linear-gradient(135deg, #041b14 0%, #052e21 45%, #02110c 100%) !important;
    border: 1px solid rgba(255,255,255,.06) !important;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 16px;
    overflow: hidden;
    padding: 18px 24px;
    position: relative;
    min-height: 96px;
}
.ef-ad-hero::before {
    background: radial-gradient(circle, rgba(15,123,95,.18) 0%, transparent 68%);
    border-radius: 50%;
    content: "";
    height: 320px;
    pointer-events: none;
    position: absolute;
    right: -100px;
    top: -140px;
    width: 320px;
}
.ef-ad-hero-main { position: relative; z-index: 1; min-width: 220px; }
.ef-ad-eyebrow {
    color: rgba(184,137,62,.88);
    font-size: .64rem;
    font-weight: 760;
    letter-spacing: .16em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
.ef-ad-hero-title {
    color: #f0fdf8;
    font-size: 1.3rem;
    font-weight: 800;
    letter-spacing: -.02em;
    line-height: 1.2;
    margin-bottom: 4px;
}
.ef-ad-hero-summary {
    color: rgba(240,253,248,.50);
    font-size: .8rem;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
    margin-bottom: 0;
}
.ef-ad-hero-summary b   { color: rgba(240,253,248,.88); font-weight: 700; }
.ef-ad-hero-summary .dot { opacity: .3; }

.ef-ad-hero-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
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
    padding: 9px 16px;
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

/* ── Section heading ───────────────────────────────────────────── */
.ef-ad-section-title {
    align-items: center;
    color: var(--ad-ink);
    display: flex;
    font-size: .82rem;
    font-weight: 760;
    gap: 8px;
    letter-spacing: .02em;
    margin-bottom: 10px;
    text-transform: uppercase;
}
.ef-ad-section-title .count-pill {
    background: rgba(200,75,68,.12);
    border-radius: 20px;
    color: var(--ad-danger);
    font-size: .7rem;
    font-weight: 800;
    padding: 1px 9px;
}

/* ── Needs Attention cards ─────────────────────────────────────── */
.ef-ad-attention-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 10px;
    margin-bottom: 20px;
}
.ef-ad-attn-card {
    background: var(--ad-surface);
    border: 1px solid var(--ad-border);
    border-left: 3px solid var(--ad-amber);
    border-radius: var(--ad-radius);
    box-shadow: var(--ad-shadow);
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 14px 16px;
    text-decoration: none;
    transition: box-shadow .18s var(--ad-ease), transform .18s var(--ad-ease);
}
.ef-ad-attn-card:hover { box-shadow: var(--ad-shadow-h); transform: translateY(-2px); }
.ef-ad-attn-card.critical { border-left-color: var(--ad-danger); }
.ef-ad-attn-head { align-items: center; display: flex; gap: 8px; justify-content: space-between; }
.ef-ad-attn-label { align-items: center; color: var(--ad-ink); display: flex; font-size: .82rem; font-weight: 760; gap: 7px; }
.ef-ad-attn-label i { color: var(--ad-amber); }
.ef-ad-attn-card.critical .ef-ad-attn-label i { color: var(--ad-danger); }
.ef-ad-attn-count { color: var(--ad-amber); font-size: 1.35rem; font-weight: 800; line-height: 1; }
.ef-ad-attn-card.critical .ef-ad-attn-count { color: var(--ad-danger); }
.ef-ad-attn-desc { color: var(--ad-muted); font-size: .76rem; line-height: 1.3; }
.ef-ad-attn-cta {
    align-items: center;
    color: var(--ad-emerald-dk);
    display: inline-flex;
    font-size: .76rem;
    font-weight: 700;
    gap: 4px;
    margin-top: 2px;
}

/* ── All-clear compact state ──────────────────────────────────── */
.ef-ad-allclear {
    align-items: center;
    background: rgba(15,123,95,.06);
    border: 1px solid var(--ad-border);
    border-radius: var(--ad-radius);
    color: var(--ad-emerald-dk);
    display: flex;
    font-size: .84rem;
    font-weight: 700;
    gap: 8px;
    margin-bottom: 20px;
    padding: 14px 18px;
}
.ef-ad-allclear i { color: var(--ad-emerald); font-size: 1rem; }

/* ── KPI Metrics Strip ─────────────────────────────────────────── */
.ef-ad-metrics {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}
.ef-ad-metric {
    background: var(--ad-surface);
    border: 1px solid var(--ad-border);
    border-top: 3px solid rgba(15,123,95,.15);
    border-radius: var(--ad-radius);
    box-shadow: var(--ad-shadow);
    padding: 16px 16px 14px;
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

.ef-ad-metric[data-accent="emerald"] { border-top-color: var(--ad-emerald); }
.ef-ad-metric[data-accent="amber"]   { border-top-color: var(--ad-amber); }
.ef-ad-metric[data-accent="danger"]  { border-top-color: var(--ad-danger); }
.ef-ad-metric[data-accent="gold"]    { border-top-color: var(--ad-gold); }
.ef-ad-metric[data-accent="teal"]    { border-top-color: var(--ad-teal); }
.ef-ad-metric[data-accent="muted"]   { border-top-color: rgba(110,106,100,.2); }

/* De-emphasized KPI strip: quieter than the attention cards above it —
   smaller values, thinner shadow, tighter padding — so it reads as
   secondary context, not a competing priority signal. */
.ef-ad-metrics-muted { grid-template-columns: repeat(3, 1fr); }
.ef-ad-metrics-muted .ef-ad-metric { box-shadow: none; padding: 12px 14px 11px; }
.ef-ad-metrics-muted .ef-ad-metric-value { font-size: 1.15rem !important; }
.ef-ad-metrics-muted .ef-ad-metric-icon { opacity: .35; }

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
    padding: 14px 20px;
}
.ef-ad-card-title {
    color: var(--ad-ink);
    font-size: .86rem;
    font-weight: 760;
}
.ef-ad-card-aside {
    color: var(--ad-muted);
    font-size: .8rem;
    font-weight: 660;
    text-decoration: none;
}
a.ef-ad-card-aside { color: var(--ad-emerald); }
a.ef-ad-card-aside:hover { color: var(--ad-emerald-dk); }
.ef-ad-card-body { padding: 16px 20px; }

/* ── Compact request list ──────────────────────────────────────── */
.ef-ad-req-list { display: flex; flex-direction: column; }
.ef-ad-req-item {
    align-items: center;
    border-bottom: 1px solid var(--ad-border);
    display: flex;
    gap: 12px;
    padding: 10px 20px;
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
    font-size: .68rem;
    font-weight: 800;
    height: 30px;
    justify-content: center;
    letter-spacing: .04em;
    text-transform: uppercase;
    width: 30px;
}
.ef-ad-req-main { flex: 1; min-width: 0; }
.ef-ad-req-title {
    color: var(--ad-ink);
    font-size: .82rem;
    font-weight: 700;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ef-ad-req-meta {
    color: var(--ad-muted);
    font-size: .72rem;
    margin-top: 2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ef-ad-req-right { flex-shrink: 0; text-align: right; }
.ef-ad-req-amount {
    color: var(--ad-ink);
    font-size: .88rem;
    font-weight: 800;
}
.ef-ad-req-time { margin-top: 3px; }

/* ── Priority badge ────────────────────────────────────────────── */
.ef-ad-priority {
    border-radius: 5px;
    display: inline-flex;
    align-items: center;
    font-size: .62rem;
    font-weight: 760;
    letter-spacing: .04em;
    padding: 1px 6px;
    text-transform: uppercase;
}
.ef-ad-priority.urgent { background: rgba(200,75,68,.10); color: #9B2C2C; }
.ef-ad-priority.high   { background: rgba(216,154,61,.12); color: #7D5218; }
.ef-ad-priority.medium { background: rgba(15,123,95,.10); color: var(--ad-emerald-dk); }
.ef-ad-priority.low    { background: rgba(110,106,100,.07); color: #9A9690; }

/* ── Compact horizontal pipeline ──────────────────────────────── */
.ef-ad-pipeline {
    display: flex;
    align-items: center;
    gap: 0;
    padding: 2px 2px 2px;
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
    top: 13px;
    width: 100%;
}
.ef-ad-pipe-dot {
    align-items: center;
    border-radius: 50%;
    display: inline-flex;
    font-size: .62rem;
    height: 26px;
    justify-content: center;
    margin: 0 auto 5px;
    position: relative;
    width: 26px;
    z-index: 1;
}
.ef-ad-pipe-dot.done    { background: var(--ad-emerald); color: #fff; }
.ef-ad-pipe-dot.active  { background: var(--ad-amber); color: #fff; box-shadow: 0 0 0 4px rgba(216,154,61,.18); }
.ef-ad-pipe-dot.pending { background: var(--ad-faint); color: var(--ad-muted); }
.ef-ad-pipe-label {
    color: var(--ad-muted);
    font-size: .64rem;
    font-weight: 700;
    letter-spacing: .03em;
}
.ef-ad-pipe-count {
    color: var(--ad-ink);
    font-size: .95rem;
    font-weight: 800;
}

/* ── Action hub tiles ──────────────────────────────────────────── */
.ef-ad-action-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.ef-ad-action-tile {
    align-items: center;
    background: rgba(15,123,95,.06);
    border: 1px solid var(--ad-border);
    border-radius: 12px;
    color: var(--ad-ink);
    display: flex;
    flex-direction: column;
    font-size: .74rem;
    font-weight: 700;
    gap: 5px;
    padding: 12px 8px;
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
.ef-ad-action-tile i { color: var(--ad-emerald); font-size: 1.05rem; }
.ef-ad-action-tile.primary {
    background: var(--ad-emerald);
    border-color: var(--ad-emerald);
    color: #fff;
}
.ef-ad-action-tile.primary:hover { background: var(--ad-emerald-hi); border-color: var(--ad-emerald-hi); }
.ef-ad-action-tile.primary i { color: #fff; }

.ef-ad-quick-actions { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; margin-top: 10px; }
.ef-ad-quick-actions .ef-ad-action-tile { padding: 8px 6px; font-size: .68rem; }
.ef-ad-quick-actions .ef-ad-action-tile i { font-size: .9rem; }
.ef-ad-subhead {
    color: var(--ad-muted);
    font-size: .66rem;
    font-weight: 760;
    letter-spacing: .06em;
    margin: 12px 0 4px;
    text-transform: uppercase;
}
.ef-ad-subhead:first-child { margin-top: 0; }

/* ── Summary rows ──────────────────────────────────────────────── */
.ef-ad-summary-strip {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}
.ef-ad-summary-tile {
    background: var(--ad-surface);
    border: 1px solid var(--ad-border);
    border-radius: var(--ad-radius);
    box-shadow: var(--ad-shadow);
    padding: 12px 16px;
    text-align: center;
}
.ef-ad-summary-tile .val { color: var(--ad-ink); font-size: 1.05rem; font-weight: 800; }
.ef-ad-summary-tile .lbl { color: var(--ad-muted); font-size: .68rem; font-weight: 700; letter-spacing: .04em; margin-top: 2px; text-transform: uppercase; }

/* ── Recent Activity (compact timeline) ─────────────────────────── */
.ef-ad-timeline { display: flex; flex-direction: column; }
.ef-ad-tl-item {
    align-items: center;
    border-bottom: 1px solid var(--ad-border);
    display: flex;
    gap: 10px;
    padding: 8px 20px;
}
.ef-ad-tl-item:last-child { border-bottom: none; }
.ef-ad-tl-dot { background: var(--ad-emerald); border-radius: 50%; flex-shrink: 0; height: 6px; width: 6px; }
.ef-ad-tl-text { color: var(--ad-ink); flex: 1; font-size: .78rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ef-ad-tl-time { color: var(--ad-muted); flex-shrink: 0; font-size: .7rem; }

/* ── Empty state ───────────────────────────────────────────────── */
.ef-ad-empty { padding: 28px 20px; text-align: center; }
.ef-ad-empty-orb {
    align-items: center;
    background: rgba(15,123,95,.08);
    border: 1px solid var(--ad-border);
    border-radius: 50%;
    color: var(--ad-emerald);
    display: inline-flex;
    font-size: 1.2rem;
    height: 46px;
    justify-content: center;
    margin-bottom: 10px;
    width: 46px;
}
.ef-ad-empty p { color: var(--ad-muted); font-size: .82rem; margin-bottom: 0; }

/* ── Responsive ────────────────────────────────────────────────── */
@media (max-width: 1199.98px) {
    .ef-ad-metrics { grid-template-columns: repeat(4, 1fr); }
}
@media (max-width: 991.98px) {
    .ef-ad-grid { grid-template-columns: 1fr; }
    .ef-ad-metrics { grid-template-columns: repeat(2, 1fr); }
    .ef-ad-metrics-muted { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 767.98px) {
    .ef-ad-hero { padding: 16px 18px; border-radius: 14px; align-items: flex-start; }
    .ef-ad-hero-title { font-size: 1.15rem; }
    .ef-ad-metrics { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .ef-ad-metrics-muted { grid-template-columns: repeat(2, 1fr); }
    .ef-ad-attention-grid { grid-template-columns: 1fr; }
}
@media (max-width: 575.98px) {
    .ef-ad-hero { flex-direction: column; align-items: stretch; }
    .ef-ad-hero-actions { flex-direction: column; }
    .ef-ad-metrics { grid-template-columns: 1fr 1fr; }
    .ef-ad-metrics-muted { grid-template-columns: 1fr 1fr; }
    .ef-ad-quick-actions { grid-template-columns: repeat(2, 1fr); }
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

    // Decision Actions: highlight whichever module actually has the most
    // outstanding work as the primary tile, instead of always hardcoding
    // "Review Expenses" — reuses the exact same pending counts already
    // computed in the controller, just picks the max for presentation.
    $decisionTiles = collect([
        [
            'key'   => 'expenses',
            'label' => 'Review Expenses',
            'count' => $stats['pending_approvals'],
            'icon'  => 'bi-check2-square',
            'url'   => route('admin.expense-requests.index', ['status' => 'pending']),
        ],
        [
            'key'   => 'overtime',
            'label' => 'Overtime',
            'count' => $stats['overtime_pending'],
            'icon'  => 'bi-clock-history',
            'url'   => route('admin.overtime.index'),
        ],
        [
            'key'   => 'advances',
            'label' => 'Advances',
            'count' => $stats['advance_pending'],
            'icon'  => 'bi-cash-coin',
            'url'   => route('admin.advances.index'),
        ],
        [
            'key'   => 'leave',
            'label' => 'Leave',
            'count' => $stats['leave_pending'],
            'icon'  => 'bi-calendar-check',
            'url'   => route('admin.leave.requests.index', ['status' => 'pending']),
        ],
    ]);
    $primaryTileKey = $decisionTiles->sortByDesc('count')->first()['key'];
@endphp

<div class="ef-ad-page">

{{-- ── Compact hero ────────────────────────────────────────────── --}}
<section class="ef-ad-hero">
    <div class="ef-ad-hero-main">
        <div class="ef-ad-eyebrow">Admin Dashboard</div>
        <h1 class="ef-ad-hero-title">{{ $greeting }}, {{ $name }}</h1>
        <div class="ef-ad-hero-summary">
            @if($needsActionTotal > 0)
                <span style="color:rgba(216,154,61,.9)"><b>{{ $needsActionTotal }}</b> need attention</span>
                <span class="dot">·</span>
            @endif
            <span><b>₹{{ number_format($stats['total_wallet_balance'], 0) }}</b> wallet balance</span>
            <span class="dot">·</span>
            <span><b>{{ $stats['total_submitted'] }}</b> requests this period</span>
            <span class="dot">·</span>
            <span>{{ now()->format('d F Y') }}</span>
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
</section>

{{-- ── Needs Your Attention ────────────────────────────────────── --}}
<div class="ef-ad-section-title">
    <i class="bi bi-bell-fill" style="color:var(--ad-amber)"></i> Needs Your Attention
    @if($needsActionTotal > 0)
        <span class="count-pill">{{ $needsActionTotal }}</span>
    @endif
</div>

@if($needsAttention->isEmpty())
    <div class="ef-ad-allclear">
        <i class="bi bi-check-circle-fill"></i> All caught up — nothing requires review right now.
    </div>
@else
    <div class="ef-ad-attention-grid">
        @foreach($needsAttention as $item)
            <a href="{{ $item['url'] }}" class="ef-ad-attn-card{{ !empty($item['critical']) ? ' critical' : '' }}">
                <div class="ef-ad-attn-head">
                    <span class="ef-ad-attn-label"><i class="bi {{ $item['icon'] }}"></i> {{ $item['label'] }}</span>
                    <span class="ef-ad-attn-count">{{ $item['count'] }}</span>
                </div>
                <div class="ef-ad-attn-desc">{{ $item['count'] }} {{ $item['desc'] }}</div>
                <span class="ef-ad-attn-cta">{{ $item['cta'] }} <i class="bi bi-arrow-right"></i></span>
            </a>
        @endforeach
    </div>
@endif

{{-- ── KPI Row (secondary context — de-emphasized vs. attention cards) ── --}}
<div class="ef-ad-metrics ef-ad-metrics-muted">
    <div class="ef-ad-metric" data-accent="teal">
        <div class="ef-ad-metric-icon"><i class="bi bi-currency-rupee"></i></div>
        <div class="ef-ad-metric-label">Month Spend</div>
        <div class="ef-ad-metric-value c-teal" style="font-size:1.15rem">₹{{ number_format($stats['total_expenses_month'], 0) }}</div>
        <div class="ef-ad-metric-note">{{ now()->format('M Y') }}</div>
    </div>
    <a href="{{ route('admin.wallets.index') }}" class="ef-ad-metric" data-accent="emerald">
        <div class="ef-ad-metric-icon"><i class="bi bi-wallet2"></i></div>
        <div class="ef-ad-metric-label">Wallet Balance</div>
        <div class="ef-ad-metric-value c-emerald" style="font-size:1.15rem">₹{{ number_format($stats['total_wallet_balance'], 0) }}</div>
        <div class="ef-ad-metric-note">across all employees</div>
    </a>
    <a href="{{ route('admin.employees.index') }}" class="ef-ad-metric" data-accent="gold">
        <div class="ef-ad-metric-icon"><i class="bi bi-people-fill"></i></div>
        <div class="ef-ad-metric-label">Team Size</div>
        <div class="ef-ad-metric-value">{{ $stats['total_employees'] }}</div>
        <div class="ef-ad-metric-note">{{ $stats['total_managers'] }} managers</div>
    </a>
</div>

{{-- ── 70/30: Recent Requests + Approval Pipeline ─────────────────── --}}
<div class="ef-ad-grid mb-4">
    <div class="ef-ad-card">
        <div class="ef-ad-card-head">
            <span class="ef-ad-card-title">
                <i class="bi bi-clock-history me-2" style="color:var(--ad-gold)"></i>
                Recent Requests
                @if($stats['pending_approvals'] > 0)
                    <span class="ms-2" style="background:rgba(216,154,61,.12);color:var(--ad-amber);font-size:.66rem;font-weight:760;border-radius:6px;padding:2px 7px">
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

    <div class="ef-ad-card">
        <div class="ef-ad-card-head">
            <span class="ef-ad-card-title">
                <i class="bi bi-diagram-3 me-2" style="color:var(--ad-emerald)"></i>Pipeline
            </span>
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
                    <div class="ef-ad-pipe-label">Review</div>
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

            @php
                $total        = $stats['total_processed'] ?: 1;
                $approvalRate = round(($stats['approved_total'] / $total) * 100);
                $rateColor    = $approvalRate >= 70
                    ? 'var(--ad-emerald)'
                    : ($approvalRate >= 40 ? 'var(--ad-amber)' : 'var(--ad-danger)');
            @endphp
            <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                <span style="font-size:.7rem;font-weight:720;color:var(--ad-muted);text-transform:uppercase;letter-spacing:.05em">Approval Rate</span>
                <span style="font-size:1rem;font-weight:800;color:{{ $rateColor }}">{{ $approvalRate }}%</span>
            </div>
            <div class="ef-ad-health-bar-track" style="background:var(--ad-faint);border-radius:6px;height:6px;overflow:hidden">
                <div style="background:{{ $rateColor }};width:{{ $approvalRate }}%;height:6px;border-radius:6px"></div>
            </div>

            {{-- Rejected / reimbursement-pending folded in here rather than a
                 separate full-width strip further down the page — same figures
                 the pipeline above is already built from, so this avoids
                 repeating "Approved" / "Paid" a second time and keeps the
                 sidebar card from trailing off into empty space next to the
                 taller Recent Requests list. --}}
            <div style="display:flex;justify-content:space-between;padding:10px 0 0;margin-top:14px;border-top:1px solid var(--ad-border)">
                <span style="color:var(--ad-muted);font-size:.72rem;font-weight:700">Rejected</span>
                <span style="color:var(--ad-danger);font-size:.82rem;font-weight:800">{{ $stats['rejected'] }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 0 0">
                <span style="color:var(--ad-muted);font-size:.72rem;font-weight:700">Reimb. Pending</span>
                <span style="color:var(--ad-ink);font-size:.82rem;font-weight:800">{{ $stats['pending_reimb_count'] }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ── Decision Actions / Quick Actions ────────────────────────── --}}
<div class="ef-ad-grid mb-4">
    <div class="ef-ad-card">
        <div class="ef-ad-card-head">
            <span class="ef-ad-card-title">Decision Actions</span>
        </div>
        <div class="ef-ad-card-body">
            <div class="ef-ad-action-grid">
                @foreach($decisionTiles as $tile)
                    <a href="{{ $tile['url'] }}"
                       class="ef-ad-action-tile{{ $tile['key'] === $primaryTileKey && $tile['count'] > 0 ? ' primary' : '' }}">
                        <i class="bi {{ $tile['icon'] }}"></i>
                        {{ $tile['label'] }} ({{ $tile['count'] }})
                    </a>
                @endforeach
            </div>

            <div class="ef-ad-subhead">Quick Actions</div>
            <div class="ef-ad-quick-actions">
                <a href="{{ route('admin.employees.create') }}" class="ef-ad-action-tile">
                    <i class="bi bi-person-plus-fill"></i> Add Employee
                </a>
                <a href="{{ route('admin.categories.create') }}" class="ef-ad-action-tile">
                    <i class="bi bi-tag-fill"></i> Add Category
                </a>
                <a href="{{ route('admin.vendors.create') }}" class="ef-ad-action-tile">
                    <i class="bi bi-shop"></i> Add Vendor
                </a>
            </div>
        </div>
    </div>

    <div class="ef-ad-card">
        <div class="ef-ad-card-head">
            <span class="ef-ad-card-title">
                <i class="bi bi-info-circle me-2" style="color:var(--ad-gold)"></i>Workforce
            </span>
        </div>
        <div class="ef-ad-card-body">
            <div class="ef-ad-summary-row" style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--ad-border)">
                <span style="color:var(--ad-muted);font-size:.78rem;font-weight:660">Active Users</span>
                <span style="color:var(--ad-ink);font-size:.84rem;font-weight:760">{{ $stats['active_users'] }}</span>
            </div>
            <div class="ef-ad-summary-row" style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--ad-border)">
                <span style="color:var(--ad-muted);font-size:.78rem;font-weight:660">Inactive Users</span>
                <span style="color:var(--ad-muted);font-size:.84rem;font-weight:760">{{ $stats['inactive_users'] }}</span>
            </div>
            <div class="ef-ad-summary-row" style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--ad-border)">
                <span style="color:var(--ad-muted);font-size:.78rem;font-weight:660">Managers</span>
                <span style="color:var(--ad-ink);font-size:.84rem;font-weight:760">{{ $stats['total_managers'] }}</span>
            </div>
            <div class="ef-ad-summary-row" style="display:flex;justify-content:space-between;padding:8px 0">
                <span style="color:var(--ad-muted);font-size:.78rem;font-weight:660">Employees</span>
                <span style="color:var(--ad-ink);font-size:.84rem;font-weight:760">{{ $stats['total_employees'] }}</span>
            </div>
        </div>
    </div>
</div>

</div>

</x-admin-layout>
