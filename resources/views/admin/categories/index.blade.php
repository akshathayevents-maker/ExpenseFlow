<x-admin-layout title="Categories">
@push('styles')
<style>

/* ── Hero ─────────────────────────────────────────────── */
.ef-ct-hero {
    background: var(--ef-hero-grad);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 20px;
    box-shadow: var(--ef-shadow);
    padding: 32px;
    position: relative;
    overflow: hidden;
    margin-bottom: 20px;
}
.ef-ct-hero::before {
    content: '';
    position: absolute;
    background: radial-gradient(circle, rgba(184,137,62,.18) 0%, transparent 68%);
    height: 480px; width: 480px;
    right: -80px; top: -140px;
    pointer-events: none;
}
.ef-ct-hero::after {
    content: '';
    position: absolute;
    background: radial-gradient(circle, rgba(15,123,95,.10) 0%, transparent 68%);
    height: 320px; width: 320px;
    bottom: -80px; left: 30%;
    pointer-events: none;
}
.ef-ct-kicker {
    font-size: .67rem; font-weight: 760; letter-spacing: .18em;
    text-transform: uppercase; color: var(--ef-on-dark-gold); margin-bottom: 8px;
}
.ef-ct-title {
    font-size: clamp(1.5rem, 3.5vw, 2.4rem); font-weight: 800; color: var(--ef-on-dark);
    margin-bottom: 4px; line-height: 1.15; letter-spacing: -.02em;
}
.ef-ct-subtitle { font-size: .86rem; color: var(--ef-on-dark-muted); margin-bottom: 0; }
.ef-ct-hero .ef-btn {
    background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.14);
    color: var(--ef-on-dark-muted); font-size: .82rem; font-weight: 680;
    padding: 0 16px; min-height: 38px; border-radius: 10px; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
    transition: background .16s var(--ef-ease), border-color .16s var(--ef-ease);
}
.ef-ct-hero .ef-btn:hover { background: rgba(255,255,255,.13); border-color: rgba(255,255,255,.22); color: var(--ef-on-dark); }
.ef-ct-hero .ef-btn-gold  { background: var(--ef-gold); border-color: var(--ef-gold); color: #fff; }
.ef-ct-hero .ef-btn-gold:hover { background: var(--ef-gold-soft); border-color: var(--ef-gold-soft); }

/* ── KPI strip ────────────────────────────────────────── */
.ef-ct-kpi { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px; }
.ef-ct-kpi-card {
    background: var(--ef-surface); border: 1px solid var(--ef-border-em);
    border-top: 3px solid transparent; border-radius: var(--ef-radius);
    padding: 18px 20px; position: relative; overflow: hidden;
    transition: box-shadow .18s var(--ef-ease), transform .18s var(--ef-ease);
    box-shadow: var(--ef-shadow-dk);
}
.ef-ct-kpi-card:hover { box-shadow: var(--ef-shadow-dk-h); transform: translateY(-2px); }
.ef-ct-kpi-card.kpi-total    { border-top-color: var(--ef-gold); }
.ef-ct-kpi-card.kpi-active   { border-top-color: var(--ef-emerald); }
.ef-ct-kpi-card.kpi-inactive { border-top-color: var(--ef-danger); }
.ef-ct-kpi-label { font-size: .64rem; font-weight: 720; letter-spacing: .06em; text-transform: uppercase; color: var(--ef-muted); margin-bottom: 6px; }
.ef-ct-kpi-value { font-size: 1.75rem; font-weight: 800; color: var(--ef-ink); line-height: 1; letter-spacing: -.02em; }
.ef-ct-kpi-sub   { font-size: .72rem; color: var(--ef-faint); margin-top: 4px; }

/* ── Search bar ───────────────────────────────────────── */
.ef-ct-search-bar {
    background: var(--ef-surface); border: 1px solid var(--ef-border); border-radius: var(--ef-radius);
    padding: 14px 18px; margin-bottom: 20px; display: flex; gap: 10px; align-items: center;
    box-shadow: var(--ef-shadow-dk);
}
.ef-ct-search-wrap { flex: 1; position: relative; }
.ef-ct-search-wrap .bi-search { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--ef-faint); font-size: .85rem; }
.ef-ct-search-input {
    width: 100%; border: 1px solid var(--ef-border-strong); border-radius: 10px;
    padding: 8px 12px 8px 34px; font-size: .86rem; color: var(--ef-ink);
    background: var(--ef-surface-2); outline: none; transition: border-color .16s var(--ef-ease), box-shadow .16s var(--ef-ease);
}
.ef-ct-search-input:focus { border-color: var(--ef-gold); background: var(--ef-surface); box-shadow: 0 0 0 3px rgba(184,137,62,.1); }
.ef-ct-search-btn {
    background: var(--ef-gold); border: none; color: #fff; font-size: .82rem;
    font-weight: 680; padding: 0 18px; min-height: 38px; border-radius: 10px;
    cursor: pointer; white-space: nowrap; transition: background .16s var(--ef-ease);
}
.ef-ct-search-btn:hover { background: var(--ef-gold-soft); }
.ef-ct-clear-btn {
    background: var(--ef-bg); border: 1px solid var(--ef-border-strong); color: var(--ef-muted);
    font-size: .82rem; font-weight: 600; padding: 0 14px; min-height: 38px; border-radius: 10px;
    text-decoration: none; display: inline-flex; align-items: center; white-space: nowrap;
    transition: background .16s var(--ef-ease);
}
.ef-ct-clear-btn:hover { background: var(--ef-border); color: var(--ef-ink-2); }

