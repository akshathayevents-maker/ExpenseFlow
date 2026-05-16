<x-admin-layout title="Meal Plans">
@push('styles')
<style>
/* ── Meal Plans — ef-mp-* ────────────────────────────────────── */
.ef-mp-shell {
    max-width: 1500px;
    margin: 0 auto;
    padding-bottom: 88px;
}

/* ── Hero ──────────────────────────────────────────────────────── */
.ef-mp-hero {
    align-items: end;
    display: grid;
    gap: 24px;
    grid-template-columns: minmax(0, 1fr) auto;
    margin-bottom: 28px;
}

.ef-mp-kicker {
    color: var(--ef-faint);
    font-size: .67rem;
    font-weight: 760;
    letter-spacing: .17em;
    text-transform: uppercase;
}

.ef-mp-title {
    color: var(--ef-ink);
    font-size: clamp(2rem, 4vw, 3.35rem);
    font-weight: 780;
    letter-spacing: 0;
    line-height: 1;
    margin: 8px 0 10px;
}

.ef-mp-subtitle {
    color: var(--ef-muted);
    display: flex;
    flex-wrap: wrap;
    font-size: .9rem;
    gap: 6px 14px;
}

.ef-mp-subtitle span + span::before {
    color: var(--ef-border-strong);
    content: '·';
    margin-right: 14px;
}

.ef-mp-hdr-actions {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-end;
}

/* ── Insight strip ─────────────────────────────────────────────── */
.ef-mp-insights {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    margin-bottom: 28px;
}

.ef-mp-insight {
    background: rgba(255, 253, 250, .92);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    min-height: 110px;
    padding: 18px;
    transition: border-color .18s var(--ef-ease), box-shadow .18s var(--ef-ease);
}

.ef-mp-insight:hover {
    border-color: var(--ef-border-strong);
    box-shadow: var(--ef-shadow-hover);
}

.ef-mp-insight-val {
    color: var(--ef-ink);
    font-size: 1.45rem;
    font-variant-numeric: tabular-nums;
    font-weight: 780;
    line-height: 1;
    margin-top: 12px;
}

.ef-mp-insight-val.--sm {
    font-size: 1rem;
    font-weight: 760;
    line-height: 1.2;
    margin-top: 10px;
}

.ef-mp-insight-note {
    color: var(--ef-muted);
    font-size: .74rem;
    margin-top: 6px;
}

/* ── Plan grid ─────────────────────────────────────────────────── */
.ef-mp-grid {
    display: grid;
    gap: 16px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin-bottom: 28px;
}

/* ── Plan card ─────────────────────────────────────────────────── */
.ef-mp-card {
    background: rgba(255, 253, 250, .94);
    border: 1px solid var(--ef-border);
    border-radius: 18px;
    box-shadow: var(--ef-shadow);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: border-color .18s var(--ef-ease), box-shadow .18s var(--ef-ease), transform .18s var(--ef-ease);
}

.ef-mp-card:hover {
    border-color: var(--ef-border-strong);
    box-shadow: var(--ef-shadow-hover);
    transform: translateY(-2px);
}

.ef-mp-card.--inactive {
    opacity: .72;
}

.ef-mp-card-head {
    align-items: center;
    display: flex;
    justify-content: space-between;
    padding: 16px 18px 0;
}

/* Category badge */
.ef-mp-cat {
    background: rgba(20, 20, 18, .04);
    border: 1px solid rgba(20, 20, 18, .07);
    border-radius: 7px;
    color: var(--ef-muted);
    font-size: .61rem;
    font-weight: 760;
    letter-spacing: .11em;
    padding: 4px 9px;
    text-transform: uppercase;
}

.ef-mp-cat.--premium {
    background: rgba(169, 131, 56, .09);
    border-color: rgba(169, 131, 56, .22);
    color: var(--ef-gold);
}

.ef-mp-cat.--custom {
    background: rgba(61, 115, 88, .08);
    border-color: rgba(61, 115, 88, .2);
    color: var(--ef-emerald);
}

