<x-admin-layout title="Analytics">

@push('styles')
@verbatim
<style>
/* ═══════════════════════════════════════════════════════
   Mobile Analytics — ef-man-* namespace
   Desktop view unchanged. Mobile overlay shown ≤ 767px.
   ═══════════════════════════════════════════════════════ */

.ef-man-view    { display: none; }
.ef-an-desktop-view { display: block; }

@media (max-width: 767.98px) {
    .ef-an-desktop-view { display: none !important; }
    .ef-man-view        { display: block; padding-bottom: 96px; }

/* ── Hero ─────────────────────────────────────────────── */
.ef-man-hero {
    background: linear-gradient(148deg, #0f1410 0%, #192219 52%, #0d110b 100%);
    border-radius: 18px;
    margin-bottom: 10px;
    overflow: hidden;
    padding: 18px 18px 16px;
    position: relative;
}
.ef-man-hero::before {
    background: radial-gradient(ellipse at 75% 15%, rgba(184,137,62,.13) 0%, transparent 58%),
                radial-gradient(ellipse at 20% 80%, rgba(61,92,58,.1) 0%, transparent 50%);
    content: '';
    inset: 0;
    pointer-events: none;
    position: absolute;
}
.ef-man-hero-toprow {
    align-items: center;
    display: flex;
    justify-content: space-between;
    margin-bottom: 18px;
    position: relative;
}
.ef-man-hero-eyebrow {
    align-items: center;
    color: rgba(245,240,232,.38);
    display: flex;
    font-size: .59rem;
    font-weight: 760;
    gap: 6px;
    letter-spacing: .13em;
    text-transform: uppercase;
}
.ef-man-hero-inv-btn {
    align-items: center;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 20px;
    color: rgba(245,240,232,.62);
    display: inline-flex;
    font-size: .67rem;
    font-weight: 700;
    gap: 5px;
    padding: 4px 11px;
    text-decoration: none;
    transition: background .14s, color .14s;
}
.ef-man-hero-inv-btn:hover { background: rgba(255,255,255,.12); color: rgba(245,240,232,.88); }

.ef-man-hero-lbl {
    color: rgba(245,240,232,.34);
    font-size: .58rem;
    font-weight: 760;
    letter-spacing: .13em;
    margin-bottom: 5px;
    position: relative;
    text-transform: uppercase;
}
.ef-man-hero-amount {
    align-items: flex-start;
    display: flex;
    gap: 2px;
    line-height: 1;
    margin-bottom: 7px;
    position: relative;
}
.ef-man-hero-currency {
    color: rgba(184,137,62,.72);
    font-size: 1.55rem;
    font-weight: 700;
    padding-top: 6px;
}
.ef-man-hero-num {
    color: rgba(245,240,232,.97);
    font-size: 3rem;
    font-variant-numeric: tabular-nums;
    font-weight: 820;
    letter-spacing: -.025em;
}
.ef-man-hero-period {
    align-items: center;
    color: rgba(245,240,232,.38);
    display: flex;
    font-size: .73rem;
    font-weight: 600;
    gap: 6px;
    margin-bottom: 16px;
    position: relative;
}
.ef-man-hero-divider {
    background: rgba(255,255,255,.07);
    border: none;
    height: 1px;
    margin: 0 0 13px;
    position: relative;
}
.ef-man-ranges {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    padding-bottom: 2px;
    position: relative;
    scrollbar-width: none;
}
.ef-man-ranges::-webkit-scrollbar { display: none; }
.ef-man-rchip {
    align-items: center;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.09);
    border-radius: 20px;
    color: rgba(245,240,232,.5);
    display: inline-flex;
    flex-shrink: 0;
    font-size: .71rem;
    font-weight: 720;
    height: 30px;
    padding: 0 13px;
    text-decoration: none;
    transition: background .13s, color .13s, border-color .13s;
}
.ef-man-rchip:hover {
    background: rgba(255,255,255,.1);
    color: rgba(245,240,232,.78);
}
.ef-man-rchip.--active {
    background: rgba(184,137,62,.2);
    border-color: rgba(184,137,62,.38);
    color: rgba(220,185,100,.95);
}

/* ── Date filter card ─────────────────────────────────── */
.ef-man-fcard {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: 14px;
    box-shadow: var(--ef-shadow);
    margin-bottom: 10px;
    padding: 12px 14px 14px;
}
.ef-man-fcard-lbl {
    color: var(--ef-faint);
    font-size: .59rem;
    font-weight: 760;
    letter-spacing: .11em;
    margin-bottom: 10px;
    text-transform: uppercase;
}
.ef-man-date-row { display: flex; margin-bottom: 10px; }
.ef-man-di {
    background: var(--ef-bg-subtle);
    border: 1.5px solid var(--ef-border);
    color: var(--ef-ink-2);
    flex: 1;
    font-size: 16px;
    font-weight: 600;
    height: 40px;
    min-width: 0;
    outline: none;
    padding: 0 10px;
    transition: border-color .15s, box-shadow .15s;
}
.ef-man-di:first-of-type { border-radius: 10px 0 0 10px; }
.ef-man-di:last-of-type  { border-left: none; border-radius: 0 10px 10px 0; }
.ef-man-di:focus {
    border-color: rgba(184,137,62,.42);
    box-shadow: 0 0 0 3px rgba(184,137,62,.08);
    position: relative;
    z-index: 1;
}
.ef-man-apply {
    align-items: center;
    background: var(--ef-ink);
    border: none;
    border-radius: 10px;
    color: rgba(255,253,250,.94);
    cursor: pointer;
    display: flex;
    font-size: .85rem;
    font-weight: 700;
    gap: 6px;
    height: 40px;
    justify-content: center;
    letter-spacing: .02em;
    transition: opacity .13s;
    width: 100%;
}
.ef-man-apply:active { opacity: .82; }

/* ── KPI 2×2 grid ─────────────────────────────────────── */
.ef-man-kpi-grid {
    display: grid;
    gap: 8px;
    grid-template-columns: 1fr 1fr;
    margin-bottom: 10px;
}
.ef-man-kpi {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: 14px;
    box-shadow: var(--ef-shadow);
    padding: 14px 14px 12px;
}
.ef-man-kpi-icon { color: var(--ef-faint); font-size: .82rem; margin-bottom: 8px; }
.ef-man-kpi-val {
    color: var(--ef-ink);
    font-size: 1.28rem;
    font-variant-numeric: tabular-nums;
    font-weight: 820;
    letter-spacing: -.01em;
    line-height: 1;
    margin-bottom: 4px;
}
.ef-man-kpi-lbl {
    color: var(--ef-faint);
    font-size: .59rem;
    font-weight: 760;
    letter-spacing: .1em;
    text-transform: uppercase;
}
.ef-man-kpi-note {
    color: var(--ef-muted);
    font-size: .67rem;
    margin-top: 3px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ef-man-kpi.--em .ef-man-kpi-icon,
.ef-man-kpi.--em .ef-man-kpi-val { color: var(--ef-emerald); }
.ef-man-kpi.--gd .ef-man-kpi-icon,
.ef-man-kpi.--gd .ef-man-kpi-val { color: #a07838; }

/* ── Section card ─────────────────────────────────────── */
.ef-man-sec {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: 14px;
    box-shadow: var(--ef-shadow);
    margin-bottom: 10px;
    overflow: hidden;
}
.ef-man-sec-head {
    align-items: center;
    border-bottom: 1px solid var(--ef-border);
    display: flex;
    gap: 10px;
    justify-content: space-between;
    padding: 11px 16px;
}
.ef-man-sec-title {
    align-items: center;
    color: var(--ef-ink);
    display: flex;
    font-size: .86rem;
    font-weight: 780;
    gap: 7px;
}
.ef-man-sec-title i { font-size: .8rem; }
.ef-man-sec-badge {
    background: var(--ef-bg-subtle);
    border-radius: 20px;
    color: var(--ef-faint);
    font-size: .64rem;
    font-weight: 720;
    padding: 2px 9px;
}

/* ── Leaderboard item ─────────────────────────────────── */
.ef-man-lb {
    border-bottom: 1px solid rgba(20,20,18,.05);
    padding: 11px 16px;
}
.ef-man-lb:last-child { border-bottom: none; }
.ef-man-lb-row {
    align-items: center;
    display: flex;
    gap: 9px;
    margin-bottom: 8px;
}
.ef-man-lb-rank {
    align-items: center;
    border-radius: 8px;
    display: flex;
    flex-shrink: 0;
    font-size: .68rem;
    font-weight: 820;
    height: 28px;
    justify-content: center;
    width: 28px;
}
.ef-man-lb-rank.--r1 { background: rgba(184,137,62,.15); border: 1.5px solid rgba(184,137,62,.3);  color: #c49540; }
.ef-man-lb-rank.--r2 { background: rgba(155,163,172,.1);  border: 1.5px solid rgba(155,163,172,.26); color: #8a9099; }
.ef-man-lb-rank.--r3 { background: rgba(200,121,65,.1);   border: 1.5px solid rgba(200,121,65,.26);  color: #c07841; }
.ef-man-lb-rank.--rn { background: var(--ef-bg-subtle);   border: 1.5px solid var(--ef-border);       color: var(--ef-faint); }

.ef-man-lb-av {
    align-items: center;
    border-radius: 8px;
    color: rgba(255,253,250,.9);
    display: flex;
    flex-shrink: 0;
    font-size: .63rem;
    font-weight: 800;
    height: 28px;
    justify-content: center;
    letter-spacing: .01em;
    width: 28px;
}
.ef-man-lb-av.--cat { background: linear-gradient(135deg,#2a6644,#1e4d33); }
.ef-man-lb-av.--emp { background: linear-gradient(135deg,#3d5c3a,#2a4228); }
.ef-man-lb-av.--vnd { background: linear-gradient(135deg,#4a5060,#363c48); }

.ef-man-lb-body { flex: 1; min-width: 0; }
.ef-man-lb-name { color: var(--ef-ink-2); font-size: .84rem; font-weight: 680; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ef-man-lb-meta { color: var(--ef-faint); font-size: .64rem; margin-top: 1px; }
.ef-man-lb-amt  { color: var(--ef-ink); flex-shrink: 0; font-size: .86rem; font-variant-numeric: tabular-nums; font-weight: 780; }

.ef-man-lb-barrow { align-items: center; display: flex; gap: 8px; }
.ef-man-lb-track  { background: var(--ef-border); border-radius: 4px; flex: 1; height: 4px; overflow: hidden; }
.ef-man-lb-fill   { border-radius: 4px; height: 100%; transition: width .55s cubic-bezier(.4,0,.2,1); }
.ef-man-lb-fill.--cat { background: linear-gradient(90deg,#0D9E78,#4ade80); }
.ef-man-lb-fill.--emp { background: linear-gradient(90deg,#a07238,#c4a05a); }
.ef-man-lb-fill.--vnd { background: linear-gradient(90deg,#607080,#8a9aaa); }
.ef-man-lb-pct { color: var(--ef-muted); flex-shrink: 0; font-size: .64rem; font-weight: 700; min-width: 30px; text-align: right; }

/* ── Monthly trend ────────────────────────────────────── */
.ef-man-trend {
    border-bottom: 1px solid rgba(20,20,18,.05);
    padding: 11px 16px;
}
.ef-man-trend:last-child { border-bottom: none; }
.ef-man-trend-row {
    align-items: baseline;
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
}
.ef-man-trend-month { color: var(--ef-ink-2); font-size: .86rem; font-weight: 700; }
.ef-man-trend-amt   { color: var(--ef-ink); font-size: .9rem; font-variant-numeric: tabular-nums; font-weight: 780; }
.ef-man-trend-meta  { margin-bottom: 7px; }
.ef-man-trend-cnt   { background: var(--ef-bg-subtle); border-radius: 10px; color: var(--ef-muted); font-size: .62rem; font-weight: 720; padding: 2px 8px; }
.ef-man-trend-track { background: var(--ef-border); border-radius: 4px; height: 5px; overflow: hidden; }
.ef-man-trend-fill  { background: linear-gradient(90deg,var(--ef-ink),rgba(60,55,50,.5)); border-radius: 4px; height: 100%; transition: width .6s cubic-bezier(.4,0,.2,1); }

.ef-man-empty { color: var(--ef-faint); font-size: .82rem; padding: 20px; text-align: center; }

} /* end @media ≤767px */
</style>
@endverbatim
@endpush

@php
/* ─── Mobile pre-computations ─── */
$maxCat    = $topCategories->max('total') ?: 1;
$maxEmp    = $topEmployees->max('total') ?: 1;
$maxVnd    = $topVendors->max('total') ?: 1;
$maxTrend  = $monthlyTrend->max('total') ?: 1;
$totalTxns = (int) $monthlyTrend->sum('count');
$dayCount  = max(1, (int) \Carbon\Carbon::parse($from)->diffInDays(\Carbon\Carbon::parse($to)) + 1);
$avgDaily  = $grandTotal / $dayCount;

$today    = now()->toDateString();
$ytdStart = now()->startOfYear()->toDateString();
$r7d      = now()->subDays(6)->toDateString();
$r30d     = now()->subDays(29)->toDateString();
$r90d     = now()->subDays(89)->toDateString();

$activeRange = match(true) {
    $from === $r7d    && $to === $today => '7d',
    $from === $r30d   && $to === $today => '30d',
    $from === $r90d   && $to === $today => '90d',
    $from === $ytdStart && $to === $today => 'ytd',
    default => 'custom',
};

$periodLbl = \Carbon\Carbon::parse($from)->format('d M') . ' → ' . \Carbon\Carbon::parse($to)->format('d M Y');
$rankCls   = fn($i) => ['--r1','--r2','--r3'][$i] ?? '--rn';
$inits     = fn($n) => implode('', array_map(fn($p) => strtoupper(substr($p,0,1)), array_filter(explode(' ',trim($n)))));
@endphp

{{-- ══════════════════════════════════════════════════════
     MOBILE ANALYTICS VIEW  (hidden ≥ 768px)
     ══════════════════════════════════════════════════════ --}}
<div class="ef-man-view">

    {{-- ── Analytics Hero ──────────────────────────────── --}}
    <div class="ef-man-hero">
        <div class="ef-man-hero-toprow">
            <span class="ef-man-hero-eyebrow">
                <i class="bi bi-bar-chart-line"></i> Analytics & Insights
            </span>
            <a href="{{ route('admin.analytics.inventory') }}" class="ef-man-hero-inv-btn">
                <i class="bi bi-boxes"></i> Inventory
            </a>
        </div>

        <div class="ef-man-hero-lbl">Total Settled Expenses</div>
        <div class="ef-man-hero-amount">
            <span class="ef-man-hero-currency">₹</span>
            <span class="ef-man-hero-num">{{ number_format($grandTotal, 0) }}</span>
        </div>
        <div class="ef-man-hero-period">
            <i class="bi bi-calendar3"></i> {{ $periodLbl }}
        </div>

        <hr class="ef-man-hero-divider">

        <div class="ef-man-ranges">
            <a href="{{ route('admin.analytics.index') }}?from={{ $r7d }}&to={{ $today }}"
               class="ef-man-rchip {{ $activeRange === '7d'  ? '--active' : '' }}">7D</a>
            <a href="{{ route('admin.analytics.index') }}?from={{ $r30d }}&to={{ $today }}"
               class="ef-man-rchip {{ $activeRange === '30d' ? '--active' : '' }}">30D</a>
            <a href="{{ route('admin.analytics.index') }}?from={{ $r90d }}&to={{ $today }}"
               class="ef-man-rchip {{ $activeRange === '90d' ? '--active' : '' }}">90D</a>
            <a href="{{ route('admin.analytics.index') }}?from={{ $ytdStart }}&to={{ $today }}"
               class="ef-man-rchip {{ $activeRange === 'ytd' ? '--active' : '' }}">YTD</a>
            <span class="ef-man-rchip {{ $activeRange === 'custom' ? '--active' : '' }}"
                  style="pointer-events:none">Custom</span>
        </div>
    </div>

    {{-- ── Date range filter ───────────────────────────── --}}
    <div class="ef-man-fcard">
        <div class="ef-man-fcard-lbl">Custom Date Range</div>
        <form method="GET" action="{{ route('admin.analytics.index') }}">
            <div class="ef-man-date-row">
                <input type="date" name="from" value="{{ $from }}" class="ef-man-di">
                <input type="date" name="to"   value="{{ $to }}"   class="ef-man-di">
            </div>
            <button type="submit" class="ef-man-apply">
                <i class="bi bi-check2"></i> Apply Range
            </button>
        </form>
    </div>

    {{-- ── KPI 2×2 ─────────────────────────────────────── --}}
    <div class="ef-man-kpi-grid">

        <div class="ef-man-kpi">
            <div class="ef-man-kpi-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="ef-man-kpi-val">₹{{ number_format($grandTotal, 0) }}</div>
            <div class="ef-man-kpi-lbl">Total Settled</div>
        </div>

        <div class="ef-man-kpi --gd">
            <div class="ef-man-kpi-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="ef-man-kpi-val">₹{{ number_format($avgDaily, 0) }}</div>
            <div class="ef-man-kpi-lbl">Avg / Day</div>
            <div class="ef-man-kpi-note">over {{ $dayCount }}d period</div>
        </div>

        <div class="ef-man-kpi --em">
            <div class="ef-man-kpi-icon"><i class="bi bi-receipt"></i></div>
            <div class="ef-man-kpi-val">{{ number_format($totalTxns) }}</div>
            <div class="ef-man-kpi-lbl">Requests</div>
        </div>

        <div class="ef-man-kpi">
            <div class="ef-man-kpi-icon"><i class="bi bi-tag"></i></div>
            <div class="ef-man-kpi-val">{{ $topCategories->count() }}</div>
            <div class="ef-man-kpi-lbl">Categories</div>
            @if($topCategories->first())
            <div class="ef-man-kpi-note">Top: {{ $topCategories->first()->name }}</div>
            @endif
        </div>

    </div>

    {{-- ── Top Categories ──────────────────────────────── --}}
    <div class="ef-man-sec">
        <div class="ef-man-sec-head">
            <span class="ef-man-sec-title">
                <i class="bi bi-tag" style="color:var(--ef-emerald)"></i> Top Categories
            </span>
            <span class="ef-man-sec-badge">{{ $topCategories->count() }}</span>
        </div>
        @forelse($topCategories as $i => $cat)
        @php
            $bw  = $maxCat > 0 ? round(($cat->total / $maxCat) * 100) : 0;
            $sh  = $grandTotal > 0 ? round(($cat->total / $grandTotal) * 100, 1) : 0;
            $av  = substr($inits($cat->name), 0, 2);
        @endphp
        <div class="ef-man-lb">
            <div class="ef-man-lb-row">
                <div class="ef-man-lb-rank {{ $rankCls($i) }}">{{ $i + 1 }}</div>
                <div class="ef-man-lb-av --cat">{{ $av }}</div>
                <div class="ef-man-lb-body">
                    <div class="ef-man-lb-name">{{ $cat->name }}</div>
                </div>
                <div class="ef-man-lb-amt">₹{{ number_format($cat->total, 0) }}</div>
            </div>
            <div class="ef-man-lb-barrow">
                <div class="ef-man-lb-track">
                    <div class="ef-man-lb-fill --cat" style="width:{{ $bw }}%"></div>
                </div>
                <div class="ef-man-lb-pct">{{ $sh }}%</div>
            </div>
        </div>
        @empty
        <div class="ef-man-empty">No category data for this period.</div>
        @endforelse
    </div>

    {{-- ── Top Spenders ────────────────────────────────── --}}
    <div class="ef-man-sec">
        <div class="ef-man-sec-head">
            <span class="ef-man-sec-title">
                <i class="bi bi-people" style="color:#a07238"></i> Top Spenders
            </span>
            <span class="ef-man-sec-badge">{{ $topEmployees->count() }}</span>
        </div>
        @forelse($topEmployees as $i => $emp)
        @php
            $bw  = $maxEmp > 0 ? round(($emp->total / $maxEmp) * 100) : 0;
            $sh  = $grandTotal > 0 ? round(($emp->total / $grandTotal) * 100, 1) : 0;
            $av  = substr($inits($emp->name), 0, 2);
        @endphp
        <div class="ef-man-lb">
            <div class="ef-man-lb-row">
                <div class="ef-man-lb-rank {{ $rankCls($i) }}">{{ $i + 1 }}</div>
                <div class="ef-man-lb-av --emp">{{ $av }}</div>
                <div class="ef-man-lb-body">
                    <div class="ef-man-lb-name">{{ $emp->name }}</div>
                    <div class="ef-man-lb-meta">{{ ucfirst($emp->role) }}</div>
                </div>
                <div class="ef-man-lb-amt">₹{{ number_format($emp->total, 0) }}</div>
            </div>
            <div class="ef-man-lb-barrow">
                <div class="ef-man-lb-track">
                    <div class="ef-man-lb-fill --emp" style="width:{{ $bw }}%"></div>
                </div>
                <div class="ef-man-lb-pct">{{ $sh }}%</div>
            </div>
        </div>
        @empty
        <div class="ef-man-empty">No spend data for this period.</div>
        @endforelse
    </div>

    {{-- ── Top Vendors ─────────────────────────────────── --}}
    <div class="ef-man-sec">
        <div class="ef-man-sec-head">
            <span class="ef-man-sec-title">
                <i class="bi bi-shop" style="color:#607080"></i> Top Vendors
            </span>
            <span class="ef-man-sec-badge">{{ $topVendors->count() }}</span>
        </div>
        @forelse($topVendors as $i => $vendor)
        @php
            $bw  = $maxVnd > 0 ? round(($vendor->total / $maxVnd) * 100) : 0;
            $sh  = $grandTotal > 0 ? round(($vendor->total / $grandTotal) * 100, 1) : 0;
            $av  = substr($inits($vendor->name), 0, 2);
        @endphp
        <div class="ef-man-lb">
            <div class="ef-man-lb-row">
                <div class="ef-man-lb-rank {{ $rankCls($i) }}">{{ $i + 1 }}</div>
                <div class="ef-man-lb-av --vnd">{{ $av }}</div>
                <div class="ef-man-lb-body">
                    <div class="ef-man-lb-name">{{ $vendor->name }}</div>
                </div>
                <div class="ef-man-lb-amt">₹{{ number_format($vendor->total, 0) }}</div>
            </div>
            <div class="ef-man-lb-barrow">
                <div class="ef-man-lb-track">
                    <div class="ef-man-lb-fill --vnd" style="width:{{ $bw }}%"></div>
                </div>
                <div class="ef-man-lb-pct">{{ $sh }}%</div>
            </div>
        </div>
        @empty
        <div class="ef-man-empty">No vendor data for this period.</div>
        @endforelse
    </div>

    {{-- ── Monthly Trend ────────────────────────────────── --}}
    @if($monthlyTrend->isNotEmpty())
    <div class="ef-man-sec">
        <div class="ef-man-sec-head">
            <span class="ef-man-sec-title">
                <i class="bi bi-calendar-range" style="color:var(--ef-muted)"></i> Monthly Trend
            </span>
            <span class="ef-man-sec-badge">{{ $monthlyTrend->count() }}mo</span>
        </div>
        @foreach($monthlyTrend as $row)
        @php $tw = $maxTrend > 0 ? round(($row->total / $maxTrend) * 100) : 0; @endphp
        <div class="ef-man-trend">
            <div class="ef-man-trend-row">
                <span class="ef-man-trend-month">{{ $row->month }}</span>
                <span class="ef-man-trend-amt">₹{{ number_format($row->total, 0) }}</span>
            </div>
            <div class="ef-man-trend-meta">
                <span class="ef-man-trend-cnt">{{ $row->count }} request{{ $row->count != 1 ? 's' : '' }}</span>
            </div>
            <div class="ef-man-trend-track">
                <div class="ef-man-trend-fill" style="width:{{ $tw }}%"></div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>{{-- /ef-man-view --}}

{{-- ══════════════════════════════════════════════════════
     DESKTOP VIEW  (hidden ≤ 767px, unchanged)
     ══════════════════════════════════════════════════════ --}}
<div class="ef-an-desktop-view">

<x-ds.hero eyebrow="Reports" title="Analytics & Insights"
    :meta="[['icon' => 'bi-calendar3', 'text' => 'Period: ' . \Carbon\Carbon::parse($from)->format('d M') . ' — ' . \Carbon\Carbon::parse($to)->format('d M Y')]]">
    <x-slot:actions>
        <a href="{{ route('admin.analytics.inventory') }}" class="ef-btn">
            <i class="bi bi-boxes"></i> Inventory Analytics
        </a>
    </x-slot:actions>
</x-ds.hero>

{{-- Date filter --}}
<x-ds.card>
    <form method="GET" class="ef-an-filter">
        <div class="ef-an-filter-field">
            <label class="ef-label" for="from">From</label>
            <input type="date" name="from" id="from" class="ef-input" style="min-height:38px;padding:7px 12px" value="{{ $from }}">
        </div>
        <div class="ef-an-filter-field">
            <label class="ef-label" for="to">To</label>
            <input type="date" name="to" id="to" class="ef-input" style="min-height:38px;padding:7px 12px" value="{{ $to }}">
        </div>
        <div class="ef-an-filter-actions">
            <button class="ef-btn ef-btn-dark" style="height:38px">Apply</button>
            <a href="{{ route('admin.analytics.index') }}" class="ef-btn" style="height:38px;display:inline-flex;align-items:center">Reset</a>
        </div>
    </form>
</x-ds.card>

{{-- Grand total --}}
<div class="ef-an-total">
    <div class="ef-an-total-icon"><i class="bi bi-cash-stack"></i></div>
    <div>
        <div class="ef-an-total-label">
            Total Settled Expenses &middot; {{ \Carbon\Carbon::parse($from)->format('d M') }} &mdash; {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
        </div>
        <div class="ef-an-total-value">₹{{ number_format($grandTotal, 2) }}</div>
    </div>
</div>

{{-- Top 3 breakdown --}}
<div class="ef-an-grid">

    {{-- Top categories --}}
    <x-ds.card title="Top Categories">
        @forelse($topCategories as $i => $cat)
            <div class="ef-an-rank-item">
                <div class="ef-an-rank-row">
                    <div class="ef-an-rank-name">
                        <span class="ef-an-rank-num">{{ $i + 1 }}</span>
                        {{ $cat->name }}
                    </div>
                    <div class="ef-an-rank-val">₹{{ number_format($cat->total, 2) }}</div>
                </div>
                @if($grandTotal > 0)
                <div class="ef-an-bar-wrap">
                    <div class="ef-an-bar --emerald" style="width:{{ ($cat->total / $grandTotal) * 100 }}%"></div>
                </div>
                @endif
            </div>
        @empty
            <div style="color:var(--ef-faint);font-size:.84rem;padding:12px 0;text-align:center">No data for period.</div>
        @endforelse
    </x-ds.card>

    {{-- Top spenders --}}
    <x-ds.card title="Top Spenders">
        @forelse($topEmployees as $i => $emp)
            <div class="ef-an-rank-item">
                <div class="ef-an-rank-row">
                    <div class="ef-an-rank-name">
                        <span class="ef-an-rank-num">{{ $i + 1 }}</span>
                        {{ $emp->name }}
                    </div>
                    <div class="ef-an-rank-val">₹{{ number_format($emp->total, 2) }}</div>
                </div>
                @if($grandTotal > 0)
                <div class="ef-an-bar-wrap">
                    <div class="ef-an-bar --gold" style="width:{{ ($emp->total / $grandTotal) * 100 }}%"></div>
                </div>
                @endif
            </div>
        @empty
            <div style="color:var(--ef-faint);font-size:.84rem;padding:12px 0;text-align:center">No data for period.</div>
        @endforelse
    </x-ds.card>

    {{-- Top vendors --}}
    <x-ds.card title="Top Vendors">
        @forelse($topVendors as $i => $vendor)
            <div class="ef-an-rank-item">
                <div class="ef-an-rank-row">
                    <div class="ef-an-rank-name">
                        <span class="ef-an-rank-num --amber">{{ $i + 1 }}</span>
                        {{ $vendor->name }}
                    </div>
                    <div class="ef-an-rank-val">₹{{ number_format($vendor->total, 2) }}</div>
                </div>
                @if($grandTotal > 0)
                <div class="ef-an-bar-wrap">
                    <div class="ef-an-bar --amber" style="width:{{ ($vendor->total / $grandTotal) * 100 }}%"></div>
                </div>
                @endif
            </div>
        @empty
            <div style="color:var(--ef-faint);font-size:.84rem;padding:12px 0;text-align:center">No data for period.</div>
        @endforelse
    </x-ds.card>

</div>

{{-- Monthly trend --}}
<x-ds.card title="Monthly Expense Trend" :no-pad="true">
    <div style="overflow-x:auto">
        <table class="ef-an-trend-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th class="r">Requests</th>
                    <th class="r">Total</th>
                    <th style="width:180px">Relative</th>
                </tr>
            </thead>
            <tbody>
                @php $maxTotal = $monthlyTrend->max('total') ?: 1; @endphp
                @forelse($monthlyTrend as $row)
                <tr>
                    <td class="fw">{{ $row->month }}</td>
                    <td class="r">{{ $row->count }}</td>
                    <td class="r fw">₹{{ number_format($row->total, 2) }}</td>
                    <td>
                        <div class="ef-an-trend-bar-wrap">
                            <div class="ef-an-trend-bar" style="width:{{ ($row->total / $maxTotal) * 100 }}%"></div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;padding:24px;color:var(--ef-faint)">No data for period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ds.card>

</div>{{-- /ef-an-desktop-view --}}

</x-admin-layout>
