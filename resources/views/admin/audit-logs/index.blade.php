<x-admin-layout title="Audit Logs">

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   Audit Logs — Operational Activity Intelligence
   ═══════════════════════════════════════════════════════ */

.ef-al-shell {
    max-width: 1480px;
    margin: 0 auto;
    padding-bottom: 88px;
}

/* ── Hero ─────────────────────────────────────────────── */
.ef-al-hero {
    align-items: stretch;
    background: linear-gradient(135deg, rgba(255,253,250,.98), rgba(249,247,242,.94));
    border: 1px solid var(--ef-border);
    border-radius: 20px;
    box-shadow: var(--ef-shadow);
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(280px, 380px);
    margin-bottom: 18px;
    overflow: hidden;
}

.ef-al-hero-main { padding: 32px 36px; }

.ef-al-hero-side {
    background: rgba(20,20,18,.022);
    border-left: 1px solid var(--ef-border);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 32px 36px;
}

.ef-al-title {
    color: var(--ef-ink);
    font-size: clamp(2.4rem, 4vw, 3.75rem);
    font-weight: 780;
    letter-spacing: -.01em;
    line-height: .96;
    margin: 8px 0 16px;
}

.ef-al-subtitle {
    color: var(--ef-muted);
    display: flex;
    flex-wrap: wrap;
    font-size: .92rem;
    gap: 5px 16px;
    margin: 0;
}

.ef-al-subtitle i { font-size: .76rem; opacity: .58; }

.ef-al-today-block {
    margin-bottom: 22px;
}

.ef-al-today-label {
    color: var(--ef-faint);
    font-size: .6rem;
    font-weight: 760;
    letter-spacing: .14em;
    text-transform: uppercase;
}

.ef-al-today-value {
    color: var(--ef-ink);
    font-size: 2.4rem;
    font-variant-numeric: tabular-nums;
    font-weight: 780;
    line-height: 1.05;
    margin-top: 4px;
}

.ef-al-today-note {
    color: var(--ef-muted);
    font-size: .78rem;
    margin-top: 5px;
}

.ef-al-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-end;
}

/* ── Insights Strip ───────────────────────────────────── */
.ef-al-insights {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    margin-bottom: 18px;
}

.ef-al-insight {
    background: rgba(255,253,250,.92);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    min-height: 108px;
    padding: 18px 20px;
    transition: border-color .18s var(--ef-ease), box-shadow .18s var(--ef-ease), transform .18s var(--ef-ease);
}

.ef-al-insight:hover {
    border-color: var(--ef-border-strong);
    box-shadow: var(--ef-shadow-hover);
    transform: translateY(-1px);
}

.ef-al-insight-icon {
    color: var(--ef-faint);
    font-size: .86rem;
    margin-bottom: 10px;
}

.ef-al-insight-label {
    color: var(--ef-faint);
    font-size: .62rem;
    font-weight: 760;
    letter-spacing: .13em;
    text-transform: uppercase;
}

.ef-al-insight-value {
    color: var(--ef-ink);
    font-size: 1.35rem;
    font-variant-numeric: tabular-nums;
    font-weight: 780;
    line-height: 1;
    margin-top: 9px;
}

.ef-al-insight-note {
    color: var(--ef-muted);
    font-size: .72rem;
    line-height: 1.4;
    margin-top: 6px;
}

.ef-al-insight.--approvals .ef-al-insight-value { color: var(--ef-emerald); }
.ef-al-insight.--critical  .ef-al-insight-value { color: var(--ef-danger); }
.ef-al-insight.--financial .ef-al-insight-value { color: var(--ef-gold); }

/* ── Filter Bar ───────────────────────────────────────── */
.ef-al-filter-bar {
    background: rgba(255,253,250,.94);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    margin-bottom: 16px;
}

.ef-al-filter-inner {
    align-items: flex-end;
    display: flex;
    flex-wrap: wrap;
    gap: 10px 14px;
    padding: 16px 22px;
}

