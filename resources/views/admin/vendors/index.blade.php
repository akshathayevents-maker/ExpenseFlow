<x-admin-layout title="Vendors">
@push('styles')
<style>
:root {
    --vd-gold: #a07238;
    --vd-gold-hi: #b8854a;
    --vd-emerald: #1a6645;
    --vd-danger: #b91c1c;
}

/* ── Hero ─────────────────────────────────────────────── */
.ef-vd-hero {
    background: linear-gradient(135deg, #1a1410 0%, #2a2218 50%, #1e1812 100%);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(26,22,18,.16), 0 1px 4px rgba(26,22,18,.1);
    padding: 32px;
    position: relative;
    overflow: hidden;
    margin-bottom: 24px;
}
.ef-vd-hero::before {
    content: '';
    position: absolute;
    background: radial-gradient(circle, rgba(160,114,56,.16) 0%, transparent 68%);
    height: 480px; width: 480px;
    right: -80px; top: -140px;
    pointer-events: none;
}
.ef-vd-hero::after {
    content: '';
    position: absolute;
    background: radial-gradient(circle, rgba(26,102,69,.1) 0%, transparent 68%);
    height: 320px; width: 320px;
    bottom: -80px; left: 30%;
    pointer-events: none;
}
.ef-vd-kicker {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: rgba(160,114,56,.9);
    margin-bottom: 6px;
}
.ef-vd-title {
    font-size: 1.6rem;
    font-weight: 700;
    color: #fffdfa;
    margin-bottom: 4px;
    line-height: 1.2;
}
.ef-vd-subtitle {
    font-size: .85rem;
    color: rgba(255,253,250,.48);
    margin-bottom: 0;
}
.ef-vd-hero .ef-btn {
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.14);
    color: rgba(255,253,250,.85);
    font-size: .8rem;
    font-weight: 600;
    padding: 7px 16px;
    border-radius: 8px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background .15s, border-color .15s;
}
.ef-vd-hero .ef-btn:hover {
    background: rgba(255,255,255,.13);
    border-color: rgba(255,255,255,.22);
    color: #fffdfa;
}
.ef-vd-hero .ef-btn-gold {
    background: var(--vd-gold);
    border-color: var(--vd-gold);
    color: #fff;
}
.ef-vd-hero .ef-btn-gold:hover {
    background: var(--vd-gold-hi);
    border-color: var(--vd-gold-hi);
    color: #fff;
}

/* ── KPI strip ────────────────────────────────────────── */
.ef-vd-kpi {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}
.ef-vd-kpi-card {
    background: #fff;
    border: 1px solid #e8e3dc;
    border-radius: 14px;
    padding: 18px 20px;
    position: relative;
    overflow: hidden;
    transition: box-shadow .15s, transform .15s;
}
.ef-vd-kpi-card:hover {
    box-shadow: 0 4px 16px rgba(160,114,56,.1);
    transform: translateY(-1px);
}
.ef-vd-kpi-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 14px 14px 0 0;
}
.ef-vd-kpi-card.kpi-total::before    { background: var(--vd-gold); }
.ef-vd-kpi-card.kpi-active::before   { background: var(--vd-emerald); }
.ef-vd-kpi-card.kpi-inactive::before { background: var(--vd-danger); }
.ef-vd-kpi-label {
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #9c8e7e;
    margin-bottom: 6px;
}
.ef-vd-kpi-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1c1612;
    line-height: 1;
}
.ef-vd-kpi-sub {
    font-size: .72rem;
    color: #b0a090;
    margin-top: 4px;
}

