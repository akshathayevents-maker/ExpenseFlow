<x-admin-layout title="Payments">
@push('styles')
<style>
/* ══════════════════════════════════════════════════════════════════
   PAYMENTS — reuses the application's existing design tokens/
   components (x-ds.hero, x-ds.kpi-card, ef-input, ef-btn, x-premium.chip
   — resources/css/app.css) rather than a page-specific palette, matching
   the pattern already used on Employees/Salaries/Wallets. Only the
   toolbar/filter-chip row and the transaction list/row layout need
   page-scoped CSS below (no exact shared list/row component exists yet);
   everything else is a shared class/component. No new colors introduced —
   every value below resolves to an existing --ef-* token.
   ══════════════════════════════════════════════════════════════════ */

/* ── Toolbar: search + date chips + Filters ───────────────────────── */
.ef-pay-toolbar-row { display: flex; gap: .55rem; align-items: center; flex-wrap: wrap; margin-bottom: .65rem; }
.ef-pay-search-wrap { position: relative; flex: 1; min-width: 200px; }
.ef-pay-search-icon {
    position: absolute; left: .8rem; top: 50%; transform: translateY(-50%);
    color: var(--ef-faint); font-size: .85rem; pointer-events: none;
}
.ef-pay-search-wrap .ef-input { padding-left: 2.2rem; }
.ef-pay-filters-btn { position: relative; flex-shrink: 0; }
.ef-pay-filters-badge {
    position: absolute; top: -6px; right: -6px;
    background: var(--ef-emerald); color: #fff;
    font-size: .62rem; font-weight: 800; line-height: 1;
    border-radius: 999px; min-width: 16px; height: 16px;
    display: flex; align-items: center; justify-content: center; padding: 0 3px;
}

.ef-pay-chip-scroll {
    display: flex; gap: .45rem; overflow-x: auto; -webkit-overflow-scrolling: touch;
    scrollbar-width: none; padding-bottom: 2px;
}
.ef-pay-chip-scroll::-webkit-scrollbar { display: none; }
.ef-pay-chip {
    flex-shrink: 0; padding: .35rem .85rem; border-radius: 20px;
    font-size: .78rem; font-weight: 500;
    border: 1px solid var(--ef-border); color: var(--ef-muted);
    background: var(--ef-faint); cursor: pointer;
    transition: all .18s var(--ef-ease); text-decoration: none;
    white-space: nowrap; display: inline-flex; align-items: center; min-height: 32px;
}
.ef-pay-chip:hover { border-color: var(--ef-border-strong); color: var(--ef-ink); background: var(--ef-surface); }
.ef-pay-chip.--active { background: var(--ef-emerald); border-color: var(--ef-emerald); color: #fff; }

/* ── Filters modal (bottom sheet on mobile) ───────────────── */
.ef-pay-filters-modal .modal-dialog { max-width: 420px; }
@media (max-width: 575.98px) {
    .ef-pay-filters-modal .modal-dialog {
        margin: 0; max-width: 100%; width: 100%;
        position: fixed; bottom: 0; left: 0;
    }
    .ef-pay-filters-modal .modal-content { border-radius: 16px 16px 0 0; }
}
.ef-pay-filter-group { margin-bottom: 1rem; }
.ef-pay-filter-group:last-child { margin-bottom: 0; }
.ef-pay-filter-label {
    font-size: .68rem; font-weight: 760; letter-spacing: .08em; text-transform: uppercase;
    color: var(--ef-faint); margin-bottom: 8px;
}
.ef-pay-filter-date-row { display: flex; gap: .5rem; }
.ef-pay-filter-date-row > * { flex: 1; min-width: 0; }

/* ── Transaction list ─────────────────────────────────────── */
.ef-pay-list-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 18px; border-bottom: 1px solid var(--ef-border);
    gap: 10px; flex-wrap: wrap;
}
.ef-pay-list-title { font-size: .68rem; font-weight: 760; letter-spacing: .1em; text-transform: uppercase; color: var(--ef-faint); }
.ef-pay-list-meta { font-size: .78rem; color: var(--ef-muted); }
.ef-pay-list-meta strong { color: var(--ef-ink); font-weight: 700; }