.ef-al-filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.ef-al-filter-label {
    color: var(--ef-faint);
    font-size: .59rem;
    font-weight: 760;
    letter-spacing: .14em;
    text-transform: uppercase;
}

.ef-al-filter-select,
.ef-al-filter-input {
    background: rgba(251,250,247,.96);
    border: 1px solid var(--ef-border-strong);
    border-radius: 10px;
    color: var(--ef-ink-2);
    font-size: .84rem;
    font-weight: 520;
    height: 38px;
    padding: 0 11px;
    transition: background .16s var(--ef-ease), border-color .16s var(--ef-ease), box-shadow .16s var(--ef-ease);
}

.ef-al-filter-select:focus,
.ef-al-filter-input:focus {
    background: #fff;
    border-color: rgba(20,20,18,.46);
    box-shadow: 0 0 0 4px rgba(20,20,18,.05);
    outline: 0;
}

.ef-al-filter-sep {
    background: var(--ef-border);
    height: 30px;
    width: 1px;
    flex-shrink: 0;
}

.ef-al-filter-actions {
    align-items: center;
    display: flex;
    gap: 8px;
    margin-left: auto;
}

.ef-al-filter-active-chip {
    align-items: center;
    background: rgba(96,112,128,.08);
    border: 1px solid rgba(96,112,128,.18);
    border-radius: 999px;
    color: var(--ef-bluegray);
    display: flex;
    font-size: .64rem;
    font-weight: 760;
    gap: 5px;
    letter-spacing: .06em;
    padding: 4px 10px;
    text-transform: uppercase;
}

/* ── Timeline Shell ───────────────────────────────────── */
.ef-al-timeline-wrap {
    background: rgba(255,253,250,.94);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    overflow: hidden;
}

/* ── Date Separator ───────────────────────────────────── */
.ef-al-date-sep {
    align-items: center;
    display: flex;
    gap: 12px;
    padding: 18px 24px 10px;
}

.ef-al-date-sep-line {
    background: rgba(20,20,18,.08);
    flex: 1;
    height: 1px;
}

.ef-al-date-sep-label {
    align-items: center;
    color: var(--ef-muted);
    display: flex;
    font-size: .7rem;
    font-weight: 760;
    gap: 8px;
    letter-spacing: .08em;
    text-transform: uppercase;
    white-space: nowrap;
}

.ef-al-date-sep-count {
    background: rgba(20,20,18,.05);
    border: 1px solid rgba(20,20,18,.07);
    border-radius: 999px;
    color: var(--ef-faint);
    font-size: .6rem;
    font-weight: 760;
    padding: 2px 8px;
}

/* ── Timeline Event ───────────────────────────────────── */
.ef-al-event {
    display: grid;
    grid-template-columns: 68px 20px minmax(0, 1fr) auto;
    gap: 0 14px;
    padding: 0 24px;
    align-items: start;
    transition: background .14s var(--ef-ease);
}

.ef-al-event:hover { background: rgba(20,20,18,.014); }

/* Time column */
.ef-al-time {
    padding: 16px 0;
    text-align: right;
}

.ef-al-time-value {
    color: var(--ef-muted);
    font-size: .76rem;
    font-variant-numeric: tabular-nums;
    font-weight: 640;
    line-height: 1;
    white-space: nowrap;
}

.ef-al-time-ampm {
    color: var(--ef-faint);
    font-size: .6rem;
    font-weight: 760;
    letter-spacing: .06em;
    margin-top: 3px;
    text-transform: uppercase;
}

/* Track column (line + dot) */
.ef-al-track {
    align-self: stretch;
    align-items: center;
    display: flex;
    flex-direction: column;
}

.ef-al-line-t,
.ef-al-line-b {
    background: rgba(20,20,18,.09);
    flex: 1;
    min-height: 16px;
    width: 1px;
    transition: background .15s;
}

.ef-al-line-t.--off,
.ef-al-line-b.--off { background: transparent; }

