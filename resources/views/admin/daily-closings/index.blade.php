<x-admin-layout title="Daily Closings">

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   Daily Closing — compact financial reconciliation screen
   ═══════════════════════════════════════════════════════ */

.ef-dc-shell { max-width: 1360px; margin: 0 auto; padding-bottom: 88px; }

/* ── Header ───────────────────────────────────────────── */
.ef-dc-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}
.ef-dc-eyebrow { font-size: .68rem; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; color: var(--ef-gold); margin-bottom: .2rem; }
.ef-dc-title { font-size: 1.5rem; font-weight: 800; color: var(--ef-ink); letter-spacing: -.01em; line-height: 1.15; }
.ef-dc-sub { color: var(--ef-muted); font-size: .82rem; margin-top: .2rem; }

.ef-dc-header-right { display: flex; flex-direction: column; align-items: flex-end; gap: .5rem; flex-shrink: 0; }
.ef-dc-status-pill {
    display: inline-flex; align-items: center; gap: .45rem;
    border-radius: 999px; padding: .3rem .75rem .3rem .5rem;
    font-size: .78rem; font-weight: 700;
}
.ef-dc-status-pill.--open   { background: rgba(184,137,62,.1);  color: var(--ef-gold); border: 1px solid rgba(184,137,62,.22); }
.ef-dc-status-pill.--closed { background: rgba(61,115,88,.1);   color: var(--ef-emerald); border: 1px solid rgba(61,115,88,.22); }
.ef-dc-status-pill .dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; flex-shrink: 0; }

.ef-dc-actions { display: flex; gap: .5rem; flex-wrap: wrap; justify-content: flex-end; }
@media (max-width: 575.98px) {
    .ef-dc-header { flex-direction: column; }
    .ef-dc-header-right { align-items: stretch; width: 100%; }
    .ef-dc-actions { display: none; } /* mobile uses the sticky bottom bar instead */
}

/* ── Reconciliation strip: Expenses / Payments / Variance ─ */
.ef-dc-strip {
    display: grid;
    grid-template-columns: 1fr 1fr 1.2fr;
    gap: .65rem;
    margin-bottom: .85rem;
}
@media (max-width: 575.98px) { .ef-dc-strip { grid-template-columns: 1fr 1fr; } }

.ef-dc-figure {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius-sm);
    padding: .8rem .95rem;
}
.ef-dc-figure-label { font-size: .66rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; color: var(--ef-faint); }
.ef-dc-figure-value { font-size: 1.25rem; font-weight: 800; color: var(--ef-ink); letter-spacing: -.01em; margin-top: .25rem; font-variant-numeric: tabular-nums; }
.ef-dc-figure-note { font-size: .72rem; color: var(--ef-muted); margin-top: .2rem; }

.ef-dc-variance-card {
    display: flex; align-items: center; justify-content: space-between; gap: .5rem;
}
.ef-dc-variance-card .ef-dc-figure-value { font-size: 1.35rem; }
@media (max-width: 575.98px) {
    .ef-dc-variance-card { grid-column: 1 / -1; }
}
.ef-dc-variance-card.--pos     { border-color: rgba(184,137,62,.3);  background: rgba(184,137,62,.05); }
.ef-dc-variance-card.--pos     .ef-dc-figure-value { color: var(--ef-gold); }
.ef-dc-variance-card.--neg     { border-color: rgba(200,75,68,.3);   background: rgba(200,75,68,.04); }
.ef-dc-variance-card.--neg     .ef-dc-figure-value { color: var(--ef-danger); }
.ef-dc-variance-card.--zero    { border-color: rgba(61,115,88,.3);   background: rgba(61,115,88,.05); }
.ef-dc-variance-card.--zero    .ef-dc-figure-value { color: var(--ef-emerald); }
.ef-dc-variance-icon { font-size: 1.3rem; opacity: .5; flex-shrink: 0; }

