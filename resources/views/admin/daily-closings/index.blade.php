<x-admin-layout title="Daily Closings">

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   Daily Closings — End-of-Day Financial Operations Center
   ═══════════════════════════════════════════════════════ */

.ef-dc-shell {
    max-width: 1480px;
    margin: 0 auto;
    padding-bottom: 88px;
}

/* ── Hero ─────────────────────────────────────────────── */
.ef-dc-hero {
    align-items: stretch;
    background: linear-gradient(135deg, rgba(255,253,250,.98), rgba(249,247,242,.94));
    border: 1px solid var(--ef-border);
    border-radius: 20px;
    box-shadow: var(--ef-shadow);
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(300px, 420px);
    margin-bottom: 18px;
    overflow: hidden;
}

.ef-dc-hero-main { padding: 32px 36px; }

.ef-dc-hero-side {
    background: rgba(20,20,18,.022);
    border-left: 1px solid var(--ef-border);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 32px 36px;
}

.ef-dc-title {
    color: var(--ef-ink);
    font-size: clamp(2.4rem, 4vw, 3.8rem);
    font-weight: 780;
    letter-spacing: -.01em;
    line-height: .96;
    margin: 8px 0 16px;
}

.ef-dc-subtitle {
    color: var(--ef-muted);
    display: flex;
    flex-wrap: wrap;
    font-size: .92rem;
    gap: 6px 16px;
    margin: 0;
}

.ef-dc-subtitle i { font-size: .75rem; opacity: .6; }

.ef-dc-today-status {
    align-items: center;
    border-radius: 14px;
    display: flex;
    gap: 12px;
    margin-bottom: 22px;
    padding: 14px 16px;
}

.ef-dc-today-status.--closed {
    background: rgba(61,115,88,.08);
    border: 1px solid rgba(61,115,88,.2);
}

.ef-dc-today-status.--open {
    background: rgba(169,131,56,.07);
    border: 1px solid rgba(169,131,56,.18);
}

.ef-dc-today-icon {
    align-items: center;
    border-radius: 50%;
    display: flex;
    flex-shrink: 0;
    font-size: 1.1rem;
    height: 36px;
    justify-content: center;
    width: 36px;
}

.ef-dc-today-status.--closed .ef-dc-today-icon {
    background: rgba(61,115,88,.14);
    color: var(--ef-emerald);
}

.ef-dc-today-status.--open .ef-dc-today-icon {
    background: rgba(169,131,56,.13);
    color: var(--ef-gold);
}

.ef-dc-today-label {
    color: var(--ef-faint);
    font-size: .62rem;
    font-weight: 760;
    letter-spacing: .12em;
    text-transform: uppercase;
}

.ef-dc-today-value {
    color: var(--ef-ink);
    font-size: .96rem;
    font-weight: 760;
    margin-top: 2px;
}

.ef-dc-today-status.--open .ef-dc-today-value { color: var(--ef-gold); }
.ef-dc-today-status.--closed .ef-dc-today-value { color: var(--ef-emerald); }

.ef-dc-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-end;
}

/* ── Metrics Strip ────────────────────────────────────── */
.ef-dc-metrics {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    margin-bottom: 18px;
}

.ef-dc-metric {
    background: rgba(255,253,250,.92);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    min-height: 112px;
    padding: 20px 22px;
    transition: border-color .18s var(--ef-ease), box-shadow .18s var(--ef-ease), transform .18s var(--ef-ease);
}

.ef-dc-metric:hover {
    border-color: var(--ef-border-strong);
    box-shadow: var(--ef-shadow-hover);
    transform: translateY(-1px);
}

.ef-dc-metric-icon {
    color: var(--ef-faint);
    font-size: .88rem;
    margin-bottom: 12px;
}

.ef-dc-metric-label {
    color: var(--ef-faint);
    font-size: .63rem;
    font-weight: 760;
    letter-spacing: .13em;
    text-transform: uppercase;
}

.ef-dc-metric-value {
    color: var(--ef-ink);
    font-size: 1.32rem;
    font-variant-numeric: tabular-nums;
    font-weight: 780;
    line-height: 1;
    margin-top: 10px;
}

.ef-dc-metric-note {
    color: var(--ef-muted);
    font-size: .73rem;
    line-height: 1.45;
    margin-top: 7px;
}

