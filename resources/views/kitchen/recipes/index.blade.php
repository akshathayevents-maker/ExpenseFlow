<x-admin-layout title="Recipe Library">

@push('styles')
<style>
/* ── Recipe Library — scoped redesign (ef-rl-*) ───────────────────────
   These classes are exclusive to this page; the old .ef-recipe-* /
   .ef-cat-chip global rules in app.css are still used by the recipe
   show/preview page (.ef-recipe-doc-*) and are left untouched. ────── */

.ef-rl-shell { max-width: 1360px; margin: 0 auto; padding-bottom: 86px; }

/* Header */
.ef-rl-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: .85rem; }
.ef-rl-eyebrow { font-size: .68rem; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; color: var(--ef-gold); margin-bottom: .15rem; }
.ef-rl-title { font-size: 1.5rem; font-weight: 800; color: var(--ef-ink); letter-spacing: -.01em; line-height: 1.15; }
.ef-rl-sub { color: var(--ef-muted); font-size: .82rem; margin-top: .15rem; }
.ef-rl-new {
    display: inline-flex; align-items: center; gap: .4rem;
    background: var(--ef-ink); color: #fff; border-radius: 10px;
    padding: 0 1rem; height: 42px; font-size: .85rem; font-weight: 650;
    text-decoration: none; white-space: nowrap; flex-shrink: 0;
}
.ef-rl-new:hover { opacity: .88; color: #fff; text-decoration: none; }
@media (max-width: 575.98px) {
    .ef-rl-header { flex-direction: column; }
    .ef-rl-new { width: 100%; justify-content: center; }
}

/* Toolbar: search + status */
.ef-rl-toolbar { display: flex; gap: .5rem; margin-bottom: .6rem; }
.ef-rl-search-wrap { position: relative; flex: 1; min-width: 0; }
.ef-rl-search-wrap i { position: absolute; left: .75rem; top: 50%; transform: translateY(-50%); color: var(--ef-muted); font-size: .85rem; pointer-events: none; }
.ef-rl-search {
    width: 100%; height: 42px; border: 1px solid var(--ef-border-strong); border-radius: 9px;
    padding: 0 .75rem 0 2.1rem; font-size: .85rem; color: var(--ef-ink); background: var(--ef-surface);
    outline: none;
}
.ef-rl-search:focus { border-color: var(--ef-gold); box-shadow: 0 0 0 3px rgba(184,137,62,.12); }
.ef-rl-status {
    height: 42px; border: 1px solid var(--ef-border-strong); border-radius: 9px;
    padding: 0 2rem 0 .75rem; font-size: .84rem; font-weight: 600; color: var(--ef-ink-2);
    background: var(--ef-surface); flex-shrink: 0; min-width: 130px;
}
.ef-rl-status:focus { border-color: var(--ef-gold); outline: none; }
.ef-rl-clear {
    display: inline-flex; align-items: center; gap: .3rem;
    height: 42px; padding: 0 .8rem; border-radius: 9px; border: 1px solid var(--ef-border);
    color: var(--ef-muted); font-size: .82rem; font-weight: 600; text-decoration: none; flex-shrink: 0;
}
.ef-rl-clear:hover { border-color: var(--ef-danger); color: var(--ef-danger); text-decoration: none; }

/* Category chip row — single line, horizontal scroll only */
.ef-rl-chip-scroll {
    display: flex; gap: .4rem; overflow-x: auto; -webkit-overflow-scrolling: touch;
    scrollbar-width: none; padding-bottom: 2px; margin-bottom: .75rem;
}
.ef-rl-chip-scroll::-webkit-scrollbar { display: none; }
.ef-rl-chip {
    flex-shrink: 0; display: inline-flex; align-items: center;
    height: 32px; padding: 0 .8rem; border-radius: 999px;
    border: 1px solid var(--ef-border); background: var(--ef-surface);
    color: var(--ef-muted); font-size: .78rem; font-weight: 600;
    text-decoration: none; white-space: nowrap;
}
.ef-rl-chip:hover { border-color: var(--ef-border-strong); color: var(--ef-ink-2); text-decoration: none; }
.ef-rl-chip.--active { background: var(--ef-gold); border-color: var(--ef-gold); color: #fff; }

/* Result summary */
.ef-rl-result { font-size: .8rem; color: var(--ef-muted); margin-bottom: .7rem; }
.ef-rl-result strong { color: var(--ef-ink); font-weight: 700; }

/* Grid */
.ef-rl-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .85rem; }
@media (min-width: 768px) and (max-width: 1023.98px) { .ef-rl-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 767.98px) { .ef-rl-grid { grid-template-columns: 1fr; } }

/* Card */
.ef-rl-card {
    background: var(--ef-surface); border: 1px solid var(--ef-border); border-radius: 12px;
    box-shadow: 0 1px 2px rgba(0,0,0,.03);
    display: flex; flex-direction: column; overflow: hidden;
    transition: border-color .15s, box-shadow .15s;
    position: relative;
}
.ef-rl-card:hover { border-color: var(--ef-border-strong); box-shadow: 0 2px 10px rgba(0,0,0,.06); }
.ef-rl-card.--inactive { opacity: .72; }
.ef-rl-card-hit { position: absolute; inset: 0; z-index: 1; }
.ef-rl-card-hit:focus-visible { outline: 2px solid var(--ef-gold); outline-offset: -2px; }

.ef-rl-card-body { padding: .85rem .95rem .7rem; }
.ef-rl-card-top { display: flex; align-items: center; justify-content: space-between; gap: .5rem; margin-bottom: .35rem; }
.ef-rl-cat {
    font-size: .64rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase;
    padding: .15rem .55rem; border-radius: 999px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 60%;
}
.ef-rl-cat.--none { background: var(--ef-surface-2); color: var(--ef-faint); font-style: italic; text-transform: none; letter-spacing: 0; font-weight: 600; }
.ef-cat--breakfast   { background: rgba(251,146,60,.13);  color: #b45309; }
.ef-cat--lunch       { background: rgba(34,197,94,.13);   color: #15803d; }
.ef-cat--dinner      { background: rgba(99,102,241,.13);  color: #4338ca; }
.ef-cat--main-course { background: rgba(67,56,202,.15);   color: #3730a3; }
.ef-cat--starter     { background: rgba(20,184,166,.13);  color: #0f766e; }
.ef-cat--soup        { background: rgba(245,158,11,.13);  color: #92400e; }
.ef-cat--salad       { background: rgba(132,204,22,.15);  color: #3f6212; }
.ef-cat--side-dish   { background: rgba(168,85,247,.13);  color: #6d28d9; }
.ef-cat--snacks      { background: rgba(234,179,8,.13);   color: #854d0e; }
.ef-cat--beverage    { background: rgba(6,182,212,.13);   color: #0e7490; }
.ef-cat--sweet       { background: rgba(236,72,153,.13);  color: #9d174d; }
.ef-cat--dessert     { background: rgba(244,63,94,.13);   color: #be123c; }
.ef-cat--other       { background: rgba(113,113,122,.13); color: #52525b; }

.ef-rl-status-tag { display: inline-flex; align-items: center; gap: .3rem; font-size: .68rem; font-weight: 650; color: var(--ef-muted); flex-shrink: 0; }
.ef-rl-status-tag::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #22c55e; flex-shrink: 0; }
.ef-rl-status-tag.--inactive { color: var(--ef-faint); }
.ef-rl-status-tag.--inactive::before { background: var(--ef-faint); }

.ef-rl-name { font-size: 1rem; font-weight: 700; color: var(--ef-ink); line-height: 1.3; margin: 0 0 .45rem; overflow-wrap: anywhere; }

.ef-rl-meta { display: flex; flex-wrap: wrap; gap: .3rem .7rem; }
.ef-rl-meta-item { display: inline-flex; align-items: center; gap: .3rem; font-size: .74rem; color: var(--ef-muted); white-space: nowrap; }
.ef-rl-meta-item i { color: var(--ef-faint); font-size: .78rem; }

.ef-rl-card-footer {
    margin-top: auto; border-top: 1px solid var(--ef-border);
    display: flex; align-items: center; gap: .4rem; padding: .5rem .6rem;
    position: relative; z-index: 2;
}
.ef-rl-view-btn {
    flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: .35rem;
    height: 40px; border-radius: 8px; background: var(--ef-surface-2); color: var(--ef-ink-2);
    font-size: .82rem; font-weight: 650; text-decoration: none; border: 1px solid transparent;
}
.ef-rl-view-btn:hover { background: var(--ef-surface); border-color: var(--ef-border-strong); color: var(--ef-ink); text-decoration: none; }
.ef-rl-more-btn {
    width: 40px; height: 40px; flex-shrink: 0; border-radius: 8px; border: 1px solid var(--ef-border);
    background: var(--ef-surface); color: var(--ef-muted); display: inline-flex; align-items: center; justify-content: center;
}
.ef-rl-more-btn:hover { background: var(--ef-surface-2); color: var(--ef-ink); }
.ef-rl-more-menu .dropdown-item { font-size: .84rem; padding: .5rem .75rem; }
.ef-rl-more-menu .dropdown-item.text-danger:hover { background: rgba(200,75,68,.08); }

/* Empty state */
.ef-rl-empty { grid-column: 1 / -1; text-align: center; padding: 3rem 1.5rem; }
.ef-rl-empty-icon { font-size: 2.4rem; color: var(--ef-faint); margin-bottom: .9rem; }
.ef-rl-empty-title { font-size: 1rem; font-weight: 700; color: var(--ef-ink); margin-bottom: .35rem; }
.ef-rl-empty-body { font-size: .85rem; color: var(--ef-muted); max-width: 320px; margin: 0 auto 1rem; }
</style>
@endpush

@php
    $catSlugs = collect($categories)->mapWithKeys(fn($c) => [$c => Str::slug($c)]);
    $activeFilters = array_filter($filters);
    $statusVal = $filters['status'] ?? '';
@endphp

<div class="ef-rl-shell">

    {{-- Header --}}
    <div class="ef-rl-header">
        <div>
            <div class="ef-rl-eyebrow d-none d-md-block">Kitchen</div>
            <div class="ef-rl-title">Recipe Library</div>
            <div class="ef-rl-sub">Manage recipes, ingredients and preparation.</div>
        </div>
        <a href="{{ route('kitchen.recipes.create') }}" class="ef-rl-new">
            <i class="bi bi-plus-lg"></i> New Recipe
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="ef-card mb-3" style="border-left:4px solid #16a34a">
            <div class="ef-card-body py-2" style="color:#15803d;font-size:.88rem">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            </div>
        </div>
    @endif

    {{-- Toolbar --}}
    <form method="GET" action="{{ route('kitchen.recipes.index') }}" id="filterForm">
        @if(!empty($filters['category']))
            <input type="hidden" name="category" value="{{ $filters['category'] }}">
        @endif
        <div class="ef-rl-toolbar">
            <div class="ef-rl-search-wrap">
                <i class="bi bi-search"></i>
                <input type="text" name="search" class="ef-rl-search" placeholder="Search recipes…"
                       value="{{ $filters['search'] ?? '' }}">
            </div>
            <select name="status" class="ef-rl-status" onchange="document.getElementById('filterForm').submit()" aria-label="Filter by status">
                <option value="">Status: All</option>
                <option value="active"   @selected($statusVal === 'active')>Status: Active</option>
                <option value="inactive" @selected($statusVal === 'inactive')>Status: Inactive</option>
            </select>
            @if($activeFilters)
                <a href="{{ route('kitchen.recipes.index') }}" class="ef-rl-clear"><i class="bi bi-x-lg"></i> Clear</a>
            @endif
        </div>
    </form>

    {{-- Category chip row — single row, scrolls horizontally only --}}
    <div class="ef-rl-chip-scroll" role="group" aria-label="Filter by category">
        <a href="{{ route('kitchen.recipes.index', array_merge($filters, ['category' => ''])) }}"
           class="ef-rl-chip {{ empty($filters['category'] ?? '') ? '--active' : '' }}">All</a>
        @foreach($categories as $cat)
            <a href="{{ route('kitchen.recipes.index', array_merge($filters, ['category' => $cat])) }}"
               class="ef-rl-chip {{ ($filters['category'] ?? '') === $cat ? '--active' : '' }}">{{ $cat }}</a>
        @endforeach
    </div>

    {{-- Result summary --}}
    <div class="ef-rl-result">
        <strong>{{ $recipes->count() }}</strong> {{ Str::plural('recipe', $recipes->count()) }}
        @if(!empty($filters['category'])) &middot; {{ $filters['category'] }} @endif
        @if(!empty($filters['search'])) &middot; matching &ldquo;{{ $filters['search'] }}&rdquo; @endif
    </div>

    {{-- Grid --}}
    <div class="ef-rl-grid">

        @forelse($recipes as $recipe)
            @php $catSlug = $recipe->category ? ($catSlugs[$recipe->category] ?? Str::slug($recipe->category)) : null; @endphp
            <div class="ef-rl-card {{ $recipe->is_active ? '' : '--inactive' }}">
                <a href="{{ route('kitchen.recipes.show', $recipe) }}" class="ef-rl-card-hit"
                   aria-label="View {{ $recipe->name }}"></a>

                <div class="ef-rl-card-body">
                    <div class="ef-rl-card-top">
                        @if($recipe->category)
                            <span class="ef-rl-cat ef-cat--{{ $catSlug }}">{{ $recipe->category }}</span>
                        @else
                            <span class="ef-rl-cat --none">Uncategorised</span>
                        @endif
                        <span class="ef-rl-status-tag {{ $recipe->is_active ? '' : '--inactive' }}">
                            {{ $recipe->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <h2 class="ef-rl-name">{{ $recipe->name }}</h2>

                    <div class="ef-rl-meta">
                        <span class="ef-rl-meta-item" title="Base yield">
                            <i class="bi bi-people"></i>{{ number_format($recipe->yield_per_batch, 0) }} {{ $recipe->yield_unit }}
                        </span>
                        <span class="ef-rl-meta-item" title="Ingredients">
                            <i class="bi bi-basket2"></i>{{ $recipe->ingredients_count }} {{ Str::plural('ingredient', $recipe->ingredients_count) }}
                        </span>
                        @if($recipe->prep_time_minutes || $recipe->cook_time_minutes)
                            <span class="ef-rl-meta-item" title="Total time">
                                <i class="bi bi-clock"></i>{{ $recipe->totalTimeMinutes() }} min
                            </span>
                        @endif
                    </div>
                </div>

                <div class="ef-rl-card-footer">
                    <a href="{{ route('kitchen.recipes.show', $recipe) }}" class="ef-rl-view-btn">
                        <i class="bi bi-eye"></i> View Recipe
                    </a>
                    <div class="dropdown">
                        <button class="ef-rl-more-btn" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false" aria-label="More actions for {{ $recipe->name }}">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end ef-rl-more-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('kitchen.recipes.edit', $recipe) }}">
                                    <i class="bi bi-pencil me-2"></i>Edit Recipe
                                </a>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('kitchen.recipes.toggle-active', $recipe) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="dropdown-item">
                                        @if($recipe->is_active)
                                            <i class="bi bi-eye-slash me-2"></i>Deactivate
                                        @else
                                            <i class="bi bi-check-circle me-2"></i>Activate
                                        @endif
                                    </button>
                                </form>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('kitchen.recipes.destroy', $recipe) }}"
                                      onsubmit="return confirm('Delete recipe &quot;{{ addslashes($recipe->name) }}&quot;? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-trash me-2"></i>Delete
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        @empty
            <div class="ef-rl-empty">
                <div class="ef-rl-empty-icon"><i class="bi bi-journal-x"></i></div>
                @if($activeFilters)
                    <div class="ef-rl-empty-title">No matching recipes</div>
                    <div class="ef-rl-empty-body">Try another search or clear your filters.</div>
                    <a href="{{ route('kitchen.recipes.index') }}" class="ef-rl-new" style="display:inline-flex">Clear Filters</a>
                @else
                    <div class="ef-rl-empty-title">No recipes yet</div>
                    <div class="ef-rl-empty-body">Create your first recipe to start managing kitchen production.</div>
                    <a href="{{ route('kitchen.recipes.create') }}" class="ef-rl-new" style="display:inline-flex">
                        <i class="bi bi-plus-lg"></i> Create Recipe
                    </a>
                @endif
            </div>
        @endforelse

    </div>

</div>

</x-admin-layout>