/* Status chip */
.ef-mp-status {
    background: rgba(20, 20, 18, .035);
    border: 1px solid rgba(20, 20, 18, .07);
    border-radius: 999px;
    color: var(--ef-faint);
    font-size: .6rem;
    font-weight: 720;
    letter-spacing: .09em;
    padding: 4px 10px;
    text-transform: uppercase;
}

.ef-mp-status.--active {
    background: rgba(61, 115, 88, .08);
    border-color: rgba(61, 115, 88, .2);
    color: var(--ef-emerald);
}

/* Card body */
.ef-mp-card-body {
    flex: 1;
    padding: 14px 18px 16px;
}

.ef-mp-card-name {
    color: var(--ef-ink);
    font-size: 1.18rem;
    font-weight: 760;
    letter-spacing: -.01em;
    line-height: 1.15;
    margin-bottom: 8px;
}

.ef-mp-card-desc {
    -webkit-box-orient: vertical;
    color: var(--ef-muted);
    display: -webkit-box;
    font-size: .81rem;
    line-height: 1.6;
    margin-bottom: 12px;
    overflow: hidden;
    -webkit-line-clamp: 2;
}

.ef-mp-card-nodesc {
    color: var(--ef-faint);
    font-size: .78rem;
    font-style: italic;
    margin-bottom: 12px;
}

.ef-mp-meal-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.ef-mp-meal-tag {
    background: rgba(20, 20, 18, .035);
    border: 1px solid rgba(20, 20, 18, .07);
    border-radius: 6px;
    color: var(--ef-muted);
    font-size: .62rem;
    font-weight: 720;
    letter-spacing: .09em;
    padding: 3px 8px;
    text-transform: uppercase;
}

/* Card footer */
.ef-mp-card-footer {
    align-items: center;
    border-top: 1px solid var(--ef-border);
    display: flex;
    gap: 10px;
    justify-content: space-between;
    padding: 12px 18px;
}

.ef-mp-price {
    color: var(--ef-ink);
    font-size: 1.3rem;
    font-variant-numeric: tabular-nums;
    font-weight: 780;
    line-height: 1;
}

.ef-mp-price-unit {
    color: var(--ef-faint);
    font-size: .62rem;
    font-weight: 720;
    letter-spacing: .09em;
    margin-top: 4px;
    text-transform: uppercase;
}

.ef-mp-card-actions {
    align-items: center;
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}

/* Dropdown */
.ef-mp-dropdown {
    background: #fff;
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius-sm);
    box-shadow: 0 12px 36px rgba(24, 22, 18, .12);
    min-width: 170px;
    padding: 6px;
}

.ef-mp-dropdown .dropdown-item {
    align-items: center;
    border-radius: 8px;
    color: var(--ef-ink-2);
    display: flex;
    font-size: .81rem;
    font-weight: 600;
    gap: 9px;
    padding: 8px 10px;
    transition: background .12s;
}

.ef-mp-dropdown .dropdown-item:hover {
    background: rgba(20, 20, 18, .045);
    color: var(--ef-ink);
}

.ef-mp-dropdown .dropdown-item.--danger {
    color: var(--ef-danger);
}

.ef-mp-dropdown .dropdown-item.--danger:hover {
    background: rgba(141, 74, 60, .07);
}

.ef-mp-dropdown .dropdown-divider {
    border-color: var(--ef-border);
    margin: 5px 0;
}

/* ── Pagination ─────────────────────────────────────────────────── */
.ef-mp-pagination {
    display: flex;
    justify-content: center;
    padding: 8px 0 16px;
}

/* ── Empty state ─────────────────────────────────────────────────── */
.ef-mp-empty-wrap {
    align-items: center;
    background: rgba(255, 253, 250, .92);
    border: 1px solid var(--ef-border);
    border-radius: 20px;
    box-shadow: var(--ef-shadow);
    display: flex;
    flex-direction: column;
    padding: 72px 32px;
    text-align: center;
}