.ef-dc-metric.--draft .ef-dc-metric-value    { color: var(--ef-bluegray); }
.ef-dc-metric.--verified .ef-dc-metric-value { color: var(--ef-emerald); }
.ef-dc-metric.--variance-pos .ef-dc-metric-value { color: var(--ef-gold); }
.ef-dc-metric.--variance-neg .ef-dc-metric-value { color: var(--ef-danger); }
.ef-dc-metric.--balanced .ef-dc-metric-value { color: var(--ef-emerald); }

/* ── Filter Bar ───────────────────────────────────────── */
.ef-dc-filter-bar {
    background: rgba(255,253,250,.94);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    margin-bottom: 16px;
}

.ef-dc-filter-inner {
    align-items: flex-end;
    display: flex;
    flex-wrap: wrap;
    gap: 10px 16px;
    padding: 16px 22px;
}

.ef-dc-filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.ef-dc-filter-label {
    color: var(--ef-faint);
    font-size: .6rem;
    font-weight: 760;
    letter-spacing: .13em;
    text-transform: uppercase;
}

.ef-dc-filter-input,
.ef-dc-filter-select {
    background: rgba(251,250,247,.96);
    border: 1px solid var(--ef-border-strong);
    border-radius: 10px;
    color: var(--ef-ink-2);
    font-size: .84rem;
    font-weight: 540;
    height: 38px;
    padding: 0 11px;
    transition: background .16s var(--ef-ease), border-color .16s var(--ef-ease), box-shadow .16s var(--ef-ease);
}

.ef-dc-filter-input:focus,
.ef-dc-filter-select:focus {
    background: #fff;
    border-color: rgba(20,20,18,.48);
    box-shadow: 0 0 0 4px rgba(20,20,18,.052);
    outline: 0;
}

.ef-dc-filter-sep {
    background: var(--ef-border);
    height: 30px;
    width: 1px;
    flex-shrink: 0;
}

.ef-dc-filter-range-label {
    color: var(--ef-faint);
    font-size: .78rem;
    padding-bottom: 8px;
}

.ef-dc-filter-actions {
    align-items: center;
    display: flex;
    gap: 8px;
    margin-left: auto;
}

.ef-dc-filter-active-chip {
    align-items: center;
    background: rgba(169,131,56,.09);
    border: 1px solid rgba(169,131,56,.18);
    border-radius: 999px;
    color: var(--ef-gold);
    display: flex;
    font-size: .66rem;
    font-weight: 760;
    gap: 5px;
    letter-spacing: .06em;
    padding: 4px 10px;
    text-transform: uppercase;
}

/* ── Closings List ────────────────────────────────────── */
.ef-dc-list-wrap {
    background: rgba(255,253,250,.94);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    overflow: hidden;
}

.ef-dc-list-header {
    align-items: center;
    border-bottom: 1px solid rgba(20,20,18,.065);
    display: flex;
    gap: 14px;
    justify-content: space-between;
    padding: 14px 24px;
}

.ef-dc-list-title {
    color: var(--ef-faint);
    font-size: .63rem;
    font-weight: 760;
    letter-spacing: .14em;
    text-transform: uppercase;
}

.ef-dc-list-count {
    color: var(--ef-muted);
    font-size: .76rem;
}

.ef-dc-row {
    align-items: center;
    border-bottom: 1px solid rgba(20,20,18,.06);
    display: grid;
    gap: 18px;
    grid-template-columns: 68px minmax(0, 1fr) 120px auto;
    padding: 20px 24px;
    transition: background .15s var(--ef-ease);
}

.ef-dc-row:last-child { border-bottom: 0; }

.ef-dc-row:hover { background: rgba(20,20,18,.016); }

/* Date column */
.ef-dc-date { line-height: 1; }

.ef-dc-date-day {
    color: var(--ef-ink);
    font-size: 2rem;
    font-variant-numeric: tabular-nums;
    font-weight: 780;
    line-height: .92;
}

.ef-dc-date-mon {
    color: var(--ef-muted);
    font-size: .7rem;
    font-weight: 680;
    letter-spacing: .03em;
    margin-top: 5px;
    text-transform: uppercase;
}