.ef-al-dot {
    background: var(--ef-faint);
    border: 2.5px solid rgba(255,253,250,.95);
    border-radius: 50%;
    box-shadow: 0 0 0 0 rgba(20,20,18,.12);
    flex-shrink: 0;
    height: 10px;
    position: relative;
    transition: transform .14s var(--ef-ease), box-shadow .14s var(--ef-ease);
    width: 10px;
    z-index: 1;
}

.ef-al-event:hover .ef-al-dot {
    box-shadow: 0 0 0 5px rgba(20,20,18,.07);
    transform: scale(1.22);
}

/* Tone-coded dots */
.ef-al-event[data-tone="emerald"] .ef-al-dot {
    background: var(--ef-emerald);
    border-color: rgba(61,115,88,.12);
}
.ef-al-event[data-tone="gold"] .ef-al-dot {
    background: var(--ef-gold);
    border-color: rgba(169,131,56,.12);
}
.ef-al-event[data-tone="danger"] .ef-al-dot {
    background: var(--ef-danger);
    border-color: rgba(141,74,60,.12);
}
.ef-al-event[data-tone="bluegray"] .ef-al-dot {
    background: var(--ef-bluegray);
    border-color: rgba(96,112,128,.12);
}

/* Body column */
.ef-al-body {
    padding: 14px 0 14px;
    min-width: 0;
}

.ef-al-event-title {
    color: var(--ef-ink);
    font-size: .92rem;
    font-weight: 720;
    line-height: 1.35;
}