.ef-mp-empty-icon {
    align-items: center;
    background: rgba(20, 20, 18, .03);
    border: 1px solid rgba(20, 20, 18, .07);
    border-radius: 20px;
    color: var(--ef-faint);
    display: inline-flex;
    font-size: 1.8rem;
    height: 66px;
    justify-content: center;
    margin-bottom: 22px;
    width: 66px;
}

.ef-mp-empty-title {
    color: var(--ef-ink);
    font-size: 1.2rem;
    font-weight: 760;
    margin-bottom: 8px;
}

.ef-mp-empty-note {
    color: var(--ef-muted);
    font-size: .87rem;
    line-height: 1.65;
    margin-bottom: 28px;
    max-width: 340px;
}

/* ── Delete modal ────────────────────────────────────────────────── */
.ef-mp-modal .modal-content {
    background: #fffdfa;
    border: 1px solid var(--ef-border);
    border-radius: 18px;
    box-shadow: 0 28px 90px rgba(24, 22, 18, .22);
}

.ef-mp-modal .modal-header,
.ef-mp-modal .modal-footer {
    border-color: var(--ef-border);
    padding: 20px 24px;
}

.ef-mp-modal .modal-body {
    padding: 24px;
}

/* ── Mobile sticky bar ───────────────────────────────────────────── */
.ef-mp-mob-bar {
    display: none;
}