.ef-dc-date-today {
    background: rgba(169,131,56,.11);
    border: 1px solid rgba(169,131,56,.2);
    border-radius: 6px;
    color: var(--ef-gold);
    display: inline-block;
    font-size: .58rem;
    font-weight: 780;
    letter-spacing: .08em;
    margin-top: 6px;
    padding: 2px 6px;
    text-transform: uppercase;
}

/* Body column */
.ef-dc-row-amounts {
    display: flex;
    gap: 22px;
    margin-bottom: 7px;
}

.ef-dc-amount-group {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.ef-dc-amount-label {
    color: var(--ef-faint);
    font-size: .6rem;
    font-weight: 760;
    letter-spacing: .11em;
    text-transform: uppercase;
}

.ef-dc-amount-value {
    color: var(--ef-ink);
    font-size: .98rem;
    font-variant-numeric: tabular-nums;
    font-weight: 760;
}

.ef-dc-amount-value.--payment {
    color: var(--ef-muted);
    font-weight: 580;
}

.ef-dc-row-meta {
    color: var(--ef-faint);
    font-size: .74rem;
    line-height: 1.5;
}

/* Variance column */
.ef-dc-variance {
    text-align: right;
}

.ef-dc-variance-label {
    color: var(--ef-faint);
    font-size: .6rem;
    font-weight: 760;
    letter-spacing: .1em;
    margin-bottom: 5px;
    text-transform: uppercase;
}

.ef-dc-variance-value {
    color: var(--ef-muted);
    font-size: .9rem;
    font-variant-numeric: tabular-nums;
    font-weight: 680;
}

.ef-dc-variance.--pos .ef-dc-variance-value { color: var(--ef-gold); }
.ef-dc-variance.--neg .ef-dc-variance-value { color: var(--ef-danger); }
.ef-dc-variance.--zero .ef-dc-variance-value { color: var(--ef-emerald); }

/* End column */
.ef-dc-row-end {
    align-items: center;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.ef-dc-row-actions {
    display: flex;
    gap: 5px;
}

/* Pagination */
.ef-dc-pagination {
    display: flex;
    justify-content: center;
    margin-top: 16px;
}

.ef-dc-pagination .pagination { gap: 4px; margin: 0; }

.ef-dc-pagination .page-link {
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

.ef-dc-pagination .page-link:hover {
    background: var(--ef-surface-2);
    border-color: var(--ef-border-strong);
    color: var(--ef-ink);
}

.ef-dc-pagination .active .page-link {
    background: var(--ef-ink);
    border-color: var(--ef-ink);
    color: #fffdfa;
}

.ef-dc-pagination .disabled .page-link { opacity: .38; }

/* Modal */
.ef-dc-modal .modal-content {
    background: #fffdfa;
    border: 1px solid var(--ef-border);
    border-radius: 18px;
    box-shadow: 0 28px 80px rgba(24,22,18,.2);
}

.ef-dc-modal .modal-header,
.ef-dc-modal .modal-footer {
    border-color: var(--ef-border);
    padding: 20px 24px;
}

.ef-dc-modal .modal-body { padding: 24px; }

.ef-dc-modal .modal-title {
    color: var(--ef-ink);
    font-size: .96rem;
    font-weight: 760;
}

/* Mobile sticky bar */
.ef-dc-mobile-bar {
    backdrop-filter: blur(18px) saturate(160%);
    background: rgba(255,253,250,.94);
    border-top: 1px solid var(--ef-border);
    bottom: 0;
    display: none;
    gap: 8px;
    grid-template-columns: 1fr 1fr auto;
    left: 0;
    padding: 10px 14px calc(10px + env(safe-area-inset-bottom));
    position: fixed;
    right: 0;
    z-index: 1040;
}

/* ── Responsive ───────────────────────────────────────── */
@media (max-width: 1199.98px) {
    .ef-dc-hero { grid-template-columns: 1fr; }
    .ef-dc-hero-side {
        border-left: 0;
        border-top: 1px solid var(--ef-border);
    }
    .ef-dc-actions { justify-content: flex-start; }
    .ef-dc-metrics { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}

@media (max-width: 767.98px) {
    .ef-dc-shell { padding-bottom: 86px; }

    .ef-dc-hero-main,
    .ef-dc-hero-side { padding: 24px; }

    .ef-dc-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }

    .ef-dc-filter-bar { display: none; }
    .ef-dc-filter-bar.--mobile-open { display: block; }
    .ef-dc-filter-inner {
        flex-direction: column;
        align-items: stretch;
    }
    .ef-dc-filter-sep { display: none; }
    .ef-dc-filter-input,
    .ef-dc-filter-select { width: 100%; }
    .ef-dc-filter-actions { margin-left: 0; }

    .ef-dc-row {
        grid-template-columns: 56px minmax(0, 1fr) auto;
        gap: 12px;
        padding: 16px 18px;
    }

    .ef-dc-variance { display: none; }

    .ef-dc-row-end {
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .ef-dc-mobile-bar { display: grid; }
}

@media print {
    .ef-dc-filter-bar,
    .ef-dc-actions,
    .ef-dc-mobile-bar,
    .ef-dc-row-actions { display: none !important; }
}
</style>
@endpush

<div class="ef-dc-shell">

    {{-- ═══ HERO ════════════════════════════════════════════════════════════ --}}
    <header class="ef-dc-hero">

        <div class="ef-dc-hero-main">
            <p class="ef-eyebrow">End-of-Day Financial Operations</p>
            <h1 class="ef-dc-title">Daily Closings</h1>
            <p class="ef-dc-subtitle">
                <span><i class="bi bi-calendar3"></i> {{ now()->format('l, d F Y') }}</span>
                <span><i class="bi bi-building"></i> Operational reconciliation center</span>
            </p>
        </div>

        <div class="ef-dc-hero-side">

            <div class="ef-dc-today-status {{ $todayClosed ? '--closed' : '--open' }}">
                <div class="ef-dc-today-icon">
                    <i class="bi {{ $todayClosed ? 'bi-check-circle-fill' : 'bi-clock' }}"></i>
                </div>
                <div>
                    <div class="ef-dc-today-label">Today's status</div>
                    <div class="ef-dc-today-value">
                        {{ $todayClosed ? 'Day closed' : 'Awaiting closure' }}
                    </div>
                </div>
            </div>

            <div class="ef-dc-actions">
                @if(!$todayClosed)
                    <a href="{{ route('admin.daily-closings.create') }}" class="ef-btn ef-btn-dark">
                        <i class="bi bi-calendar-check"></i> Close Today
                    </a>
                @endif
                <button class="ef-btn" data-bs-toggle="modal" data-bs-target="#pastDateModal">
                    <i class="bi bi-calendar-plus"></i> Past Date
                </button>
                <button class="ef-btn" onclick="window.print()" title="Print Summary">
                    <i class="bi bi-printer"></i>
                </button>
            </div>

        </div>
    </header>

    {{-- ═══ METRICS STRIP ════════════════════════════════════════════════════ --}}
    @php
        $variance      = $summary['variance'];
        $varianceTone  = $variance > 0.005  ? '--variance-pos'
                       : ($variance < -0.005 ? '--variance-neg' : '--balanced');
        $varianceSign  = $variance < -0.005 ? '−' : ($variance > 0.005 ? '' : '');
        $varianceNote  = $variance > 0.005  ? 'Outstanding balance'
                       : ($variance < -0.005 ? 'Overpaid' : 'Balanced');
    @endphp

    <div class="ef-dc-metrics">

        <div class="ef-dc-metric">
            <div class="ef-dc-metric-icon"><i class="bi bi-arrow-up-circle"></i></div>
            <div class="ef-dc-metric-label">Total Expenses</div>
            <div class="ef-dc-metric-value">₹{{ number_format($summary['expense_total'], 2) }}</div>
            <div class="ef-dc-metric-note">{{ $summary['total_count'] }} closing{{ $summary['total_count'] != 1 ? 's' : '' }} in view</div>
        </div>

        <div class="ef-dc-metric">
            <div class="ef-dc-metric-icon"><i class="bi bi-arrow-down-circle"></i></div>
            <div class="ef-dc-metric-label">Total Payments</div>
            <div class="ef-dc-metric-value">₹{{ number_format($summary['payment_total'], 2) }}</div>
            <div class="ef-dc-metric-note">Disbursed this period</div>
        </div>

        <div class="ef-dc-metric --draft">
            <div class="ef-dc-metric-icon"><i class="bi bi-pencil-square"></i></div>
            <div class="ef-dc-metric-label">Draft Closings</div>
            <div class="ef-dc-metric-value">{{ $summary['draft_count'] }}</div>
            <div class="ef-dc-metric-note">Pending review</div>
        </div>

        <div class="ef-dc-metric --verified">
            <div class="ef-dc-metric-icon"><i class="bi bi-shield-check"></i></div>
            <div class="ef-dc-metric-label">Verified</div>
            <div class="ef-dc-metric-value">{{ $summary['verified_count'] }}</div>
            <div class="ef-dc-metric-note">{{ $summary['closed_count'] }} finalized</div>
        </div>

        <div class="ef-dc-metric {{ $varianceTone }}">
            <div class="ef-dc-metric-icon"><i class="bi bi-plusminus"></i></div>
            <div class="ef-dc-metric-label">Net Variance</div>
            <div class="ef-dc-metric-value">₹{{ number_format(abs($variance), 2) }}</div>
            <div class="ef-dc-metric-note">{{ $varianceNote }}</div>
        </div>

    </div>

    {{-- ═══ FILTER BAR ═══════════════════════════════════════════════════════ --}}
    <div class="ef-dc-filter-bar" id="filterBar">
        <form method="GET" class="ef-dc-filter-inner">

            <div class="ef-dc-filter-group">
                <label class="ef-dc-filter-label">From</label>
                <input type="date" name="from" class="ef-dc-filter-input"
                       value="{{ request('from') }}" max="{{ today()->toDateString() }}">
            </div>

            <div class="ef-dc-filter-range-label">—</div>

            <div class="ef-dc-filter-group">
                <label class="ef-dc-filter-label">To</label>
                <input type="date" name="to" class="ef-dc-filter-input"
                       value="{{ request('to') }}" max="{{ today()->toDateString() }}">
            </div>

            <div class="ef-dc-filter-sep"></div>

            <div class="ef-dc-filter-group">
                <label class="ef-dc-filter-label">Status</label>
                <select name="status" class="ef-dc-filter-select">
                    <option value="">All statuses</option>
                    <option value="draft"    {{ request('status') === 'draft'    ? 'selected' : '' }}>Draft</option>
                    <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="closed"   {{ request('status') === 'closed'   ? 'selected' : '' }}>Closed</option>
                </select>
            </div>

            <div class="ef-dc-filter-group">
                <label class="ef-dc-filter-label">Created By</label>
                <select name="created_by" class="ef-dc-filter-select">
                    <option value="">All users</option>
                    @foreach($adminUsers as $u)
                        <option value="{{ $u->id }}" {{ request('created_by') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ef-dc-filter-actions">
                @if(request()->hasAny(['from','to','status','created_by']))
                    <span class="ef-dc-filter-active-chip">
                        <i class="bi bi-funnel-fill"></i> Filtered
                    </span>
                    <a href="{{ route('admin.daily-closings.index') }}" class="ef-btn" title="Clear filters">
                        <i class="bi bi-x"></i> Reset
                    </a>
                @endif
                <button type="submit" class="ef-btn ef-btn-dark">
                    <i class="bi bi-funnel"></i> Apply
                </button>
            </div>

        </form>
    </div>

    {{-- ═══ CLOSINGS LIST ═════════════════════════════════════════════════════ --}}
    <div class="ef-dc-list-wrap">

        <div class="ef-dc-list-header">
            <span class="ef-dc-list-title">Reconciliation Entries</span>
            <span class="ef-dc-list-count">
                {{ $closings->total() }} record{{ $closings->total() != 1 ? 's' : '' }}
                @if($closings->total() > 0)
                    · showing {{ $closings->firstItem() }}–{{ $closings->lastItem() }}
                @endif
            </span>
        </div>

        @forelse($closings as $closing)
        @php
            $tones     = ['draft' => 'neutral', 'verified' => 'emerald', 'closed' => 'bluegray'];
            $tone      = $tones[$closing->status] ?? 'neutral';
            $rowVar    = (float)$closing->expense_total - (float)$closing->payment_total;
            $varClass  = $rowVar > 0.005 ? '--pos' : ($rowVar < -0.005 ? '--neg' : '--zero');
        @endphp

        <div class="ef-dc-row">

            {{-- Date block --}}
            <div class="ef-dc-date">
                <div class="ef-dc-date-day">{{ $closing->date->format('d') }}</div>
                <div class="ef-dc-date-mon">{{ $closing->date->format('M Y') }}</div>
                @if($closing->date->isToday())
                    <span class="ef-dc-date-today">Today</span>
                @endif
            </div>

            {{-- Financial body --}}
            <div>
                <div class="ef-dc-row-amounts">
                    <div class="ef-dc-amount-group">
                        <span class="ef-dc-amount-label">Expenses</span>
                        <span class="ef-dc-amount-value">₹{{ number_format($closing->expense_total, 2) }}</span>
                    </div>
                    <div class="ef-dc-amount-group">
                        <span class="ef-dc-amount-label">Payments</span>
                        <span class="ef-dc-amount-value --payment">₹{{ number_format($closing->payment_total, 2) }}</span>
                    </div>
                </div>
                <div class="ef-dc-row-meta">
                    {{ $closing->expense_count }} expense{{ $closing->expense_count != 1 ? 's' : '' }}
                    &nbsp;·&nbsp;
                    @if($closing->updater)
                        Updated {{ $closing->updated_at->format('d M, h:i A') }}&nbsp;by&nbsp;{{ $closing->updater->name }}
                    @else
                        Created {{ $closing->created_at->format('d M, h:i A') }}&nbsp;by&nbsp;{{ $closing->creator->name }}
                    @endif
                </div>
            </div>

            {{-- Variance --}}
            <div class="ef-dc-variance {{ $varClass }}">
                <div class="ef-dc-variance-label">Variance</div>
                <div class="ef-dc-variance-value">
                    @if($rowVar < -0.005) −@endif₹{{ number_format(abs($rowVar), 2) }}
                </div>
            </div>

            {{-- Status + Actions --}}
            <div class="ef-dc-row-end">
                <x-premium.chip :tone="$tone">{{ ucfirst($closing->status) }}</x-premium.chip>

                <div class="ef-dc-row-actions">
                    <a href="{{ route('admin.daily-closings.show', $closing) }}"
                       class="ef-btn ef-btn-icon" title="View">
                        <i class="bi bi-eye"></i>
                    </a>

                    @if($closing->canEdit())
                        <a href="{{ route('admin.daily-closings.edit', $closing) }}"
                           class="ef-btn ef-btn-icon" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>

                        <div class="dropdown">
                            <button class="ef-btn ef-btn-icon" data-bs-toggle="dropdown"
                                    aria-expanded="false" title="More actions">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm"
                                style="border-color:var(--ef-border);border-radius:12px;min-width:170px">
                                <li>
                                    <form method="POST"
                                          action="{{ route('admin.daily-closings.recalculate', $closing) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="dropdown-item"
                                                style="font-size:.84rem">
                                            <i class="bi bi-arrow-repeat me-2 opacity-60"></i> Recalculate
                                        </button>
                                    </form>
                                </li>
                                @if($closing->canDelete())
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button class="dropdown-item text-danger"
                                                style="font-size:.84rem"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal{{ $closing->id }}">
                                            <i class="bi bi-trash me-2 opacity-70"></i> Delete
                                        </button>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        @empty

        <div class="ef-empty-state">
            <div class="ef-empty-orb"><i class="bi bi-calendar-check"></i></div>
            <h3 style="color:var(--ef-ink);font-size:1.1rem;font-weight:760;margin:0 0 8px">
                No daily closings found
            </h3>
            <p style="color:var(--ef-muted);font-size:.88rem;margin:0 0 22px;max-width:320px;line-height:1.6">
                Financial reconciliation entries will appear here once the first closing is recorded.
            </p>
            @if(!$todayClosed)
                <a href="{{ route('admin.daily-closings.create') }}" class="ef-btn ef-btn-dark">
                    <i class="bi bi-calendar-check"></i> Close Today
                </a>
            @endif
        </div>

        @endforelse
    </div>

    {{-- Pagination --}}
    @if($closings->hasPages())
        <div class="ef-dc-pagination">{{ $closings->links() }}</div>
    @endif

</div>

{{-- ═══ MOBILE STICKY BAR ════════════════════════════════════════════════ --}}
<div class="ef-dc-mobile-bar">
    @if(!$todayClosed)
        <a href="{{ route('admin.daily-closings.create') }}" class="ef-btn ef-btn-dark" style="justify-content:center">
            <i class="bi bi-calendar-check"></i> Close Today
        </a>
    @else
        <div></div>
    @endif
    <button class="ef-btn" data-bs-toggle="modal" data-bs-target="#pastDateModal"
            style="justify-content:center">
        <i class="bi bi-calendar-plus"></i> Past Date
    </button>
    <button class="ef-btn ef-btn-icon" id="mobileFilterBtn" title="Filter">
        <i class="bi bi-funnel"></i>
        @if(request()->hasAny(['from','to','status','created_by']))
            <span style="position:absolute;top:6px;right:6px;width:7px;height:7px;border-radius:50%;background:var(--ef-gold);"></span>
        @endif
    </button>
</div>

{{-- ═══ DELETE MODALS ══════════════════════════════════════════════════════ --}}
@foreach($closings as $closing)
    @if($closing->canDelete())
    <div class="modal fade ef-dc-modal" id="deleteModal{{ $closing->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h6 class="modal-title">
                        <i class="bi bi-trash text-danger me-2"></i> Delete Closing
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="rounded-3 p-3 mb-3"
                         style="background:rgba(141,74,60,.06);border:1px solid rgba(141,74,60,.14)">
                        <p class="mb-0" style="color:var(--ef-danger);font-size:.82rem;font-weight:680">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            This cannot be undone.
                        </p>
                    </div>
                    <p style="color:var(--ef-ink-2);font-size:.88rem;margin:0">
                        Delete the closing for
                        <strong>{{ $closing->date->format('d M Y') }}</strong>?
                    </p>
                </div>
                <div class="modal-footer border-0 py-2 gap-2">
                    <button type="button" class="ef-btn" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST"
                          action="{{ route('admin.daily-closings.destroy', $closing) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="ef-btn"
                                style="background:var(--ef-danger);border-color:var(--ef-danger);color:#fff"
                                data-loading-text="Deleting…">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

{{-- ═══ PAST DATE MODAL ══════════════════════════════════════════════════ --}}
<div class="modal fade ef-dc-modal" id="pastDateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title">
                    <i class="bi bi-calendar-plus me-2" style="color:var(--ef-bluegray)"></i>
                    Close Past Date
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="color:var(--ef-muted);font-size:.86rem;margin-bottom:18px;line-height:1.6">
                    Select a past date to create its daily closing record.
                </p>
                <div>
                    <label class="ef-dc-filter-label d-block mb-2">Select Date</label>
                    <input type="date" id="pastDateInput" class="ef-dc-filter-input w-100"
                           style="height:44px;font-size:.9rem"
                           max="{{ today()->subDay()->toDateString() }}" required>
                    <div style="color:var(--ef-faint);font-size:.74rem;margin-top:7px">
                        Future dates are not allowed.
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 gap-2">
                <button type="button" class="ef-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="ef-btn ef-btn-dark" id="pastDateProceed">
                    <i class="bi bi-arrow-right"></i> Proceed
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    // Past date modal
    const input   = document.getElementById('pastDateInput');
    const proceed = document.getElementById('pastDateProceed');

    proceed.addEventListener('click', function () {
        if (!input.value) {
            input.style.borderColor = 'var(--ef-danger)';
            input.focus();
            return;
        }
        window.location.href =
            "{{ route('admin.daily-closings.create') }}?date=" + encodeURIComponent(input.value);
    });

    input.addEventListener('input', function () {
        input.style.borderColor = '';
    });

    // Mobile filter toggle
    const filterBar     = document.getElementById('filterBar');
    const mobileFilterBtn = document.getElementById('mobileFilterBtn');

    if (mobileFilterBtn && filterBar) {
        mobileFilterBtn.addEventListener('click', function () {
            filterBar.classList.toggle('--mobile-open');
            const open = filterBar.classList.contains('--mobile-open');
            this.querySelector('i').className = open ? 'bi bi-x-lg' : 'bi bi-funnel';
        });
    }
})();
</script>
@endpush

</x-admin-layout>
