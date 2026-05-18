<x-admin-layout title="Vendors">
@push('styles')
<style>
/* ── Hero ─────────────────────────────────────────────── */
.ef-vd-hero {
    background: var(--ef-hero-grad);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 20px;
    box-shadow: var(--ef-shadow);
    padding: 32px;
    position: relative;
    overflow: hidden;
    margin-bottom: 20px;
}
.ef-vd-hero::before {
    content: '';
    position: absolute;
    background: radial-gradient(circle, rgba(184,137,62,.18) 0%, transparent 68%);
    height: 480px; width: 480px;
    right: -80px; top: -140px;
    pointer-events: none;
}
.ef-vd-hero::after {
    content: '';
    position: absolute;
    background: radial-gradient(circle, rgba(15,123,95,.10) 0%, transparent 68%);
    height: 320px; width: 320px;
    bottom: -80px; left: 30%;
    pointer-events: none;
}
.ef-vd-kicker {
    font-size: .67rem;
    font-weight: 760;
    letter-spacing: .18em;
    text-transform: uppercase;
    color: var(--ef-on-dark-gold);
    margin-bottom: 8px;
}
.ef-vd-title {
    font-size: clamp(1.5rem, 3.5vw, 2.4rem);
    font-weight: 800;
    color: var(--ef-on-dark);
    margin-bottom: 4px;
    line-height: 1.15;
    letter-spacing: -.02em;
}
.ef-vd-subtitle {
    font-size: .86rem;
    color: var(--ef-on-dark-muted);
    margin-bottom: 0;
}
.ef-vd-hero .ef-btn {
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.14);
    color: var(--ef-on-dark-muted);
    font-size: .82rem;
    font-weight: 680;
    padding: 0 16px;
    min-height: 38px;
    border-radius: 10px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background .16s var(--ef-ease), border-color .16s var(--ef-ease);
}
.ef-vd-hero .ef-btn:hover { background: rgba(255,255,255,.13); border-color: rgba(255,255,255,.22); color: var(--ef-on-dark); }
.ef-vd-hero .ef-btn-gold  { background: var(--ef-gold); border-color: var(--ef-gold); color: #fff; }
.ef-vd-hero .ef-btn-gold:hover { background: var(--ef-gold-soft); border-color: var(--ef-gold-soft); }

/* ── KPI strip ────────────────────────────────────────── */
.ef-vd-kpi {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}
.ef-vd-kpi-card {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border-em);
    border-top: 3px solid transparent;
    border-radius: var(--ef-radius);
    padding: 18px 20px;
    position: relative;
    overflow: hidden;
    transition: box-shadow .18s var(--ef-ease), transform .18s var(--ef-ease);
    box-shadow: var(--ef-shadow-dk);
}
.ef-vd-kpi-card:hover { box-shadow: var(--ef-shadow-dk-h); transform: translateY(-2px); }
.ef-vd-kpi-card.kpi-total::before    { display: none; }
.ef-vd-kpi-card.kpi-total    { border-top-color: var(--ef-gold); }
.ef-vd-kpi-card.kpi-active   { border-top-color: var(--ef-emerald); }
.ef-vd-kpi-card.kpi-inactive { border-top-color: var(--ef-danger); }
.ef-vd-kpi-label { font-size: .64rem; font-weight: 720; letter-spacing: .06em; text-transform: uppercase; color: var(--ef-muted); margin-bottom: 6px; }
.ef-vd-kpi-value { font-size: 1.75rem; font-weight: 800; color: var(--ef-ink); line-height: 1; letter-spacing: -.02em; }
.ef-vd-kpi-sub   { font-size: .72rem; color: var(--ef-faint); margin-top: 4px; }

