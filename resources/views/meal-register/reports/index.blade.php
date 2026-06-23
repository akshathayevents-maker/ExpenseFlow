<x-admin-layout title="Corporate Meal Reports">
@push('styles')
<style>
/* == Meal Reports Dashboard - mr-* == */
:root {
    --mr-gold:    #a0763a;
    --mr-gold-hi: #b8882a;
    --mr-ink:     #131110;
    --mr-sub:     #50473f;
    --mr-muted:   #8a827a;
    --mr-faint:   #bab3aa;
    --mr-border:  rgba(100,82,42,.12);
    --mr-border-s:rgba(100,82,42,.24);
    --mr-surface: #ffffff;
    --mr-cream:   #faf8f3;
    --mr-r:       12px;
    --mr-r-sm:    8px;
    --mr-shadow:  0 1px 3px rgba(18,14,8,.05), 0 2px 8px rgba(18,14,8,.04);

    --mr-green:  #16a34a;
    --mr-red:    #dc2626;
    --mr-blue:   #2563eb;
    --mr-amber:  #d97706;
}

*, *::before, *::after { box-sizing: border-box; }

.mr-wrap { max-width: 1200px; margin: 0 auto; padding-bottom: 80px; }

/* Page header */
.mr-page-hdr {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 14px;
    flex-wrap: wrap;
}
.mr-page-title {
    font-size: 1.2rem;
    font-weight: 900;
    color: var(--mr-ink);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.mr-page-title i { color: var(--mr-gold); }
.mr-today-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--mr-cream);
    border: 1.5px solid var(--mr-border-s);
    border-radius: 100px;
    padding: 5px 12px;
    font-size: .74rem;
    font-weight: 700;
    color: var(--mr-sub);
    white-space: nowrap;
}
.mr-today-pill strong { color: var(--mr-ink); }

/* Filter bar */
.mr-filter {
    background: var(--mr-surface);
    border: 1.5px solid var(--mr-border);
    border-radius: var(--mr-r);
    padding: 12px 14px;
    margin-bottom: 14px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: flex-end;
    box-shadow: var(--mr-shadow);
}
.mr-ff { display: flex; flex-direction: column; gap: 4px; min-width: 120px; flex: 1; }
.mr-ff-label {
    font-size: .64rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--mr-faint);
}
.mr-input, .mr-select {
    height: 38px;
    padding: 0 10px;
    border: 1.5px solid var(--mr-border);
    border-radius: var(--mr-r-sm);
    font-size: .83rem;
    color: var(--mr-ink);
    background: var(--mr-surface);
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    width: 100%;
}
.mr-input:focus, .mr-select:focus { border-color: var(--mr-gold); }
.mr-filter-btns { display: flex; gap: 6px; align-items: flex-end; flex-shrink: 0; }
.mr-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    height: 38px;
    padding: 0 14px;
    border-radius: var(--mr-r-sm);
    font-size: .8rem;
    font-weight: 700;
    border: 1.5px solid transparent;
    cursor: pointer;
    text-decoration: none;
    transition: all .13s;
    white-space: nowrap;
}
.mr-btn--primary { background: var(--mr-gold); color: #fff; border-color: var(--mr-gold); }
.mr-btn--primary:hover { background: var(--mr-gold-hi); color: #fff; text-decoration: none; }
.mr-btn--ghost { background: none; border-color: var(--mr-border-s); color: var(--mr-muted); }
.mr-btn--ghost:hover { border-color: var(--mr-ink); color: var(--mr-ink); text-decoration: none; }
.mr-btn--green { background: #16a34a; color: #fff; border-color: #16a34a; }
.mr-btn--green:hover { background: #15803d; color: #fff; text-decoration: none; }

/* Preset chips */
.mr-chips {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}
.mr-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    height: 32px;
    padding: 0 12px;
    border-radius: 100px;
    border: 1.5px solid var(--mr-border-s);
    background: var(--mr-surface);
    color: var(--mr-sub);
    font-size: .76rem;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    transition: all .13s;
    white-space: nowrap;
}
.mr-chip:hover { border-color: var(--mr-gold); color: var(--mr-gold); text-decoration: none; }
.mr-chip.--active { background: var(--mr-ink); border-color: var(--mr-ink); color: #fff; }
.mr-chip.--active:hover { background: #2d2820; text-decoration: none; }

/* KPI row */
.mr-kpis {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0;
    background: var(--mr-surface);
    border: 1.5px solid var(--mr-border);
    border-radius: var(--mr-r);
    overflow: hidden;
    margin-bottom: 14px;
    box-shadow: var(--mr-shadow);
}
.mr-kpi {
    padding: 14px 16px;
    position: relative;
    text-align: center;
}
.mr-kpi + .mr-kpi::before {
    content: '';
    position: absolute;
    left: 0; top: 18%; bottom: 18%;
    width: 1px;
    background: var(--mr-border);
}
.mr-kpi-icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: var(--mr-gold);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .8rem;
    margin: 0 auto 8px;
}
.mr-kpi-val {
    font-size: 1.6rem;
    font-weight: 900;
    color: var(--mr-ink);
    letter-spacing: -.03em;
    line-height: 1;
}
.mr-kpi-label {
    font-size: .63rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--mr-faint);
    margin-top: 4px;
}
.mr-kpi-sub {
    font-size: .7rem;
    color: var(--mr-muted);
    margin-top: 2px;
}
.mr-kpi-val.--green  { color: var(--mr-green); }
.mr-kpi-val.--red    { color: var(--mr-red); }
.mr-kpi-val.--amber  { color: var(--mr-amber); }
.mr-kpi-val.--gold   { color: var(--mr-gold); }

/* Section header */
.mr-sec-hdr {
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--mr-faint);
    margin: 16px 0 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.mr-sec-hdr::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--mr-border);
}

/* Meal type cards */
.mr-meal-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 4px;
}
.mr-meal-card {
    background: var(--mr-surface);
    border: 1.5px solid var(--mr-border);
    border-radius: var(--mr-r);
    padding: 14px;
    box-shadow: var(--mr-shadow);
    display: flex;
    flex-direction: column;
    gap: 10px;
    position: relative;
    overflow: hidden;
}
.mr-meal-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
}
.mr-meal-card.--over::before   { background: var(--mr-green); }
.mr-meal-card.--under::before  { background: var(--mr-red); }
.mr-meal-card.--equal::before  { background: var(--mr-border-s); }
.mr-meal-card.--empty::before  { background: var(--mr-border-s); }

