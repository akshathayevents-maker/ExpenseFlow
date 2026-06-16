<x-admin-layout title="{{ $recipe->name }} — Recipe">
@php $catSlug = $recipe->category ? Str::slug($recipe->category) : 'none'; @endphp

<div class="ef-recipe-doc">

    {{-- ── Back nav ─────────────────────────────────────────────────────── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;flex-wrap:wrap;gap:10px">
        <a href="{{ route('kitchen.recipes.index') }}" class="ef-back">
            <i class="bi bi-arrow-left"></i> Recipe Library
        </a>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="{{ route('kitchen.recipes.edit', $recipe) }}" class="ef-btn">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('employee.kitchen.calculator') }}" class="ef-btn">
                <i class="bi bi-calculator"></i> Open Calculator
            </a>
        </div>
    </div>

    {{-- ── Flash ────────────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="ef-card mb-3" style="border-left:4px solid #16a34a">
            <div class="ef-card-body py-2" style="color:#15803d;font-size:.88rem">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            </div>
        </div>
    @endif

    {{-- ── Hero ──────────────────────────────────────────────────────────── --}}
    <div style="margin-bottom:8px">
        @if($recipe->category)
            <span class="ef-recipe-cat-badge ef-cat--{{ $catSlug }}" style="font-size:.72rem">
                {{ $recipe->category }}
            </span>
        @else
            <span class="ef-recipe-cat-badge ef-cat--none" style="font-size:.72rem;background:#f5f2ee;color:#9c8e7e;border:1px solid #e4dfd8">
                Uncategorised
            </span>
        @endif
        &nbsp;
        <span class="ef-recipe-status {{ $recipe->is_active ? '' : '--inactive' }}" style="display:inline-flex">
            {{ $recipe->is_active ? 'Active' : 'Inactive' }}
        </span>
    </div>

    <h1 class="ef-recipe-doc-name">{{ $recipe->name }}</h1>

    @if($recipe->description)
        <p style="color:var(--ef-muted);font-size:1rem;margin:0 0 16px;line-height:1.65">{{ $recipe->description }}</p>
    @endif

    <div class="ef-recipe-doc-meta">
        <span class="ef-recipe-doc-meta-item">
            <i class="bi bi-people"></i>
            1 batch serves <strong>{{ number_format($recipe->yield_per_batch, 0) }} {{ $recipe->yield_unit }}</strong>
        </span>
        @if($recipe->prep_time_minutes)
            <span class="ef-recipe-doc-meta-item">
                <i class="bi bi-hourglass-split"></i>
                Prep <strong>{{ $recipe->prep_time_minutes }} min</strong>
            </span>
        @endif
        @if($recipe->cook_time_minutes)
            <span class="ef-recipe-doc-meta-item">
                <i class="bi bi-fire"></i>
                Cook <strong>{{ $recipe->cook_time_minutes }} min</strong>
            </span>
        @endif
        @if($recipe->totalTimeMinutes() > 0)
            <span class="ef-recipe-doc-meta-item">
                <i class="bi bi-clock"></i>
                Total <strong>{{ $recipe->totalTimeMinutes() }} min</strong>
            </span>
        @endif
        <span class="ef-recipe-doc-meta-item" style="color:var(--ef-faint);font-size:.78rem">
            Created by {{ $recipe->createdBy->name ?? '—' }}
        </span>
    </div>

    {{-- ── Ingredients ─────────────────────────────────────────────────── --}}
    @if($recipe->ingredients->count())
        <hr class="ef-recipe-doc-divider">
        <div class="ef-recipe-doc-section-lbl">
            Ingredients
            <span style="color:var(--ef-faint);font-weight:400;text-transform:none;letter-spacing:0;font-size:.78rem">
                — for 1 batch ({{ number_format($recipe->yield_per_batch, 0) }} {{ $recipe->yield_unit }})
            </span>
        </div>

        <div>
            @foreach($recipe->ingredients as $ing)
                <div class="ef-recipe-ing-row">
                    <div>
                        <span class="ef-recipe-ing-name">
                            {{ $ing->ingredient_name }}
                            @if($ing->prep_note)
                                <span class="ef-recipe-ing-note">({{ $ing->prep_note }})</span>
                            @endif
                        </span>
                        @if($ing->is_optional)
                            &nbsp;<span class="ef-optional-pill">optional</span>
                        @endif
                    </div>
                    <div class="ef-recipe-ing-detail">
                        @if($ing->isScalable())
                            {{ $ing->displayQuantity() }}
                            @if($ing->unit) <span style="color:var(--ef-muted);font-weight:400">{{ $ing->unit }}</span> @endif
                        @else
                            <span style="color:var(--ef-muted);font-style:italic">{{ $ing->displayQuantity() }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <hr class="ef-recipe-doc-divider">
        <div style="color:var(--ef-faint);font-size:.9rem;padding:12px 0">No ingredients added yet.</div>
    @endif

    {{-- ── SOP Steps ────────────────────────────────────────────────────── --}}
    @if($recipe->sops->count())
        <hr class="ef-recipe-doc-divider">
        <div class="ef-recipe-doc-section-lbl">Preparation Steps</div>

        @foreach($recipe->sops as $sop)
            <div class="ef-sop-doc-step">
                <div class="ef-sop-doc-num">{{ $sop->step_number }}</div>
                <div>
                    <div class="ef-sop-doc-title">{{ $sop->title }}</div>
                    <p class="ef-sop-doc-inst">{{ $sop->instruction }}</p>
                    @if($sop->duration_minutes)
                        <div class="ef-sop-doc-dur">
                            <i class="bi bi-clock me-1"></i>{{ $sop->duration_minutes }} min
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <hr class="ef-recipe-doc-divider">
        <div style="color:var(--ef-faint);font-size:.9rem;padding:12px 0">No preparation steps added yet.</div>
    @endif

    {{-- ── Footer actions ───────────────────────────────────────────────── --}}
    <hr class="ef-recipe-doc-divider">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="{{ route('kitchen.recipes.edit', $recipe) }}" class="ef-btn">
                <i class="bi bi-pencil"></i> Edit Recipe
            </a>

            <form method="POST" action="{{ route('kitchen.recipes.toggle-active', $recipe) }}" style="margin:0">
                @csrf @method('PATCH')
                <button type="submit" class="ef-btn">
                    @if($recipe->is_active)
                        <i class="bi bi-eye-slash"></i> Deactivate
                    @else
                        <i class="bi bi-check-circle"></i> Activate
                    @endif
                </button>
            </form>

            <form method="POST"
                  action="{{ route('kitchen.recipes.destroy', $recipe) }}"
                  style="margin:0"
                  onsubmit="return confirm('Permanently delete recipe {{ addslashes($recipe->name) }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="ef-btn" style="color:var(--ef-danger)">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </form>
        </div>

        <a href="{{ route('employee.kitchen.calculator') }}" class="ef-btn-dark"
           style="display:inline-flex;align-items:center;gap:7px;padding:10px 18px;background:var(--ef-ink);color:#fff;border-radius:12px;font-size:.88rem;text-decoration:none;">
            <i class="bi bi-calculator"></i> Open Calculator
        </a>
    </div>

</div>

</x-admin-layout>