/* ── Search bar ───────────────────────────────────────── */
.ef-vd-search-bar {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    padding: 14px 18px;
    margin-bottom: 20px;
    display: flex;
    gap: 10px;
    align-items: center;
    box-shadow: var(--ef-shadow-dk);
}
.ef-vd-search-wrap { flex: 1; position: relative; }
.ef-vd-search-wrap .bi-search { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--ef-faint); font-size: .85rem; }
.ef-vd-search-input {
    width: 100%;
    border: 1px solid var(--ef-border-strong);
    border-radius: 10px;
    padding: 8px 12px 8px 34px;
    font-size: .86rem;
    color: var(--ef-ink);
    background: var(--ef-surface-2);
    outline: none;
    transition: border-color .16s var(--ef-ease), box-shadow .16s var(--ef-ease);
}
.ef-vd-search-input:focus { border-color: var(--ef-gold); background: var(--ef-surface); box-shadow: 0 0 0 3px rgba(184,137,62,.1); }
.ef-vd-search-btn {
    background: var(--ef-gold);
    border: none;
    color: #fff;
    font-size: .82rem;
    font-weight: 680;
    padding: 0 18px;
    min-height: 38px;
    border-radius: 10px;
    cursor: pointer;
    white-space: nowrap;
    transition: background .16s var(--ef-ease);
}
.ef-vd-search-btn:hover { background: var(--ef-gold-soft); }
.ef-vd-clear-btn {
    background: var(--ef-bg);
    border: 1px solid var(--ef-border-strong);
    color: var(--ef-muted);
    font-size: .82rem;
    font-weight: 600;
    padding: 0 14px;
    min-height: 38px;
    border-radius: 10px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
    transition: background .16s var(--ef-ease);
}
.ef-vd-clear-btn:hover { background: var(--ef-border); color: var(--ef-ink-2); }

/* ── Vendor cards ─────────────────────────────────────── */
.ef-vd-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }
.ef-vd-card {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: 16px;
    overflow: hidden;
    transition: box-shadow .18s var(--ef-ease), transform .18s var(--ef-ease);
    box-shadow: var(--ef-shadow-dk);
}
.ef-vd-card:hover { box-shadow: var(--ef-shadow-hover); transform: translateY(-2px); }
.ef-vd-card.is-inactive { opacity: .72; border-color: var(--ef-border); }
.ef-vd-card-stripe { height: 4px; }
.stripe-active   { background: var(--ef-emerald); }
.stripe-inactive { background: var(--ef-border-strong); }

.ef-vd-card-body { padding: 20px; }
.ef-vd-card-head { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px; }
.ef-vd-avatar {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--ef-bg), var(--ef-border));
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem; font-weight: 700; color: var(--ef-gold);
    flex-shrink: 0; letter-spacing: -.01em;
}
.ef-vd-name  { font-size: .95rem; font-weight: 700; color: var(--ef-ink); margin-bottom: 2px; }
.ef-vd-meta  { font-size: .75rem; color: var(--ef-muted); }
.ef-vd-status-badge {
    font-size: .65rem; font-weight: 700; letter-spacing: .06em;
    text-transform: uppercase; padding: 3px 10px; border-radius: 20px;
    border: 1px solid; white-space: nowrap; margin-left: auto; flex-shrink: 0;
}
.badge-active   { background: rgba(15,123,95,.1); color: var(--ef-emerald); border-color: rgba(15,123,95,.2); }
.badge-inactive { background: rgba(100,116,139,.08); color: var(--ef-muted); border-color: var(--ef-border-strong); }

.ef-vd-details {
    display: flex; flex-direction: column; gap: 5px;
    padding: 12px 0; border-top: 1px solid var(--ef-border); border-bottom: 1px solid var(--ef-border); margin-bottom: 14px;
}
.ef-vd-detail-row { font-size: .78rem; color: var(--ef-muted); display: flex; align-items: flex-start; gap: 7px; }
.ef-vd-detail-row i { color: var(--ef-faint); margin-top: 1px; font-size: .8rem; }

