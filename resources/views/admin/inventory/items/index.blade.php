<x-admin-layout title="Inventory Items">
@push('styles')
<style>
/* ══════════════════════════════════════════════════════════════════
   INVENTORY — reuses the application's existing design tokens/
   components (x-ds.hero, x-ds.kpi-card, ef-ds-filter-bar, ef-input,
   ef-select, ef-btn — resources/css/app.css) rather than the old
   page-specific brown/gold palette. Only the item-card grid (a layout
   with no existing shared component) needs page-scoped CSS below, and
   its colors are the same semantic tokens used everywhere else
   (--ef-emerald / --ef-amber / --ef-danger, and the exact
   rgba(15,123,95,.11)/#0A5240, rgba(216,154,61,.13)/#7D5218,
   rgba(200,75,68,.11)/#9B2C2C triple used by status-badge.blade.php,
   employee/attendance, leave & advance status chips app-wide).
   ══════════════════════════════════════════════════════════════════ */

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
    font-weight: 600;
    border: 1px solid var(--ef-border);
    color: var(--ef-muted);
    background: var(--ef-surface-2);
    cursor: pointer;
    transition: all .18s var(--ef-ease);
    text-decoration: none;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: .3rem;
}
.ef-inv-chip:hover { border-color: var(--ef-border-strong); color: var(--ef-ink); }
.ef-inv-chip.--active          { background: var(--ef-emerald); border-color: var(--ef-emerald); color: #fff; }
.ef-inv-chip.--warn            { background: rgba(216,154,61,.10); border-color: rgba(216,154,61,.32); color: #7D5218; }
.ef-inv-chip.--warn.--active   { background: #7D5218; border-color: #7D5218; color: #fff; }
.ef-inv-chip.--danger          { background: rgba(200,75,68,.08); border-color: rgba(200,75,68,.3); color: var(--ef-danger); }
.ef-inv-chip.--danger.--active { background: var(--ef-danger); border-color: var(--ef-danger); color: #fff; }

.ef-inv-filter-row { display: flex; gap: .6rem; align-items: center; flex-wrap: wrap; }
.ef-inv-search-wrap { position: relative; flex: 1; min-width: 200px; }
.ef-inv-search-icon {
    position: absolute; left: .8rem; top: 50%; transform: translateY(-50%);
    color: var(--ef-muted); font-size: .85rem; pointer-events: none;
}
.ef-inv-search-wrap .ef-input { padding-left: 2.2rem; }
.ef-inv-adv-panel { overflow: hidden; max-height: 0; transition: max-height .35s var(--ef-ease); }
.ef-inv-adv-panel.--open { max-height: 120px; }
.ef-inv-adv-inner {
    padding-top: .75rem; border-top: 1px solid var(--ef-border); margin-top: .75rem;
    display: flex; gap: .6rem; flex-wrap: wrap; align-items: center;
}
.ef-inv-adv-toggle { position: relative; }
.ef-inv-adv-dot {
    width: 6px; height: 6px; border-radius: 50%; background: var(--ef-emerald);
    display: none; position: absolute; top: 6px; right: 6px;
}
.ef-inv-adv-toggle.--has-filter .ef-inv-adv-dot { display: block; }

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
    background: var(--ef-surface);
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
.ef-inv-card.--healthy .ef-inv-card-accent  { background: var(--ef-emerald); }
.ef-inv-card.--low .ef-inv-card-accent      { background: var(--ef-amber); }
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
    color: var(--ef-emerald-dk);
    background: rgba(15,123,95,.09);
    border: 1px solid rgba(15,123,95,.18);
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
.ef-inv-health-chip.--healthy  { background: rgba(15,123,95,.11);  color: #0A5240; }
.ef-inv-health-chip.--low      { background: rgba(216,154,61,.13); color: #7D5218; }
.ef-inv-health-chip.--out      { background: rgba(200,75,68,.11);  color: #9B2C2C; }
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
    background: var(--ef-surface-2);
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
.ef-inv-stat-val.--danger { color: #9B2C2C; }
.ef-inv-stat-val.--warn   { color: #7D5218; }
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
    background: var(--ef-surface-2);
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: .3rem;
}
.ef-inv-bar-fill {
    height: 100%;
    border-radius: 10px;
    background: var(--ef-emerald);
    transition: width .4s var(--ef-ease);
}
.ef-inv-bar-fill.--low { background: var(--ef-amber); }
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
.ef-inv-foot-btn.--primary { background: var(--ef-emerald); color: #fff; }
.ef-inv-foot-btn.--primary:hover { background: var(--ef-emerald-hi); color: #fff; }
.ef-inv-foot-btn.--outline { background: transparent; color: var(--ef-muted); border: 1px solid var(--ef-border); }
.ef-inv-foot-btn.--outline:hover { border-color: var(--ef-border-strong); color: var(--ef-ink); background: var(--ef-surface-2); }
.ef-inv-foot-menu {
    width: 34px; height: 34px;
    border-radius: 7px;
    background: var(--ef-surface-2);
    border: 1px solid var(--ef-border);
    color: var(--ef-muted);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: all .18s;
    flex-shrink: 0;
}
.ef-inv-foot-menu:hover { border-color: var(--ef-border-strong); color: var(--ef-ink); background: var(--ef-surface-2); }

/* ── Empty state ─────────────────────────────────────────── */
.ef-inv-empty {
    background: var(--ef-surface);
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
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    padding: .85rem 1.2rem;
    box-shadow: var(--ef-shadow);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .5rem;
    margin-bottom: 1.5rem;
}
.ef-inv-pagination-info { font-size: .8rem; color: var(--ef-muted); }
</style>
@endpush

@php
    $search    = $filters['search'] ?? '';
    $catId     = $filters['category_id'] ?? '';
    $stockSt   = $filters['stock_status'] ?? '';
    $statusFil = $filters['status'] ?? '';
    $hasAdv    = $catId || $statusFil;
    $alertCount = $stats['low_stock'] + $stats['out_of_stock'];
@endphp

{{-- ── Hero ──────────────────────────────────────────────────── --}}
<x-ds.hero eyebrow="Stock Operations" title="Inventory"
    :meta="[['icon' => 'bi-boxes', 'text' => 'Stock levels · item management · purchase tracking']]">
    <x-slot:actions>
        @if($alertCount > 0)
            <a href="{{ route('admin.inventory.alerts.index') }}" class="ef-ds-btn">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>{{ $alertCount }} Alert{{ $alertCount !== 1 ? 's' : '' }}</span>
            </a>
        @endif
        <a href="{{ route('admin.inventory.bills.index') }}" class="ef-ds-btn">
            <i class="bi bi-clock-history"></i> <span>Bill History</span>
        </a>
        <button type="button" class="ef-ds-btn" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="bi bi-cloud-upload"></i> <span>Upload Bill</span>
        </button>
        <a href="{{ route('admin.inventory.items.create') }}" class="ef-ds-btn --primary">
            <i class="bi bi-plus-lg"></i> <span>Add Item</span>
        </a>
    </x-slot:actions>
    <x-slot:mobile_stat>
        <span class="ef-ds-hero-mstat-val">{{ number_format($stats['total_active']) }}</span>
        <span class="ef-ds-hero-mstat-note">active items &middot; {{ $alertCount }} alert{{ $alertCount !== 1 ? 's' : '' }}</span>
    </x-slot:mobile_stat>
</x-ds.hero>

{{-- ── Upload modal (preserved) ────────────────────────────────── --}}
@include('admin.inventory.bills._upload-modal')

{{-- ── Insight strip ───────────────────────────────────────────── --}}
<div class="ef-ds-kpi-wrap">
    <div class="ef-ds-kpi-grid" style="--kpi-cols:6">
        <x-ds.kpi-card icon="bi-boxes" label="Active Items" :value="number_format($stats['total_active'])" accent="emerald" value-color="c-emerald" />
        <x-ds.kpi-card icon="bi-arrow-down-circle" label="Low Stock"
            :value="$stats['low_stock']"
            :accent="$stats['low_stock'] > 0 ? 'amber' : 'muted'"
            :value-color="$stats['low_stock'] > 0 ? 'c-amber' : ''"
            :href="route('admin.inventory.items.index', array_merge(request()->except('stock_status', 'page'), ['stock_status' => 'low']))" />
        <x-ds.kpi-card icon="bi-x-circle" label="Out of Stock"
            :value="$stats['out_of_stock']"
            :accent="$stats['out_of_stock'] > 0 ? 'danger' : 'muted'"
            :value-color="$stats['out_of_stock'] > 0 ? 'c-danger' : ''"
            :href="route('admin.inventory.items.index', array_merge(request()->except('stock_status', 'page'), ['stock_status' => 'out']))" />
        <x-ds.kpi-card icon="bi-cart3" label="Need Reorder"
            :value="$stats['critical']"
            :accent="$stats['critical'] > 0 ? 'bluegray' : 'muted'"
            :value-color="$stats['critical'] > 0 ? 'c-bluegray' : ''"
            :href="route('admin.purchase-plans.suggestions')" />
        <x-ds.kpi-card icon="bi-currency-rupee" label="Inventory Value"
            value="{{ $stats['inventory_value'] >= 100000 ? '₹' . number_format($stats['inventory_value'] / 100000, 1) . 'L' : '₹' . number_format($stats['inventory_value']) }}"
            accent="gold" value-color="c-gold" />
        <x-ds.kpi-card icon="bi-bag-check" label="This Month's Spend"
            value="{{ $stats['monthly_spend'] >= 100000 ? '₹' . number_format($stats['monthly_spend'] / 100000, 1) . 'L' : '₹' . number_format($stats['monthly_spend']) }}"
            accent="teal" value-color="c-teal" />
    </div>
</div>

{{-- ── Filter bar ──────────────────────────────────────────────── --}}
<form method="GET" id="invFilterForm" action="{{ route('admin.inventory.items.index') }}">
<div class="ef-ds-filter-bar">
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
           class="ef-inv-chip --warn {{ $stockSt === 'critical' ? '--active' : '' }}">
            <i class="bi bi-exclamation-triangle"></i> Critical
        </a>
    </div>

    <div class="ef-inv-filter-row">
        <div class="ef-inv-search-wrap">
            <i class="bi bi-search ef-inv-search-icon"></i>
            <input type="text" name="search" class="ef-input"
                   placeholder="Search name or SKU…" value="{{ $search }}">
        </div>
        <button type="button" class="ef-btn ef-inv-adv-toggle {{ $hasAdv ? '--has-filter' : '' }}" onclick="invToggleAdv(this)">
            <i class="bi bi-sliders2"></i> Filters
            <span class="ef-inv-adv-dot"></span>
        </button>
        <button type="submit" class="ef-btn ef-btn-dark">Search</button>
    </div>

    <div class="ef-inv-adv-panel {{ $hasAdv ? '--open' : '' }}" id="invAdvPanel">
        <div class="ef-inv-adv-inner">
            <select name="category_id" class="ef-select">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $catId == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="ef-select">
                <option value="">All Status</option>
                <option value="active"   {{ $statusFil === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $statusFil === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @if($stockSt)
                <input type="hidden" name="stock_status" value="{{ $stockSt }}">
            @endif
            <a href="{{ route('admin.inventory.items.index') }}" class="ef-btn">
                <i class="bi bi-x-lg"></i> Clear
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
            Try different filters or <a href="{{ route('admin.inventory.items.index') }}" style="color:var(--ef-emerald)">clear all filters</a>.
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
                            <i class="bi bi-clock-history me-2"></i>Transaction History
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <form method="POST"
                              action="{{ route('admin.inventory.items.toggle-status', $item) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="dropdown-item">
                                @if($item->status === 'active')
                                    <i class="bi bi-pause-circle me-2"></i>Deactivate
                                @else
                                    <i class="bi bi-play-circle me-2"></i>Activate
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
