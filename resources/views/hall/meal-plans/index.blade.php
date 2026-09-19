<x-admin-layout title="Meal Plans">
@php
    $categories = \App\Models\MealPlan::categories();
    $catTone    = ['premium' => '--premium', 'custom' => '--info', 'standard' => '--muted'];
    $catIcon    = ['premium' => 'bi-star', 'custom' => 'bi-sliders', 'standard' => 'bi-egg-fried'];
    $top        = $stats['top_plan'];
    $planMeta   = [
        ['icon' => 'bi-collection', 'text' => $stats['total'] . ' catering ' . Str::plural('package', $stats['total'])],
        ['icon' => 'bi-check-circle', 'text' => $stats['active'] . ' active'],
        ['icon' => 'bi-calendar3', 'text' => now()->format('l, j F Y')],
    ];
@endphp

@push('styles')
<style>
/* Meal Plans — mp-*
   Hero, KPI strip use the shared x-ds.* components. Card, badge and button
   geometry mirrors /admin/wallets (.ef-wlt-*); colours are --ef-* tokens only. */
.mp-page { display: flex; flex-direction: column; gap: 16px; }
.mp-page .ef-ds-hero, .mp-page .ef-ds-kpi-wrap { margin-bottom: 0; }
.mp-page .ef-ds-hero-main { padding: 20px 24px; min-width: 0; }
.mp-page a:focus-visible, .mp-page button:focus-visible, .mp-mobile-new a:focus-visible,
.mp-menu .dropdown-item:focus-visible { outline: 2px solid var(--ef-emerald); outline-offset: 2px; }

.mp-section-head { display: flex; align-items: center; gap: 12px; }
.mp-section-title { font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--ef-muted); white-space: nowrap; }
.mp-section-sub { font-size: .78rem; color: var(--ef-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.mp-section-line { flex: 1; height: 1px; background: var(--ef-border); min-width: 16px; }

.mp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 300px), 1fr)); gap: 16px; align-items: start; }

/* No overflow:hidden / transform / opacity on the card — they clip or re-parent the More menu */
.mp-card {
    position: relative; display: flex; flex-direction: column; min-width: 0;
    background: var(--ef-surface); border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius); box-shadow: var(--ef-shadow);
    transition: box-shadow .2s var(--ef-ease), border-color .2s var(--ef-ease);
}
.mp-card:hover { box-shadow: var(--ef-shadow-hover); border-color: var(--ef-border-strong); }
.mp-card::before { content: ''; position: absolute; top: -1px; left: -1px; right: -1px; height: 3px; border-radius: var(--ef-radius) var(--ef-radius) 0 0; background: var(--ef-emerald); }
.mp-card.--inactive::before { background: var(--ef-border-strong); }
.mp-card.--inactive .mp-name, .mp-card.--inactive .mp-price { color: var(--ef-muted); }