/* ── Responsive ──────────────────────────────────────────────────── */
@media (max-width: 1199.98px) {
    .ef-mp-insights {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .ef-mp-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 767.98px) {
    .ef-mp-hero {
        grid-template-columns: minmax(0, 1fr);
    }

    .ef-mp-hdr-actions {
        display: none;
    }

    .ef-mp-title {
        font-size: clamp(1.8rem, 7vw, 2.4rem);
    }

    .ef-mp-insights {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .ef-mp-insight {
        min-height: auto;
        padding: 14px;
    }

    .ef-mp-insight-val {
        font-size: 1.25rem;
        margin-top: 8px;
    }

    .ef-mp-grid {
        gap: 12px;
        grid-template-columns: minmax(0, 1fr);
    }

    .ef-mp-mob-bar {
        backdrop-filter: blur(18px) saturate(160%);
        background: rgba(255, 253, 250, .94);
        border-top: 1px solid var(--ef-border);
        bottom: 0;
        display: grid;
        gap: 8px;
        grid-template-columns: 1fr;
        left: 0;
        padding: 10px 16px calc(10px + env(safe-area-inset-bottom));
        position: fixed;
        right: 0;
        z-index: 1040;
    }
}
</style>
@endpush

@php
    $categories = \App\Models\MealPlan::categories();
    $catTone    = ['premium' => '--premium', 'custom' => '--custom', 'standard' => ''];
    $todayLabel = now()->format('l, d F Y');
@endphp

<div class="ef-mp-shell">

    {{-- ══ HERO ══════════════════════════════════════════════════ --}}
    <header class="ef-mp-hero">
        <div>
            <div class="ef-mp-kicker">Hospitality Catering Operations</div>
            <h1 class="ef-mp-title">Meal Plans</h1>
            <div class="ef-mp-subtitle">
                <span>{{ $stats['total'] }} catering {{ Str::plural('package', $stats['total']) }}</span>
                <span>{{ $stats['active'] }} active</span>
                <span>{{ $todayLabel }}</span>
            </div>
        </div>
        <div class="ef-mp-hdr-actions">
            <a href="{{ route('hall.bookings.index') }}" class="ef-btn">
                <i class="bi bi-calendar3"></i> Bookings
            </a>
            <a href="{{ route('hall.meal-plans.create') }}" class="ef-btn ef-btn-dark">
                <i class="bi bi-plus-lg"></i> New Meal Plan
            </a>
        </div>
    </header>

    {{-- ══ INSIGHT STRIP ══════════════════════════════════════════ --}}
    <section class="ef-mp-insights" aria-label="Catering overview">
        <div class="ef-mp-insight">
            <span class="ef-label">Active Plans</span>
            <div class="ef-mp-insight-val">{{ $stats['active'] }}</div>
            <div class="ef-mp-insight-note">of {{ $stats['total'] }} total</div>
        </div>
        <div class="ef-mp-insight">
            <span class="ef-label">Avg Price</span>
            <div class="ef-mp-insight-val">₹{{ number_format($stats['avg_price']) }}</div>
            <div class="ef-mp-insight-note">per guest · active plans</div>
        </div>
        <div class="ef-mp-insight">
            <span class="ef-label">Most Booked</span>
            <div class="ef-mp-insight-val {{ $stats['top_plan'] ? '--sm' : '' }}">
                {{ $stats['top_plan']?->name ?? '—' }}
            </div>
            <div class="ef-mp-insight-note">
                {{ $stats['top_plan'] ? $stats['top_plan']->bookings_count . ' bookings' : 'no bookings yet' }}
            </div>
        </div>
        <div class="ef-mp-insight">
            <span class="ef-label">Premium</span>
            <div class="ef-mp-insight-val">{{ $stats['premium'] }}</div>
            <div class="ef-mp-insight-note">premium packages</div>
        </div>
        <div class="ef-mp-insight">
            <span class="ef-label">Total Packages</span>
            <div class="ef-mp-insight-val">{{ $stats['total'] }}</div>
            <div class="ef-mp-insight-note">catering offerings</div>
        </div>
    </section>

    {{-- ══ SUCCESS / ERROR FLASH ══════════════════════════════════ --}}
    @if(session('success'))
        <div class="alert border-0 mb-4" style="background:rgba(61,115,88,.08);border-left:3px solid var(--ef-emerald)!important;border-radius:10px;color:var(--ef-emerald);font-size:.85rem;padding:12px 16px;" role="alert">
            <i class="bi bi-check2-circle me-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert border-0 mb-4" style="background:rgba(141,74,60,.07);border-left:3px solid var(--ef-danger)!important;border-radius:10px;color:var(--ef-danger);font-size:.85rem;padding:12px 16px;" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        </div>
    @endif

    {{-- ══ MEAL PLAN GRID ══════════════════════════════════════════ --}}
    @forelse($plans as $plan)
        @php
            // Detect meal keywords in name + description
            $text     = strtolower(($plan->name ?? '') . ' ' . ($plan->description ?? ''));
            $mealTags = array_keys(array_filter([
                'Breakfast' => str_contains($text, 'breakfast') || str_contains($text, 'morning'),
                'Lunch'     => str_contains($text, 'lunch') || str_contains($text, 'midday'),
                'Dinner'    => str_contains($text, 'dinner') || str_contains($text, 'evening'),
                'Snacks'    => str_contains($text, 'snack') || str_contains($text, 'tea'),
            ]));
            $catClass = $catTone[$plan->category] ?? '';
        @endphp

        @if($loop->first)
            <div class="ef-mp-grid">
        @endif

        <div class="ef-mp-card {{ !$plan->is_active ? '--inactive' : '' }}" role="article" aria-label="{{ $plan->name }}">

            {{-- Head: category + status --}}
            <div class="ef-mp-card-head">
                <span class="ef-mp-cat {{ $catClass }}">{{ $categories[$plan->category] ?? ucfirst($plan->category) }}</span>
                <span class="ef-mp-status {{ $plan->is_active ? '--active' : '' }}" aria-label="Status: {{ $plan->is_active ? 'Active' : 'Inactive' }}">
                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            {{-- Body: name + description + meal tags --}}
            <div class="ef-mp-card-body">
                <div class="ef-mp-card-name">{{ $plan->name }}</div>

                @if($plan->description)
                    <div class="ef-mp-card-desc">{{ $plan->description }}</div>
                @else
                    <div class="ef-mp-card-nodesc">No description added</div>
                @endif

                @if(!empty($mealTags))
                    <div class="ef-mp-meal-tags" aria-label="Meal services">
                        @foreach($mealTags as $tag)
                            <span class="ef-mp-meal-tag">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Footer: price + actions --}}
            <div class="ef-mp-card-footer">
                <div>
                    <div class="ef-mp-price">₹{{ number_format($plan->price_per_person, 0) }}</div>
                    <div class="ef-mp-price-unit">per guest</div>
                </div>

                <div class="ef-mp-card-actions">
                    <a href="{{ route('hall.meal-plans.edit', $plan) }}" class="ef-btn ef-btn-sm" title="Edit {{ $plan->name }}">
                        <i class="bi bi-pencil"></i>
                        <span class="d-none d-sm-inline">Edit</span>
                    </a>

                    <div class="dropdown">
                        <button type="button"
                                class="ef-btn ef-btn-sm ef-btn-icon"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                aria-label="More actions">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="ef-mp-dropdown dropdown-menu dropdown-menu-end">
                            <li>
                                <form method="POST" action="{{ route('hall.meal-plans.toggle-status', $plan) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-{{ $plan->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                        {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </li>
                            <li>
                                <a href="{{ route('hall.bookings.index', ['meal_plan_search' => $plan->name]) }}"
                                   class="dropdown-item">
                                    <i class="bi bi-calendar-check"></i> View Bookings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button type="button"
                                        class="dropdown-item --danger"
                                        data-plan-name="{{ $plan->name }}"
                                        data-delete-url="{{ route('hall.meal-plans.destroy', $plan) }}"
                                        data-has-bookings="{{ $plan->bookings_count > 0 ? '1' : '0' }}"
                                        onclick="openMpDelete(this)">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @if($loop->last)
            </div>{{-- .ef-mp-grid --}}
        @endif

    @empty
        <div class="ef-mp-empty-wrap">
            <div class="ef-mp-empty-icon"><i class="bi bi-egg-fried"></i></div>
            <div class="ef-mp-empty-title">No catering packages yet</div>
            <p class="ef-mp-empty-note">Create your first meal plan to start attaching hospitality offerings to venue bookings.</p>
            <a href="{{ route('hall.meal-plans.create') }}" class="ef-btn ef-btn-dark">
                <i class="bi bi-plus-lg"></i> Add Meal Plan
            </a>
        </div>
    @endforelse

    {{-- Pagination --}}
    @if($plans->hasPages())
        <div class="ef-mp-pagination">{{ $plans->links() }}</div>
    @endif