.mr-meal-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.mr-meal-title {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: .82rem;
    font-weight: 800;
    color: var(--mr-ink);
}
.mr-meal-icon { font-size: 1.1rem; }
.mr-meal-vbadge {
    font-size: .66rem;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 100px;
    white-space: nowrap;
}
.mr-meal-vbadge.--over  { background: rgba(22,163,74,.1);  color: var(--mr-green); }
.mr-meal-vbadge.--under { background: rgba(220,38,38,.1);  color: var(--mr-red); }
.mr-meal-vbadge.--equal { background: rgba(0,0,0,.05);     color: var(--mr-muted); }

.mr-meal-nums {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0;
    border: 1px solid var(--mr-border);
    border-radius: var(--mr-r-sm);
    overflow: hidden;
}
.mr-meal-num {
    padding: 8px 6px;
    text-align: center;
    border-right: 1px solid var(--mr-border);
    position: relative;
}
.mr-meal-num:last-child { border-right: none; }
.mr-meal-num-val {
    font-size: 1.05rem;
    font-weight: 900;
    color: var(--mr-ink);
    line-height: 1;
}
.mr-meal-num-val.--over  { color: var(--mr-green); }
.mr-meal-num-val.--under { color: var(--mr-red); }
.mr-meal-num-lbl {
    font-size: .6rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--mr-faint);
    margin-top: 3px;
}

/* Progress bar */
.mr-prog-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}
.mr-prog-bg {
    flex: 1;
    height: 5px;
    background: var(--mr-cream);
    border-radius: 3px;
    overflow: hidden;
}
.mr-prog-fill {
    height: 100%;
    border-radius: 3px;
    transition: width .3s;
}
.mr-prog-fill.--over  { background: var(--mr-green); }
.mr-prog-fill.--under { background: var(--mr-red); }
.mr-prog-fill.--equal { background: var(--mr-faint); }
.mr-prog-pct {
    font-size: .68rem;
    font-weight: 800;
    color: var(--mr-muted);
    flex-shrink: 0;
    min-width: 34px;
    text-align: right;
}