/* ── Category cards ───────────────────────────────────── */
.ef-ct-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; }
.ef-ct-card {
    background: var(--ef-surface); border: 1px solid var(--ef-border); border-radius: 16px;
    overflow: hidden; transition: box-shadow .18s var(--ef-ease), transform .18s var(--ef-ease);
    box-shadow: var(--ef-shadow-dk);
}
.ef-ct-card:hover { box-shadow: var(--ef-shadow-hover); transform: translateY(-2px); }
.ef-ct-card.is-inactive { opacity: .72; border-color: var(--ef-border); }
.ef-ct-card-stripe { height: 4px; }
.stripe-active   { background: var(--ef-emerald); }
.stripe-inactive { background: var(--ef-border-strong); }

.ef-ct-card-body { padding: 20px; }
.ef-ct-card-head { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px; }
.ef-ct-icon {
    width: 44px; height: 44px; border-radius: 12px;
    background: linear-gradient(135deg, var(--ef-bg), var(--ef-border));
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; color: var(--ef-gold); flex-shrink: 0;
}
.ef-ct-name { font-size: .95rem; font-weight: 700; color: var(--ef-ink); margin-bottom: 2px; }
.ef-ct-desc { font-size: .75rem; color: var(--ef-muted); }
.ef-ct-status-badge {
    font-size: .65rem; font-weight: 700; letter-spacing: .06em;
    text-transform: uppercase; padding: 3px 10px; border-radius: 20px;
    border: 1px solid; white-space: nowrap; margin-left: auto; flex-shrink: 0;
}
.badge-active   { background: rgba(15,123,95,.1); color: var(--ef-emerald); border-color: rgba(15,123,95,.2); }
.badge-inactive { background: rgba(100,116,139,.08); color: var(--ef-muted); border-color: var(--ef-border-strong); }

.ef-ct-usage {
    padding: 12px 0; border-top: 1px solid var(--ef-border); border-bottom: 1px solid var(--ef-border);
    margin-bottom: 14px; display: flex; align-items: center; gap: 6px;
    font-size: .78rem; color: var(--ef-muted);
}
.ef-ct-usage i { color: var(--ef-faint); }

.ef-ct-card-actions { display: flex; gap: 8px; align-items: center; justify-content: flex-end; }
.ef-ct-action-btn {
    font-size: .75rem; font-weight: 600; padding: 5px 12px;
    border-radius: 8px; border: 1px solid; cursor: pointer;
    display: inline-flex; align-items: center; gap: 4px;
    text-decoration: none; transition: all .16s var(--ef-ease); background: transparent;
}
.ef-ct-action-edit        { color: var(--ef-gold);    border-color: rgba(184,137,62,.3); }
.ef-ct-action-edit:hover  { background: var(--ef-gold); border-color: var(--ef-gold); color: #fff; }
.ef-ct-action-toggle-off  { color: var(--ef-faint);   border-color: var(--ef-border-strong); }
.ef-ct-action-toggle-off:hover { background: var(--ef-emerald); border-color: var(--ef-emerald); color: #fff; }
.ef-ct-action-toggle-on   { color: var(--ef-amber);   border-color: rgba(216,154,61,.3); }
.ef-ct-action-toggle-on:hover  { background: var(--ef-amber); border-color: var(--ef-amber); color: #fff; }
.ef-ct-action-delete       { color: var(--ef-danger); border-color: rgba(200,75,68,.25); }
.ef-ct-action-delete:hover { background: var(--ef-danger); border-color: var(--ef-danger); color: #fff; }

/* ── Empty ────────────────────────────────────────────── */
.ef-ct-empty { text-align: center; padding: 64px 24px; color: var(--ef-muted); grid-column: 1 / -1; }
.ef-ct-empty-icon {
    width: 64px; height: 64px; border-radius: 50%; background: var(--ef-bg);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px; font-size: 1.5rem; color: var(--ef-gold);
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
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;position:relative;z-index:1">
        <div>
            <p class="ef-ct-kicker">Taxonomy</p>
            <h1 class="ef-ct-title">Expense Categories</h1>
            <p class="ef-ct-subtitle">Classify &amp; organise expense requests across the organisation</p>
        </div>
        <div>
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
        <p style="font-weight:700;color:var(--ef-ink-2);margin-bottom:4px">
            @if($search) No categories match "{{ $search }}" @else No categories yet @endif
        </p>
        <p style="font-size:.86rem;color:var(--ef-faint);margin:0">Create categories to classify expense requests.</p>
    </div>
    @endforelse
</div>

@if($categories->hasPages())
<div style="margin-top:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
    <p style="color:var(--ef-faint);font-size:.82rem;margin:0">
        Showing {{ $categories->firstItem() }}–{{ $categories->lastItem() }} of {{ $categories->total() }}
    </p>
    {{ $categories->links() }}
</div>
@endif

</x-admin-layout>