/* ── Attention / reconciled banner ────────────────────── */
.ef-dc-attention {
    display: flex; align-items: center; justify-content: space-between; gap: .85rem; flex-wrap: wrap;
    border-radius: var(--ef-radius-sm);
    padding: .75rem .95rem;
    margin-bottom: .85rem;
}
.ef-dc-attention.--draft {
    background: rgba(184,137,62,.06); border: 1px solid rgba(184,137,62,.22);
}
.ef-dc-attention.--reconciled {
    background: rgba(61,115,88,.06); border: 1px solid rgba(61,115,88,.22);
}
.ef-dc-attention-text { display: flex; align-items: center; gap: .6rem; min-width: 0; }
.ef-dc-attention-icon {
    width: 32px; height: 32px; border-radius: 8px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: .95rem;
}
.ef-dc-attention.--draft .ef-dc-attention-icon { background: rgba(184,137,62,.14); color: var(--ef-gold); }
.ef-dc-attention.--reconciled .ef-dc-attention-icon { background: rgba(61,115,88,.14); color: var(--ef-emerald); }
.ef-dc-attention-title { font-size: .85rem; font-weight: 700; color: var(--ef-ink); }
.ef-dc-attention-sub { font-size: .76rem; color: var(--ef-muted); margin-top: .05rem; }
.ef-dc-attention .ef-btn { flex-shrink: 0; }

/* ── Filter bar ───────────────────────────────────────── */
.ef-dc-filter-bar {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius-sm);
    margin-bottom: .85rem;
}
.ef-dc-filter-inner { align-items: flex-end; display: flex; flex-wrap: wrap; gap: .5rem .85rem; padding: .7rem .85rem; }
.ef-dc-filter-group { display: flex; flex-direction: column; gap: .25rem; }
.ef-dc-filter-label { color: var(--ef-faint); font-size: .64rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; }
.ef-dc-filter-input, .ef-dc-filter-select {
    background: var(--ef-surface-2); border: 1px solid var(--ef-border-strong); border-radius: 8px;
    color: var(--ef-ink-2); font-size: .82rem; height: 38px; padding: 0 .6rem;
}
.ef-dc-filter-input:focus, .ef-dc-filter-select:focus { background: #fff; border-color: var(--ef-gold); box-shadow: 0 0 0 3px rgba(184,137,62,.12); outline: 0; }
.ef-dc-filter-sep { color: var(--ef-faint); font-size: .78rem; padding-bottom: .5rem; }
.ef-dc-filter-actions { align-items: center; display: flex; gap: .5rem; margin-left: auto; }
.ef-dc-filter-chip {
    align-items: center; background: rgba(184,137,62,.09); border: 1px solid rgba(184,137,62,.2);
    border-radius: 999px; color: var(--ef-gold); display: flex; font-size: .68rem; font-weight: 700;
    gap: .3rem; padding: .2rem .6rem;
}
@media (max-width: 767.98px) {
    .ef-dc-filter-bar { display: none; }
    .ef-dc-filter-bar.--mobile-open { display: block; }
    .ef-dc-filter-inner { flex-direction: column; align-items: stretch; }
    .ef-dc-filter-sep { display: none; }
    .ef-dc-filter-input, .ef-dc-filter-select { width: 100%; min-height: 44px; }
    .ef-dc-filter-actions { margin-left: 0; }
}

/* ── Closings list ─────────────────────────────────────── */
.ef-dc-list {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius-sm);
    overflow: hidden;
    margin-bottom: .85rem;
}
.ef-dc-list-head {
    padding: .65rem .95rem; border-bottom: 1px solid var(--ef-border);
    display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
}
.ef-dc-list-title { font-size: .72rem; font-weight: 750; color: var(--ef-ink); letter-spacing: .05em; text-transform: uppercase; }
.ef-dc-list-count { font-size: .78rem; color: var(--ef-muted); }

