<x-admin-layout title="Categories">
@push('styles')
<style>
:root {
    --ct-gold: #a07238;
    --ct-gold-hi: #b8854a;
    --ct-emerald: #1a6645;
    --ct-danger: #b91c1c;
    --ct-indigo: #4338ca;
}

/* ── Hero ─────────────────────────────────────────────── */
.ef-ct-hero {
    background: linear-gradient(135deg, #1a1410 0%, #2a2218 50%, #1e1812 100%);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(26,22,18,.16), 0 1px 4px rgba(26,22,18,.1);
    padding: 32px;
    position: relative;
    overflow: hidden;
    margin-bottom: 24px;
}
.ef-ct-hero::before {
    content: '';
    position: absolute;
    background: radial-gradient(circle, rgba(160,114,56,.16) 0%, transparent 68%);
    height: 480px; width: 480px;
    right: -80px; top: -140px;
    pointer-events: none;
}
.ef-ct-hero::after {
    content: '';
    position: absolute;
    background: radial-gradient(circle, rgba(26,102,69,.1) 0%, transparent 68%);
    height: 320px; width: 320px;
    bottom: -80px; left: 30%;
    pointer-events: none;
}
.ef-ct-kicker {
    font-size: .7rem; font-weight: 700; letter-spacing: .12em;
    text-transform: uppercase; color: rgba(160,114,56,.9); margin-bottom: 6px;
}
.ef-ct-title {
    font-size: 1.6rem; font-weight: 700; color: #fffdfa;
    margin-bottom: 4px; line-height: 1.2;
}
.ef-ct-subtitle { font-size: .85rem; color: rgba(255,253,250,.48); margin-bottom: 0; }
.ef-ct-hero .ef-btn {
    background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.14);
    color: rgba(255,253,250,.85); font-size: .8rem; font-weight: 600;
    padding: 7px 16px; border-radius: 8px; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
    transition: background .15s, border-color .15s;
}
.ef-ct-hero .ef-btn:hover {
    background: rgba(255,255,255,.13); border-color: rgba(255,255,255,.22); color: #fffdfa;
}
.ef-ct-hero .ef-btn-gold {
    background: var(--ct-gold); border-color: var(--ct-gold); color: #fff;
}
.ef-ct-hero .ef-btn-gold:hover {
    background: var(--ct-gold-hi); border-color: var(--ct-gold-hi); color: #fff;
}