</div>

{{-- ══ MOBILE STICKY BAR ══════════════════════════════════════════ --}}
<div class="ef-mp-mob-bar">
    <a href="{{ route('hall.meal-plans.create') }}" class="ef-btn ef-btn-dark w-100">
        <i class="bi bi-plus-lg"></i> New Meal Plan
    </a>
</div>

{{-- ══ DELETE CONFIRMATION MODAL ══════════════════════════════════ --}}
<div class="modal fade ef-mp-modal" id="mpDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="ef-label mb-1">Catering Management</div>
                    <h2 class="modal-title fs-5 fw-bold mb-0">Delete Meal Plan</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="ef-shell-note mb-3">
                    Remove <strong id="mpDeleteName" class="fw-semibold" style="color:var(--ef-ink)"></strong> from the catering catalogue.
                    This action cannot be undone.
                </p>
                <div id="mpDeleteWarning" class="d-none" style="background:rgba(141,74,60,.06);border:1px solid rgba(141,74,60,.16);border-radius:10px;color:var(--ef-danger);font-size:.82rem;padding:12px 14px;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    This plan has existing bookings. Deleting it may affect booking records.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="ef-btn" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" id="mpDeleteForm">
                    @csrf @method('DELETE')
                    <button type="submit" class="ef-btn" style="background:var(--ef-danger);border-color:var(--ef-danger);color:#fffdfa;">
                        <i class="bi bi-trash"></i> Delete Plan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openMpDelete(btn) {
    const name        = btn.dataset.planName;
    const url         = btn.dataset.deleteUrl;
    const hasBookings = btn.dataset.hasBookings === '1';

    document.getElementById('mpDeleteName').textContent = name;
    document.getElementById('mpDeleteForm').action      = url;

    const warn = document.getElementById('mpDeleteWarning');
    warn.classList.toggle('d-none', !hasBookings);

    new bootstrap.Modal(document.getElementById('mpDeleteModal')).show();
}
</script>
@endpush
</x-admin-layout>