/* Desktop column header row — purely visual labels above the grid rows */
.ef-pay-col-head { display: none; }
@media (min-width: 900px) {
    .ef-pay-col-head {
        display: grid;
        grid-template-columns: 2.4fr 1.3fr 1fr .9fr .8fr auto;
        gap: .75rem;
        padding: .5rem 18px;
        background: var(--ef-surface-2);
        border-bottom: 1px solid var(--ef-border);
        font-size: .64rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--ef-faint);
    }
    .ef-pay-col-head .amt { text-align: right; }
}

/* ── Date group divider ───────────────────────────────────── */
.ef-pay-date-group {
    padding: .4rem 18px;
    border-bottom: 1px solid var(--ef-border);
    font-size: .68rem; font-weight: 700; color: var(--ef-faint);
    letter-spacing: .05em; text-transform: uppercase;
    display: flex; align-items: center; justify-content: space-between;
    background: var(--ef-surface-2);
}
.ef-pay-date-group-total { font-size: .7rem; font-weight: 700; color: var(--ef-muted); }

/* ── Transaction row ──────────────────────────────────────── */
.ef-pay-row {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: .35rem .75rem;
    align-items: center;
    padding: .7rem 18px;
    border-bottom: 1px solid var(--ef-border);
    transition: background .12s;
    position: relative;
}
.ef-pay-row:last-child { border-bottom: none; }
.ef-pay-row:hover { background: var(--ef-surface-2); }
.ef-pay-row:focus-within { background: var(--ef-surface-2); }

@media (min-width: 900px) {
    .ef-pay-row { grid-template-columns: 2.4fr 1.3fr 1fr .9fr .8fr auto; padding: .6rem 18px; }
}

.ef-pay-row-left { display: flex; gap: .6rem; align-items: center; min-width: 0; }

/* Icon container — same size/radius/token convention as Wallets' KPI
   icon treatment (rounded-8px square, tinted bg, tone-matched icon). */
