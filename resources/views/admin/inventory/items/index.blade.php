<x-admin-layout title="Inventory Items">
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
}

/* ── Hero ──────────────────────────────────────────────── */
.ef-inv-hero {
    background: linear-gradient(135deg, #1a1612 0%, #2d2420 60%, #3a2e22 100%);
    border-radius: var(--ef-radius);
    padding: 2rem 2.2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}
.ef-inv-hero-title {
    font-size: clamp(1.7rem, 3.5vw, 2.6rem);
    font-weight: 700;
    color: #fff;
    line-height: 1.1;
    letter-spacing: -.02em;
}
.ef-inv-hero-sub {
    color: rgba(255,255,255,.55);
    font-size: .92rem;
    margin-top: .3rem;
}
.ef-inv-hero-actions { display: flex; gap: .6rem; flex-wrap: wrap; align-items: center; }
.ef-inv-btn-gold {
    background: var(--ef-gold);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: .55rem 1.2rem;
    font-size: .875rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .2s var(--ef-ease), transform .15s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    white-space: nowrap;
}
.ef-inv-btn-gold:hover { background: var(--ef-gold-hi); color: #fff; transform: translateY(-1px); }
.ef-inv-btn-ghost {
    background: rgba(255,255,255,.10);
    color: rgba(255,255,255,.85);
    border: 1px solid rgba(255,255,255,.18);
    border-radius: 8px;
    padding: .5rem 1rem;
    font-size: .875rem;
    font-weight: 500;
    cursor: pointer;
    transition: background .2s var(--ef-ease);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    white-space: nowrap;
}
.ef-inv-btn-ghost:hover { background: rgba(255,255,255,.18); color: #fff; }
.ef-inv-alert-pill {
    background: rgba(192,57,43,.18);
    border: 1px solid rgba(192,57,43,.35);
    color: #e07060;
    border-radius: 20px;
    padding: .38rem .9rem;
    font-size: .8rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    transition: background .2s;
}
.ef-inv-alert-pill:hover { background: rgba(192,57,43,.28); color: #e07060; }

/* ── Insight strip ─────────────────────────────────────── */
.ef-inv-strip {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: .85rem;
    margin-bottom: 1.5rem;
}
@media (max-width: 1399px) { .ef-inv-strip { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 767px)  { .ef-inv-strip { grid-template-columns: repeat(2, 1fr); } }
.ef-inv-kpi {
    background: #fff;
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    padding: 1.15rem 1.2rem;
    box-shadow: var(--ef-shadow);
    text-decoration: none;
    transition: box-shadow .2s var(--ef-ease), transform .15s;
    display: block;
    color: inherit;
}
a.ef-inv-kpi:hover { box-shadow: var(--ef-shadow-hover); transform: translateY(-2px); }
.ef-inv-kpi-icon {
    width: 34px; height: 34px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: .9rem;
    margin-bottom: .7rem;
}
.ef-inv-kpi-icon.--gold   { background: rgba(160,114,56,.12); color: var(--ef-gold); }
.ef-inv-kpi-icon.--warn   { background: rgba(202,138,4,.12);  color: #b45309; }
.ef-inv-kpi-icon.--danger { background: rgba(192,57,43,.10);  color: var(--ef-danger); }
.ef-inv-kpi-icon.--orange { background: rgba(234,88,12,.10);  color: #c2410c; }
.ef-inv-kpi-icon.--green  { background: rgba(22,163,74,.10);  color: #15803d; }
.ef-inv-kpi-icon.--blue   { background: rgba(37,99,235,.10);  color: #1d4ed8; }
.ef-inv-kpi-val { font-size: 1.45rem; font-weight: 700; color: var(--ef-ink); line-height: 1.1; }
.ef-inv-kpi-val.--danger  { color: var(--ef-danger); }
.ef-inv-kpi-val.--warn    { color: #b45309; }
.ef-inv-kpi-label { font-size: .78rem; color: var(--ef-muted); margin-top: .2rem; }

/* ── Filter bar ────────────────────────────────────────── */
.ef-inv-filter-bar {
    background: #fff;
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    padding: 1rem 1.2rem;
    box-shadow: var(--ef-shadow);
    margin-bottom: 1.5rem;
}
.ef-inv-chips {
    display: flex;
    gap: .5rem;
    overflow-x: auto;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 2px;
    flex-wrap: nowrap;
    align-items: center;
    margin-bottom: .75rem;
}
.ef-inv-chips::-webkit-scrollbar { display: none; }
.ef-inv-chip {
    flex-shrink: 0;
    padding: .38rem .9rem;
    border-radius: 20px;
    font-size: .8rem;
    font-weight: 500;
    border: 1px solid var(--ef-border);
    color: var(--ef-muted);
    background: var(--ef-faint);
    cursor: pointer;
    transition: all .18s var(--ef-ease);
    text-decoration: none;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: .3rem;
}
.ef-inv-chip:hover { border-color: var(--ef-gold); color: var(--ef-gold); background: rgba(160,114,56,.06); }
.ef-inv-chip.--active         { background: var(--ef-gold); border-color: var(--ef-gold); color: #fff; }
.ef-inv-chip.--warn           { background: #fef3c7; border-color: #fbbf24; color: #92400e; }
.ef-inv-chip.--warn.--active  { background: #b45309; border-color: #b45309; color: #fff; }
.ef-inv-chip.--danger         { background: rgba(192,57,43,.07); border-color: rgba(192,57,43,.3); color: var(--ef-danger); }
.ef-inv-chip.--danger.--active{ background: var(--ef-danger); border-color: var(--ef-danger); color: #fff; }
.ef-inv-chip.--orange         { background: rgba(234,88,12,.07); border-color: rgba(234,88,12,.3); color: #c2410c; }
.ef-inv-chip.--orange.--active{ background: #c2410c; border-color: #c2410c; color: #fff; }
.ef-inv-filter-row {
    display: flex;
    gap: .6rem;
    align-items: center;
    flex-wrap: wrap;
}
.ef-inv-search {
    flex: 1;
    min-width: 180px;
    border: 1px solid var(--ef-border);
    border-radius: 8px;
    padding: .5rem .85rem;
    font-size: .875rem;
    color: var(--ef-ink);
    background: var(--ef-faint);
    outline: none;
    transition: border-color .18s, background .18s;
}
.ef-inv-search:focus { border-color: var(--ef-gold); background: #fff; box-shadow: 0 0 0 3px rgba(160,114,56,.12); }
.ef-inv-select {
    border: 1px solid var(--ef-border);
    border-radius: 8px;
    padding: .5rem .85rem;
    font-size: .875rem;
    color: var(--ef-ink);
    background: var(--ef-faint);
    outline: none;
    transition: border-color .18s;
    cursor: pointer;
    min-width: 140px;
}
.ef-inv-select:focus { border-color: var(--ef-gold); background: #fff; box-shadow: 0 0 0 3px rgba(160,114,56,.12); }
.ef-inv-adv-toggle {
    border: 1px solid var(--ef-border);
    border-radius: 8px;
    padding: .5rem .85rem;
    font-size: .8rem;
    font-weight: 500;
    color: var(--ef-muted);
    background: var(--ef-faint);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    transition: all .18s;
    white-space: nowrap;
    position: relative;
}
.ef-inv-adv-toggle:hover { border-color: var(--ef-gold); color: var(--ef-gold); background: rgba(160,114,56,.06); }
.ef-inv-adv-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--ef-gold);
    display: none;
    position: absolute;
    top: 6px; right: 6px;
}
.ef-inv-adv-toggle.--has-filter .ef-inv-adv-dot { display: block; }
.ef-inv-adv-panel {
    overflow: hidden;
    max-height: 0;
    transition: max-height .35s var(--ef-ease);
}
.ef-inv-adv-panel.--open { max-height: 120px; }
.ef-inv-adv-inner {
    padding-top: .75rem;
    border-top: 1px solid var(--ef-border);
    margin-top: .75rem;
    display: flex;
    gap: .6rem;
    flex-wrap: wrap;
    align-items: center;
}
.ef-inv-btn-apply {
    background: var(--ef-gold);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: .5rem 1.1rem;
    font-size: .875rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .18s;
}
.ef-inv-btn-apply:hover { background: var(--ef-gold-hi); }
.ef-inv-btn-clear {
    background: transparent;
    color: var(--ef-muted);
    border: 1px solid var(--ef-border);
    border-radius: 8px;
    padding: .5rem .9rem;
    font-size: .875rem;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: all .18s;
}
.ef-inv-btn-clear:hover { border-color: var(--ef-danger); color: var(--ef-danger); }

/* ── Items grid ────────────────────────────────────────── */
.ef-inv-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
@media (max-width: 1199px) { .ef-inv-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 767px)  { .ef-inv-grid { grid-template-columns: 1fr; } }

.ef-inv-card {
    background: #fff;
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    overflow: hidden;
    transition: box-shadow .2s var(--ef-ease), transform .15s;
    display: flex;
    flex-direction: column;
}
.ef-inv-card:hover { box-shadow: var(--ef-shadow-hover); transform: translateY(-2px); }
.ef-inv-card.--inactive { opacity: .65; }
.ef-inv-card.--inactive:hover { opacity: .88; }
.ef-inv-card-accent { height: 3px; background: var(--ef-border); }
.ef-inv-card.--healthy .ef-inv-card-accent  { background: #16a34a; }
.ef-inv-card.--low .ef-inv-card-accent      { background: #d97706; }
.ef-inv-card.--out .ef-inv-card-accent      { background: var(--ef-danger); }
.ef-inv-card.--inactive .ef-inv-card-accent { background: #9ca3af; }

.ef-inv-card-head {
    padding: .9rem 1rem .5rem;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .5rem;
}
.ef-inv-cat-badge {
    font-size: .7rem;
    font-weight: 600;
    letter-spacing: .03em;
    text-transform: uppercase;
    color: var(--ef-gold);
    background: rgba(160,114,56,.09);
    border: 1px solid rgba(160,114,56,.18);
    border-radius: 5px;
    padding: .18rem .55rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 120px;
}
.ef-inv-health-chip {
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .04em;
    border-radius: 12px;
    padding: .2rem .6rem;
    white-space: nowrap;
    flex-shrink: 0;
}
.ef-inv-health-chip.--healthy  { background: rgba(22,163,74,.12);  color: #15803d; }
.ef-inv-health-chip.--low      { background: rgba(217,119,6,.12);  color: #92400e; }
.ef-inv-health-chip.--out      { background: rgba(192,57,43,.12);  color: var(--ef-danger); }
.ef-inv-health-chip.--inactive { background: rgba(107,101,96,.1);  color: var(--ef-muted); }

.ef-inv-card-identity { padding: 0 1rem .7rem; }
.ef-inv-item-name {
    font-size: 1rem;
    font-weight: 700;
    color: var(--ef-ink);
    margin-bottom: .15rem;
    line-height: 1.2;
}
.ef-inv-item-sku {
    font-size: .75rem;
    color: var(--ef-muted);
    font-family: monospace;
}

.ef-inv-card-body { padding: .2rem 1rem .9rem; flex: 1; }
.ef-inv-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .5rem;
    margin-bottom: .75rem;
}
.ef-inv-stat {
    background: var(--ef-faint);
    border-radius: 8px;
    padding: .55rem .5rem;
    text-align: center;
}
.ef-inv-stat-val {
    font-size: .875rem;
    font-weight: 700;
    color: var(--ef-ink);
    line-height: 1;
}
.ef-inv-stat-val.--danger { color: var(--ef-danger); }
.ef-inv-stat-val.--warn   { color: #b45309; }
.ef-inv-stat-label {
    font-size: .65rem;
    color: var(--ef-muted);
    margin-top: .18rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ef-inv-bar-wrap {
    height: 5px;
    background: var(--ef-faint);
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: .3rem;
}
.ef-inv-bar-fill {
    height: 100%;
    border-radius: 10px;
    background: #16a34a;
    transition: width .4s var(--ef-ease);
}
.ef-inv-bar-fill.--low { background: #d97706; }
.ef-inv-bar-fill.--out { background: var(--ef-danger); }
.ef-inv-bar-caption {
    font-size: .68rem;
    color: var(--ef-muted);
    display: flex;
    justify-content: space-between;
}

/* ── Card foot ─────────────────────────────────────────── */
.ef-inv-card-foot {
    padding: .65rem 1rem;
    border-top: 1px solid var(--ef-border);
    display: flex;
    gap: .5rem;
    align-items: center;
}
.ef-inv-foot-btn {
    flex: 1;
    text-align: center;
    padding: .45rem .5rem;
    border-radius: 7px;
    font-size: .78rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .18s var(--ef-ease);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .3rem;
    border: none;
}
.ef-inv-foot-btn.--primary { background: var(--ef-gold); color: #fff; }
.ef-inv-foot-btn.--primary:hover { background: var(--ef-gold-hi); color: #fff; }
.ef-inv-foot-btn.--outline { background: transparent; color: var(--ef-muted); border: 1px solid var(--ef-border); }
.ef-inv-foot-btn.--outline:hover { border-color: var(--ef-gold); color: var(--ef-gold); background: rgba(160,114,56,.05); }
.ef-inv-foot-menu {
    width: 34px; height: 34px;
    border-radius: 7px;
    background: var(--ef-faint);
    border: 1px solid var(--ef-border);
    color: var(--ef-muted);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: all .18s;
    flex-shrink: 0;
}
.ef-inv-foot-menu:hover { border-color: var(--ef-gold); color: var(--ef-gold); background: rgba(160,114,56,.05); }

/* ── Empty state ─────────────────────────────────────────── */
.ef-inv-empty {
    background: #fff;
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    padding: 4rem 2rem;
    text-align: center;
}
.ef-inv-empty-icon { font-size: 3rem; color: var(--ef-border-strong); margin-bottom: 1rem; }
.ef-inv-empty-title { font-size: 1.1rem; font-weight: 700; color: var(--ef-ink); margin-bottom: .4rem; }
.ef-inv-empty-sub { color: var(--ef-muted); font-size: .875rem; }

/* ── Pagination bar ──────────────────────────────────────── */
.ef-inv-pagination {
    background: #fff;
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    padding: .85rem 1.2rem;
    box-shadow: var(--ef-shadow);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .5rem;
    margin-bottom: 5rem;
}
.ef-inv-pagination-info { font-size: .8rem; color: var(--ef-muted); }

/* ── Mobile sticky bar ───────────────────────────────────── */
.ef-inv-sticky {
    display: none;
    position: fixed;
    bottom: 0; left: 0; right: 0;
    background: rgba(26,22,18,.96);
    backdrop-filter: blur(10px);
    padding: .8rem 1rem;
    z-index: 1000;
    gap: .5rem;
}
@media (max-width: 767px) { .ef-inv-sticky { display: flex; } }
.ef-inv-sticky-btn {
    flex: 1;
    padding: .65rem .5rem;
    border-radius: 8px;
    font-size: .82rem;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .35rem;
    cursor: pointer;
    border: none;
    transition: opacity .18s;
}
.ef-inv-sticky-btn.--gold  { background: var(--ef-gold); color: #fff; }
.ef-inv-sticky-btn.--ghost { background: rgba(255,255,255,.1); color: rgba(255,255,255,.85); }
</style>
@endpush

@php
    $search    = $filters['search'] ?? '';
    $catId     = $filters['category_id'] ?? '';
    $stockSt   = $filters['stock_status'] ?? '';
    $statusFil = $filters['status'] ?? '';
    $hasAdv    = $catId || $statusFil;
@endphp

{{-- ── Hero ──────────────────────────────────────────────────── --}}
<div class="ef-inv-hero">
    <div>
        <div class="ef-inv-hero-title">Inventory</div>
        <div class="ef-inv-hero-sub">Stock levels · item management · purchase tracking</div>
    </div>
    <div class="ef-inv-hero-actions">
        @if($stats['low_stock'] + $stats['out_of_stock'] > 0)
            <a href="{{ route('admin.inventory.alerts.index') }}" class="ef-inv-alert-pill">
                <i class="bi bi-exclamation-triangle-fill"></i>
                {{ $stats['low_stock'] + $stats['out_of_stock'] }} Alert{{ ($stats['low_stock'] + $stats['out_of_stock']) !== 1 ? 's' : '' }}
            </a>
        @endif
        <a href="{{ route('admin.inventory.bills.index') }}" class="ef-inv-btn-ghost">
            <i class="bi bi-clock-history"></i> Bill History
        </a>
        <button type="button" class="ef-inv-btn-ghost" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="bi bi-cloud-upload"></i> Upload Bill
        </button>
        <a href="{{ route('admin.inventory.items.create') }}" class="ef-inv-btn-gold">
            <i class="bi bi-plus-lg"></i> Add Item
        </a>
    </div>
</div>

{{-- ── Upload modal (preserved) ────────────────────────────────── --}}
@include('admin.inventory.bills._upload-modal')

{{-- ── Insight strip ───────────────────────────────────────────── --}}
<div class="ef-inv-strip">
    <div class="ef-inv-kpi">
        <div class="ef-inv-kpi-icon --gold"><i class="bi bi-boxes"></i></div>
        <div class="ef-inv-kpi-val">{{ number_format($stats['total_active']) }}</div>
        <div class="ef-inv-kpi-label">Active Items</div>
    </div>
    <a href="{{ route('admin.inventory.items.index', array_merge(request()->except('stock_status', 'page'), ['stock_status' => 'low'])) }}" class="ef-inv-kpi">
        <div class="ef-inv-kpi-icon --warn"><i class="bi bi-arrow-down-circle"></i></div>
        <div class="ef-inv-kpi-val {{ $stats['low_stock'] > 0 ? '--warn' : '' }}">{{ $stats['low_stock'] }}</div>
        <div class="ef-inv-kpi-label">Low Stock</div>
    </a>
    <a href="{{ route('admin.inventory.items.index', array_merge(request()->except('stock_status', 'page'), ['stock_status' => 'out'])) }}" class="ef-inv-kpi">
        <div class="ef-inv-kpi-icon --danger"><i class="bi bi-x-circle"></i></div>
        <div class="ef-inv-kpi-val {{ $stats['out_of_stock'] > 0 ? '--danger' : '' }}">{{ $stats['out_of_stock'] }}</div>
        <div class="ef-inv-kpi-label">Out of Stock</div>
    </a>
    <a href="{{ route('admin.purchase-plans.suggestions') }}" class="ef-inv-kpi">
        <div class="ef-inv-kpi-icon --blue"><i class="bi bi-cart3"></i></div>
        <div class="ef-inv-kpi-val">{{ $stats['critical'] }}</div>
        <div class="ef-inv-kpi-label">Need Reorder</div>
    </a>
    <div class="ef-inv-kpi">
        <div class="ef-inv-kpi-icon --green"><i class="bi bi-currency-rupee"></i></div>
        <div class="ef-inv-kpi-val" style="font-size:1.1rem">
            @php
                $val = $stats['inventory_value'];
                echo $val >= 100000 ? '₹' . number_format($val / 100000, 1) . 'L'
                                   : '₹' . number_format($val);
            @endphp
        </div>
        <div class="ef-inv-kpi-label">Inventory Value</div>
    </div>
    <div class="ef-inv-kpi">
        <div class="ef-inv-kpi-icon --orange"><i class="bi bi-bag-check"></i></div>
        <div class="ef-inv-kpi-val" style="font-size:1.1rem">
            @php
                $ms = $stats['monthly_spend'];
                echo $ms >= 100000 ? '₹' . number_format($ms / 100000, 1) . 'L'
                                  : '₹' . number_format($ms);
            @endphp
        </div>
        <div class="ef-inv-kpi-label">This Month's Spend</div>
    </div>
</div>

{{-- ── Filter bar ──────────────────────────────────────────────── --}}
<form method="GET" id="invFilterForm" action="{{ route('admin.inventory.items.index') }}">
<div class="ef-inv-filter-bar">
    <div class="ef-inv-chips">
        <a href="{{ route('admin.inventory.items.index') }}"
           class="ef-inv-chip {{ !$stockSt ? '--active' : '' }}">
            All Items
        </a>
        <a href="{{ route('admin.inventory.items.index', array_merge(request()->except('stock_status', 'page'), ['stock_status' => 'low'])) }}"
           class="ef-inv-chip --warn {{ $stockSt === 'low' ? '--active' : '' }}">
            <i class="bi bi-arrow-down-circle"></i> Low Stock
            @if($stats['low_stock'] > 0)
                <span style="font-size:.7rem;opacity:.8">({{ $stats['low_stock'] }})</span>
            @endif
        </a>
        <a href="{{ route('admin.inventory.items.index', array_merge(request()->except('stock_status', 'page'), ['stock_status' => 'out'])) }}"
           class="ef-inv-chip --danger {{ $stockSt === 'out' ? '--active' : '' }}">
            <i class="bi bi-x-circle"></i> Out of Stock
            @if($stats['out_of_stock'] > 0)
                <span style="font-size:.7rem;opacity:.8">({{ $stats['out_of_stock'] }})</span>
            @endif
        </a>
        <a href="{{ route('admin.inventory.items.index', array_merge(request()->except('stock_status', 'page'), ['stock_status' => 'critical'])) }}"
           class="ef-inv-chip --orange {{ $stockSt === 'critical' ? '--active' : '' }}">
            <i class="bi bi-exclamation-triangle"></i> Critical
        </a>
    </div>

    <div class="ef-inv-filter-row">
        <input type="text" name="search" class="ef-inv-search"
               placeholder="Search name or SKU…" value="{{ $search }}">
        <button type="button" class="ef-inv-adv-toggle {{ $hasAdv ? '--has-filter' : '' }}" onclick="invToggleAdv(this)">
            <i class="bi bi-sliders2"></i> Filters
            <span class="ef-inv-adv-dot"></span>
        </button>
        <button type="submit" class="ef-inv-btn-apply">Search</button>
    </div>

    <div class="ef-inv-adv-panel {{ $hasAdv ? '--open' : '' }}" id="invAdvPanel">
        <div class="ef-inv-adv-inner">
            <select name="category_id" class="ef-inv-select">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $catId == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="ef-inv-select">
                <option value="">All Status</option>
                <option value="active"   {{ $statusFil === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $statusFil === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @if($stockSt)
                <input type="hidden" name="stock_status" value="{{ $stockSt }}">
            @endif
            <a href="{{ route('admin.inventory.items.index') }}" class="ef-inv-btn-clear">
                <i class="bi bi-x-lg me-1"></i> Clear
            </a>
        </div>
    </div>
</div>
</form>

{{-- ── Items grid ──────────────────────────────────────────────── --}}
@if($items->isEmpty())
<div class="ef-inv-empty">
    <div class="ef-inv-empty-icon"><i class="bi bi-boxes"></i></div>
    <div class="ef-inv-empty-title">No items found</div>
    <div class="ef-inv-empty-sub">
        @if($search || $stockSt || $catId || $statusFil)
            Try different filters or <a href="{{ route('admin.inventory.items.index') }}" style="color:var(--ef-gold)">clear all filters</a>.
        @else
            Add your first inventory item to get started.
        @endif
    </div>
</div>
@else
<div class="ef-inv-grid">
    @foreach($items as $item)
    @php
        $isOut      = $item->isOutOfStock();
        $isLow      = !$isOut && $item->isLowStock();
        $isInactive = $item->status !== 'active';
        $health     = $isOut ? 'out' : ($isLow ? 'low' : ($isInactive ? 'inactive' : 'healthy'));

        $maxRef  = $item->maximum_stock > 0 ? $item->maximum_stock
                 : ($item->minimum_stock > 0 ? $item->minimum_stock * 3 : 0);
        $barPct  = ($maxRef > 0 && $item->current_stock > 0)
                 ? min(100, round($item->current_stock / $maxRef * 100))
                 : 0;
        $barMod  = $isOut ? '--out' : ($isLow ? '--low' : '');
        $lblMap  = ['out' => 'OUT', 'low' => 'LOW', 'inactive' => 'Inactive', 'healthy' => 'Healthy'];
        $valMod  = $isOut ? '--danger' : ($isLow ? '--warn' : '');

        $fmt = fn($n) => $n == intval($n) ? intval($n) : number_format($n, 1);
    @endphp
    <div class="ef-inv-card --{{ $health }}">
        <div class="ef-inv-card-accent"></div>

        <div class="ef-inv-card-head">
            <span class="ef-inv-cat-badge" title="{{ $item->category->name }}">{{ $item->category->name }}</span>
            <span class="ef-inv-health-chip --{{ $health }}">{{ $lblMap[$health] }}</span>
        </div>

        <div class="ef-inv-card-identity">
            <div class="ef-inv-item-name">{{ $item->name }}</div>
            <div class="ef-inv-item-sku">{{ $item->sku ?? 'No SKU' }}</div>
        </div>

        <div class="ef-inv-card-body">
            <div class="ef-inv-stats">
                <div class="ef-inv-stat">
                    <div class="ef-inv-stat-val {{ $valMod }}">{{ $fmt($item->current_stock) }}</div>
                    <div class="ef-inv-stat-label">{{ $item->unit }} current</div>
                </div>
                <div class="ef-inv-stat">
                    <div class="ef-inv-stat-val">{{ $fmt($item->minimum_stock) }}</div>
                    <div class="ef-inv-stat-label">{{ $item->unit }} min</div>
                </div>
                <div class="ef-inv-stat">
                    <div class="ef-inv-stat-val">
                        {{ $item->average_cost ? '₹' . number_format($item->average_cost, 0) : '—' }}
                    </div>
                    <div class="ef-inv-stat-label">avg cost</div>
                </div>
            </div>

            @if($maxRef > 0)
            <div class="ef-inv-bar-wrap">
                <div class="ef-inv-bar-fill {{ $barMod }}" style="width:{{ $barPct }}%"></div>
            </div>
            <div class="ef-inv-bar-caption">
                <span>{{ $barPct }}% of capacity</span>
                <span>max {{ $item->maximum_stock > 0 ? number_format($item->maximum_stock, 0) : '~' . number_format($maxRef, 0) }} {{ $item->unit }}</span>
            </div>
            @endif
        </div>

        <div class="ef-inv-card-foot">
            <a href="{{ route('admin.inventory.items.show', $item) }}" class="ef-inv-foot-btn --primary">
                <i class="bi bi-eye"></i> View
            </a>
            <a href="{{ route('admin.inventory.items.edit', $item) }}" class="ef-inv-foot-btn --outline">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <div class="dropdown">
                <button class="ef-inv-foot-menu" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical" style="font-size:.8rem"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm"
                    style="font-size:.82rem;border-color:var(--ef-border);border-radius:10px;min-width:180px">
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.inventory.items.show', $item) }}">
                            <i class="bi bi-clock-history me-2 text-muted"></i>Transaction History
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <form method="POST"
                              action="{{ route('admin.inventory.items.toggle-status', $item) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="dropdown-item">
                                @if($item->status === 'active')
                                    <i class="bi bi-pause-circle me-2 text-muted"></i>Deactivate
                                @else
                                    <i class="bi bi-play-circle me-2 text-muted"></i>Activate
                                @endif
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if($items->hasPages())
<div class="ef-inv-pagination">
    <div class="ef-inv-pagination-info">
        Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ number_format($items->total()) }} items
    </div>
    {{ $items->links() }}
</div>
@endif
@endif

{{-- ── Mobile sticky bar ──────────────────────────────────────── --}}
<div class="ef-inv-sticky">
    <a href="{{ route('admin.inventory.items.create') }}" class="ef-inv-sticky-btn --gold">
        <i class="bi bi-plus-lg"></i> Add Item
    </a>
    <button type="button" class="ef-inv-sticky-btn --ghost"
            data-bs-toggle="modal" data-bs-target="#uploadModal">
        <i class="bi bi-cloud-upload"></i> Upload Bill
    </button>
</div>

@push('scripts')
<script>
function invToggleAdv(btn) {
    const panel = document.getElementById('invAdvPanel');
    panel.classList.toggle('--open');
    btn.classList.toggle('--has-filter');
}
</script>
@endpush
</x-admin-layout>