.ef-al-event-ref {
    color: var(--ef-ink-2);
    font-size: .82rem;
    font-weight: 540;
    line-height: 1.45;
    margin-top: 3px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.ef-al-event-meta {
    align-items: center;
    color: var(--ef-faint);
    display: flex;
    flex-wrap: wrap;
    font-size: .73rem;
    gap: 4px 10px;
    line-height: 1.5;
    margin-top: 5px;
}

.ef-al-event-meta .sep { color: rgba(20,20,18,.14); }

.ef-al-mobile-time {
    display: none;
    color: var(--ef-faint);
    font-size: .7rem;
    font-variant-numeric: tabular-nums;
    font-weight: 640;
}

/* "View changes" button */
.ef-al-changes-btn {
    align-items: center;
    background: none;
    border: 1px solid rgba(20,20,18,.1);
    border-radius: 7px;
    color: var(--ef-muted);
    cursor: pointer;
    display: inline-flex;
    font-size: .72rem;
    font-weight: 650;
    gap: 5px;
    margin-top: 9px;
    padding: 4px 9px;
    transition: background .14s var(--ef-ease), border-color .14s var(--ef-ease), color .14s var(--ef-ease);
}

.ef-al-changes-btn:hover {
    background: rgba(20,20,18,.04);
    border-color: rgba(20,20,18,.18);
    color: var(--ef-ink);
}

.ef-al-changes-btn .bi {
    transition: transform .14s var(--ef-ease);
    font-size: .8rem;
}

.ef-al-changes-btn[aria-expanded="true"] .bi-chevron-down {
    transform: rotate(180deg);
}

/* Expandable changes diff */
.ef-al-changes {
    background: rgba(20,20,18,.022);
    border: 1px solid var(--ef-border);
    border-radius: 10px;
    margin-top: 10px;
    overflow: hidden;
}

.ef-al-changes-section {
    padding: 10px 14px;
}

.ef-al-changes-section + .ef-al-changes-section {
    border-top: 1px solid var(--ef-border);
}

.ef-al-changes-head {
    color: var(--ef-faint);
    font-size: .58rem;
    font-weight: 760;
    letter-spacing: .14em;
    margin-bottom: 8px;
    text-transform: uppercase;
}

.ef-al-changes-grid {
    display: grid;
    gap: 3px 12px;
    grid-template-columns: auto 1fr;
}

.ef-al-changes-key {
    color: var(--ef-muted);
    font-family: ui-monospace, 'SF Mono', 'Cascadia Code', Consolas, monospace;
    font-size: .72rem;
    font-weight: 600;
    padding: 1px 0;
    white-space: nowrap;
}

.ef-al-changes-val {
    color: var(--ef-ink-2);
    font-family: ui-monospace, 'SF Mono', 'Cascadia Code', Consolas, monospace;
    font-size: .72rem;
    overflow: hidden;
    padding: 1px 0;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.ef-al-changes-val.--old { color: var(--ef-danger); }
.ef-al-changes-val.--new { color: var(--ef-emerald); }

/* Chips column */
.ef-al-chips {
    align-items: flex-end;
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 16px 0 14px;
}

/* ── Pagination ────────────────────────────────────────── */
.ef-al-pagination {
    display: flex;
    justify-content: center;
    margin-top: 16px;
}

.ef-al-pagination .pagination { gap: 4px; margin: 0; }

.ef-al-pagination .page-link {
    background: rgba(255,253,250,.92);
    border: 1px solid var(--ef-border);
    border-radius: 10px !important;
    color: var(--ef-ink-2);
    font-size: .8rem;
    font-weight: 650;
    height: 36px;
    line-height: 36px;
    min-width: 36px;
    padding: 0 10px;
    text-align: center;
    transition: background .15s var(--ef-ease), border-color .15s var(--ef-ease);
}

.ef-al-pagination .page-link:hover {
    background: var(--ef-surface-2);
    border-color: var(--ef-border-strong);
    color: var(--ef-ink);
}

.ef-al-pagination .active .page-link {
    background: var(--ef-ink);
    border-color: var(--ef-ink);
    color: #fffdfa;
}

.ef-al-pagination .disabled .page-link { opacity: .36; }

/* ── Mobile Sticky Bar ────────────────────────────────── */
.ef-al-mobile-bar {
    backdrop-filter: blur(18px) saturate(160%);
    background: rgba(255,253,250,.94);
    border-top: 1px solid var(--ef-border);
    bottom: 0;
    display: none;
    gap: 8px;
    grid-template-columns: 1fr auto auto;
    left: 0;
    padding: 10px 14px calc(10px + env(safe-area-inset-bottom));
    position: fixed;
    right: 0;
    z-index: 1040;
}

/* ── Responsive ────────────────────────────────────────── */
@media (max-width: 1199.98px) {
    .ef-al-hero { grid-template-columns: 1fr; }
    .ef-al-hero-side {
        border-left: 0;
        border-top: 1px solid var(--ef-border);
    }
    .ef-al-actions { justify-content: flex-start; }
    .ef-al-insights { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .ef-al-today-value { font-size: 1.9rem; }
}

@media (max-width: 767.98px) {
    .ef-al-shell { padding-bottom: 84px; }
    .ef-al-hero-main, .ef-al-hero-side { padding: 24px; }
    .ef-al-insights { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ef-al-insights .ef-al-insight:last-child { display: none; } /* hide top user on mobile */

    .ef-al-filter-bar { display: none; }
    .ef-al-filter-bar.--open { display: block; }
    .ef-al-filter-inner { flex-direction: column; align-items: stretch; }
    .ef-al-filter-sep { display: none; }
    .ef-al-filter-select,
    .ef-al-filter-input { width: 100%; }
    .ef-al-filter-actions { margin-left: 0; }

    .ef-al-event {
        grid-template-columns: 20px minmax(0, 1fr) auto;
        grid-template-areas:
            "track body chips"
            "track body chips";
        gap: 0 12px;
        padding: 0 16px;
    }

    .ef-al-time { display: none; }
    .ef-al-track { grid-area: track; }
    .ef-al-body  { grid-area: body; }
    .ef-al-chips {
        grid-area: chips;
        flex-direction: column;
        padding-top: 14px;
    }

    .ef-al-mobile-time { display: inline; }

    .ef-al-date-sep { padding: 16px 16px 8px; }
    .ef-al-mobile-bar { display: grid; }
}

@media print {
    .ef-al-filter-bar,
    .ef-al-actions,
    .ef-al-mobile-bar,
    .ef-al-chips { display: none !important; }

    .ef-al-event { grid-template-columns: 68px 20px 1fr; }
}
</style>
@endpush

@php
/* ── Lookup tables ───────────────────────────────────── */
$moduleNames = [
    'expense_request' => 'expense request',
    'daily_closing'   => 'daily closing',
    'employee'        => 'employee',
    'category'        => 'category',
    'vendor'          => 'vendor',
    'payment'         => 'payment',
    'wallet'          => 'wallet',
    'inventory_item'  => 'inventory item',
    'purchase_plan'   => 'purchase plan',
    'booking'         => 'booking',
    'hall_booking'    => 'hall booking',
    'meal_plan'       => 'meal plan',
    'adjustment'      => 'adjustment',
    'report'          => 'report',
    'setting'         => 'setting',
    'user'            => 'user account',
];

$actionVerbs = [
    'approved'     => 'Approved',
    'rejected'     => 'Rejected',
    'created'      => 'Created',
    'updated'      => 'Updated',
    'deleted'      => 'Deleted',
    'credited'     => 'Credited',
    'debited'      => 'Debited',
    'settled'      => 'Settled',
    'reimbursed'   => 'Reimbursed',
    'adjusted'     => 'Adjusted',
    'verified'     => 'Verified',
    'finalized'    => 'Finalized',
    'recalculated' => 'Recalculated',
    'paid'         => 'Paid',
    'cancelled'    => 'Cancelled',
    'restored'     => 'Restored',
    'locked'       => 'Locked',
    'unlocked'     => 'Unlocked',
];

$toneMap = [
    'approved'     => 'emerald',
    'verified'     => 'emerald',
    'finalized'    => 'emerald',
    'settled'      => 'emerald',
    'reimbursed'   => 'emerald',
    'credited'     => 'emerald',
    'restored'     => 'emerald',
    'created'      => 'neutral',
    'updated'      => 'neutral',
    'recalculated' => 'neutral',
    'locked'       => 'bluegray',
    'unlocked'     => 'bluegray',
    'adjusted'     => 'gold',
    'debited'      => 'gold',
    'paid'         => 'gold',
    'rejected'     => 'danger',
    'deleted'      => 'danger',
    'cancelled'    => 'danger',
];

$hasFilters = collect($filters)->filter()->isNotEmpty();
$exportUrl  = route('admin.audit-logs.index', array_merge($filters, ['export' => '1']));
@endphp

<div class="ef-al-shell">

    {{-- ═══ HERO ════════════════════════════════════════════════════════════ --}}
    <header class="ef-al-hero">

        <div class="ef-al-hero-main">
            <p class="ef-eyebrow">Operational Activity Intelligence</p>
            <h1 class="ef-al-title">Audit Logs</h1>
            <p class="ef-al-subtitle">
                <span><i class="bi bi-calendar3"></i> {{ now()->format('l, d F Y') }}</span>
                <span><i class="bi bi-shield-check"></i> All critical actions tracked</span>
            </p>
        </div>

        <div class="ef-al-hero-side">
            <div class="ef-al-today-block">
                <div class="ef-al-today-label">Today's Events</div>
                <div class="ef-al-today-value">{{ number_format($insights['today']) }}</div>
                <div class="ef-al-today-note">operational activities logged</div>
            </div>

            <div class="ef-al-actions">
                <a href="{{ $exportUrl }}" class="ef-btn">
                    <i class="bi bi-download"></i> Export CSV
                </a>
                <button class="ef-btn" onclick="window.print()" title="Print">
                    <i class="bi bi-printer"></i>
                </button>
                <button class="ef-btn ef-btn-icon" id="alFilterToggle" title="Toggle filters">
                    <i class="bi bi-funnel{{ $hasFilters ? '-fill' : '' }}"></i>
                </button>
            </div>
        </div>

    </header>

    {{-- ═══ INSIGHTS STRIP ════════════════════════════════════════════════ --}}
    <div class="ef-al-insights">

        <div class="ef-al-insight">
            <div class="ef-al-insight-icon"><i class="bi bi-activity"></i></div>
            <div class="ef-al-insight-label">Total Events</div>
            <div class="ef-al-insight-value">{{ number_format($insights['total']) }}</div>
            <div class="ef-al-insight-note">{{ $hasFilters ? 'in filtered view' : 'all time' }}</div>
        </div>

        <div class="ef-al-insight --approvals">
            <div class="ef-al-insight-icon"><i class="bi bi-check-circle"></i></div>
            <div class="ef-al-insight-label">Approvals</div>
            <div class="ef-al-insight-value">{{ number_format($insights['approvals']) }}</div>
            <div class="ef-al-insight-note">approved, verified, finalized</div>
        </div>

        <div class="ef-al-insight --financial">
            <div class="ef-al-insight-icon"><i class="bi bi-arrow-left-right"></i></div>
            <div class="ef-al-insight-label">Financial Events</div>
            <div class="ef-al-insight-value">{{ number_format($insights['financial']) }}</div>
            <div class="ef-al-insight-note">credits, debits, settlements</div>
        </div>

        <div class="ef-al-insight --critical">
            <div class="ef-al-insight-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="ef-al-insight-label">Critical Actions</div>
            <div class="ef-al-insight-value">{{ number_format($insights['critical']) }}</div>
            <div class="ef-al-insight-note">deletions and rejections</div>
        </div>

        <div class="ef-al-insight">
            <div class="ef-al-insight-icon"><i class="bi bi-person-check"></i></div>
            <div class="ef-al-insight-label">Most Active</div>
            <div class="ef-al-insight-value" style="font-size:1rem;margin-top:12px">
                {{ $insights['top_user'] ?? '—' }}
            </div>
            <div class="ef-al-insight-note">{{ $insights['top_user'] ? 'top contributor' : 'no activity' }}</div>
        </div>

    </div>

    {{-- ═══ FILTER BAR ══════════════════════════════════════════════════════ --}}
    <div class="ef-al-filter-bar {{ $hasFilters ? '--open' : '' }}" id="alFilterBar">
        <form method="GET" class="ef-al-filter-inner" id="alFilterForm">

            <div class="ef-al-filter-group">
                <label class="ef-al-filter-label">Module</label>
                <select name="module" class="ef-al-filter-select">
                    <option value="">All modules</option>
                    @foreach($modules as $mod)
                        <option value="{{ $mod }}"
                                {{ $filters['module'] === $mod ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $mod)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ef-al-filter-group">
                <label class="ef-al-filter-label">Action</label>
                <select name="action" class="ef-al-filter-select">
                    <option value="">All actions</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}"
                                {{ $filters['action'] === $act ? 'selected' : '' }}>
                            {{ ucfirst($act) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ef-al-filter-group">
                <label class="ef-al-filter-label">User</label>
                <select name="user_id" class="ef-al-filter-select">
                    <option value="">All users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}"
                                {{ $filters['user_id'] == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ef-al-filter-sep"></div>

            <div class="ef-al-filter-group">
                <label class="ef-al-filter-label">From</label>
                <input type="date" name="from" class="ef-al-filter-input"
                       value="{{ $filters['from'] }}" max="{{ today()->toDateString() }}">
            </div>

            <div class="ef-al-filter-group">
                <label class="ef-al-filter-label">To</label>
                <input type="date" name="to" class="ef-al-filter-input"
                       value="{{ $filters['to'] }}" max="{{ today()->toDateString() }}">
            </div>

            <div class="ef-al-filter-actions">
                @if($hasFilters)
                    <span class="ef-al-filter-active-chip">
                        <i class="bi bi-funnel-fill"></i> Filtered
                    </span>
                    <a href="{{ route('admin.audit-logs.index') }}" class="ef-btn" title="Clear all filters">
                        <i class="bi bi-x"></i> Reset
                    </a>
                @endif
                <button type="submit" class="ef-btn ef-btn-dark">
                    <i class="bi bi-funnel"></i> Apply
                </button>
            </div>

        </form>
    </div>

    {{-- ═══ TIMELINE ══════════════════════════════════════════════════════ --}}
    <div class="ef-al-timeline-wrap">

        @forelse($grouped as $dateStr => $dayLogs)
        @php
            $dateObj   = \Carbon\Carbon::parse($dateStr);
            $dateLabel = $dateObj->isToday()
                       ? 'Today'
                       : ($dateObj->isYesterday() ? 'Yesterday' : $dateObj->format('d M Y'));
        @endphp

        {{-- Date separator --}}
        <div class="ef-al-date-sep">
            <span class="ef-al-date-sep-line"></span>
            <span class="ef-al-date-sep-label">
                {{ $dateLabel }}
                <span class="ef-al-date-sep-count">{{ $dayLogs->count() }}</span>
            </span>
            <span class="ef-al-date-sep-line"></span>
        </div>

        {{-- Events for this date --}}
        @foreach($dayLogs as $log)
        @php
            $tone      = $toneMap[$log->action] ?? 'neutral';
            $modName   = $moduleNames[$log->module] ?? str_replace('_', ' ', $log->module);
            $verb      = $actionVerbs[$log->action] ?? ucfirst($log->action);
            $title     = $verb . ' ' . $modName;
            $timeHour  = $log->created_at->format('h:i');
            $timeAmPm  = $log->created_at->format('A');
            $isFirst   = $loop->first;
            $isLast    = $loop->last;
        @endphp

        <div class="ef-al-event" data-tone="{{ $tone }}">

            {{-- Time --}}
            <div class="ef-al-time">
                <div class="ef-al-time-value">{{ $timeHour }}</div>
                <div class="ef-al-time-ampm">{{ $timeAmPm }}</div>
            </div>

            {{-- Timeline track --}}
            <div class="ef-al-track">
                <div class="ef-al-line-t {{ $isFirst ? '--off' : '' }}"></div>
                <div class="ef-al-dot"></div>
                <div class="ef-al-line-b {{ $isLast ? '--off' : '' }}"></div>
            </div>

            {{-- Body --}}
            <div class="ef-al-body">

                <div class="ef-al-event-title">{{ $title }}</div>

                @if($log->reference_label || $log->reference_id)
                <div class="ef-al-event-ref">
                    @if($log->reference_id)<span style="color:var(--ef-faint);font-size:.72rem">#{{ $log->reference_id }}</span>@endif
                    @if($log->reference_label) {{ $log->reference_label }}@endif
                </div>
                @endif

                <div class="ef-al-event-meta">
                    <span class="ef-al-mobile-time">{{ $timeHour }} {{ $timeAmPm }}</span>
                    @if($log->user)
                        <span><i class="bi bi-person" style="font-size:.7rem;opacity:.5"></i> {{ $log->user->name }}</span>
                        <span class="sep">·</span>
                    @endif
                    @if($log->ip_address)
                        <span>{{ $log->ip_address }}</span>
                        <span class="sep">·</span>
                    @endif
                    <span>{{ $log->created_at->diffForHumans() }}</span>
                </div>

                {{-- Changes diff --}}
                @if($log->old_values || $log->new_values)
                <button class="ef-al-changes-btn"
                        type="button"
                        aria-expanded="false"
                        aria-controls="chg-{{ $log->id }}"
                        onclick="alToggleChanges(this)">
                    <i class="bi bi-chevron-down"></i>
                    View changes
                </button>
                <div class="ef-al-changes" id="chg-{{ $log->id }}" hidden>
                    @if($log->old_values)
                    <div class="ef-al-changes-section">
                        <div class="ef-al-changes-head">Before</div>
                        <div class="ef-al-changes-grid">
                            @foreach($log->old_values as $k => $v)
                            <div class="ef-al-changes-key">{{ $k }}</div>
                            <div class="ef-al-changes-val --old" title="{{ is_array($v) ? json_encode($v) : $v }}">
                                {{ is_array($v) ? json_encode($v) : $v }}
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @if($log->new_values)
                    <div class="ef-al-changes-section">
                        <div class="ef-al-changes-head">After</div>
                        <div class="ef-al-changes-grid">
                            @foreach($log->new_values as $k => $v)
                            <div class="ef-al-changes-key">{{ $k }}</div>
                            <div class="ef-al-changes-val --new" title="{{ is_array($v) ? json_encode($v) : $v }}">
                                {{ is_array($v) ? json_encode($v) : $v }}
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif

            </div>

            {{-- Chips --}}
            <div class="ef-al-chips">
                <x-premium.chip tone="{{ $tone }}">{{ $log->action }}</x-premium.chip>
                <x-premium.chip tone="neutral">{{ $log->module }}</x-premium.chip>
            </div>

        </div>
        @endforeach

        @empty

        <div class="ef-empty-state">
            <div class="ef-empty-orb"><i class="bi bi-shield-check"></i></div>
            <h3 style="color:var(--ef-ink);font-size:1.1rem;font-weight:760;margin:0 0 8px">
                No activity logs found
            </h3>
            <p style="color:var(--ef-muted);font-size:.88rem;margin:0 0 22px;max-width:300px;line-height:1.6">
                Operational events will appear here as actions are performed in the system.
            </p>
            @if($hasFilters)
                <a href="{{ route('admin.audit-logs.index') }}" class="ef-btn ef-btn-dark">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </a>
            @endif
        </div>

        @endforelse
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
        <div class="ef-al-pagination">{{ $logs->links() }}</div>
    @endif

</div>

{{-- ═══ MOBILE STICKY BAR ════════════════════════════════════════════════ --}}
<div class="ef-al-mobile-bar">
    <button class="ef-btn" id="alMobileFilterBtn" style="justify-content:center">
        <i class="bi bi-funnel{{ $hasFilters ? '-fill' : '' }}"></i>
        Filters
        @if($hasFilters)
            <span style="background:var(--ef-bluegray);border-radius:50%;width:7px;height:7px;display:inline-block;margin-left:2px"></span>
        @endif
    </button>
    <a href="{{ $exportUrl }}" class="ef-btn" style="justify-content:center">
        <i class="bi bi-download"></i> Export
    </a>
    <button class="ef-btn ef-btn-icon" onclick="window.print()">
        <i class="bi bi-printer"></i>
    </button>
</div>

@push('scripts')
<script>
(function () {
    // ── Changes panel toggle ──────────────────────────────────────────────
    window.alToggleChanges = function (btn) {
        const panelId = btn.getAttribute('aria-controls');
        const panel   = document.getElementById(panelId);
        if (!panel) return;

        const open = btn.getAttribute('aria-expanded') === 'true';
        if (open) {
            panel.hidden = true;
            btn.setAttribute('aria-expanded', 'false');
            btn.querySelector('.bi').className = 'bi bi-chevron-down';
        } else {
            panel.hidden = false;
            btn.setAttribute('aria-expanded', 'true');
            btn.querySelector('.bi').className = 'bi bi-chevron-up';
        }
    };

    // ── Filter bar toggle (desktop button + mobile) ───────────────────────
    function toggleFilter() {
        const bar = document.getElementById('alFilterBar');
        if (!bar) return;
        bar.classList.toggle('--open');
        const open = bar.classList.contains('--open');
        const icon = document.querySelector('#alFilterToggle .bi');
        if (icon) icon.className = 'bi bi-funnel' + (open ? '-fill' : '');
    }

    const desktopBtn = document.getElementById('alFilterToggle');
    const mobileBtn  = document.getElementById('alMobileFilterBtn');
    if (desktopBtn) desktopBtn.addEventListener('click', toggleFilter);
    if (mobileBtn)  mobileBtn.addEventListener('click', function () {
        toggleFilter();
        const bar = document.getElementById('alFilterBar');
        if (bar && bar.classList.contains('--open')) {
            bar.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
})();
</script>
@endpush

</x-admin-layout>