/* Desktop column header */
.ef-dc-col-head { display: none; }
@media (min-width: 900px) {
    .ef-dc-col-head {
        display: grid; grid-template-columns: 1.3fr 1fr 1fr 1fr .9fr auto;
        gap: .75rem; padding: .5rem .95rem; background: var(--ef-surface-2); border-bottom: 1px solid var(--ef-border);
        font-size: .64rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--ef-muted);
    }
    .ef-dc-col-head .num { text-align: right; }
}

.ef-dc-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: .3rem;
    padding: .7rem .95rem;
    border-bottom: 1px solid var(--ef-border);
    position: relative;
    transition: background .12s;
}
.ef-dc-row:last-child { border-bottom: none; }
.ef-dc-row:hover { background: var(--ef-surface-2); }
@media (min-width: 900px) {
    .ef-dc-row { grid-template-columns: 1.3fr 1fr 1fr 1fr .9fr auto; align-items: center; gap: .75rem; padding: .6rem .95rem; }
}

.ef-dc-row-date { display: flex; align-items: center; gap: .5rem; min-width: 0; }
.ef-dc-row-date-label { font-size: .87rem; font-weight: 700; color: var(--ef-ink); white-space: nowrap; }
.ef-dc-row-today {
    background: rgba(184,137,62,.12); border: 1px solid rgba(184,137,62,.25); border-radius: 5px;
    color: var(--ef-gold); font-size: .6rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase;
    padding: .12rem .4rem; flex-shrink: 0;
}
.ef-dc-row-status-mobile { margin-left: auto; }
@media (min-width: 900px) { .ef-dc-row-status-mobile { display: none; } }

.ef-dc-row-figure { display: flex; flex-direction: row; align-items: baseline; justify-content: space-between; gap: .5rem; }
.ef-dc-row-figure .k { font-size: .72rem; font-weight: 600; color: var(--ef-muted); }
.ef-dc-row-figure .v { font-size: .87rem; font-weight: 700; color: var(--ef-ink); font-variant-numeric: tabular-nums; }
@media (min-width: 900px) {
    .ef-dc-row-figure { flex-direction: column; text-align: right; }
    .ef-dc-row-figure .k { display: none; }
}

.ef-dc-row-variance .v { font-weight: 800; }
.ef-dc-row-variance.--pos .v  { color: var(--ef-gold); }
.ef-dc-row-variance.--neg .v  { color: var(--ef-danger); }
.ef-dc-row-variance.--zero .v { color: var(--ef-emerald); }

.ef-dc-row-status { display: none; }
@media (min-width: 900px) { .ef-dc-row-status { display: flex; align-items: center; } }

.ef-dc-row-meta { font-size: .72rem; color: var(--ef-faint); margin-top: .1rem; }
@media (min-width: 900px) { .ef-dc-row-meta { display: none; } }

.ef-dc-row-actions { display: flex; gap: .4rem; align-items: center; position: relative; z-index: 2; }
.ef-dc-row-actions .ef-btn { min-height: 36px; font-size: .78rem; padding: 0 .65rem; }
@media (max-width: 899.98px) {
    .ef-dc-row-actions { margin-top: .3rem; }
    .ef-dc-row-actions .ef-btn { flex: 1; justify-content: center; min-height: 40px; }
    .ef-dc-row-actions .dropdown { flex-shrink: 0; }
    .ef-dc-row-actions .dropdown .ef-btn { flex: none; width: 44px; }
}