/* Top variance callouts */
.mr-callout-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 4px;
}
.mr-callout {
    background: var(--mr-surface);
    border: 1.5px solid var(--mr-border);
    border-radius: var(--mr-r);
    padding: 12px 14px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    box-shadow: var(--mr-shadow);
}
.mr-callout-ico {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .85rem;
    flex-shrink: 0;
    color: #fff;
}
.mr-callout-ico.--pos { background: var(--mr-green); }
.mr-callout-ico.--neg { background: var(--mr-red); }
.mr-callout-ico.--none { background: var(--mr-border-s); color: var(--mr-faint); }
.mr-callout-label {
    font-size: .64rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--mr-faint);
    margin-bottom: 2px;
}
.mr-callout-name {
    font-size: .85rem;
    font-weight: 800;
    color: var(--mr-ink);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 160px;
}
.mr-callout-val {
    font-size: .78rem;
    font-weight: 700;
    margin-top: 2px;
}
.mr-callout-val.--pos { color: var(--mr-green); }
.mr-callout-val.--neg { color: var(--mr-red); }

/* Client performance table */
.mr-card {
    background: var(--mr-surface);
    border: 1.5px solid var(--mr-border);
    border-radius: var(--mr-r);
    box-shadow: var(--mr-shadow);
    overflow: hidden;
}
.mr-card-hdr {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 11px 14px;
    background: var(--mr-cream);
    border-bottom: 1px solid var(--mr-border);
}
.mr-card-title {
    font-size: .76rem;
    font-weight: 800;
    color: var(--mr-ink);
    text-transform: uppercase;
    letter-spacing: .05em;
}
.mr-card-aside { font-size: .72rem; color: var(--mr-muted); font-weight: 600; }
.mr-table {
    width: 100%;
    border-collapse: collapse;
}
.mr-table th {
    font-size: .62rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--mr-faint);
    padding: 8px 14px;
    background: var(--mr-cream);
    border-bottom: 1px solid var(--mr-border);
    text-align: left;
    white-space: nowrap;
}
.mr-table td {
    padding: 10px 14px;
    border-bottom: 1px solid var(--mr-border);
    font-size: .82rem;
    color: var(--mr-ink);
    vertical-align: middle;
}
.mr-table tr:last-child td { border-bottom: none; }
.mr-table tr:hover td { background: rgba(160,118,58,.025); }
.mr-table .mr-client-name { font-weight: 800; }
.mr-table .mr-days { font-size: .72rem; color: var(--mr-muted); }
.mr-var-chip {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: .72rem;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 6px;
    white-space: nowrap;
}
.mr-var-chip.--over  { background: rgba(22,163,74,.1);  color: var(--mr-green); }
.mr-var-chip.--under { background: rgba(220,38,38,.1);  color: var(--mr-red); }
.mr-var-chip.--equal { background: rgba(0,0,0,.05);     color: var(--mr-muted); }

.mr-bar-cell { min-width: 80px; }
.mr-tbl-prog-bg {
    height: 4px;
    background: var(--mr-cream);
    border-radius: 2px;
    overflow: hidden;
    margin-top: 4px;
}
.mr-tbl-prog-fill {
    height: 100%;
    border-radius: 2px;
}
.mr-tbl-prog-fill.--over  { background: var(--mr-green); }
.mr-tbl-prog-fill.--under { background: var(--mr-red); }
.mr-tbl-prog-fill.--equal { background: var(--mr-faint); }

/* Empty state */
.mr-empty {
    text-align: center;
    padding: 36px 16px;
    color: var(--mr-faint);
    font-size: .85rem;
}
.mr-empty-ico { font-size: 2rem; margin-bottom: 8px; opacity: .4; }