.ef-vd-card-actions { display: flex; gap: 8px; align-items: center; justify-content: flex-end; }
.ef-vd-action-btn {
    font-size: .75rem; font-weight: 600; padding: 5px 12px;
    border-radius: 8px; border: 1px solid; cursor: pointer;
    display: inline-flex; align-items: center; gap: 4px;
    text-decoration: none; transition: all .16s var(--ef-ease); background: transparent;
}
.ef-vd-action-edit        { color: var(--ef-gold);    border-color: rgba(184,137,62,.3); }
.ef-vd-action-edit:hover  { background: var(--ef-gold); border-color: var(--ef-gold); color: #fff; }
.ef-vd-action-toggle-off  { color: var(--ef-faint);   border-color: var(--ef-border-strong); }
.ef-vd-action-toggle-off:hover { background: var(--ef-emerald); border-color: var(--ef-emerald); color: #fff; }
.ef-vd-action-toggle-on   { color: var(--ef-amber);   border-color: rgba(216,154,61,.3); }
.ef-vd-action-toggle-on:hover  { background: var(--ef-amber); border-color: var(--ef-amber); color: #fff; }
.ef-vd-action-delete       { color: var(--ef-danger); border-color: rgba(200,75,68,.25); }
.ef-vd-action-delete:hover { background: var(--ef-danger); border-color: var(--ef-danger); color: #fff; }

/* ── Empty + pagination ───────────────────────────────── */
.ef-vd-empty { text-align: center; padding: 64px 24px; color: var(--ef-muted); grid-column: 1 / -1; }
.ef-vd-empty-icon {
    width: 64px; height: 64px; border-radius: 50%;
    background: var(--ef-bg); display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px; font-size: 1.5rem; color: var(--ef-gold);
}

/* ── Responsive ───────────────────────────────────────── */
@media (max-width: 1199.98px) {
    .ef-vd-hero { padding: 28px; }
    .ef-vd-grid { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); }
}
@media (max-width: 767.98px) {
    .ef-vd-hero  { padding: 20px; }
    .ef-vd-kpi   { grid-template-columns: 1fr 1fr; }
    .ef-vd-title { font-size: 1.3rem; }
    .ef-vd-grid  { grid-template-columns: 1fr; }
    .ef-vd-search-bar { flex-wrap: wrap; }
}
</style>
@endpush

{{-- Hero --}}
<header class="ef-vd-hero">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;position:relative;z-index:1">
        <div>
            <p class="ef-vd-kicker">Supplier Management</p>
            <h1 class="ef-vd-title">Vendors</h1>
            <p class="ef-vd-subtitle">Manage suppliers, track activity &amp; control vendor access</p>
        </div>
        <div>
            <a href="{{ route('admin.vendors.create') }}" class="ef-btn ef-btn-gold">
                <i class="bi bi-plus-lg"></i> Add Vendor
            </a>
        </div>
    </div>
</header>

{{-- KPI strip --}}
<div class="ef-vd-kpi">
    <div class="ef-vd-kpi-card kpi-total">
        <p class="ef-vd-kpi-label">Total Vendors</p>
        <div class="ef-vd-kpi-value">{{ $stats['total'] }}</div>
        <p class="ef-vd-kpi-sub">All suppliers</p>
    </div>
    <div class="ef-vd-kpi-card kpi-active">
        <p class="ef-vd-kpi-label">Active</p>
        <div class="ef-vd-kpi-value">{{ $stats['active'] }}</div>
        <p class="ef-vd-kpi-sub">Accepting orders</p>
    </div>
    <div class="ef-vd-kpi-card kpi-inactive">
        <p class="ef-vd-kpi-label">Inactive</p>
        <div class="ef-vd-kpi-value">{{ $stats['inactive'] }}</div>
        <p class="ef-vd-kpi-sub">Paused / disabled</p>
    </div>
</div>

{{-- Search bar --}}
<div class="ef-vd-search-bar">
    <form method="GET" class="d-contents" style="display:contents">
        <div class="ef-vd-search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" name="search" class="ef-vd-search-input"
                   placeholder="Search vendors by name or phone…"
                   value="{{ $search ?? '' }}">
        </div>
        <button type="submit" class="ef-vd-search-btn">Search</button>
        @if($search)
            <a href="{{ route('admin.vendors.index') }}" class="ef-vd-clear-btn">Clear</a>
        @endif
    </form>
</div>

{{-- Vendor cards --}}
<div class="ef-vd-grid">
    @forelse($vendors as $vendor)
    @php
        $initials = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $vendor->name), 0, 2))));
    @endphp
    <div class="ef-vd-card {{ $vendor->is_active ? '' : 'is-inactive' }}">
        <div class="ef-vd-card-stripe {{ $vendor->is_active ? 'stripe-active' : 'stripe-inactive' }}"></div>
        <div class="ef-vd-card-body">
            <div class="ef-vd-card-head">
                <div class="ef-vd-avatar">{{ $initials }}</div>
                <div class="flex-1 min-w-0" style="min-width:0">
                    <div class="ef-vd-name">{{ $vendor->name }}</div>
                    @if($vendor->notes)
                    <div class="ef-vd-meta">{{ Str::limit($vendor->notes, 50) }}</div>
                    @endif
                </div>
                <span class="ef-vd-status-badge {{ $vendor->is_active ? 'badge-active' : 'badge-inactive' }}">
                    {{ $vendor->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <div class="ef-vd-details">
                @if($vendor->phone)
                <div class="ef-vd-detail-row">
                    <i class="bi bi-telephone"></i>
                    <span>{{ $vendor->phone }}</span>
                </div>
                @endif
                @if($vendor->address)
                <div class="ef-vd-detail-row">
                    <i class="bi bi-geo-alt"></i>
                    <span>{{ Str::limit($vendor->address, 55) }}</span>
                </div>
                @endif
                <div class="ef-vd-detail-row">
                    <i class="bi bi-receipt"></i>
                    <span>{{ $vendor->expense_requests_count }} expense {{ Str::plural('request', $vendor->expense_requests_count) }}</span>
                </div>
            </div>

            <div class="ef-vd-card-actions">
                <a href="{{ route('admin.vendors.edit', $vendor) }}" class="ef-vd-action-btn ef-vd-action-edit">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <form method="POST" action="{{ route('admin.vendors.toggle-status', $vendor) }}" style="display:contents">
                    @csrf @method('PATCH')
                    <button type="submit" class="ef-vd-action-btn {{ $vendor->is_active ? 'ef-vd-action-toggle-on' : 'ef-vd-action-toggle-off' }}">
                        <i class="bi bi-{{ $vendor->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                        {{ $vendor->is_active ? 'Pause' : 'Activate' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.vendors.destroy', $vendor) }}" style="display:contents">
                    @csrf @method('DELETE')
                    <button type="submit" class="ef-vd-action-btn ef-vd-action-delete"
                            onclick="return confirm('Delete {{ addslashes($vendor->name) }}?')">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="ef-vd-empty">
        <div class="ef-vd-empty-icon"><i class="bi bi-shop"></i></div>
        <p style="font-weight:700;color:var(--ef-ink-2);margin-bottom:4px">
            @if($search) No vendors match "{{ $search }}" @else No vendors yet @endif
        </p>
        <p style="font-size:.86rem;color:var(--ef-faint);margin:0">Add your first vendor to start tracking suppliers.</p>
    </div>
    @endforelse
</div>

@if($vendors->hasPages())
<div style="margin-top:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
    <p style="color:var(--ef-faint);font-size:.82rem;margin:0">
        Showing {{ $vendors->firstItem() }}–{{ $vendors->lastItem() }} of {{ $vendors->total() }} vendors
    </p>
    {{ $vendors->links() }}
</div>
@endif

</x-admin-layout>