.mp-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 16px 16px 0; }
.mp-body { padding: 12px 16px 16px; flex: 1; min-width: 0; }
.mp-name { font-size: 1.05rem; font-weight: 800; color: var(--ef-ink); letter-spacing: -.01em; line-height: 1.25; overflow-wrap: anywhere; }
.mp-desc { margin-top: 4px; font-size: .82rem; line-height: 1.5; color: var(--ef-ink-2); display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
.mp-desc.--none { color: var(--ef-muted); font-style: italic; }
.mp-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
.mp-tag { display: inline-flex; align-items: center; min-height: 24px; padding: 0 8px; border-radius: 6px; background: var(--ef-surface-2); border: 1px solid var(--ef-border); font-size: .72rem; font-weight: 600; color: var(--ef-ink-2); }

.mp-badge {
    display: inline-flex; align-items: center; gap: 4px; min-height: 24px; padding: 0 8px;
    border-radius: 6px; border: 1px solid transparent; font-size: .72rem; font-weight: 700; line-height: 1; white-space: nowrap;
}
.mp-badge i { font-size: .72rem; }
.mp-badge.--success { background: rgba(15,123,95,.10); color: var(--ef-emerald-dk); border-color: rgba(15,123,95,.2); }
.mp-badge.--muted   { background: var(--ef-surface-2); color: var(--ef-muted); border-color: var(--ef-border); }
.mp-badge.--info    { background: rgba(47,111,237,.08); color: #2350b8; border-color: rgba(47,111,237,.2); }
.mp-badge.--premium { background: rgba(184,137,62,.10); color: #7D5218; border-color: rgba(184,137,62,.28); }

.mp-foot { padding: 16px; border-top: 1px solid var(--ef-border); display: flex; flex-direction: column; gap: 12px; }
.mp-price-row { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; }
.mp-price { font-size: 1.6rem; font-weight: 800; letter-spacing: -.03em; line-height: 1; color: var(--ef-ink); font-variant-numeric: tabular-nums; }
.mp-price-unit { margin-top: 4px; font-size: .75rem; color: var(--ef-muted); }
.mp-bookings { display: inline-flex; align-items: center; gap: 4px; font-size: .78rem; color: var(--ef-muted); white-space: nowrap; }
.mp-actions { display: flex; gap: 8px; align-items: center; }
.mp-actions .dropdown { flex-shrink: 0; }

.mp-btn { min-height: 40px; padding: 0 12px; border-radius: var(--ef-radius-sm); background: transparent; color: var(--ef-muted); font-size: .8rem; cursor: pointer; }
.mp-btn.--edit { flex: 1; min-width: 0; background: var(--ef-emerald); border-color: var(--ef-emerald); color: #fff; }
.mp-btn.--edit:hover { background: var(--ef-emerald-hi); border-color: var(--ef-emerald-hi); color: #fff; }
.mp-btn.--more { width: 40px; padding: 0; font-size: 1rem; }
.mp-btn.--more[aria-expanded="true"] { background: var(--ef-surface-2); border-color: var(--ef-border-strong); color: var(--ef-ink); }
.mp-btn.--primary { background: var(--ef-emerald); border-color: var(--ef-emerald); color: #fff; }
.mp-btn.--primary:hover { background: var(--ef-emerald-hi); border-color: var(--ef-emerald-hi); color: #fff; }

.mp-menu {
    min-width: 200px; max-height: min(70vh, 320px); overflow-y: auto; padding: 4px;
    border: 1px solid var(--ef-border); border-radius: var(--ef-radius-sm);
    background: var(--ef-surface); box-shadow: var(--ef-shadow-hover);
}
.mp-menu .dropdown-item { display: flex; align-items: center; gap: 8px; min-height: 40px; padding: 0 12px; font-size: .84rem; border-radius: 8px; color: var(--ef-ink-2); }
.mp-menu .dropdown-item i { width: 16px; text-align: center; color: var(--ef-muted); }
.mp-menu .dropdown-item:hover, .mp-menu .dropdown-item:focus { background: var(--ef-surface-2); color: var(--ef-ink); }
.mp-menu .dropdown-item.--danger, .mp-menu .dropdown-item.--danger i { color: var(--ef-danger); }
.mp-menu .dropdown-item.--danger:hover { background: rgba(200,75,68,.08); }
.mp-menu .dropdown-divider { border-color: var(--ef-border); margin: 4px 0; }

.mp-empty { background: var(--ef-surface); border: 1px solid var(--ef-border); border-radius: var(--ef-radius); box-shadow: var(--ef-shadow); padding: 48px 24px; text-align: center; }
.mp-empty-icon { width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 16px; background: var(--ef-surface-2); border: 1px solid var(--ef-border); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: var(--ef-faint); }
.mp-empty-title { font-size: 1.05rem; font-weight: 700; color: var(--ef-ink); margin-bottom: 4px; }
.mp-empty-text { font-size: .85rem; color: var(--ef-muted); margin: 0 auto 16px; max-width: 360px; }

.mp-pagination { background: var(--ef-surface); border: 1px solid var(--ef-border); border-radius: var(--ef-radius); box-shadow: var(--ef-shadow); padding: 12px 16px; }

.mp-mobile-new {
    display: none; position: fixed; left: 0; right: 0; bottom: 0; z-index: 200;
    padding: 12px 16px calc(12px + env(safe-area-inset-bottom, 0px));
    background: var(--ef-surface); border-top: 1px solid var(--ef-border); box-shadow: 0 -8px 24px rgba(24,22,18,.06);
}
.mp-mobile-new a { width: 100%; min-height: 44px; }
body:has(#pwa-install-banner:not([aria-hidden="true"])) .mp-mobile-new { display: none !important; }

@media (max-width: 767.98px) {
    .mp-mobile-new { display: block; }
    .mp-page { padding-bottom: calc(72px + env(safe-area-inset-bottom, 0px)); }
    .mp-page .ef-ds-hero-acts .ef-ds-btn { min-height: 44px; min-width: 44px; justify-content: center; }
    .mp-page .ef-ds-kpi:last-child:nth-child(odd) { grid-column: 1 / -1; }
    .mp-btn { min-height: 44px; }
    .mp-btn.--more { width: 44px; }
    .mp-section-sub { display: none; }
}
@media (max-width: 380px) {
    .mp-page .ef-ds-hero-acts { flex-wrap: wrap; overflow: visible; }
}
</style>
@endpush

<div class="mp-page">

    <x-ds.hero eyebrow="Catering Operations" title="Meal Plans" :meta="$planMeta">
        <x-slot:actions>
            <a href="{{ route('hall.bookings.index') }}" class="ef-ds-btn" aria-label="Bookings">
                <i class="bi bi-calendar3"></i> <span>Bookings</span>
            </a>
            <a href="{{ route('hall.meal-plans.create') }}" class="ef-ds-btn --primary" aria-label="New meal plan">
                <i class="bi bi-plus-lg"></i> <span>New Meal Plan</span>
            </a>
        </x-slot:actions>
    </x-ds.hero>

    <div class="ef-ds-kpi-wrap">
        <div class="ef-ds-kpi-grid" style="--kpi-cols:5">
            <x-ds.kpi-card icon="bi-check-circle" label="Active Plans" :value="(string) $stats['active']" :note="'of ' . $stats['total'] . ' total'" accent="emerald" value-color="c-emerald" />
            <x-ds.kpi-card icon="bi-currency-rupee" label="Avg Price" :value="'₹' . number_format($stats['avg_price'])" note="per guest · active plans" accent="gold" />
            <x-ds.kpi-card icon="bi-trophy" label="Most Booked" :value="$top?->name ?? '—'" :note="$top ? $top->bookings_count . ' ' . Str::plural('booking', $top->bookings_count) : 'no bookings yet'" accent="bluegray" />
            <x-ds.kpi-card icon="bi-star" label="Premium" :value="(string) $stats['premium']" note="premium packages" :accent="$stats['premium'] > 0 ? 'amber' : 'muted'" />
            <x-ds.kpi-card icon="bi-collection" label="Total Packages" :value="(string) $stats['total']" note="catering offerings" accent="muted" />
        </div>
    </div>

    <div class="mp-section-head">
        <span class="mp-section-title">Meal Plans</span>
        <span class="mp-section-sub">Manage your catering packages and pricing</span>
        <span class="mp-section-line"></span>
    </div>

    @forelse($plans as $plan)
        @php
            $text     = strtolower(($plan->name ?? '') . ' ' . ($plan->description ?? ''));
            $mealTags = array_keys(array_filter([
                'Breakfast' => str_contains($text, 'breakfast') || str_contains($text, 'morning'),
                'Lunch'     => str_contains($text, 'lunch') || str_contains($text, 'midday'),
                'Dinner'    => str_contains($text, 'dinner') || str_contains($text, 'evening'),
                'Snacks'    => str_contains($text, 'snack') || str_contains($text, 'tea'),
            ]));
        @endphp

        @if($loop->first)<div class="mp-grid">@endif

        <article class="mp-card {{ !$plan->is_active ? '--inactive' : '' }}" aria-label="{{ $plan->name }}">
            <div class="mp-head">
                <span class="mp-badge {{ $catTone[$plan->category] ?? '--muted' }}"><i class="bi {{ $catIcon[$plan->category] ?? 'bi-egg-fried' }}"></i>{{ $categories[$plan->category] ?? ucfirst($plan->category) }}</span>
                <span class="mp-badge {{ $plan->is_active ? '--success' : '--muted' }}"><i class="bi {{ $plan->is_active ? 'bi-check-circle' : 'bi-pause-circle' }}"></i>{{ $plan->is_active ? 'Active' : 'Inactive' }}</span>
            </div>

            <div class="mp-body">
                <h2 class="mp-name">{{ $plan->name }}</h2>
                @if($plan->description)
                    <div class="mp-desc">{{ $plan->description }}</div>
                @else
                    <div class="mp-desc --none">No description added</div>
                @endif
                @if(!empty($mealTags))
                    <div class="mp-tags" aria-label="Meal services">
                        @foreach($mealTags as $tag)<span class="mp-tag">{{ $tag }}</span>@endforeach
                    </div>
                @endif
            </div>

            <div class="mp-foot">
                <div class="mp-price-row">
                    <div>
                        <div class="mp-price">₹{{ number_format($plan->price_per_person, 0) }}</div>
                        <div class="mp-price-unit">per guest</div>
                    </div>
                    <div class="mp-bookings"><i class="bi bi-calendar-check"></i>{{ $plan->bookings_count }} {{ Str::plural('booking', $plan->bookings_count) }}</div>
                </div>

                <div class="mp-actions">
                    <a href="{{ route('hall.meal-plans.edit', $plan) }}" class="ef-btn mp-btn --edit" aria-label="Edit {{ $plan->name }}">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <div class="dropdown">
                        <button type="button" class="ef-btn mp-btn --more"
                                data-bs-toggle="dropdown" data-bs-offset="0,4"
                                aria-expanded="false" aria-haspopup="true"
                                aria-label="More actions for {{ $plan->name }}">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="mp-menu dropdown-menu dropdown-menu-end">
                            <li>
                                <form method="POST" action="{{ route('hall.meal-plans.toggle-status', $plan) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="dropdown-item w-100 border-0 bg-transparent">
                                        <i class="bi bi-{{ $plan->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                        {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </li>
                            <li>
                                <a href="{{ route('hall.bookings.index', ['meal_plan_search' => $plan->name]) }}" class="dropdown-item">
                                    <i class="bi bi-calendar-check"></i> View Bookings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button type="button" class="dropdown-item --danger w-100 border-0 bg-transparent"
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
        </article>

        @if($loop->last)</div>@endif
    @empty
        <div class="mp-empty">
            <div class="mp-empty-icon"><i class="bi bi-egg-fried"></i></div>
            <div class="mp-empty-title">No meal plans yet</div>
            <p class="mp-empty-text">Create a meal plan to start managing catering options and attaching them to venue bookings.</p>
            <a href="{{ route('hall.meal-plans.create') }}" class="ef-btn mp-btn --primary"><i class="bi bi-plus-lg"></i> Create Meal Plan</a>
        </div>
    @endforelse

    @if($plans->hasPages())
        <div class="mp-pagination">{{ $plans->links() }}</div>
    @endif
</div>

<div class="mp-mobile-new">
    <a href="{{ route('hall.meal-plans.create') }}" class="ef-btn mp-btn --primary"><i class="bi bi-plus-lg"></i> New Meal Plan</a>
</div>

{{-- Delete confirmation modal --}}
<div class="modal fade" id="mpDeleteModal" tabindex="-1" aria-labelledby="mpDeleteTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border:1px solid var(--ef-border);border-radius:var(--ef-radius);background:var(--ef-surface)">
            <div class="modal-header">
                <h2 class="modal-title fs-5 fw-bold mb-0" id="mpDeleteTitle">Delete Meal Plan</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3" style="color:var(--ef-ink-2);font-size:.9rem">
                    Remove <strong id="mpDeleteName" style="color:var(--ef-ink)"></strong> from the catering catalogue.
                    This action cannot be undone.
                </p>
                <div id="mpDeleteWarning" class="d-none" style="background:rgba(200,75,68,.07);border:1px solid rgba(200,75,68,.22);border-radius:var(--ef-radius-sm);color:var(--ef-danger);font-size:.82rem;padding:12px 14px;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    This plan has existing bookings. Deleting it may affect booking records.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="ef-btn mp-btn" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" id="mpDeleteForm">
                    @csrf @method('DELETE')
                    <button type="submit" class="ef-btn mp-btn" style="background:var(--ef-danger);border-color:var(--ef-danger);color:#fff">
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
    document.getElementById('mpDeleteName').textContent = btn.dataset.planName;
    document.getElementById('mpDeleteForm').action      = btn.dataset.deleteUrl;
    document.getElementById('mpDeleteWarning').classList.toggle('d-none', btn.dataset.hasBookings !== '1');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('mpDeleteModal')).show();
}

/* More menu positioning.
   Bootstrap's default (absolute, clippingParents boundary) can clip or misplace
   the menu; use fixed positioning against the viewport and reserve room for the
   topbar, the sticky mobile CTA and the PWA install banner so it flips/shifts
   instead of opening under them. Must be a window-capture hook: Bootstrap's own
   delegated dropdown handler is a document-capture listener and would win. */
(function () {
    function visibleBottom(el) {
        if (!el || getComputedStyle(el).display === 'none' || el.getAttribute('aria-hidden') === 'true') return 0;
        return el.offsetHeight;
    }
    function menuPadding() {
        var tb = document.getElementById('topbar');
        var bottom = Math.max(
            visibleBottom(document.querySelector('.mp-mobile-new')),
            visibleBottom(document.getElementById('pwa-install-banner'))
        );
        return { top: (tb ? tb.getBoundingClientRect().bottom : 0) + 8, bottom: bottom + 8, left: 8, right: 8 };
    }
    window.addEventListener('click', function (e) {
        var btn = e.target.closest('.mp-btn.--more');
        if (!btn || !window.bootstrap || bootstrap.Dropdown.getInstance(btn)) return;
        new bootstrap.Dropdown(btn, {
            popperConfig: function (cfg) {
                var opts = { boundary: document.documentElement, rootBoundary: 'viewport', padding: menuPadding() };
                return Object.assign({}, cfg, {
                    strategy: 'fixed',
                    modifiers: cfg.modifiers.concat([
                        { name: 'preventOverflow', options: Object.assign({ altAxis: true }, opts) },
                        { name: 'flip', options: Object.assign({ fallbackPlacements: ['top-end', 'bottom-start', 'top-start'] }, opts) }
                    ])
                });
            }
        });
    }, true);
})();
</script>
@endpush
</x-admin-layout>