/* Responsive */
@media (max-width: 640px) {
    .mr-kpis { grid-template-columns: repeat(2, 1fr); }
    .mr-kpi + .mr-kpi::before { display: none; }
    .mr-kpi:nth-child(odd) { border-right: 1px solid var(--mr-border); }
    .mr-kpi:nth-child(1), .mr-kpi:nth-child(2) { border-bottom: 1px solid var(--mr-border); }
    .mr-meal-grid { grid-template-columns: repeat(2, 1fr); }
    .mr-callout-row { grid-template-columns: 1fr; }
    .mr-table th:nth-child(n+4), .mr-table td:nth-child(n+4) { display: none; }
    .mr-filter { flex-direction: column; }
    .mr-ff { min-width: 100%; }
}
@media (min-width: 641px) and (max-width: 900px) {
    .mr-kpis { grid-template-columns: repeat(4, 1fr); }
    .mr-meal-grid { grid-template-columns: repeat(3, 1fr); }
}
</style>
@endpush

@php
// Preset chip active states
$chipToday  = ($preset === 'today')  || (request('from') === now()->toDateString() && request('to') === now()->toDateString() && !$preset);
$chipWeek   = $preset === 'week';
$chipMonth  = $preset === 'month';

// Variance helpers (inline closures)
$varClass = function(int $v): string {
    if ($v > 0) return '--over';
    if ($v < 0) return '--under';
    return '--equal';
};
$varSign = function(int $v): string {
    return $v > 0 ? '+' . number_format($v) : number_format($v);
};
$pct = function(int $actual, int $planned): int {
    return $planned > 0 ? min(200, (int) round(($actual / $planned) * 100)) : ($actual > 0 ? 100 : 0);
};
$pctStr = function(int $actual, int $planned) use ($pct): string {
    return $pct($actual, $planned) . '%';
};
$totalVariance = $grandActual - $grandPlanned;

// Export URL preserving current filters
$exportParams = array_filter(request()->only(['from','to','client_id','meal_type','preset']));
@endphp