/* ── Search bar ───────────────────────────────────────── */
.ef-vd-search-bar {
    background: #fff;
    border: 1px solid #e8e3dc;
    border-radius: 14px;
    padding: 16px 20px;
    margin-bottom: 20px;
    display: flex;
    gap: 10px;
    align-items: center;
}
.ef-vd-search-wrap {
    flex: 1;
    position: relative;
}
.ef-vd-search-wrap .bi-search {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #b0a090;
    font-size: .85rem;
}
.ef-vd-search-input {
    width: 100%;
    border: 1px solid #e2ddd6;
    border-radius: 8px;
    padding: 7px 12px 7px 34px;
    font-size: .85rem;
    color: #1c1612;
    background: #faf8f5;
    outline: none;
    transition: border-color .15s;
}
.ef-vd-search-input:focus { border-color: var(--vd-gold); background: #fff; }
.ef-vd-search-btn {
    background: var(--vd-gold);
    border: none;
    color: #fff;
    font-size: .8rem;
    font-weight: 600;
    padding: 8px 18px;
    border-radius: 8px;
    cursor: pointer;
    white-space: nowrap;
    transition: background .15s;
}
.ef-vd-search-btn:hover { background: var(--vd-gold-hi); }
.ef-vd-clear-btn {
    background: #f5f1eb;
    border: 1px solid #e2ddd6;
    color: #6b5e4e;
    font-size: .8rem;
    font-weight: 600;
    padding: 7px 14px;
    border-radius: 8px;
    text-decoration: none;
    white-space: nowrap;
    transition: background .15s;
}
.ef-vd-clear-btn:hover { background: #ece6dd; color: #3d3528; }

/* ── Vendor cards ─────────────────────────────────────── */
.ef-vd-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
}
.ef-vd-card {
    background: #fff;
    border: 1px solid #e8e3dc;
    border-radius: 16px;
    overflow: hidden;
    transition: box-shadow .18s, transform .18s;
}
.ef-vd-card:hover {
    box-shadow: 0 6px 24px rgba(160,114,56,.12);
    transform: translateY(-2px);
}
.ef-vd-card.is-inactive {
    opacity: .72;
    border-color: #f0ece6;
}
.ef-vd-card-stripe {
    height: 4px;
}
.stripe-active   { background: var(--vd-emerald); }
.stripe-inactive { background: #d1d5db; }

.ef-vd-card-body { padding: 20px; }
.ef-vd-card-head {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 14px;
}
.ef-vd-avatar {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #f5f1eb, #ece6dd);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .95rem;
    font-weight: 700;
    color: var(--vd-gold);
    flex-shrink: 0;
    letter-spacing: -.01em;
}
.ef-vd-name {
    font-size: .95rem;
    font-weight: 700;
    color: #1c1612;
    margin-bottom: 2px;
}
.ef-vd-meta {
    font-size: .75rem;
    color: #9c8e7e;
}
.ef-vd-status-badge {
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 20px;
    border: 1px solid;
    white-space: nowrap;
    margin-left: auto;
    flex-shrink: 0;
}
.badge-active   { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
.badge-inactive { background: #f3f4f6; color: #6b7280; border-color: #d1d5db; }

.ef-vd-details {
    display: flex;
    flex-direction: column;
    gap: 5px;
    padding: 12px 0;
    border-top: 1px solid #f0ece6;
    border-bottom: 1px solid #f0ece6;
    margin-bottom: 14px;
}
.ef-vd-detail-row {
    font-size: .78rem;
    color: #6b5e4e;
    display: flex;
    align-items: flex-start;
    gap: 7px;
}
.ef-vd-detail-row i { color: #b0a090; margin-top: 1px; font-size: .8rem; }

.ef-vd-card-actions {
    display: flex;
    gap: 8px;
    align-items: center;
    justify-content: flex-end;
}
.ef-vd-action-btn {
    font-size: .75rem;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 8px;
    border: 1px solid;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    text-decoration: none;
    transition: all .15s;
    background: transparent;
}
.ef-vd-action-edit {
    color: var(--vd-gold);
    border-color: rgba(160,114,56,.3);
}
.ef-vd-action-edit:hover {
    background: var(--vd-gold);
    border-color: var(--vd-gold);
    color: #fff;
}
.ef-vd-action-toggle-off {
    color: #b0a090;
    border-color: #e2ddd6;
}
.ef-vd-action-toggle-off:hover {
    background: var(--vd-emerald);
    border-color: var(--vd-emerald);
    color: #fff;
}
.ef-vd-action-toggle-on {
    color: #d97706;
    border-color: rgba(217,119,6,.25);
}
.ef-vd-action-toggle-on:hover {
    background: #d97706;
    border-color: #d97706;
    color: #fff;
}
.ef-vd-action-delete {
    color: var(--vd-danger);
    border-color: rgba(185,28,28,.25);
}
.ef-vd-action-delete:hover {
    background: var(--vd-danger);
    border-color: var(--vd-danger);
    color: #fff;
}

/* ── Empty + pagination ───────────────────────────────── */
.ef-vd-empty {
    text-align: center;
    padding: 64px 24px;
    color: #9c8e7e;
    grid-column: 1 / -1;
}
.ef-vd-empty-icon {
    width: 64px; height: 64px;
    border-radius: 50%;
    background: #f5f1eb;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 1.5rem;
    color: var(--vd-gold);
}

/* ── Responsive ───────────────────────────────────────── */
@media (max-width: 1199.98px) {
    .ef-vd-hero { padding: 28px; }
    .ef-vd-grid { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); }
}
@media (max-width: 767.98px) {
    .ef-vd-hero  { padding: 20px; }
    .ef-vd-kpi   { grid-template-columns: repeat(3, 1fr); }
    .ef-vd-title { font-size: 1.3rem; }
    .ef-vd-grid  { grid-template-columns: 1fr; }
    .ef-vd-search-bar { flex-wrap: wrap; }
}
</style>
@endpush

{{-- Hero --}}
<header class="ef-vd-hero">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div style="position:relative;z-index:1">
            <p class="ef-vd-kicker">Supplier Management</p>
            <h1 class="ef-vd-title">Vendors</h1>
            <p class="ef-vd-subtitle">Manage suppliers, track activity &amp; control vendor access</p>
        </div>
        <div style="position:relative;z-index:1">
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
        <p class="fw-semibold mb-1" style="color:#3d3528">
            @if($search) No vendors match "{{ $search }}" @else No vendors yet @endif
        </p>
        <p class="small mb-0">Add your first vendor to start tracking suppliers.</p>
    </div>
    @endforelse
</div>

@if($vendors->hasPages())
<div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <p class="text-muted small mb-0">
        Showing {{ $vendors->firstItem() }}–{{ $vendors->lastItem() }} of {{ $vendors->total() }} vendors
    </p>
    {{ $vendors->links('pagination::bootstrap-5') }}
</div>
@endif

</x-admin-layout>