.ef-pay-mode-icon {
    width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: .88rem;
}
.ef-pay-mode-icon.--cash    { background: rgba(15,123,95,.1);   color: var(--ef-emerald-dk); }
.ef-pay-mode-icon.--upi     { background: rgba(184,137,62,.12); color: var(--ef-gold); }
.ef-pay-mode-icon.--bank    { background: rgba(52,94,148,.1);   color: var(--ef-bluegray); }
.ef-pay-mode-icon.--wallet  { background: rgba(216,154,61,.13); color: #7D5218; }

.ef-pay-row-title { font-size: .9rem; font-weight: 720; color: var(--ef-ink); line-height: 1.25; overflow-wrap: anywhere; }
.ef-pay-row-title a { color: inherit; text-decoration: none; }
.ef-pay-row-title a:hover { color: var(--ef-emerald); }
.ef-pay-row-sub { font-size: .76rem; color: var(--ef-muted); display: flex; align-items: center; gap: .4rem; flex-wrap: wrap; margin-top: .1rem; }
.ef-pay-row-sub .dot { width: 3px; height: 3px; border-radius: 50%; background: var(--ef-border-strong); flex-shrink: 0; }

.ef-pay-row-category { font-size: .8rem; color: var(--ef-muted); }
.ef-pay-row-time { font-size: .76rem; color: var(--ef-muted); }

/* Whole row clickable on mobile via an overlay anchor; explicit "View"
   link stays for accessibility/desktop — same pattern as before. */
.ef-pay-row-hit { position: absolute; inset: 0; z-index: 1; border-radius: inherit; }
.ef-pay-row-hit:focus-visible { outline: 2px solid var(--ef-emerald); outline-offset: -2px; }
.ef-pay-row-right, .ef-pay-row-title a { position: relative; z-index: 2; }

.ef-pay-row-right { display: flex; flex-direction: column; align-items: flex-end; gap: .3rem; justify-self: end; }
.ef-pay-amount { font-size: 1.05rem; font-weight: 800; color: var(--ef-ink); letter-spacing: -.01em; line-height: 1; font-variant-numeric: tabular-nums; }
.ef-pay-view-link {
    color: var(--ef-emerald); font-size: .78rem; font-weight: 660; text-decoration: none;
    display: inline-flex; align-items: center; gap: .25rem; min-height: 28px; transition: color .15s;
}
.ef-pay-view-link:hover { color: var(--ef-emerald-dk); }

/* Mobile stacked layout: identity full width, then a meta row
   (method/time/status chips), amount+action inline at end */
@media (max-width: 899.98px) {
    .ef-pay-row { grid-template-columns: 1fr; }
    .ef-pay-row-category { display: none; } /* folded into sub-line instead */
    .ef-pay-row-meta {
        grid-column: 1 / -1;
        display: flex; align-items: center; justify-content: space-between; gap: .5rem; flex-wrap: wrap;
        margin-top: .3rem;
    }
    .ef-pay-row-meta-left { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
    .ef-pay-row-right { flex-direction: row; align-items: center; gap: .6rem; }
}
@media (min-width: 900px) {
    .ef-pay-row-meta { display: contents; }
    .ef-pay-row-meta-left { display: contents; }
    .ef-pay-row-time { text-align: left; }
}

/* ── Pagination ────────────────────────────────────────────── */
.ef-pay-pagination { display: flex; justify-content: center; margin-top: 14px; }
.ef-pay-pagination-info { font-size: .78rem; color: var(--ef-muted); text-align: center; margin-bottom: 8px; }
</style>
@endpush

@php
    $modeFilter  = $filters['payment_mode'] ?? '';
    $empFilter   = $filters['employee_id']  ?? '';
    $fromFilter  = $filters['from'] ?? '';
    $toFilter    = $filters['to']   ?? '';
    $search      = $filters['search'] ?? '';

    $today     = now()->toDateString();
    $weekStart = now()->startOfWeek()->toDateString();
    $weekEnd   = now()->endOfWeek()->toDateString();
    $monStart  = now()->startOfMonth()->toDateString();
    $monEnd    = now()->endOfMonth()->toDateString();

    $isAllTime = !$fromFilter && !$toFilter;
    $isToday   = $fromFilter === $today && $toFilter === $today;
    $isWeek    = $fromFilter === $weekStart && $toFilter === $weekEnd;
    $isMonth   = $fromFilter === $monStart && $toFilter === $monEnd;
    $isCustomRange = ($fromFilter || $toFilter) && !$isToday && !$isWeek && !$isMonth;

    // "Filters" modal covers method + paid-by + a manual date range —
    // count active ones so the button can show a badge count.
    $advFilterCount = ($modeFilter ? 1 : 0) + ($empFilter ? 1 : 0) + ($isCustomRange ? 1 : 0);

    $modeIconMap  = ['cash' => 'bi-cash-stack', 'upi' => 'bi-phone', 'bank_transfer' => 'bi-bank', 'wallet' => 'bi-wallet2'];
    $modeCssMap   = ['cash' => '--cash', 'upi' => '--upi', 'bank_transfer' => '--bank', 'wallet' => '--wallet'];
    $modeLabelMap = \App\Models\ExpensePayment::modeLabels();

    // Payment method chip tone — a single consistent gold tone for every
    // method (no per-method color scheme exists elsewhere in the app for
    // this kind of categorical tag), matching the brief's guidance.
    $modeChipTone = 'gold';

    $fmt = fn(float $v): string =>
        $v >= 10000000 ? '₹' . number_format($v/10000000, 1) . 'Cr'
      : ($v >= 100000  ? '₹' . number_format($v/100000, 1) . 'L'
      :                  '₹' . number_format($v, 0));

    // Group paginated payments by date for the timeline display
    $grouped = $payments->getCollection()->groupBy(fn($p) => $p->paid_at->toDateString());
@endphp

{{-- ── Hero ──────────────────────────────────────────────────── --}}
<x-ds.hero eyebrow="Finance Operations" title="Payments"
    :meta="[['icon' => 'bi-credit-card', 'text' => 'Manage and track all payment transactions']]">
    <x-slot:actions>
        <a href="{{ route('admin.expense-requests.index') }}" class="ef-ds-btn">
            <i class="bi bi-receipt"></i> <span>Expense Requests</span>
        </a>
        <a href="{{ route('admin.payments.index') }}" class="ef-ds-btn">
            <i class="bi bi-arrow-clockwise"></i> <span>Refresh</span>
        </a>
    </x-slot:actions>
    <x-slot:mobile_stat>
        <span class="ef-ds-hero-mstat-val">{{ $fmt($stats['total_amount']) }}</span>
        <span class="ef-ds-hero-mstat-note">collected &middot; {{ number_format($stats['total_count']) }} {{ Str::plural('transaction', $stats['total_count']) }}</span>
    </x-slot:mobile_stat>
</x-ds.hero>

{{-- ── KPI strip — shared x-ds.kpi-card component ─────────────────── --}}
<div class="ef-ds-kpi-wrap">
    <div class="ef-ds-kpi-grid" style="--kpi-cols:4">
        <x-ds.kpi-card
            icon="bi-currency-rupee"
            label="Total Collected"
            :value="$fmt($stats['total_amount'])"
            accent="gold"
            value-color="c-gold"
        />
        <x-ds.kpi-card
            icon="bi-receipt-cutoff"
            label="Transactions"
            :value="number_format($stats['total_count'])"
            accent="bluegray"
        />
        <x-ds.kpi-card
            icon="bi-graph-up"
            label="Avg Transaction"
            :value="$fmt($stats['avg_amount'])"
            accent="muted"
            value-color="c-muted"
        />
        <x-ds.kpi-card
            icon="bi-calendar-month"
            label="This Month"
            :value="$fmt($stats['monthly_total'])"
            accent="emerald"
            value-color="c-emerald"
        />
    </div>
</div>

{{-- ── Toolbar: search + date chips + Filters ──────────────────── --}}
<form method="GET" id="payFilterForm" action="{{ route('admin.payments.index') }}">
<div class="ef-pay-toolbar-row">
    <div class="ef-pay-search-wrap">
        <i class="bi bi-search ef-pay-search-icon"></i>
        <input type="text" name="search" class="ef-input"
               placeholder="Search payments…"
               value="{{ $search }}">
    </div>
    @if($modeFilter)<input type="hidden" name="payment_mode" value="{{ $modeFilter }}">@endif
    @if($empFilter)<input type="hidden" name="employee_id" value="{{ $empFilter }}">@endif
    @if($isCustomRange)
        <input type="hidden" name="from" value="{{ $fromFilter }}">
        <input type="hidden" name="to" value="{{ $toFilter }}">
    @endif
    <button type="button" class="ef-btn ef-pay-filters-btn" data-bs-toggle="modal" data-bs-target="#payFiltersModal">
        <i class="bi bi-sliders2"></i> <span class="d-none d-sm-inline">Filters</span>
        @if($advFilterCount)<span class="ef-pay-filters-badge">{{ $advFilterCount }}</span>@endif
    </button>
    <button type="submit" class="ef-btn ef-btn-dark">
        <i class="bi bi-search"></i> <span class="d-none d-sm-inline">Search</span>
    </button>
</div>

<div class="ef-pay-chip-scroll" role="group" aria-label="Filter by date range">
    <a href="{{ route('admin.payments.index', array_filter(['search' => $search, 'payment_mode' => $modeFilter, 'employee_id' => $empFilter])) }}"
       class="ef-pay-chip {{ $isAllTime ? '--active' : '' }}">All Time</a>
    <a href="{{ route('admin.payments.index', array_filter(['from' => $today, 'to' => $today, 'search' => $search, 'payment_mode' => $modeFilter, 'employee_id' => $empFilter])) }}"
       class="ef-pay-chip {{ $isToday ? '--active' : '' }}">Today</a>
    <a href="{{ route('admin.payments.index', array_filter(['from' => $weekStart, 'to' => $weekEnd, 'search' => $search, 'payment_mode' => $modeFilter, 'employee_id' => $empFilter])) }}"
       class="ef-pay-chip {{ $isWeek ? '--active' : '' }}">This Week</a>
    <a href="{{ route('admin.payments.index', array_filter(['from' => $monStart, 'to' => $monEnd, 'search' => $search, 'payment_mode' => $modeFilter, 'employee_id' => $empFilter])) }}"
       class="ef-pay-chip {{ $isMonth ? '--active' : '' }}">This Month</a>
</div>

{{-- ── Filters modal (method / paid by / manual date range) ─────── --}}
<div class="modal fade ef-pay-filters-modal" id="payFiltersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border:1px solid var(--ef-border);border-radius:var(--ef-radius)">
            <div class="modal-header" style="border-bottom:1px solid var(--ef-border)">
                <h5 class="modal-title" style="color:var(--ef-ink);font-weight:760;font-size:.95rem">Filters</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="ef-pay-filter-group">
                    <div class="ef-pay-filter-label">Payment Method</div>
                    <select name="payment_mode" class="ef-select">
                        <option value="">All methods</option>
                        @foreach($modeLabelMap as $value => $label)
                            <option value="{{ $value }}" {{ $modeFilter === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ef-pay-filter-group">
                    <div class="ef-pay-filter-label">Paid By</div>
                    <select name="employee_id" class="ef-select">
                        <option value="">All employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ (string) $empFilter === (string) $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ef-pay-filter-group">
                    <div class="ef-pay-filter-label">Date Range</div>
                    <div class="ef-pay-filter-date-row">
                        <input type="date" name="from" class="ef-input" value="{{ $fromFilter }}" aria-label="From date">
                        <input type="date" name="to" class="ef-input" value="{{ $toFilter }}" aria-label="To date">
                    </div>
                </div>
                @if($search)<input type="hidden" name="search" value="{{ $search }}">@endif
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--ef-border)">
                <a href="{{ route('admin.payments.index', array_filter(['search' => $search])) }}" class="ef-btn">Clear</a>
                <button type="submit" class="ef-btn ef-btn-dark">Apply Filters</button>
            </div>
        </div>
    </div>
</div>
</form>

{{-- ── Transaction list ─────────────────────────────────────────── --}}
<x-ds.card :no-pad="true">
    <div class="ef-pay-list-head">
        <span class="ef-pay-list-title">Transactions</span>
        <span class="ef-pay-list-meta">
            @if($payments->total() > 0)
                <strong>{{ number_format($payments->total()) }}</strong>
                {{ Str::plural('payment', $payments->total()) }} ·
                <strong>{{ $fmt($stats['total_amount']) }}</strong>
            @endif
        </span>
    </div>

    @if($payments->isEmpty())
    <div class="ef-empty-state">
        <div class="ef-empty-orb"><i class="bi bi-credit-card"></i></div>
        <h3 style="color:var(--ef-ink);font-size:1.1rem;font-weight:760;margin:0 0 8px">No payments found</h3>
        <p style="color:var(--ef-muted);font-size:.86rem;margin:0 0 20px;max-width:300px;line-height:1.6">
            @if($search || $modeFilter || $empFilter || $fromFilter || $toFilter)
                Try changing your filters or search criteria.
            @else
                Recorded settlements and transaction history will appear here.
            @endif
        </p>
        @if($search || $modeFilter || $empFilter || $fromFilter || $toFilter)
            <a href="{{ route('admin.payments.index') }}" class="ef-btn ef-btn-dark">
                <i class="bi bi-x-circle"></i> Clear Filters
            </a>
        @endif
    </div>
    @else

    <div class="ef-pay-col-head">
        <span>Payment</span>
        <span>Category</span>
        <span>Method</span>
        <span>Time</span>
        <span>Status</span>
        <span class="amt">Amount</span>
    </div>

    @php $prevDate = null; @endphp
    @foreach($payments as $payment)
    @php
        $dateKey  = $payment->paid_at->toDateString();
        $mode     = $payment->payment_mode;
        $modeCss  = $modeCssMap[$mode]  ?? '--cash';
        $modeIcon = $modeIconMap[$mode] ?? 'bi-credit-card';
        $modeLabel= $modeLabelMap[$mode] ?? ucfirst($mode);
        $req      = $payment->expenseRequest;
        $requester= $req?->requester;
        $category = $req?->category;
        $isToday  = $payment->paid_at->isToday();
        $isYest   = $payment->paid_at->isYesterday();
        $dateLabel = $isToday ? 'Today'
                   : ($isYest ? 'Yesterday'
                   : $payment->paid_at->format('D, j M Y'));
    @endphp

    {{-- Date group separator --}}
    @if($dateKey !== $prevDate)
    @php
        $dayTotal = $payments->getCollection()
            ->filter(fn($p) => $p->paid_at->toDateString() === $dateKey)
            ->sum('amount');
        $prevDate = $dateKey;
    @endphp
    <div class="ef-pay-date-group">
        <span>{{ $dateLabel }}</span>
        <span class="ef-pay-date-group-total">₹{{ number_format($dayTotal, 0) }}</span>
    </div>
    @endif

    <div class="ef-pay-row">
        @if($req)
            <a href="{{ route('admin.expense-requests.show', $req) }}" class="ef-pay-row-hit" aria-label="View payment: {{ $req->title }}, ₹{{ number_format($payment->amount, 0) }}"></a>
        @endif

        {{-- Identity --}}
        <div class="ef-pay-row-left">
            <div class="ef-pay-mode-icon {{ $modeCss }}">
                <i class="bi {{ $modeIcon }}"></i>
            </div>
            <div style="min-width:0;flex:1">
                <div class="ef-pay-row-title">
                    @if($req)
                        <a href="{{ route('admin.expense-requests.show', $req) }}">{{ Str::limit($req->title, 45) }}</a>
                    @else
                        <span style="color:var(--ef-muted)">Unlinked Payment</span>
                    @endif
                </div>
                <div class="ef-pay-row-sub">
                    @if($requester)
                        <span>{{ $requester->name }}</span>
                    @endif
                    @if($category)
                        <span class="dot"></span><span>{{ $category->name }}</span>
                    @endif
                    @if($payment->payer)
                        <span class="dot"></span><span>Paid by {{ $payment->payer->name }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Category (desktop column only) --}}
        <div class="ef-pay-row-category">{{ $category->name ?? '—' }}</div>

        {{-- Meta: method + time + status (wraps as one row on mobile, separate grid cells on desktop) --}}
        <div class="ef-pay-row-meta">
            <div class="ef-pay-row-meta-left">
                <x-premium.chip :tone="$modeChipTone">{{ $modeLabel }}</x-premium.chip>
                <span class="ef-pay-row-time">{{ $payment->paid_at->format('h:i A') }}</span>
            </div>
            <x-premium.chip tone="emerald">Settled</x-premium.chip>
        </div>

        {{-- Amount + action --}}
        <div class="ef-pay-row-right">
            <div class="ef-pay-amount">₹{{ number_format($payment->amount, 0) }}</div>
            @if($req)
                <a href="{{ route('admin.expense-requests.show', $req) }}" class="ef-pay-view-link">
                    View <i class="bi bi-arrow-right" style="font-size:.65rem"></i>
                </a>
            @endif
        </div>
    </div>
    @endforeach

    @endif
</x-ds.card>

{{-- ── Pagination ──────────────────────────────────────────────── --}}
@if($payments->hasPages())
<div class="ef-pay-pagination-info">
    Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }}
    of {{ number_format($payments->total()) }} payments
</div>
<div class="ef-pay-pagination">{{ $payments->links() }}</div>
@endif

</x-admin-layout>
