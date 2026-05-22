<x-admin-layout title="Hall Reports">
@push('styles')
<style>
/* ── Hall Reports — ef-rp-* ─────────────────────────────────── */
.ef-rp-shell {
    max-width: 1500px;
    margin: 0 auto;
    padding-bottom: 60px;
}

/* ── Hero — dark dramatic ──────────────────────────────────── */
.ef-rp-hero {
    align-items: end;
    background: linear-gradient(135deg, #1a1410 0%, #2a2218 50%, #1e1812 100%);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(26,22,18,.16), 0 1px 4px rgba(26,22,18,.1);
    display: grid;
    gap: 20px;
    grid-template-columns: minmax(0, 1fr) auto;
    margin-bottom: 24px;
    overflow: hidden;
    padding: 32px;
    position: relative;
}
.ef-rp-hero::before {
    background: radial-gradient(circle, rgba(160,114,56,.16) 0%, transparent 68%);
    border-radius: 50%;
    content: "";
    height: 420px;
    pointer-events: none;
    position: absolute;
    right: -80px;
    top: -140px;
    width: 420px;
}
.ef-rp-hero::after {
    background: radial-gradient(circle, rgba(26,102,69,.1) 0%, transparent 70%);
    border-radius: 50%;
    bottom: -80px;
    content: "";
    height: 260px;
    left: 30%;
    pointer-events: none;
    position: absolute;
    width: 260px;
}
.ef-rp-kicker {
    color: rgba(160,114,56,.9);
    font-size: .67rem;
    font-weight: 760;
    letter-spacing: .17em;
    text-transform: uppercase;
}
.ef-rp-title {
    color: #fffdfa;
    font-size: clamp(2rem, 4vw, 3.2rem);
    font-weight: 780;
    letter-spacing: -.01em;
    line-height: 1;
    margin: 8px 0 10px;
}
.ef-rp-subtitle { color: rgba(255,253,250,.55); font-size: .9rem; margin-bottom: 6px; }
.ef-rp-date     { color: rgba(255,253,250,.32); font-size: .78rem; font-weight: 640; }
.ef-rp-hero-actions {
    align-items: flex-start;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-end;
    position: relative;
    z-index: 1;
}
.ef-rp-hero .ef-btn {
    background: rgba(255,255,255,.08);
    border-color: rgba(255,255,255,.14);
    color: rgba(255,253,250,.85);
}
.ef-rp-hero .ef-btn:hover {
    background: rgba(255,255,255,.14);
    color: #fffdfa;
}

/* ── Preset chips ──────────────────────────────────────────── */
.ef-rp-presets {
    align-items: center;
    display: flex;
    flex-wrap: nowrap;
    gap: 6px;
    margin-bottom: 16px;
    overflow-x: auto;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
}
.ef-rp-presets::-webkit-scrollbar { display: none; }
.ef-rp-preset {
    background: transparent;
    border: 1.5px solid var(--ef-border-strong);
    border-radius: 20px;
    color: var(--ef-muted);
    display: inline-block;
    font-size: .73rem;
    font-weight: 660;
    padding: 5px 14px;
    text-decoration: none;
    transition: border-color .15s, color .15s, background .15s;
    white-space: nowrap;
}
.ef-rp-preset:hover { border-color: var(--ef-ink); color: var(--ef-ink); }
.ef-rp-preset.--active { background: var(--ef-ink); border-color: var(--ef-ink); color: var(--ef-bg); }

/* ── Filter bar ────────────────────────────────────────────── */
.ef-rp-filter-bar {
    background: rgba(255, 253, 250, .96);
    border: 1px solid var(--ef-border);
    border-radius: 14px;
    box-shadow: var(--ef-shadow);
    margin-bottom: 24px;
}
.ef-rp-filter-inner {
    align-items: end;
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(auto-fill, minmax(168px, 1fr));
    padding: 16px 18px;
}
.ef-rp-filter-label {
    color: var(--ef-faint);
    display: block;
    font-size: .63rem;
    font-weight: 680;
    letter-spacing: .1em;
    margin-bottom: 5px;
    text-transform: uppercase;
}
.ef-rp-filter-input,
.ef-rp-filter-select {
    background: rgba(255, 253, 250, .7);
    border: 1.5px solid var(--ef-border-strong);
    border-radius: 9px;
    color: var(--ef-ink);
    font-family: inherit;
    font-size: .83rem;
    padding: 8px 11px;
    transition: border-color .15s;
    width: 100%;
}
.ef-rp-filter-input:focus,
.ef-rp-filter-select:focus { border-color: var(--ef-gold); outline: none; }
.ef-rp-filter-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23888' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 11px center;
    padding-right: 30px;
}
.ef-rp-filter-btns {
    align-items: center;
    display: flex;
    flex-direction: column;
    gap: 6px;
    justify-content: flex-end;
}
.ef-rp-filter-btns-row {
    align-items: center;
    display: flex;
    gap: 6px;
}