/* Pagination */
.ef-dc-pagination { display: flex; justify-content: center; margin-top: 1rem; }
.ef-dc-pagination .pagination { gap: 4px; margin: 0; }
.ef-dc-pagination .page-link {
    background: var(--ef-surface); border: 1px solid var(--ef-border); border-radius: 8px !important;
    color: var(--ef-ink-2); font-size: .8rem; font-weight: 650; height: 36px; line-height: 36px; min-width: 36px; padding: 0 10px; text-align: center;
}
.ef-dc-pagination .active .page-link { background: var(--ef-ink); border-color: var(--ef-ink); color: #fffdfa; }
.ef-dc-pagination .disabled .page-link { opacity: .38; }

/* Modal (reused) */
.ef-dc-modal .modal-content { background: #fffdfa; border: 1px solid var(--ef-border); border-radius: 16px; }
.ef-dc-modal .modal-header, .ef-dc-modal .modal-footer { border-color: var(--ef-border); padding: 1rem 1.2rem; }
.ef-dc-modal .modal-body { padding: 1.2rem; }
.ef-dc-modal .modal-title { color: var(--ef-ink); font-size: .92rem; font-weight: 750; }

/* Mobile sticky bar */
.ef-dc-mobile-bar {
    backdrop-filter: blur(18px) saturate(160%);
    background: rgba(255,253,250,.94);
    border-top: 1px solid var(--ef-border);
    bottom: 0; display: none; gap: .5rem; grid-template-columns: 1fr 1fr auto;
    left: 0; padding: .6rem .85rem calc(.6rem + env(safe-area-inset-bottom));
    position: fixed; right: 0; z-index: 1040;
}
@media (max-width: 767.98px) { .ef-dc-mobile-bar { display: grid; } }

@media print {
    .ef-dc-filter-bar, .ef-dc-actions, .ef-dc-mobile-bar, .ef-dc-row-actions { display: none !important; }
}
</style>
@endpush

<div class="ef-dc-shell">

    {{-- ═══ HEADER ══════════════════════════════════════════════════════════ --}}
    <div class="ef-dc-header">
        <div>
            <div class="ef-dc-eyebrow d-none d-md-block">End-of-Day Financial Operations</div>
            <div class="ef-dc-title">Daily Closing</div>
            <div class="ef-dc-sub">
                <span class="d-md-none">Daily reconciliation</span>
                <span class="d-none d-md-inline">Review and reconcile daily expenses and payments</span>
            </div>
        </div>
        <div class="ef-dc-header-right">
            <span class="ef-dc-status-pill {{ $todayClosed ? '--closed' : '--open' }}">
                <span class="dot"></span>
                {{ $todayClosed ? 'Today closed' : 'Today awaiting closure' }}
            </span>
            <div class="ef-dc-actions">
                @if(!$todayClosed)
                    <a href="{{ route('admin.daily-closings.create') }}" class="ef-btn ef-btn-dark">
                        <i class="bi bi-calendar-check"></i> Close Today
                    </a>
                @endif
                <button class="ef-btn" data-bs-toggle="modal" data-bs-target="#pastDateModal">
                    <i class="bi bi-calendar-plus"></i> Past Date
                </button>
                <button class="ef-btn" onclick="window.print()" title="Print Summary" aria-label="Print summary">
                    <i class="bi bi-printer"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ═══ RECONCILIATION STRIP ═══════════════════════════════════════════════ --}}
    @php
        $variance      = $summary['variance'];
        $varClass      = $variance > 0.005  ? '--pos' : ($variance < -0.005 ? '--neg' : '--zero');
        $varianceNote  = $variance > 0.005  ? 'Outstanding balance'
                       : ($variance < -0.005 ? 'Overpaid' : 'Balanced');
        $varianceIcon  = $variance > 0.005  ? 'bi-exclamation-circle'
                       : ($variance < -0.005 ? 'bi-arrow-down-circle' : 'bi-check-circle');
    @endphp
    <div class="ef-dc-strip">
        <div class="ef-dc-figure">
            <div class="ef-dc-figure-label">Total Expenses</div>
            <div class="ef-dc-figure-value">₹{{ number_format($summary['expense_total'], 2) }}</div>
            <div class="ef-dc-figure-note">{{ $summary['total_count'] }} {{ Str::plural('closing', $summary['total_count']) }} in view</div>
        </div>
        <div class="ef-dc-figure">
            <div class="ef-dc-figure-label">Total Payments</div>
            <div class="ef-dc-figure-value">₹{{ number_format($summary['payment_total'], 2) }}</div>
            <div class="ef-dc-figure-note">Disbursed this period</div>
        </div>
        <div class="ef-dc-figure ef-dc-variance-card {{ $varClass }}">
            <div>
                <div class="ef-dc-figure-label">Net Variance</div>
                <div class="ef-dc-figure-value">₹{{ number_format(abs($variance), 2) }}</div>
                <div class="ef-dc-figure-note">{{ $varianceNote }}</div>
            </div>
            <i class="bi {{ $varianceIcon }} ef-dc-variance-icon"></i>
        </div>
    </div>

    {{-- ═══ ATTENTION / RECONCILED BANNER ═════════════════════════════════════ --}}
    @if($summary['total_count'] > 0)
        @if($summary['draft_count'] > 0)
            <div class="ef-dc-attention --draft">
                <div class="ef-dc-attention-text">
                    <div class="ef-dc-attention-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                    <div>
                        <div class="ef-dc-attention-title">{{ $summary['draft_count'] }} {{ Str::plural('closing', $summary['draft_count']) }} needs review</div>
                        <div class="ef-dc-attention-sub">{{ $summary['verified_count'] }} verified · {{ $summary['closed_count'] }} finalized</div>
                    </div>
                </div>
                <a href="{{ route('admin.daily-closings.index', array_merge(request()->except('status'), ['status' => 'draft'])) }}" class="ef-btn ef-btn-dark">
                    Review Drafts <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        @else
            <div class="ef-dc-attention --reconciled">
                <div class="ef-dc-attention-text">
                    <div class="ef-dc-attention-icon"><i class="bi bi-check-circle-fill"></i></div>
                    <div>
                        <div class="ef-dc-attention-title">All closings verified</div>
                        <div class="ef-dc-attention-sub">{{ $summary['verified_count'] }} verified · {{ $summary['closed_count'] }} finalized</div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- ═══ FILTER BAR ═══════════════════════════════════════════════════════ --}}
    <div class="ef-dc-filter-bar" id="filterBar">
        <form method="GET" class="ef-dc-filter-inner">

            <div class="ef-dc-filter-group">
                <label class="ef-dc-filter-label">From</label>
                <input type="date" name="from" class="ef-dc-filter-input"
                       value="{{ request('from') }}" max="{{ today()->toDateString() }}">
            </div>

            <div class="ef-dc-filter-sep">—</div>

            <div class="ef-dc-filter-group">
                <label class="ef-dc-filter-label">To</label>
                <input type="date" name="to" class="ef-dc-filter-input"
                       value="{{ request('to') }}" max="{{ today()->toDateString() }}">
            </div>

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
                    <span class="ef-dc-filter-chip"><i class="bi bi-funnel-fill"></i> Filtered</span>
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
    <div class="ef-dc-list">

        <div class="ef-dc-list-head">
            <span class="ef-dc-list-title">Reconciliation Entries</span>
            <span class="ef-dc-list-count">
                {{ $closings->total() }} {{ Str::plural('record', $closings->total()) }}
                @if($closings->total() > 0)
                    · showing {{ $closings->firstItem() }}–{{ $closings->lastItem() }}
                @endif
            </span>
        </div>

        @if($closings->isNotEmpty())
        <div class="ef-dc-col-head">
            <span>Date</span>
            <span class="num">Expenses</span>
            <span class="num">Payments</span>
            <span class="num">Variance</span>
            <span>Status</span>
            <span></span>
        </div>
        @endif

        @forelse($closings as $closing)
        @php
            $tones     = ['draft' => 'neutral', 'verified' => 'emerald', 'closed' => 'bluegray'];
            $tone      = $tones[$closing->status] ?? 'neutral';
            $rowVar    = (float) $closing->expense_total - (float) $closing->payment_total;
            $rowVarCls = $rowVar > 0.005 ? '--pos' : ($rowVar < -0.005 ? '--neg' : '--zero');
        @endphp

        <div class="ef-dc-row">
            <div class="ef-dc-row-date">
                <span class="ef-dc-row-date-label">{{ $closing->date->format('d M Y') }}</span>
                @if($closing->date->isToday())<span class="ef-dc-row-today">Today</span>@endif
                <span class="ef-dc-row-status-mobile"><x-premium.chip :tone="$tone">{{ ucfirst($closing->status) }}</x-premium.chip></span>
            </div>

            <div class="ef-dc-row-figure">
                <span class="k">Expenses</span>
                <span class="v">₹{{ number_format($closing->expense_total, 2) }}</span>
            </div>

            <div class="ef-dc-row-figure">
                <span class="k">Payments</span>
                <span class="v">₹{{ number_format($closing->payment_total, 2) }}</span>
            </div>

            <div class="ef-dc-row-figure ef-dc-row-variance {{ $rowVarCls }}">
                <span class="k">Variance</span>
                <span class="v">@if($rowVar < -0.005)−@endif₹{{ number_format(abs($rowVar), 2) }}</span>
            </div>

            <div class="ef-dc-row-status">
                <x-premium.chip :tone="$tone">{{ ucfirst($closing->status) }}</x-premium.chip>
            </div>

            <div class="ef-dc-row-actions">
                @if($closing->canEdit())
                    <a href="{{ route('admin.daily-closings.edit', $closing) }}" class="ef-btn ef-btn-dark">
                        <i class="bi bi-pencil-square"></i> Review &amp; Edit
                    </a>
                @else
                    <a href="{{ route('admin.daily-closings.show', $closing) }}" class="ef-btn">
                        <i class="bi bi-eye"></i> View Details
                    </a>
                @endif

                @if($closing->canEdit())
                    <div class="dropdown">
                        <button class="ef-btn ef-btn-icon" data-bs-toggle="dropdown"
                                aria-expanded="false" aria-label="More actions" title="More actions">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm"
                            style="border-color:var(--ef-border);border-radius:12px;min-width:170px">
                            <li>
                                <a class="dropdown-item" style="font-size:.84rem" href="{{ route('admin.daily-closings.show', $closing) }}">
                                    <i class="bi bi-eye me-2 opacity-60"></i> View Details
                                </a>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('admin.daily-closings.recalculate', $closing) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="dropdown-item" style="font-size:.84rem">
                                        <i class="bi bi-arrow-repeat me-2 opacity-60"></i> Recalculate
                                    </button>
                                </form>
                            </li>
                            @if($closing->canDelete())
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item" style="color:var(--ef-danger);font-size:.84rem"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal{{ $closing->id }}">
                                        <i class="bi bi-trash me-2 opacity-70"></i> Delete
                                    </button>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>

            <div class="ef-dc-row-meta">
                {{ $closing->expense_count }} {{ Str::plural('expense', $closing->expense_count) }}
                &nbsp;·&nbsp;
                @if($closing->updater)
                    Updated {{ $closing->updated_at->format('d M, h:i A') }} by {{ $closing->updater->name }}
                @else
                    Created {{ $closing->created_at->format('d M, h:i A') }} by {{ $closing->creator->name }}
                @endif
            </div>
        </div>

        @empty

        <div class="ef-empty-state">
            <div class="ef-empty-orb"><i class="bi bi-calendar-check"></i></div>
            <h3 style="color:var(--ef-ink);font-size:1.05rem;font-weight:750;margin:0 0 6px">
                No daily closings yet
            </h3>
            <p style="color:var(--ef-muted);font-size:.86rem;margin:0 0 18px;max-width:320px;line-height:1.6">
                Create a closing to reconcile today's expenses and payments.
            </p>
            @if(!$todayClosed)
                <a href="{{ route('admin.daily-closings.create') }}" class="ef-btn ef-btn-dark">
                    <i class="bi bi-plus-lg"></i> Create Closing
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
    <button class="ef-btn ef-btn-icon" id="mobileFilterBtn" title="Filter" aria-label="Toggle filters">
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
                        <i class="bi bi-trash me-2" style="color:var(--ef-danger)"></i> Delete Closing
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