<div class="mr-wrap">

    {{-- == Page header == --}}
    <div class="mr-page-hdr">
        <h1 class="mr-page-title">
            <i class="bi bi-bar-chart-line-fill"></i>
            Meal Reports
        </h1>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <div class="mr-today-pill">
                <i class="bi bi-calendar3" style="color:var(--mr-gold)"></i>
                Today: <strong>{{ $todayPlanned }}</strong> planned · <strong>{{ $todayActual }}</strong> actual
            </div>
            <a href="{{ route('meal-register.reports.export', $exportParams) }}" class="mr-btn mr-btn--green">
                <i class="bi bi-file-earmark-excel"></i> Export
            </a>
        </div>
    </div>

    {{-- == Preset chips == --}}
    <div class="mr-chips">
        <a href="{{ route('meal-register.reports.index', ['preset' => 'today']) }}"
           class="mr-chip {{ $chipToday ? '--active' : '' }}">
           <i class="bi bi-sun"></i> Today
        </a>
        <a href="{{ route('meal-register.reports.index', ['preset' => 'week']) }}"
           class="mr-chip {{ $chipWeek ? '--active' : '' }}">
           <i class="bi bi-calendar-week"></i> This Week
        </a>
        <a href="{{ route('meal-register.reports.index', ['preset' => 'month']) }}"
           class="mr-chip {{ $chipMonth ? '--active' : '' }}">
           <i class="bi bi-calendar-month"></i> This Month
        </a>
        @if(request()->hasAny(['from','to','client_id','meal_type','preset']))
        <a href="{{ route('meal-register.reports.index') }}" class="mr-chip" style="border-color:rgba(220,38,38,.3);color:#dc2626">
            <i class="bi bi-x"></i> Clear
        </a>
        @endif
    </div>

    {{-- == Filter bar == --}}
    <form method="GET" action="{{ route('meal-register.reports.index') }}" class="mr-filter">
        <div class="mr-ff">
            <label class="mr-ff-label">Client</label>
            <select name="client_id" class="mr-select">
                <option value="">All clients</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mr-ff" style="max-width:145px">
            <label class="mr-ff-label">From</label>
            <input type="date" name="from" class="mr-input" value="{{ $from }}">
        </div>
        <div class="mr-ff" style="max-width:145px">
            <label class="mr-ff-label">To</label>
            <input type="date" name="to" class="mr-input" value="{{ $to }}">
        </div>
        <div class="mr-ff" style="max-width:140px">
            <label class="mr-ff-label">Meal Type</label>
            <select name="meal_type" class="mr-select">
                <option value="">All meals</option>
                @foreach($mealTypes as $key => $meta)
                    <option value="{{ $key }}" {{ $filterMealType === $key ? 'selected' : '' }}>{{ $meta['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="mr-filter-btns">
            <button type="submit" class="mr-btn mr-btn--primary"><i class="bi bi-funnel"></i> Apply</button>
            <a href="{{ route('meal-register.reports.index') }}" class="mr-btn mr-btn--ghost">Reset</a>
        </div>
    </form>

    {{-- == KPI row == --}}
    <div class="mr-kpis">
        <div class="mr-kpi">
            <div class="mr-kpi-icon"><i class="bi bi-calendar-check"></i></div>
            <div class="mr-kpi-val --gold">{{ number_format($grandPlanned) }}</div>
            <div class="mr-kpi-label">Planned Meals</div>
            <div class="mr-kpi-sub">{{ $from === $to ? $from : $from . ' – ' . $to }}</div>
        </div>
        <div class="mr-kpi">
            <div class="mr-kpi-icon" style="background:var(--mr-green)"><i class="bi bi-check2-circle"></i></div>
            <div class="mr-kpi-val --green">{{ number_format($grandActual) }}</div>
            <div class="mr-kpi-label">Actual Meals</div>
            @php $grandPct = $grandPlanned > 0 ? round(($grandActual / $grandPlanned) * 100) : 0; @endphp
            <div class="mr-kpi-sub">{{ $grandPct }}% of planned</div>
        </div>
        <div class="mr-kpi">
            <div class="mr-kpi-icon" style="background:{{ $totalVariance >= 0 ? 'var(--mr-green)' : 'var(--mr-red)' }}">
                <i class="bi {{ $totalVariance >= 0 ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
            </div>
            @php $varAbsStr = ($totalVariance >= 0 ? '+' : '') . number_format($totalVariance); @endphp
            <div class="mr-kpi-val {{ $totalVariance > 0 ? '--green' : ($totalVariance < 0 ? '--red' : '') }}">{{ $varAbsStr }}</div>
            <div class="mr-kpi-label">Variance</div>
            <div class="mr-kpi-sub">{{ $totalVariance > 0 ? 'over served' : ($totalVariance < 0 ? 'short served' : 'on target') }}</div>
        </div>
        <div class="mr-kpi">
            <div class="mr-kpi-icon" style="background:var(--mr-blue)"><i class="bi bi-building"></i></div>
            <div class="mr-kpi-val" style="color:var(--mr-blue)">{{ $clientsServed }}</div>
            <div class="mr-kpi-label">Clients Served</div>
            <div class="mr-kpi-sub">{{ $entries->count() }} {{ Str::plural('entry', $entries->count()) }}</div>
        </div>
    </div>

    @if($entries->isEmpty())
        <div class="mr-card">
            <div class="mr-empty">
                <div class="mr-empty-ico"><i class="bi bi-inbox"></i></div>
                No data for selected range. Try adjusting filters.
            </div>
        </div>
    @else

    {{-- == Meal type breakdown == --}}
    <div class="mr-sec-hdr">Meal Type Breakdown</div>
    <div class="mr-meal-grid">
        @foreach($mealTypes as $key => $meta)
        @php
            $mp = $mealTypeTotals[$key]['planned'] ?? 0;
            $ma = $mealTypeTotals[$key]['actual']  ?? 0;
            $mv = $ma - $mp;
            $mvc = $mv > 0 ? '--over' : ($mv < 0 ? '--under' : '--equal');
            $mvStr = ($mv > 0 ? '+' : '') . number_format($mv);
            $mpct  = $mp > 0 ? min(200, (int)round($ma / $mp * 100)) : ($ma > 0 ? 100 : 0);
            $mpctStr = $mpct . '%';
            $pfillClass = $mv > 0 ? '--over' : ($mv < 0 ? '--under' : '--equal');
            $fillW = min(100, $mpct) . '%';
            $cardClass = ($mp === 0 && $ma === 0) ? '--empty' : $mvc;
        @endphp
        <div class="mr-meal-card {{ $cardClass }}">
            <div class="mr-meal-top">
                <div class="mr-meal-title">
                    <span class="mr-meal-icon">{{ $meta['icon'] }}</span>
                    {{ $meta['label'] }}
                </div>
                @if($mp > 0 || $ma > 0)
                <span class="mr-meal-vbadge {{ $mvc }}">{{ $mvStr }}</span>
                @endif
            </div>
            <div class="mr-meal-nums">
                <div class="mr-meal-num">
                    <div class="mr-meal-num-val">{{ number_format($mp) }}</div>
                    <div class="mr-meal-num-lbl">Planned</div>
                </div>
                <div class="mr-meal-num">
                    <div class="mr-meal-num-val {{ $mvc }}">{{ number_format($ma) }}</div>
                    <div class="mr-meal-num-lbl">Actual</div>
                </div>
                <div class="mr-meal-num">
                    <div class="mr-meal-num-val {{ $mvc }}">{{ $mvStr }}</div>
                    <div class="mr-meal-num-lbl">Diff</div>
                </div>
            </div>
            @if($mp > 0)
            <div class="mr-prog-wrap">
                <div class="mr-prog-bg">
                    <div class="mr-prog-fill {{ $pfillClass }}" style="width:{{ $fillW }}"></div>
                </div>
                <div class="mr-prog-pct">{{ $mpctStr }}</div>
            </div>
            @else
            <div style="font-size:.7rem;color:var(--mr-faint);text-align:center">No data</div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- == Top variance callouts == --}}
    @if($topPositive || $topNegative)
    <div class="mr-sec-hdr">Top Variance</div>
    <div class="mr-callout-row">
        <div class="mr-callout">
            <div class="mr-callout-ico {{ $topPositive ? '--pos' : '--none' }}">
                <i class="bi bi-arrow-up-circle-fill"></i>
            </div>
            <div style="min-width:0">
                <div class="mr-callout-label">Highest Over-Serve</div>
                @if($topPositive)
                    <div class="mr-callout-name" title="{{ $topPositive['name'] }}">{{ $topPositive['name'] }}</div>
                    <div class="mr-callout-val --pos">+{{ number_format($topPositive['variance']) }} meals over planned</div>
                @else
                    <div class="mr-callout-name" style="color:var(--mr-faint)">None</div>
                @endif
            </div>
        </div>
        <div class="mr-callout">
            <div class="mr-callout-ico {{ $topNegative ? '--neg' : '--none' }}">
                <i class="bi bi-arrow-down-circle-fill"></i>
            </div>
            <div style="min-width:0">
                <div class="mr-callout-label">Largest Shortfall</div>
                @if($topNegative)
                    <div class="mr-callout-name" title="{{ $topNegative['name'] }}">{{ $topNegative['name'] }}</div>
                    <div class="mr-callout-val --neg">{{ number_format($topNegative['variance']) }} meals short</div>
                @else
                    <div class="mr-callout-name" style="color:var(--mr-faint)">None</div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- == Client performance == --}}
    <div class="mr-sec-hdr">Client Performance</div>
    <div class="mr-card">
        <div class="mr-card-hdr">
            <span class="mr-card-title">All Clients · {{ \Carbon\Carbon::parse($from)->format('d M') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</span>
            <span class="mr-card-aside">{{ count($clientPerf) }} {{ Str::plural('client', count($clientPerf)) }}</span>
        </div>
        @if(empty($clientPerf))
            <div class="mr-empty">No client data.</div>
        @else
        <div style="overflow-x:auto">
        <table class="mr-table">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Days</th>
                    <th style="text-align:right">Planned</th>
                    <th style="text-align:right">Actual</th>
                    <th>Variance</th>
                    <th class="mr-bar-cell">Fulfillment</th>
                    @foreach($mealTypes as $key => $meta)
                        <th style="text-align:right">{{ $meta['icon'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @foreach($clientPerf as $row)
            @php
                $rv  = $row['variance'];
                $rvc = $rv > 0 ? '--over' : ($rv < 0 ? '--under' : '--equal');
                $rvs = ($rv > 0 ? '+' : '') . number_format($rv);
                $rpct = $row['planned'] > 0 ? min(200, (int)round($row['actual'] / $row['planned'] * 100)) : ($row['actual'] > 0 ? 100 : 0);
                $rpctStr = $rpct . '%';
                $rfillW  = min(100, $rpct) . '%';
            @endphp
            <tr>
                <td>
                    <div class="mr-client-name">{{ $row['name'] }}</div>
                </td>
                <td><span class="mr-days">{{ $row['days'] }}d</span></td>
                <td style="text-align:right;font-weight:700">{{ number_format($row['planned']) }}</td>
                <td style="text-align:right;font-weight:700">{{ number_format($row['actual']) }}</td>
                <td>
                    <span class="mr-var-chip {{ $rvc }}">{{ $rvs }}</span>
                </td>
                <td class="mr-bar-cell">
                    <div style="font-size:.72rem;font-weight:700;color:var(--mr-muted)">{{ $rpctStr }}</div>
                    <div class="mr-tbl-prog-bg">
                        <div class="mr-tbl-prog-fill {{ $rvc }}" style="width:{{ $rfillW }}"></div>
                    </div>
                </td>
                @foreach($mealTypes as $key => $meta)
                @php
                    $tp = $row['types'][$key]['planned'] ?? 0;
                    $ta = $row['types'][$key]['actual']  ?? 0;
                    $td = $ta - $tp;
                    $tdc = $td > 0 ? 'color:var(--mr-green)' : ($td < 0 ? 'color:var(--mr-red)' : 'color:var(--mr-faint)');
                @endphp
                <td style="text-align:right">
                    <div style="font-size:.8rem;font-weight:700">{{ $tp > 0 || $ta > 0 ? number_format($ta) : '-' }}</div>
                    @if($tp > 0 || $ta > 0)
                    <div style="font-size:.67rem;{{ $tdc }}">{{ ($td > 0 ? '+' : '') . ($td !== 0 ? number_format($td) : '-') }}</div>
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr style="background:var(--mr-cream)">
                    <td colspan="2" style="font-weight:800;font-size:.78rem;color:var(--mr-sub);padding:10px 14px">Total</td>
                    <td style="text-align:right;font-weight:900;padding:10px 14px">{{ number_format($grandPlanned) }}</td>
                    <td style="text-align:right;font-weight:900;padding:10px 14px">{{ number_format($grandActual) }}</td>
                    <td style="padding:10px 14px">
                        <span class="mr-var-chip {{ $totalVariance > 0 ? '--over' : ($totalVariance < 0 ? '--under' : '--equal') }}">
                            {{ ($totalVariance > 0 ? '+' : '') . number_format($totalVariance) }}
                        </span>
                    </td>
                    <td style="padding:10px 14px">
                        @php $gp2 = $grandPlanned > 0 ? min(200, (int)round($grandActual / $grandPlanned * 100)) : 0; @endphp
                        <div style="font-size:.72rem;font-weight:800;color:var(--mr-muted)">{{ $gp2 }}%</div>
                        <div class="mr-tbl-prog-bg">
                            <div class="mr-tbl-prog-fill {{ $totalVariance >= 0 ? '--over' : '--under' }}" style="width:{{ min(100,$gp2) }}%"></div>
                        </div>
                    </td>
                    @foreach($mealTypes as $key => $meta)
                    @php
                        $fp = $mealTypeTotals[$key]['planned'] ?? 0;
                        $fa = $mealTypeTotals[$key]['actual']  ?? 0;
                        $fd = $fa - $fp;
                        $fdc = $fd > 0 ? 'color:var(--mr-green)' : ($fd < 0 ? 'color:var(--mr-red)' : 'color:var(--mr-faint)');
                    @endphp
                    <td style="text-align:right;padding:10px 14px">
                        <div style="font-size:.8rem;font-weight:800">{{ number_format($fa) }}</div>
                        <div style="font-size:.67rem;{{ $fdc }}">{{ ($fd > 0 ? '+' : '') . number_format($fd) }}</div>
                    </td>
                    @endforeach
                </tr>
            </tfoot>
        </table>
        </div>
        @endif
    </div>

    @endif {{-- end if entries not empty --}}

</div>
</x-admin-layout>
