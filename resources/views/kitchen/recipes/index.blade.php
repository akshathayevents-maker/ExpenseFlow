<x-admin-layout title="Recipe Library">

<div class="ef-recipe-library">

    {{-- ── Hero ──────────────────────────────────────────────────────────── --}}
    <header class="ef-create-hero" style="margin-bottom:20px">
        <div>
            <div class="ef-eyebrow">Kitchen</div>
            <h1 class="ef-create-title">Recipe Library</h1>
            <p class="ef-shell-note" style="margin:0">Manage all recipes, ingredients, and preparation steps for kitchen production.</p>
        </div>
        <div class="ef-create-actions">
            <a href="{{ route('kitchen.recipes.create') }}" class="ef-btn-dark" style="display:inline-flex;align-items:center;gap:7px;padding:10px 18px;background:var(--ef-ink);color:#fff;border-radius:12px;font-size:.88rem;font-weight:620;text-decoration:none;">
                <i class="bi bi-plus-lg"></i> New Recipe
            </a>
        </div>
    </header>

    {{-- ── Flash messages ──────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="ef-card mb-3" style="border-left:4px solid #16a34a">
            <div class="ef-card-body py-2" style="color:#15803d;font-size:.88rem">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            </div>
        </div>
    @endif

    {{-- ── Filters ──────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('kitchen.recipes.index') }}" id="filterForm">
        <div class="ef-recipe-filter-bar">
            <div class="ef-recipe-search">
                <input type="text" name="search" class="ef-input" placeholder="Search recipes…"
                       value="{{ $filters['search'] ?? '' }}"
                       style="margin:0">
            </div>
            <select name="status" class="ef-select" style="width:auto;min-width:130px;margin:0" onchange="document.getElementById('filterForm').submit()">
                <option value="">All Status</option>
                <option value="active"   @selected(($filters['status'] ?? '') === 'active')>Active only</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive only</option>
            </select>
            <button type="submit" class="ef-btn" style="white-space:nowrap">
                <i class="bi bi-search"></i> Search
            </button>
            @if(array_filter($filters))
                <a href="{{ route('kitchen.recipes.index') }}" class="ef-btn" style="white-space:nowrap">
                    <i class="bi bi-x"></i> Clear
                </a>
            @endif
        </div>
    </form>

    {{-- ── Category chips ───────────────────────────────────────────────── --}}
    <div class="ef-cat-chips">
        <a href="{{ route('kitchen.recipes.index', array_merge($filters, ['category' => ''])) }}"
           class="ef-cat-chip {{ empty($filters['category'] ?? '') ? '--active' : '' }}">
            All
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('kitchen.recipes.index', array_merge($filters, ['category' => $cat])) }}"
               class="ef-cat-chip {{ ($filters['category'] ?? '') === $cat ? '--active' : '' }}">
                {{ $cat }}
            </a>
        @endforeach
    </div>

    {{-- ── Results summary ─────────────────────────────────────────────── --}}
    <div style="color:var(--ef-muted);font-size:.82rem;margin-bottom:12px">
        {{ $recipes->count() }} {{ Str::plural('recipe', $recipes->count()) }}
        @if(!empty($filters['category'])) · {{ $filters['category'] }} @endif
        @if(!empty($filters['search'])) · matching "{{ $filters['search'] }}" @endif
    </div>

    {{-- ── Card grid ───────────────────────────────────────────────────── --}}
    <div class="ef-recipe-grid">

        @forelse($recipes as $recipe)
            @php
                $catSlug = $recipe->category ? Str::slug($recipe->category) : 'none';
            @endphp
            <div class="ef-recipe-card">
                <div class="ef-recipe-card-body">

                    <div class="ef-recipe-card-top">
                        @if($recipe->category)
                            <span class="ef-recipe-cat-badge ef-cat--{{ $catSlug }}">{{ $recipe->category }}</span>
                        @else
                            <span class="ef-recipe-cat-badge ef-cat--none" style="background:#f5f2ee;color:#9c8e7e;border:1px solid #e4dfd8">Uncategorised</span>
                        @endif
                        <span class="ef-recipe-status {{ $recipe->is_active ? '' : '--inactive' }}">
                            {{ $recipe->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <h2 class="ef-recipe-card-name">{{ $recipe->name }}</h2>

                    <div class="ef-recipe-card-stats">
                        <span class="ef-recipe-card-stat" title="Base serving count">
                            <i class="bi bi-people"></i>
                            {{ number_format($recipe->yield_per_batch, 0) }}&nbsp;{{ $recipe->yield_unit }}
                        </span>
                        <span class="ef-recipe-card-stat" title="Ingredients">
                            <i class="bi bi-basket2"></i>
                            {{ $recipe->ingredients_count }}&nbsp;ingr.
                        </span>
                        <span class="ef-recipe-card-stat" title="SOP steps">
                            <i class="bi bi-list-ol"></i>
                            {{ $recipe->sops_count }}&nbsp;steps
                        </span>
                        @if($recipe->prep_time_minutes || $recipe->cook_time_minutes)
                            <span class="ef-recipe-card-stat" title="Total time">
                                <i class="bi bi-clock"></i>
                                {{ $recipe->totalTimeMinutes() }}&nbsp;min
                            </span>
                        @endif
                    </div>

                </div>

                <div class="ef-recipe-card-footer">
                    <a href="{{ route('kitchen.recipes.show', $recipe) }}" class="ef-btn-xs --primary">
                        <i class="bi bi-eye"></i> View
                    </a>
                    <a href="{{ route('kitchen.recipes.edit', $recipe) }}" class="ef-btn-xs">
                        <i class="bi bi-pencil"></i> Edit
                    </a>

                    {{-- Toggle active --}}
                    <form method="POST"
                          action="{{ route('kitchen.recipes.toggle-active', $recipe) }}"
                          style="margin:0">
                        @csrf @method('PATCH')
                        <button type="submit" class="ef-btn-xs --muted"
                                title="{{ $recipe->is_active ? 'Deactivate recipe' : 'Activate recipe' }}">
                            @if($recipe->is_active)
                                <i class="bi bi-eye-slash"></i> Deactivate
                            @else
                                <i class="bi bi-check-circle"></i> Activate
                            @endif
                        </button>
                    </form>

                    {{-- Delete (with confirmation) --}}
                    <form method="POST"
                          action="{{ route('kitchen.recipes.destroy', $recipe) }}"
                          style="margin:0"
                          onsubmit="return confirm('Delete recipe "{{ addslashes($recipe->name) }}"? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="ef-btn-xs --danger" title="Delete recipe">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>

        @empty
            <div class="ef-recipe-empty">
                <div class="ef-recipe-empty-icon"><i class="bi bi-journal-x"></i></div>
                <div class="ef-recipe-empty-title">No recipes found</div>
                <div class="ef-recipe-empty-body">
                    @if(array_filter($filters))
                        No recipes match your current filters. Try adjusting the search or category.
                    @else
                        Start by adding your first recipe to the kitchen library.
                    @endif
                </div>
                @if(!array_filter($filters))
                    <a href="{{ route('kitchen.recipes.create') }}" class="ef-btn-dark" style="display:inline-flex;align-items:center;gap:7px;padding:10px 20px;background:var(--ef-ink);color:#fff;border-radius:12px;font-size:.88rem;text-decoration:none;">
                        <i class="bi bi-plus-lg"></i> Create First Recipe
                    </a>
                @endif
            </div>
        @endforelse

    </div>{{-- /.ef-recipe-grid --}}

</div>{{-- /.ef-recipe-library --}}

</x-admin-layout>