/* ── KPI strip ────────────────────────────────────────── */
.ef-ct-kpi {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 12px; margin-bottom: 24px;
}
.ef-ct-kpi-card {
    background: #fff; border: 1px solid #e8e3dc; border-radius: 14px;
    padding: 18px 20px; position: relative; overflow: hidden;
    transition: box-shadow .15s, transform .15s;
}
.ef-ct-kpi-card:hover { box-shadow: 0 4px 16px rgba(160,114,56,.1); transform: translateY(-1px); }
.ef-ct-kpi-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0;
    height: 3px; border-radius: 14px 14px 0 0;
}
.ef-ct-kpi-card.kpi-total::before    { background: var(--ct-gold); }
.ef-ct-kpi-card.kpi-active::before   { background: var(--ct-emerald); }
.ef-ct-kpi-card.kpi-inactive::before { background: var(--ct-danger); }
.ef-ct-kpi-label {
    font-size: .68rem; font-weight: 700; letter-spacing: .08em;
    text-transform: uppercase; color: #9c8e7e; margin-bottom: 6px;
}
.ef-ct-kpi-value { font-size: 1.75rem; font-weight: 700; color: #1c1612; line-height: 1; }
.ef-ct-kpi-sub   { font-size: .72rem; color: #b0a090; margin-top: 4px; }

/* ── Search bar ───────────────────────────────────────── */
.ef-ct-search-bar {
    background: #fff; border: 1px solid #e8e3dc; border-radius: 14px;
    padding: 16px 20px; margin-bottom: 20px;
    display: flex; gap: 10px; align-items: center;
}
.ef-ct-search-wrap { flex: 1; position: relative; }
.ef-ct-search-wrap .bi-search {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    color: #b0a090; font-size: .85rem;
}
.ef-ct-search-input {
    width: 100%; border: 1px solid #e2ddd6; border-radius: 8px;
    padding: 7px 12px 7px 34px; font-size: .85rem; color: #1c1612;
    background: #faf8f5; outline: none; transition: border-color .15s;
}
.ef-ct-search-input:focus { border-color: var(--ct-gold); background: #fff; }
.ef-ct-search-btn {
    background: var(--ct-gold); border: none; color: #fff; font-size: .8rem;
    font-weight: 600; padding: 8px 18px; border-radius: 8px; cursor: pointer;
    white-space: nowrap; transition: background .15s;
}
.ef-ct-search-btn:hover { background: var(--ct-gold-hi); }
.ef-ct-clear-btn {
    background: #f5f1eb; border: 1px solid #e2ddd6; color: #6b5e4e;
    font-size: .8rem; font-weight: 600; padding: 7px 14px; border-radius: 8px;
    text-decoration: none; white-space: nowrap; transition: background .15s;
}
.ef-ct-clear-btn:hover { background: #ece6dd; color: #3d3528; }

/* ── Category cards ───────────────────────────────────── */
.ef-ct-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
}
.ef-ct-card {
    background: #fff; border: 1px solid #e8e3dc; border-radius: 16px;
    overflow: hidden; transition: box-shadow .18s, transform .18s;
}
.ef-ct-card:hover { box-shadow: 0 6px 24px rgba(160,114,56,.12); transform: translateY(-2px); }
.ef-ct-card.is-inactive { opacity: .72; border-color: #f0ece6; }
.ef-ct-card-stripe { height: 4px; }
.stripe-active   { background: var(--ct-emerald); }
.stripe-inactive { background: #d1d5db; }

.ef-ct-card-body { padding: 20px; }
.ef-ct-card-head {
    display: flex; align-items: flex-start;
    gap: 12px; margin-bottom: 14px;
}
.ef-ct-icon {
    width: 44px; height: 44px; border-radius: 12px;
    background: linear-gradient(135deg, #f5f1eb, #ece6dd);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; color: var(--ct-gold); flex-shrink: 0;
}
.ef-ct-name { font-size: .95rem; font-weight: 700; color: #1c1612; margin-bottom: 2px; }
.ef-ct-desc { font-size: .75rem; color: #9c8e7e; }
.ef-ct-status-badge {
    font-size: .65rem; font-weight: 700; letter-spacing: .06em;
    text-transform: uppercase; padding: 3px 10px; border-radius: 20px;
    border: 1px solid; white-space: nowrap; margin-left: auto; flex-shrink: 0;
}
.badge-active   { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
.badge-inactive { background: #f3f4f6; color: #6b7280; border-color: #d1d5db; }

.ef-ct-usage {
    padding: 12px 0;
    border-top: 1px solid #f0ece6;
    border-bottom: 1px solid #f0ece6;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: .78rem;
    color: #6b5e4e;
}
.ef-ct-usage i { color: #b0a090; }

.ef-ct-card-actions {
    display: flex; gap: 8px;
    align-items: center; justify-content: flex-end;
}
.ef-ct-action-btn {
    font-size: .75rem; font-weight: 600; padding: 5px 12px;
    border-radius: 8px; border: 1px solid; cursor: pointer;
    display: inline-flex; align-items: center; gap: 4px;
    text-decoration: none; transition: all .15s; background: transparent;
}
.ef-ct-action-edit { color: var(--ct-gold); border-color: rgba(160,114,56,.3); }
.ef-ct-action-edit:hover { background: var(--ct-gold); border-color: var(--ct-gold); color: #fff; }
.ef-ct-action-toggle-off { color: #b0a090; border-color: #e2ddd6; }
.ef-ct-action-toggle-off:hover { background: var(--ct-emerald); border-color: var(--ct-emerald); color: #fff; }
.ef-ct-action-toggle-on { color: #d97706; border-color: rgba(217,119,6,.25); }
.ef-ct-action-toggle-on:hover { background: #d97706; border-color: #d97706; color: #fff; }
.ef-ct-action-delete { color: var(--ct-danger); border-color: rgba(185,28,28,.25); }
.ef-ct-action-delete:hover { background: var(--ct-danger); border-color: var(--ct-danger); color: #fff; }

/* ── Empty ────────────────────────────────────────────── */
.ef-ct-empty {
    text-align: center; padding: 64px 24px; color: #9c8e7e; grid-column: 1 / -1;
}
.ef-ct-empty-icon {
    width: 64px; height: 64px; border-radius: 50%; background: #f5f1eb;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px; font-size: 1.5rem; color: var(--ct-gold);
}

/* ── Responsive ───────────────────────────────────────── */
@media (max-width: 1199.98px) {
    .ef-ct-hero { padding: 28px; }
    .ef-ct-grid { grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); }
}
@media (max-width: 767.98px) {
    .ef-ct-hero  { padding: 20px; }
    .ef-ct-title { font-size: 1.3rem; }
    .ef-ct-grid  { grid-template-columns: 1fr; }
    .ef-ct-search-bar { flex-wrap: wrap; }
}
</style>
@endpush

{{-- Hero --}}
<header class="ef-ct-hero">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div style="position:relative;z-index:1">
            <p class="ef-ct-kicker">Taxonomy</p>
            <h1 class="ef-ct-title">Expense Categories</h1>
            <p class="ef-ct-subtitle">Classify &amp; organise expense requests across the organisation</p>
        </div>
        <div style="position:relative;z-index:1">
            <a href="{{ route('admin.categories.create') }}" class="ef-btn ef-btn-gold">
                <i class="bi bi-plus-lg"></i> Add Category
            </a>
        </div>
    </div>
</header>

{{-- KPI strip --}}
<div class="ef-ct-kpi">
    <div class="ef-ct-kpi-card kpi-total">
        <p class="ef-ct-kpi-label">Total Categories</p>
        <div class="ef-ct-kpi-value">{{ $stats['total'] }}</div>
        <p class="ef-ct-kpi-sub">All expense types</p>
    </div>
    <div class="ef-ct-kpi-card kpi-active">
        <p class="ef-ct-kpi-label">Active</p>
        <div class="ef-ct-kpi-value">{{ $stats['active'] }}</div>
        <p class="ef-ct-kpi-sub">Available for use</p>
    </div>
    <div class="ef-ct-kpi-card kpi-inactive">
        <p class="ef-ct-kpi-label">Inactive</p>
        <div class="ef-ct-kpi-value">{{ $stats['inactive'] }}</div>
        <p class="ef-ct-kpi-sub">Hidden from forms</p>
    </div>
</div>

{{-- Search bar --}}
<div class="ef-ct-search-bar">
    <form method="GET" style="display:contents">
        <div class="ef-ct-search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" name="search" class="ef-ct-search-input"
                   placeholder="Search categories by name or description…"
                   value="{{ $search ?? '' }}">
        </div>
        <button type="submit" class="ef-ct-search-btn">Search</button>
        @if($search)
            <a href="{{ route('admin.categories.index') }}" class="ef-ct-clear-btn">Clear</a>
        @endif
    </form>
</div>

{{-- Category cards --}}
<div class="ef-ct-grid">
    @forelse($categories as $category)
    <div class="ef-ct-card {{ $category->is_active ? '' : 'is-inactive' }}">
        <div class="ef-ct-card-stripe {{ $category->is_active ? 'stripe-active' : 'stripe-inactive' }}"></div>
        <div class="ef-ct-card-body">
            <div class="ef-ct-card-head">
                <div class="ef-ct-icon"><i class="bi bi-tag"></i></div>
                <div style="min-width:0;flex:1">
                    <div class="ef-ct-name">{{ $category->name }}</div>
                    @if($category->description)
                    <div class="ef-ct-desc">{{ Str::limit($category->description, 55) }}</div>
                    @endif
                </div>
                <span class="ef-ct-status-badge {{ $category->is_active ? 'badge-active' : 'badge-inactive' }}">
                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <div class="ef-ct-usage">
                <i class="bi bi-receipt"></i>
                <span>{{ $category->expense_requests_count }} expense {{ Str::plural('request', $category->expense_requests_count) }}</span>
            </div>

            <div class="ef-ct-card-actions">
                <a href="{{ route('admin.categories.edit', $category) }}" class="ef-ct-action-btn ef-ct-action-edit">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <form method="POST" action="{{ route('admin.categories.toggle-status', $category) }}" style="display:contents">
                    @csrf @method('PATCH')
                    <button type="submit" class="ef-ct-action-btn {{ $category->is_active ? 'ef-ct-action-toggle-on' : 'ef-ct-action-toggle-off' }}">
                        <i class="bi bi-{{ $category->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                        {{ $category->is_active ? 'Pause' : 'Activate' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" style="display:contents">
                    @csrf @method('DELETE')
                    <button type="submit" class="ef-ct-action-btn ef-ct-action-delete"
                            onclick="return confirm('Delete category {{ addslashes($category->name) }}?')">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="ef-ct-empty">
        <div class="ef-ct-empty-icon"><i class="bi bi-tag"></i></div>
        <p class="fw-semibold mb-1" style="color:#3d3528">
            @if($search) No categories match "{{ $search }}" @else No categories yet @endif
        </p>
        <p class="small mb-0">Create categories to classify expense requests.</p>
    </div>
    @endforelse
</div>

@if($categories->hasPages())
<div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <p class="text-muted small mb-0">
        Showing {{ $categories->firstItem() }}–{{ $categories->lastItem() }} of {{ $categories->total() }}
    </p>
    {{ $categories->links('pagination::bootstrap-5') }}
</div>
@endif

</x-admin-layout>
