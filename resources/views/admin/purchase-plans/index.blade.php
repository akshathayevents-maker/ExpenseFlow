<x-admin-layout title="Purchase Plans">
@push('styles')
<style>
:root {
    --pp-gold: #a07238;
    --pp-gold-hi: #b8854a;
    --pp-emerald: #1a6645;
    --pp-danger: #b91c1c;
    --pp-indigo: #4338ca;
}

/* ── Hero ─────────────────────────────────────────────── */
.ef-pp-hero {
    background: linear-gradient(135deg, #1a1410 0%, #2a2218 50%, #1e1812 100%);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(26,22,18,.16), 0 1px 4px rgba(26,22,18,.1);
    padding: 32px;
    position: relative;
    overflow: hidden;
    margin-bottom: 24px;
}
.ef-pp-hero::before {
    content: '';
    position: absolute;
    background: radial-gradient(circle, rgba(160,114,56,.16) 0%, transparent 68%);
    height: 480px; width: 480px;
    right: -80px; top: -140px;
    pointer-events: none;
}
.ef-pp-hero::after {
    content: '';
    position: absolute;
    background: radial-gradient(circle, rgba(26,102,69,.1) 0%, transparent 68%);
    height: 320px; width: 320px;
    bottom: -80px; left: 30%;
    pointer-events: none;
}
.ef-pp-kicker {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: rgba(160,114,56,.9);
    margin-bottom: 6px;
}
.ef-pp-title {
    font-size: 1.6rem;
    font-weight: 700;
    color: #fffdfa;
    margin-bottom: 4px;
    line-height: 1.2;
}
.ef-pp-subtitle {
    font-size: .85rem;
    color: rgba(255,253,250,.48);
    margin-bottom: 0;
}
.ef-pp-hero .ef-btn {
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
.ef-pp-hero .ef-btn:hover {
    background: rgba(255,255,255,.13);
    border-color: rgba(255,255,255,.22);
    color: #fffdfa;
}
.ef-pp-hero .ef-btn-gold {
    background: var(--pp-gold);
    border-color: var(--pp-gold);
    color: #fff;
}
.ef-pp-hero .ef-btn-gold:hover {
    background: var(--pp-gold-hi);
    border-color: var(--pp-gold-hi);
    color: #fff;
}

/* ── KPI strip ────────────────────────────────────────── */
.ef-pp-kpi {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}
.ef-pp-kpi-card {
    background: #fff;
    border: 1px solid #e8e3dc;
    border-radius: 14px;
    padding: 18px 20px;
    position: relative;
    overflow: hidden;
    transition: box-shadow .15s, transform .15s;
}
.ef-pp-kpi-card:hover {
    box-shadow: 0 4px 16px rgba(160,114,56,.1);
    transform: translateY(-1px);
}
.ef-pp-kpi-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 14px 14px 0 0;
}
.ef-pp-kpi-card.kpi-draft::before    { background: #9ca3af; }
.ef-pp-kpi-card.kpi-approved::before { background: var(--pp-emerald); }
.ef-pp-kpi-card.kpi-ordered::before  { background: var(--pp-indigo); }
.ef-pp-kpi-card.kpi-done::before     { background: #0891b2; }
.ef-pp-kpi-card.kpi-total::before    { background: var(--pp-gold); }
.ef-pp-kpi-label {
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #9c8e7e;
    margin-bottom: 6px;
}
.ef-pp-kpi-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1c1612;
    line-height: 1;
}
.ef-pp-kpi-sub {
    font-size: .72rem;
    color: #b0a090;
    margin-top: 4px;
}

/* ── Filter chips ─────────────────────────────────────── */
.ef-pp-filters {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
    margin-bottom: 20px;
}
.ef-pp-chip {
    font-size: .75rem;
    font-weight: 600;
    padding: 5px 14px;
    border-radius: 20px;
    border: 1px solid #e2ddd6;
    background: #f9f6f2;
    color: #6b5e4e;
    cursor: pointer;
    text-decoration: none;
    transition: all .15s;
}
.ef-pp-chip:hover, .ef-pp-chip.active {
    background: var(--pp-gold);
    border-color: var(--pp-gold);
    color: #fff;
}
.ef-pp-chip.chip-draft.active    { background: #6b7280; border-color: #6b7280; color: #fff; }
.ef-pp-chip.chip-approved.active { background: var(--pp-emerald); border-color: var(--pp-emerald); color: #fff; }
.ef-pp-chip.chip-ordered.active  { background: var(--pp-indigo); border-color: var(--pp-indigo); color: #fff; }
.ef-pp-chip.chip-done.active     { background: #0891b2; border-color: #0891b2; color: #fff; }

/* ── Plan cards ───────────────────────────────────────── */
.ef-pp-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 16px;
}
.ef-pp-card {
    background: #fff;
    border: 1px solid #e8e3dc;
    border-radius: 16px;
    overflow: hidden;
    transition: box-shadow .18s, transform .18s;
}
.ef-pp-card:hover {
    box-shadow: 0 6px 24px rgba(160,114,56,.12);
    transform: translateY(-2px);
}
.ef-pp-card-stripe {
    height: 4px;
}
.stripe-draft     { background: #9ca3af; }
.stripe-approved  { background: var(--pp-emerald); }
.stripe-ordered   { background: var(--pp-indigo); }
.stripe-completed { background: #0891b2; }
.stripe-cancelled { background: var(--pp-danger); }

.ef-pp-card-body { padding: 20px; }
.ef-pp-card-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}
.ef-pp-card-title {
    font-size: .95rem;
    font-weight: 700;
    color: #1c1612;
    margin-bottom: 2px;
}
.ef-pp-card-meta {
    font-size: .75rem;
    color: #9c8e7e;
}
.ef-pp-status-badge {
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 20px;
    border: 1px solid;
    white-space: nowrap;
}
.badge-draft     { background: #f3f4f6; color: #6b7280; border-color: #d1d5db; }
.badge-approved  { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
.badge-ordered   { background: #ede9fe; color: #5b21b6; border-color: #ddd6fe; }
.badge-completed { background: #cffafe; color: #0e7490; border-color: #a5f3fc; }
.badge-cancelled { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }

.ef-pp-card-stats {
    display: flex;
    gap: 16px;
    padding: 12px 0;
    border-top: 1px solid #f0ece6;
    border-bottom: 1px solid #f0ece6;
    margin-bottom: 14px;
}
.ef-pp-stat-item { text-align: center; flex: 1; }
.ef-pp-stat-val {
    font-size: .9rem;
    font-weight: 700;
    color: #1c1612;
}
.ef-pp-stat-lbl {
    font-size: .65rem;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #b0a090;
}
.ef-pp-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.ef-pp-creator {
    font-size: .75rem;
    color: #9c8e7e;
    display: flex;
    align-items: center;
    gap: 5px;
}
.ef-pp-creator-avatar {
    width: 22px; height: 22px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--pp-gold), var(--pp-gold-hi));
    color: #fff;
    font-size: .6rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.ef-pp-view-btn {
    font-size: .75rem;
    font-weight: 600;
    color: var(--pp-gold);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 12px;
    border: 1px solid rgba(160,114,56,.3);
    border-radius: 8px;
    transition: all .15s;
}
.ef-pp-view-btn:hover {
    background: var(--pp-gold);
    border-color: var(--pp-gold);
    color: #fff;
}

/* ── Empty state ──────────────────────────────────────── */
.ef-pp-empty {
    text-align: center;
    padding: 64px 24px;
    color: #9c8e7e;
    grid-column: 1 / -1;
}
.ef-pp-empty-icon {
    width: 64px; height: 64px;
    border-radius: 50%;
    background: #f5f1eb;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 1.5rem;
    color: var(--pp-gold);
}

/* ── Responsive ───────────────────────────────────────── */
@media (max-width: 1199.98px) {
    .ef-pp-hero  { padding: 28px; }
    .ef-pp-kpi   { grid-template-columns: repeat(3, 1fr); }
    .ef-pp-grid  { grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); }
}
@media (max-width: 767.98px) {
    .ef-pp-hero  { padding: 20px; }
    .ef-pp-kpi   { grid-template-columns: repeat(2, 1fr); }
    .ef-pp-title { font-size: 1.3rem; }
    .ef-pp-grid  { grid-template-columns: 1fr; }
}
</style>
@endpush

{{-- Hero --}}
<header class="ef-pp-hero">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div style="position:relative;z-index:1">
            <p class="ef-pp-kicker">Procurement</p>
            <h1 class="ef-pp-title">Purchase Plans</h1>
            <p class="ef-pp-subtitle">Planned procurement from low stock suggestions &amp; manual requests</p>
        </div>
        <div class="d-flex gap-2 flex-wrap" style="position:relative;z-index:1">
            <a href="{{ route('admin.purchase-plans.suggestions') }}" class="ef-btn">
                <i class="bi bi-lightbulb"></i> Suggestions
            </a>
            <a href="{{ route('admin.purchase-plans.create') }}" class="ef-btn ef-btn-gold">
                <i class="bi bi-plus-lg"></i> New Plan
            </a>
        </div>
    </div>
</header>

{{-- KPI strip --}}
<div class="ef-pp-kpi">
    <div class="ef-pp-kpi-card kpi-draft">
        <p class="ef-pp-kpi-label">Draft</p>
        <div class="ef-pp-kpi-value">{{ $stats['draft'] }}</div>
        <p class="ef-pp-kpi-sub">Awaiting approval</p>
    </div>
    <div class="ef-pp-kpi-card kpi-approved">
        <p class="ef-pp-kpi-label">Approved</p>
        <div class="ef-pp-kpi-value">{{ $stats['approved'] }}</div>
        <p class="ef-pp-kpi-sub">Ready to order</p>
    </div>
    <div class="ef-pp-kpi-card kpi-ordered">
        <p class="ef-pp-kpi-label">Ordered</p>
        <div class="ef-pp-kpi-value">{{ $stats['ordered'] }}</div>
        <p class="ef-pp-kpi-sub">In procurement</p>
    </div>
    <div class="ef-pp-kpi-card kpi-done">
        <p class="ef-pp-kpi-label">Completed</p>
        <div class="ef-pp-kpi-value">{{ $stats['completed'] }}</div>
        <p class="ef-pp-kpi-sub">Fulfilled</p>
    </div>
    <div class="ef-pp-kpi-card kpi-total">
        <p class="ef-pp-kpi-label">Total Plans</p>
        <div class="ef-pp-kpi-value">{{ $stats['total'] }}</div>
        <p class="ef-pp-kpi-sub">All time</p>
    </div>
</div>

{{-- Filter chips --}}
<div class="ef-pp-filters">
    <span class="ef-pp-chip active">All</span>
    <a href="#" class="ef-pp-chip chip-draft">Draft <span class="ms-1 opacity-75">{{ $stats['draft'] }}</span></a>
    <a href="#" class="ef-pp-chip chip-approved">Approved <span class="ms-1 opacity-75">{{ $stats['approved'] }}</span></a>
    <a href="#" class="ef-pp-chip chip-ordered">Ordered <span class="ms-1 opacity-75">{{ $stats['ordered'] }}</span></a>
    <a href="#" class="ef-pp-chip chip-done">Completed <span class="ms-1 opacity-75">{{ $stats['completed'] }}</span></a>
</div>

{{-- Plan cards --}}
<div class="ef-pp-grid">
    @forelse($plans as $plan)
    @php
        $colors = \App\Models\PurchasePlan::statusColors();
        $color  = $colors[$plan->status] ?? 'secondary';
        $initials = strtoupper(substr($plan->creator->name ?? '?', 0, 2));
        $est = $plan->estimatedTotal();
    @endphp
    <div class="ef-pp-card">
        <div class="ef-pp-card-stripe stripe-{{ $plan->status }}"></div>
        <div class="ef-pp-card-body">
            <div class="ef-pp-card-head">
                <div>
                    <div class="ef-pp-card-title">{{ $plan->title }}</div>
                    <div class="ef-pp-card-meta">
                        <i class="bi bi-calendar3 me-1"></i>{{ $plan->planned_date->format('d M Y') }}
                    </div>
                </div>
                <span class="ef-pp-status-badge badge-{{ $plan->status }}">{{ $plan->status }}</span>
            </div>

            <div class="ef-pp-card-stats">
                <div class="ef-pp-stat-item">
                    <div class="ef-pp-stat-val">{{ $plan->items->count() }}</div>
                    <div class="ef-pp-stat-lbl">Items</div>
                </div>
                <div class="ef-pp-stat-item">
                    <div class="ef-pp-stat-val">
                        @if($est > 0) ₹{{ number_format($est, 0) }} @else — @endif
                    </div>
                    <div class="ef-pp-stat-lbl">Est. Value</div>
                </div>
                <div class="ef-pp-stat-item">
                    <div class="ef-pp-stat-val">
                        {{ $plan->created_at->diffForHumans(['short' => true, 'parts' => 1]) }}
                    </div>
                    <div class="ef-pp-stat-lbl">Created</div>
                </div>
            </div>

            <div class="ef-pp-card-footer">
                <div class="ef-pp-creator">
                    <span class="ef-pp-creator-avatar">{{ $initials }}</span>
                    {{ $plan->creator->name ?? '—' }}
                </div>
                <a href="{{ route('admin.purchase-plans.show', $plan) }}" class="ef-pp-view-btn">
                    View <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="ef-pp-empty">
        <div class="ef-pp-empty-icon"><i class="bi bi-cart3"></i></div>
        <p class="fw-semibold mb-1" style="color:#3d3528">No purchase plans yet</p>
        <p class="small mb-0">Create a plan from low-stock suggestions or manually.</p>
    </div>
    @endforelse
</div>

@if($plans->hasPages())
<div class="mt-4">{{ $plans->links() }}</div>
@endif

</x-admin-layout>