/* ── KPI strip ─────────────────────────────────────────────── */
.ef-rp-kpis {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    margin-bottom: 24px;
}
.ef-rp-kpi {
    background: rgba(255, 253, 250, .92);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    min-height: 118px;
    padding: 18px;
    transition: border-color .18s var(--ef-ease), box-shadow .18s var(--ef-ease);
}
.ef-rp-kpi:hover {
    border-color: var(--ef-border-strong);
    box-shadow: var(--ef-shadow-hover);
}
.ef-rp-kpi-label {
    color: var(--ef-faint);
    font-size: .65rem;
    font-weight: 760;
    letter-spacing: .14em;
    text-transform: uppercase;
}
.ef-rp-kpi-val {
    color: var(--ef-ink);
    font-size: 1.4rem;
    font-variant-numeric: tabular-nums;
    font-weight: 780;
    line-height: 1;
    margin-top: 12px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ef-rp-kpi-val.--lg { font-size: 1.6rem; }
.ef-rp-kpi-val.--sm { font-size: 1.1rem; }
.ef-rp-kpi-note { color: var(--ef-muted); font-size: .72rem; margin-top: 6px; }
.ef-rp-kpi-bar {
    background: rgba(20, 20, 18, .07);
    border-radius: 2px;
    height: 3px;
    margin-top: 10px;
    overflow: hidden;
}
.ef-rp-kpi-bar-fill {
    background: var(--ef-gold);
    border-radius: 2px;
    height: 100%;
    transition: width .8s ease;
}

/* ── Analytics grid ────────────────────────────────────────── */
.ef-rp-analytics {
    display: grid;
    gap: 16px;
    grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
    margin-bottom: 20px;
}

/* ── Section cards ─────────────────────────────────────────── */
.ef-rp-card {
    background: rgba(255, 253, 250, .96);
    border: 1px solid var(--ef-border);
    border-radius: 18px;
    box-shadow: var(--ef-shadow);
    overflow: hidden;
}
.ef-rp-card-head {
    align-items: center;
    border-bottom: 1px solid var(--ef-border);
    display: flex;
    justify-content: space-between;
    padding: 16px 20px;
}
.ef-rp-card-title {
    color: var(--ef-ink);
    font-size: .82rem;
    font-weight: 720;
    letter-spacing: .01em;
}
.ef-rp-card-meta { color: var(--ef-faint); font-size: .72rem; }
.ef-rp-card-body { padding: 20px; }

/* ── Chart ─────────────────────────────────────────────────── */
.ef-rp-chart-wrap { height: 272px; position: relative; }

/* ── Payment distribution ──────────────────────────────────── */
.ef-rp-pdist { display: flex; flex-direction: column; gap: 18px; padding: 22px 20px; }
.ef-rp-pdist-row { align-items: center; display: flex; flex-direction: column; gap: 6px; }
.ef-rp-pdist-top { align-items: center; display: flex; justify-content: space-between; width: 100%; }
.ef-rp-pdist-name { color: var(--ef-ink); font-size: .8rem; font-weight: 660; }
.ef-rp-pdist-meta { color: var(--ef-muted); font-size: .75rem; font-variant-numeric: tabular-nums; }
.ef-rp-pdist-track {
    background: rgba(20, 20, 18, .06);
    border-radius: 3px;
    height: 6px;
    overflow: hidden;
    width: 100%;
}
.ef-rp-pdist-fill {
    border-radius: 3px;
    height: 100%;
    transition: width .8s ease;
}
.ef-rp-pdist-fill.--paid    { background: rgba(42, 122, 84, .75); }
.ef-rp-pdist-fill.--partial { background: rgba(48, 80, 160, .65); }
.ef-rp-pdist-fill.--pending { background: rgba(160, 114, 56, .65); }
.ef-rp-pdist-empty {
    align-items: center;
    color: var(--ef-faint);
    display: flex;
    font-size: .82rem;
    height: 120px;
    justify-content: center;
}

/* ── Performance grid ──────────────────────────────────────── */
.ef-rp-perf-grid {
    display: grid;
    gap: 16px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-bottom: 20px;
}

/* ── Premium table ─────────────────────────────────────────── */
.ef-rp-table-wrap { overflow-x: auto; }
.ef-rp-table {
    border-collapse: collapse;
    width: 100%;
}
.ef-rp-table thead th {
    border-bottom: 1.5px solid var(--ef-border-strong);
    color: var(--ef-faint);
    font-size: .63rem;
    font-weight: 760;
    letter-spacing: .12em;
    padding: 0 16px 10px;
    text-align: left;
    text-transform: uppercase;
    white-space: nowrap;
}
.ef-rp-table thead th.r { text-align: right; }
.ef-rp-table tbody tr {
    border-bottom: 1px solid var(--ef-border);
    transition: background .12s;
}
.ef-rp-table tbody tr:last-child { border-bottom: none; }
.ef-rp-table tbody tr.clickable { cursor: pointer; }
.ef-rp-table tbody tr.clickable:hover { background: rgba(20, 20, 18, .025); }
.ef-rp-table tbody td {
    color: var(--ef-ink);
    font-size: .83rem;
    padding: 12px 16px;
    vertical-align: middle;
}
.ef-rp-table tbody td.r  { text-align: right; font-variant-numeric: tabular-nums; }
.ef-rp-table tbody td.dim { color: var(--ef-muted); }
.ef-rp-table tbody td.mono { font-variant-numeric: tabular-nums; }
.ef-rp-table-name { color: var(--ef-ink); font-weight: 680; }
.ef-rp-table-sub  { color: var(--ef-faint); font-size: .7rem; margin-top: 2px; }

/* Share bar in table */
.ef-rp-share {
    background: rgba(20, 20, 18, .06);
    border-radius: 2px;
    height: 4px;
    margin-top: 5px;
    overflow: hidden;
    width: 72px;
}
.ef-rp-share-fill {
    background: rgba(160, 114, 56, .55);
    border-radius: 2px;
    height: 100%;
}

/* Status chips in table */
.ef-rp-chip {
    border-radius: 6px;
    display: inline-block;
    font-size: .59rem;
    font-weight: 760;
    letter-spacing: .08em;
    padding: 2px 7px;
    text-transform: uppercase;
}
.ef-rp-chip.--confirmed { background: rgba(60,140,100,.1); border: 1px solid rgba(60,140,100,.2); color: #2a7a54; }
.ef-rp-chip.--completed { background: rgba(60,90,160,.1);  border: 1px solid rgba(60,90,160,.2);  color: #3050a0; }
.ef-rp-chip.--cancelled { background: rgba(141,74,60,.07); border: 1px solid rgba(141,74,60,.18); color: var(--ef-danger); }
.ef-rp-chip.--paid      { background: rgba(60,140,100,.1); border: 1px solid rgba(60,140,100,.2); color: #2a7a54; }
.ef-rp-chip.--partial   { background: rgba(50,80,160,.1);  border: 1px solid rgba(50,80,160,.2);  color: #3050a0; }
.ef-rp-chip.--pending   { background: rgba(170,120,30,.1); border: 1px solid rgba(170,120,30,.2); color: #8a6020; }

/* ── Collection bar inside table ───────────────────────────── */
.ef-rp-coll-bar {
    background: rgba(20, 20, 18, .06);
    border-radius: 2px;
    height: 4px;
    margin-top: 4px;
    overflow: hidden;
    width: 60px;
}
.ef-rp-coll-fill { background: rgba(42, 122, 84, .6); border-radius: 2px; height: 100%; }

/* ── Period label ──────────────────────────────────────────── */
.ef-rp-period-note {
    background: rgba(180, 145, 90, .07);
    border: 1px solid rgba(180, 145, 90, .15);
    border-radius: 8px;
    color: #8a6c3a;
    display: inline-flex;
    font-size: .72rem;
    font-weight: 640;
    gap: 6px;
    margin-bottom: 20px;
    padding: 6px 12px;
}

/* ── Empty state ───────────────────────────────────────────── */
.ef-rp-empty {
    background: rgba(255, 253, 250, .92);
    border: 1px solid var(--ef-border);
    border-radius: 18px;
    box-shadow: var(--ef-shadow);
    padding: 56px 32px;
    text-align: center;
}
.ef-rp-empty-icon  { color: var(--ef-faint); font-size: 2.8rem; margin-bottom: 16px; }
.ef-rp-empty-title { color: var(--ef-ink); font-size: 1.2rem; font-weight: 720; margin-bottom: 8px; }
.ef-rp-empty-note  { color: var(--ef-muted); font-size: .85rem; }

/* ── Flash messages ────────────────────────────────────────── */
.ef-rp-flash {
    align-items: center;
    border-radius: 12px;
    display: flex;
    font-size: .84rem;
    gap: 10px;
    margin-bottom: 20px;
    padding: 14px 16px;
}
.ef-rp-flash.--success { background: rgba(60,140,100,.08); border: 1px solid rgba(60,140,100,.2); color: #2a7a54; }
.ef-rp-flash.--error   { background: rgba(141,74,60,.08); border: 1px solid rgba(141,74,60,.2); color: var(--ef-danger); }

/* ── Responsive ────────────────────────────────────────────── */
@media (max-width: 1399.98px) {
    .ef-rp-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
@media (max-width: 1199.98px) {
    .ef-rp-hero { grid-template-columns: minmax(0, 1fr); padding: 28px; }
    .ef-rp-hero-actions { justify-content: flex-start; }
    .ef-rp-analytics { grid-template-columns: minmax(0, 1fr); }
}
@media (max-width: 991.98px) {
    .ef-rp-perf-grid { grid-template-columns: minmax(0, 1fr); }
}
@media (max-width: 767.98px) {
    .ef-rp-hero { padding: 20px; }
    .ef-rp-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ef-rp-title { font-size: clamp(1.6rem, 6vw, 2.2rem); }
    .ef-rp-chart-wrap { height: 200px; }
    .ef-rp-filter-inner { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ef-rp-table .mob-hide { display: none; }
    .ef-rp-kpi-val { font-size: 1.1rem; }
    .ef-rp-kpi-val.--lg { font-size: 1.2rem; }
}
@media (max-width: 479.98px) {
    .ef-rp-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ef-rp-filter-inner { grid-template-columns: minmax(0, 1fr); }
}
</style>
@endpush

@php
$today = now()->toDateString();
$presets = [
    'all'      => ['from' => null,                                            'to' => null,                                              'label' => 'All Time'],
    'month'    => ['from' => now()->startOfMonth()->toDateString(),           'to' => now()->endOfMonth()->toDateString(),                'label' => 'This Month'],
    'last'     => ['from' => now()->subMonth()->startOfMonth()->toDateString(),'to' => now()->subMonth()->endOfMonth()->toDateString(),   'label' => 'Last Month'],
    'quarter'  => ['from' => now()->firstOfQuarter()->toDateString(),         'to' => now()->lastOfQuarter()->toDateString(),             'label' => 'This Quarter'],
    'year'     => ['from' => now()->startOfYear()->toDateString(),            'to' => now()->endOfYear()->toDateString(),                 'label' => 'This Year'],
];

$activePreset = 'none';
$rFrom = request('date_from');
$rTo   = request('date_to');
if (!$rFrom && !$rTo && !request()->hasAny(['hall_id','payment_status','event_type','status'])) {
    $activePreset = 'all';
} else {
    foreach ($presets as $key => $p) {
        if ($key !== 'all' && $rFrom === $p['from'] && $rTo === $p['to']
            && !request()->hasAny(['hall_id','payment_status','event_type','status'])) {
            $activePreset = $key;
            break;
        }
    }
}

$hasFilter = request()->hasAny(['date_from','date_to','hall_id','payment_status','event_type','status']);
$maxRevenue = $summary['by_hall']->max('revenue') ?: 1;
$maxEventRev= $summary['by_event']->max('revenue') ?: 1;
$totalActive = $summary['active_bookings'] ?: 1;

$chartLabels  = $monthlyTrend->pluck('label');
$chartRevenue = $monthlyTrend->pluck('revenue');
$chartCounts  = $monthlyTrend->pluck('count');

$pdistTotal = $summary['pay_paid'] + $summary['pay_partial'] + $summary['pay_pending'];
$paidPct    = $pdistTotal > 0 ? round($summary['pay_paid']    / $pdistTotal * 100) : 0;
$partialPct = $pdistTotal > 0 ? round($summary['pay_partial'] / $pdistTotal * 100) : 0;
$pendingPct = $pdistTotal > 0 ? round($summary['pay_pending'] / $pdistTotal * 100) : 0;
@endphp

<div class="ef-rp-shell">

    {{-- Hero ─────────────────────────────────────────────────── --}}
    <div class="ef-rp-hero">
        <div>
            <p class="ef-rp-kicker">Venue Analytics</p>
            <h1 class="ef-rp-title">Hall Reports</h1>
            <p class="ef-rp-subtitle">Hospitality analytics and venue performance insights</p>
            <p class="ef-rp-date">{{ now()->format('l, d F Y') }}</p>
        </div>
        <div class="ef-rp-hero-actions">
            <button onclick="window.print()" class="ef-btn">
                <i class="bi bi-printer"></i> Print
            </button>
            <a href="{{ route('hall.bookings.index') }}" class="ef-btn">
                <i class="bi bi-calendar2-event"></i> All Bookings
            </a>
        </div>
    </div>

    {{-- Flash handled by global toast in admin-layout — no page-level duplicate --}}

    {{-- Date presets ──────────────────────────────────────────── --}}
    <div class="ef-rp-presets">
        @foreach($presets as $key => $p)
            @php
                $url = $key === 'all'
                    ? route('hall.reports.index')
                    : route('hall.reports.index', ['date_from' => $p['from'], 'date_to' => $p['to']]);
            @endphp
            <a href="{{ $url }}" class="ef-rp-preset {{ $activePreset === $key ? '--active' : '' }}">
                {{ $p['label'] }}
            </a>
        @endforeach
    </div>

    {{-- Filter bar ───────────────────────────────────────────── --}}
    <div class="ef-rp-filter-bar">
        <form method="GET" id="rpFilterForm">
            <div class="ef-rp-filter-inner">
                <div>
                    <label class="ef-rp-filter-label">From Date</label>
                    <input type="date" name="date_from" class="ef-rp-filter-input" value="{{ request('date_from') }}">
                </div>
                <div>
                    <label class="ef-rp-filter-label">To Date</label>
                    <input type="date" name="date_to" class="ef-rp-filter-input" value="{{ request('date_to') }}">
                </div>
                <div>
                    <label class="ef-rp-filter-label">Hall</label>
                    <select name="hall_id" class="ef-rp-filter-select">
                        <option value="">All Halls</option>
                        @foreach($halls as $h)
                            <option value="{{ $h->id }}" {{ request('hall_id') == $h->id ? 'selected' : '' }}>{{ $h->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ef-rp-filter-label">Event Type</label>
                    <select name="event_type" class="ef-rp-filter-select">
                        <option value="">All Events</option>
                        @foreach(\App\Models\HallBooking::eventTypes() as $v => $l)
                            <option value="{{ $v }}" {{ request('event_type') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ef-rp-filter-label">Payment</label>
                    <select name="payment_status" class="ef-rp-filter-select">
                        <option value="">All Payments</option>
                        @foreach(\App\Models\HallBooking::paymentStatuses() as $v => $l)
                            <option value="{{ $v }}" {{ request('payment_status') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ef-rp-filter-label">Status</label>
                    <select name="status" class="ef-rp-filter-select">
                        <option value="">All Status</option>
                        @foreach(\App\Models\HallBooking::statuses() as $v => $l)
                            <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex;flex-direction:column;justify-content:flex-end;">
                    <div class="ef-rp-filter-btns-row">
                        <button type="submit" class="ef-btn"
                                style="background:var(--ef-gold);border-color:var(--ef-gold);color:#fffdfa;font-size:.8rem;padding:8px 16px;">
                            Apply
                        </button>
                        <a href="{{ route('hall.reports.index') }}" class="ef-btn"
                           style="font-size:.8rem;padding:8px 16px;">
                            Clear
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Period note ───────────────────────────────────────────── --}}
    @if($hasFilter || $bookings->count())
    <div style="margin-bottom:20px;">
        <span class="ef-rp-period-note">
            <i class="bi bi-funnel-fill"></i>
            @if(request('date_from') && request('date_to'))
                {{ \Carbon\Carbon::parse(request('date_from'))->format('d M Y') }}
                – {{ \Carbon\Carbon::parse(request('date_to'))->format('d M Y') }}
                ·
            @elseif(request('date_from'))
                From {{ \Carbon\Carbon::parse(request('date_from'))->format('d M Y') }} ·
            @endif
            {{ $summary['active_bookings'] }} {{ Str::plural('booking', $summary['active_bookings']) }}
            @if($summary['cancelled'])
                · {{ $summary['cancelled'] }} cancelled
            @endif
        </span>
    </div>
    @endif

    {{-- KPI strip ────────────────────────────────────────────── --}}
    <div class="ef-rp-kpis">
        <div class="ef-rp-kpi">
            <div class="ef-rp-kpi-label">Total Revenue</div>
            <div class="ef-rp-kpi-val --lg">₹{{ number_format($summary['total_revenue']) }}</div>
            <div class="ef-rp-kpi-note">{{ $summary['active_bookings'] }} active {{ Str::plural('booking', $summary['active_bookings']) }}</div>
        </div>
        <div class="ef-rp-kpi">
            <div class="ef-rp-kpi-label">Collected</div>
            <div class="ef-rp-kpi-val --lg">₹{{ number_format($summary['total_collected']) }}</div>
            <div class="ef-rp-kpi-note">of ₹{{ number_format($summary['total_revenue']) }}</div>
            <div class="ef-rp-kpi-bar"><div class="ef-rp-kpi-bar-fill" style="width:{{ $summary['collection_rate'] }}%"></div></div>
        </div>
        <div class="ef-rp-kpi">
            <div class="ef-rp-kpi-label">Pending Balance</div>
            <div class="ef-rp-kpi-val --lg" style="{{ $summary['total_balance'] > 0 ? 'color:var(--ef-danger)' : '' }}">
                ₹{{ number_format($summary['total_balance']) }}
            </div>
            <div class="ef-rp-kpi-note">Outstanding amount</div>
        </div>
        <div class="ef-rp-kpi">
            <div class="ef-rp-kpi-label">Total Bookings</div>
            <div class="ef-rp-kpi-val --lg">{{ $summary['total_bookings'] }}</div>
            <div class="ef-rp-kpi-note">{{ $summary['total_people'] ? number_format($summary['total_people']).' guests' : 'No data' }}</div>
        </div>
        <div class="ef-rp-kpi">
            <div class="ef-rp-kpi-label">Collection Rate</div>
            <div class="ef-rp-kpi-val --lg">{{ $summary['collection_rate'] }}<span style="font-size:.85rem;font-weight:640">%</span></div>
            <div class="ef-rp-kpi-note">Payment efficiency</div>
            <div class="ef-rp-kpi-bar"><div class="ef-rp-kpi-bar-fill" style="width:{{ $summary['collection_rate'] }}%"></div></div>
        </div>
        <div class="ef-rp-kpi">
            <div class="ef-rp-kpi-label">Avg per Event</div>
            <div class="ef-rp-kpi-val">₹{{ number_format($summary['avg_revenue']) }}</div>
            <div class="ef-rp-kpi-note">Average booking value</div>
        </div>
    </div>

    {{-- Analytics: chart + payment dist ─────────────────────── --}}
    <div class="ef-rp-analytics">

        {{-- Revenue trend chart --}}
        <div class="ef-rp-card">
            <div class="ef-rp-card-head">
                <span class="ef-rp-card-title">Revenue Trend</span>
                <span class="ef-rp-card-meta">Last 6 months · all bookings</span>
            </div>
            <div class="ef-rp-card-body">
                <div class="ef-rp-chart-wrap">
                    <canvas id="rpRevenueChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Payment distribution --}}
        <div class="ef-rp-card">
            <div class="ef-rp-card-head">
                <span class="ef-rp-card-title">Payment Status</span>
                <span class="ef-rp-card-meta">{{ $summary['active_bookings'] }} bookings</span>
            </div>
            @if($pdistTotal > 0)
            <div class="ef-rp-pdist">
                <div class="ef-rp-pdist-row">
                    <div class="ef-rp-pdist-top">
                        <span class="ef-rp-pdist-name">Paid</span>
                        <span class="ef-rp-pdist-meta">{{ $summary['pay_paid'] }} · {{ $paidPct }}%</span>
                    </div>
                    <div class="ef-rp-pdist-track">
                        <div class="ef-rp-pdist-fill --paid" style="width:{{ $paidPct }}%"></div>
                    </div>
                </div>
                <div class="ef-rp-pdist-row">
                    <div class="ef-rp-pdist-top">
                        <span class="ef-rp-pdist-name">Partial</span>
                        <span class="ef-rp-pdist-meta">{{ $summary['pay_partial'] }} · {{ $partialPct }}%</span>
                    </div>
                    <div class="ef-rp-pdist-track">
                        <div class="ef-rp-pdist-fill --partial" style="width:{{ $partialPct }}%"></div>
                    </div>
                </div>
                <div class="ef-rp-pdist-row">
                    <div class="ef-rp-pdist-top">
                        <span class="ef-rp-pdist-name">Pending</span>
                        <span class="ef-rp-pdist-meta">{{ $summary['pay_pending'] }} · {{ $pendingPct }}%</span>
                    </div>
                    <div class="ef-rp-pdist-track">
                        <div class="ef-rp-pdist-fill --pending" style="width:{{ $pendingPct }}%"></div>
                    </div>
                </div>

                {{-- Summary amounts --}}
                <div style="border-top:1px solid var(--ef-border);display:grid;gap:10px;grid-template-columns:repeat(3,minmax(0,1fr));padding-top:16px;">
                    <div style="text-align:center">
                        <div style="color:var(--ef-faint);font-size:.62rem;font-weight:760;letter-spacing:.1em;text-transform:uppercase">Paid</div>
                        <div style="color:#2a7a54;font-size:.96rem;font-variant-numeric:tabular-nums;font-weight:760;margin-top:6px;">{{ $summary['pay_paid'] }}</div>
                    </div>
                    <div style="text-align:center">
                        <div style="color:var(--ef-faint);font-size:.62rem;font-weight:760;letter-spacing:.1em;text-transform:uppercase">Partial</div>
                        <div style="color:#3050a0;font-size:.96rem;font-variant-numeric:tabular-nums;font-weight:760;margin-top:6px;">{{ $summary['pay_partial'] }}</div>
                    </div>
                    <div style="text-align:center">
                        <div style="color:var(--ef-faint);font-size:.62rem;font-weight:760;letter-spacing:.1em;text-transform:uppercase">Pending</div>
                        <div style="color:#8a6020;font-size:.96rem;font-variant-numeric:tabular-nums;font-weight:760;margin-top:6px;">{{ $summary['pay_pending'] }}</div>
                    </div>
                </div>
            </div>
            @else
            <div class="ef-rp-pdist-empty">No booking data</div>
            @endif
        </div>
    </div>

    {{-- Hall + Event performance ─────────────────────────────── --}}
    <div class="ef-rp-perf-grid">

        {{-- By hall --}}
        <div class="ef-rp-card">
            <div class="ef-rp-card-head">
                <span class="ef-rp-card-title">Hall Performance</span>
                <span class="ef-rp-card-meta">By revenue</span>
            </div>
            @if($summary['by_hall']->count())
            <div class="ef-rp-table-wrap">
                <table class="ef-rp-table">
                    <thead>
                        <tr>
                            <th>Hall</th>
                            <th class="r">Bookings</th>
                            <th class="r">Guests</th>
                            <th class="r">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summary['by_hall'] as $row)
                        @php $share = $maxRevenue > 0 ? round($row['revenue'] / $maxRevenue * 100) : 0; @endphp
                        <tr>
                            <td>
                                <div class="ef-rp-table-name">{{ $row['name'] }}</div>
                                <div class="ef-rp-share">
                                    <div class="ef-rp-share-fill" style="width:{{ $share }}%"></div>
                                </div>
                            </td>
                            <td class="r dim">{{ $row['count'] }}</td>
                            <td class="r dim mob-hide">{{ number_format($row['people']) }}</td>
                            <td class="r mono" style="font-weight:680">₹{{ number_format($row['revenue']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="ef-rp-card-body" style="color:var(--ef-faint);font-size:.82rem;text-align:center;padding:32px;">No data</div>
            @endif
        </div>

        {{-- By event type --}}
        <div class="ef-rp-card">
            <div class="ef-rp-card-head">
                <span class="ef-rp-card-title">Event Type Breakdown</span>
                <span class="ef-rp-card-meta">By revenue</span>
            </div>
            @if($summary['by_event']->count())
            <div class="ef-rp-table-wrap">
                <table class="ef-rp-table">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th class="r">Bookings</th>
                            <th class="r">Share</th>
                            <th class="r">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summary['by_event'] as $row)
                        @php $share = $maxEventRev > 0 ? round($row['revenue'] / $maxEventRev * 100) : 0;
                             $pct   = $summary['total_revenue'] > 0 ? round($row['revenue'] / $summary['total_revenue'] * 100) : 0;
                        @endphp
                        <tr>
                            <td>
                                <div class="ef-rp-table-name">{{ $row['label'] }}</div>
                                <div class="ef-rp-share">
                                    <div class="ef-rp-share-fill" style="width:{{ $share }}%"></div>
                                </div>
                            </td>
                            <td class="r dim">{{ $row['count'] }}</td>
                            <td class="r dim mob-hide">{{ $pct }}%</td>
                            <td class="r mono" style="font-weight:680">₹{{ number_format($row['revenue']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="ef-rp-card-body" style="color:var(--ef-faint);font-size:.82rem;text-align:center;padding:32px;">No data</div>
            @endif
        </div>
    </div>

    {{-- Booking detail table ─────────────────────────────────── --}}
    <div class="ef-rp-card">
        <div class="ef-rp-card-head">
            <span class="ef-rp-card-title">Booking Details</span>
            <span class="ef-rp-card-meta">{{ $bookings->count() }} {{ Str::plural('record', $bookings->count()) }}</span>
        </div>

        @if($bookings->isNotEmpty())
        <div class="ef-rp-table-wrap">
            <table class="ef-rp-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th class="mob-hide">Hall</th>
                        <th class="mob-hide">Event</th>
                        <th>Date</th>
                        <th class="r mob-hide">Guests</th>
                        <th class="r">Total</th>
                        <th class="r mob-hide">Collected</th>
                        <th class="r mob-hide">Balance</th>
                        <th>Payment</th>
                        <th class="mob-hide">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $b)
                    @php
                        $coll    = $b->payments->sum('amount');
                        $bal     = max(0, $b->total_amount - $coll);
                        $collPct = $b->total_amount > 0 ? min(100, round($coll / $b->total_amount * 100)) : 0;
                    @endphp
                    <tr class="clickable"
                        onclick="location.href='{{ route('hall.bookings.show', $b) }}'">
                        <td>
                            <div class="ef-rp-table-name">{{ $b->customer_name }}</div>
                            <div class="ef-rp-table-sub">{{ $b->customer_mobile }}</div>
                        </td>
                        <td class="dim mob-hide">{{ $b->hall->name }}</td>
                        <td class="dim mob-hide">
                            {{ \App\Models\HallBooking::eventTypes()[$b->event_type] ?? ucwords(str_replace('_', ' ', $b->event_type)) }}
                        </td>
                        <td class="dim">{{ $b->booking_date->format('d M Y') }}</td>
                        <td class="r dim mob-hide">{{ number_format($b->number_of_people) }}</td>
                        <td class="r mono" style="font-weight:680">₹{{ number_format($b->total_amount) }}</td>
                        <td class="r mob-hide">
                            <div style="color:#2a7a54;font-variant-numeric:tabular-nums;font-size:.82rem;">
                                ₹{{ number_format($coll) }}
                            </div>
                            <div class="ef-rp-coll-bar">
                                <div class="ef-rp-coll-fill" style="width:{{ $collPct }}%"></div>
                            </div>
                        </td>
                        <td class="r mob-hide" style="{{ $bal > 0 ? 'color:var(--ef-danger)' : 'color:var(--ef-muted)' }};font-variant-numeric:tabular-nums;font-size:.82rem;">
                            ₹{{ number_format($bal) }}
                        </td>
                        <td>
                            <span class="ef-rp-chip --{{ $b->payment_status }}">
                                {{ \App\Models\HallBooking::paymentStatuses()[$b->payment_status] ?? $b->payment_status }}
                            </span>
                        </td>
                        <td class="mob-hide">
                            <span class="ef-rp-chip --{{ $b->status }}">{{ $b->status }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="ef-rp-card-body">
            <div style="color:var(--ef-faint);font-size:.84rem;padding:32px 0;text-align:center;">
                <i class="bi bi-table d-block mb-2" style="font-size:1.8rem;opacity:.4"></i>
                No bookings match the current filter selection.
            </div>
        </div>
        @endif
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels  = @json($chartLabels);
    const revenue = @json($chartRevenue);
    const counts  = @json($chartCounts);

    const ctx = document.getElementById('rpRevenueChart');
    if (!ctx) return;

    const fmt = (v) => {
        if (v >= 100000) return '₹' + (v / 100000).toFixed(1) + 'L';
        if (v >= 1000)   return '₹' + (v / 1000).toFixed(0) + 'K';
        return '₹' + v;
    };

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Revenue',
                data: revenue,
                backgroundColor: 'rgba(160, 114, 56, 0.68)',
                borderColor: 'rgba(160, 114, 56, 0.88)',
                borderWidth: 1.5,
                borderRadius: 6,
                borderSkipped: false,
                hoverBackgroundColor: 'rgba(160, 114, 56, 0.86)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(20, 20, 18, 0.88)',
                    titleColor: '#fffdfa',
                    bodyColor: 'rgba(255, 253, 250, .7)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: (ctx) => ' ₹' + ctx.raw.toLocaleString('en-IN'),
                        afterLabel: (ctx) => ' ' + counts[ctx.dataIndex] + ' bookings',
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: 'rgba(20,20,18,.38)', font: { size: 11 } }
                },
                y: {
                    grid: { color: 'rgba(20,20,18,.055)', drawBorder: false },
                    border: { display: false, dash: [4, 4] },
                    ticks: {
                        color: 'rgba(20,20,18,.38)',
                        font: { size: 11 },
                        callback: fmt,
                        maxTicksLimit: 5,
                    }
                }
            }
        }
    });
})();

// Clean empty form fields before submit
document.getElementById('rpFilterForm').addEventListener('submit', function () {
    this.querySelectorAll('input, select').forEach(el => {
        if (!el.value) el.disabled = true;
    });
});
</script>
@endpush

</x-admin-layout>
