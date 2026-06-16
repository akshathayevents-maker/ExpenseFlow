<x-admin-layout title="Edit Recipe — {{ $recipe->name }}">
<div class="ef-recipe-library" style="padding-bottom:96px">

    {{-- ── Hero ──────────────────────────────────────────────────────────── --}}
    <header class="ef-create-hero">
        <div>
            <a href="{{ route('kitchen.recipes.show', $recipe) }}" class="ef-back mb-3">
                <i class="bi bi-arrow-left"></i> {{ $recipe->name }}
            </a>
            <div class="ef-eyebrow">Kitchen · Edit Recipe</div>
            <h1 class="ef-create-title">{{ $recipe->name }}</h1>
            <p class="ef-shell-note">Update recipe details, ingredients, and preparation steps.</p>
        </div>
        <div class="ef-create-actions">
            <a href="{{ route('kitchen.recipes.show', $recipe) }}" class="ef-btn">
                <i class="bi bi-eye"></i> View Recipe
            </a>
        </div>
    </header>

    {{-- ── Validation errors ────────────────────────────────────────────── --}}
    @if($errors->any())
        <div class="ef-card mb-4" style="border-left:4px solid var(--ef-danger)">
            <div class="ef-card-body py-2" style="font-size:.88rem">
                <strong style="color:var(--ef-danger)">Please fix the following errors:</strong>
                <ul style="margin:6px 0 0;padding-left:18px;color:var(--ef-ink-2)">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('kitchen.recipes.update', $recipe) }}" id="recipeForm" novalidate>
        @csrf @method('PUT')

        <div class="ef-create-layout">

            {{-- ── Left column ────────────────────────────────────────────── --}}
            <main class="ef-flow">

                {{-- ─── Section 1: Recipe Information ─── --}}
                <x-premium.card>
                    <div class="ef-section-intro">
                        <span class="ef-section-number">1</span>
                        <h2 class="ef-section-heading">Recipe Information</h2>
                        <p class="ef-section-copy">Update the name, category, and batch yield.</p>
                    </div>

                    <div class="ef-field-grid">
                        <div class="ef-span-8">
                            <label class="ef-label" for="name">Recipe Name <span style="color:var(--ef-danger)">*</span></label>
                            <input id="name" name="name" class="ef-input @error('name') --error @enderror"
                                   value="{{ old('name', $recipe->name) }}"
                                   placeholder="e.g. Upma, Sambar, Lemon Rice" required autofocus>
                            @error('name')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="ef-span-4">
                            <label class="ef-label" for="category">
                                Category
                                <span style="color:var(--ef-muted);font-weight:400;font-size:.78rem">(optional)</span>
                            </label>
                            <select id="category" name="category" class="ef-select @error('category') --error @enderror">
                                <option value="" @selected(old('category', $recipe->category) === null || old('category', $recipe->category) === '')>— Uncategorised —</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" @selected(old('category', $recipe->category) === $cat)>{{ $cat }}</option>
                                @endforeach
                            </select>
                            @error('category')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="ef-span-4">
                            <label class="ef-label" for="yield_per_batch">
                                Batch Yield <span style="color:var(--ef-danger)">*</span>
                                <span style="color:var(--ef-muted);font-weight:400"> — people per batch</span>
                            </label>
                            <input id="yield_per_batch" name="yield_per_batch" type="number"
                                   class="ef-input @error('yield_per_batch') --error @enderror"
                                   value="{{ old('yield_per_batch', $recipe->yield_per_batch) }}"
                                   placeholder="e.g. 50" min="0.01" step="any" required>
                            @error('yield_per_batch')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="ef-span-4">
                            <label class="ef-label" for="yield_unit">Yield Unit <span style="color:var(--ef-danger)">*</span></label>
                            <input id="yield_unit" name="yield_unit"
                                   class="ef-input @error('yield_unit') --error @enderror"
                                   value="{{ old('yield_unit', $recipe->yield_unit) }}"
                                   placeholder="portions, kg, litres…"
                                   list="yieldUnitSuggestions" required>
                            <datalist id="yieldUnitSuggestions">
                                <option value="portions"><option value="kg">
                                <option value="litres"><option value="pieces">
                                <option value="servings"><option value="plates">
                            </datalist>
                            @error('yield_unit')<div class="ef-field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="ef-span-4">
                            <label class="ef-label" for="prep_time_minutes">Prep Time</label>
                            <div style="display:flex;align-items:center;gap:8px">
                                <input id="prep_time_minutes" name="prep_time_minutes" type="number"
                                       class="ef-input @error('prep_time_minutes') --error @enderror"
                                       value="{{ old('prep_time_minutes', $recipe->prep_time_minutes) }}"
                                       placeholder="0" min="0" max="1440">
                                <span style="color:var(--ef-muted);font-size:.84rem;white-space:nowrap">min</span>
                            </div>
                        </div>

                        <div class="ef-span-4">
                            <label class="ef-label" for="cook_time_minutes">Cook Time</label>
                            <div style="display:flex;align-items:center;gap:8px">
                                <input id="cook_time_minutes" name="cook_time_minutes" type="number"
                                       class="ef-input @error('cook_time_minutes') --error @enderror"
                                       value="{{ old('cook_time_minutes', $recipe->cook_time_minutes) }}"
                                       placeholder="0" min="0" max="1440">
                                <span style="color:var(--ef-muted);font-size:.84rem;white-space:nowrap">min</span>
                            </div>
                        </div>

                        <div class="ef-span-12">
                            <label class="ef-label" for="description">Description <span style="color:var(--ef-muted);font-weight:400">(optional)</span></label>
                            <textarea id="description" name="description"
                                      class="ef-textarea @error('description') --error @enderror"
                                      placeholder="Brief description…" rows="2">{{ old('description', $recipe->description) }}</textarea>
                        </div>
                    </div>
                </x-premium.card>

                {{-- ─── Section 2: Ingredients ─── --}}
                <x-premium.card>
                    <div class="ef-section-intro">
                        <span class="ef-section-number">2</span>
                        <h2 class="ef-section-heading">Ingredients</h2>
                        <p class="ef-section-copy">Ingredients for <strong>one full batch</strong>. Leave quantity blank for "As Required" items.</p>
                    </div>

                    <div class="ef-ingredient-builder" id="ingredientBuilder">
                        @forelse(old('ingredients', []) as $i => $ing)
                            <x-kitchen.ingredient-row :index="$i" :data="$ing" />
                        @empty
                            @foreach($recipe->ingredients as $i => $ing)
                                <x-kitchen.ingredient-row :index="$i" :data="[
                                    'ingredient_name'   => $ing->ingredient_name,
                                    'quantity_per_batch'=> $ing->quantity_per_batch,
                                    'quantity_note'     => $ing->quantity_note,
                                    'unit'              => $ing->unit,
                                    'prep_note'         => $ing->prep_note,
                                    'is_optional'       => $ing->is_optional,
                                    'inventory_item_id' => $ing->inventory_item_id,
                                ]" />
                            @endforeach
                        @endforelse
                    </div>

                    <button type="button" class="ef-add-row-btn" onclick="addIngredient()">
                        <i class="bi bi-plus-circle"></i> Add Ingredient
                    </button>
                </x-premium.card>

                {{-- ─── Section 3: SOP Steps ─── --}}
                <x-premium.card>
                    <div class="ef-section-intro">
                        <span class="ef-section-number">3</span>
                        <h2 class="ef-section-heading">Preparation Steps</h2>
                        <p class="ef-section-copy">Step-by-step kitchen instructions. Use ▲ ▼ to reorder.</p>
                    </div>

                    <div class="ef-sop-builder" id="sopBuilder">
                        @forelse(old('sops', []) as $i => $sop)
                            <x-kitchen.sop-row :index="$i" :stepNumber="$i + 1" :data="$sop" />
                        @empty
                            @foreach($recipe->sops as $i => $sop)
                                <x-kitchen.sop-row :index="$i" :stepNumber="$sop->step_number" :data="[
                                    'title'            => $sop->title,
                                    'instruction'      => $sop->instruction,
                                    'duration_minutes' => $sop->duration_minutes,
                                ]" />
                            @endforeach
                        @endforelse
                    </div>

                    <button type="button" class="ef-add-row-btn" onclick="addSopStep()">
                        <i class="bi bi-plus-circle"></i> Add Step
                    </button>
                </x-premium.card>

            </main>

            {{-- ── Right column: live preview ──────────────────────────────── --}}
            <aside>
                <div class="ef-recipe-preview-panel">
                    <div class="ef-preview-panel-head">
                        <i class="bi bi-eye me-1"></i> Live Preview
                    </div>
                    <div class="ef-preview-panel-body" id="recipePreview">
                        <div class="ef-preview-empty">Loading preview…</div>
                    </div>
                </div>
            </aside>

        </div>{{-- /.ef-create-layout --}}
    </form>

    {{-- ── Sticky save bar ──────────────────────────────────────────────── --}}
    <div class="ef-kitchen-save-bar">
        <a href="{{ route('kitchen.recipes.show', $recipe) }}" class="ef-btn">Cancel</a>
        <button type="submit" form="recipeForm" class="ef-btn-dark"
                style="display:inline-flex;align-items:center;gap:8px;padding:10px 22px;background:var(--ef-ink);color:#fff;border-radius:12px;font-size:.9rem;font-weight:620;border:none;cursor:pointer;">
            <i class="bi bi-floppy"></i> Save Changes
        </button>
    </div>

</div>

{{-- Templates and JS (same as create) --}}
<template id="ingredientTemplate">
    <div class="ef-ingredient-row" data-ing-index="__IDX__">
        <div class="ef-ing-grid">
            <div>
                <label class="ef-label">Ingredient Name <span style="color:var(--ef-danger)">*</span></label>
                <input type="text" name="ingredients[__IDX__][ingredient_name]" class="ef-input" placeholder="e.g. Rava, Salt, Green Chilli" required>
            </div>
            <div>
                <label class="ef-label">Quantity</label>
                <input type="number" name="ingredients[__IDX__][quantity_per_batch]" class="ef-input ing-qty" placeholder="e.g. 8" min="0" step="any" oninput="toggleQtyNote(this)">
            </div>
            <div>
                <label class="ef-label">Unit</label>
                <input type="text" name="ingredients[__IDX__][unit]" class="ef-input" placeholder="kg, L, nos…" list="unitSuggestions">
            </div>
            <button type="button" class="ef-ing-remove" onclick="removeIngredient(this)" aria-label="Remove ingredient">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="ef-ing-footer">
            <div class="ef-qty-note-wrap">
                <label class="ef-label" style="margin-bottom:4px">Display as <span style="color:var(--ef-muted);font-weight:400;font-size:.78rem">— shown when quantity is blank</span></label>
                <input type="text" name="ingredients[__IDX__][quantity_note]" class="ef-input" placeholder="As Required · To Taste · Approx 20 L">
            </div>
            <div>
                <label class="ef-label" style="margin-bottom:4px">Prep note <span style="color:var(--ef-muted);font-weight:400;font-size:.78rem">(optional)</span></label>
                <input type="text" name="ingredients[__IDX__][prep_note]" class="ef-input" placeholder="finely chopped, sifted, marinated…">
            </div>
            <label class="ef-optional-label">
                <input type="checkbox" name="ingredients[__IDX__][is_optional]" value="1"> Optional
            </label>
            <input type="hidden" name="ingredients[__IDX__][inventory_item_id]" value="">
        </div>
    </div>
</template>

<template id="sopTemplate">
    <div class="ef-sop-card" data-sop-index="__IDX__">
        <div class="ef-sop-card-head">
            <span class="ef-sop-num-badge sop-num-badge">__STEPNUM__</span>
            <span class="ef-sop-step-lbl">Step __STEPNUM__</span>
            <div style="display:flex;gap:2px">
                <button type="button" class="ef-sop-btn" onclick="moveSop(this,-1)" title="Move up" aria-label="Move up"><i class="bi bi-chevron-up"></i></button>
                <button type="button" class="ef-sop-btn" onclick="moveSop(this,1)" title="Move down" aria-label="Move down"><i class="bi bi-chevron-down"></i></button>
                <button type="button" class="ef-sop-btn --del" onclick="removeSop(this)" title="Remove" aria-label="Remove step"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>
        <div class="ef-sop-card-body">
            <div>
                <label class="ef-label">Step Title <span style="color:var(--ef-danger)">*</span></label>
                <input type="text" name="sops[__IDX__][title]" class="ef-input" placeholder="e.g. Heat the oil" required>
            </div>
            <div>
                <label class="ef-label">Instructions <span style="color:var(--ef-danger)">*</span></label>
                <textarea name="sops[__IDX__][instruction]" class="ef-textarea" placeholder="Detailed instructions…" rows="3" required></textarea>
            </div>
            <div style="max-width:160px">
                <label class="ef-label">Duration <span style="color:var(--ef-muted);font-weight:400;font-size:.78rem">(optional)</span></label>
                <div style="display:flex;align-items:center;gap:8px">
                    <input type="number" name="sops[__IDX__][duration_minutes]" class="ef-input" placeholder="0" min="0">
                    <span style="color:var(--ef-muted);font-size:.84rem;white-space:nowrap">min</span>
                </div>
            </div>
        </div>
    </div>
</template>

<datalist id="unitSuggestions">
    <option value="kg"><option value="g"><option value="L">
    <option value="ml"><option value="nos"><option value="packets">
    <option value="cups"><option value="tbsp"><option value="tsp">
</datalist>

<script>
(function () {
    'use strict';

    // Start ingIdx and sopIdx after what's already server-rendered
    let ingIdx = {{ count(old('ingredients', $recipe->ingredients->toArray())) }};
    let sopIdx = {{ count(old('sops', $recipe->sops->toArray())) }};

    window.addIngredient = function () {
        const tpl  = document.getElementById('ingredientTemplate').innerHTML;
        const html = tpl.replaceAll('__IDX__', ingIdx++);
        document.getElementById('ingredientBuilder').insertAdjacentHTML('beforeend', html);
        updatePreview();
    };

    window.removeIngredient = function (btn) {
        btn.closest('.ef-ingredient-row').remove();
        updatePreview();
    };

    window.toggleQtyNote = function (input) {
        const wrap = input.closest('.ef-ingredient-row').querySelector('.ef-qty-note-wrap');
        wrap.classList.toggle('--show', input.value === '');
        updatePreview();
    };

    window.addSopStep = function () {
        const builder = document.getElementById('sopBuilder');
        const num     = builder.children.length + 1;
        const tpl     = document.getElementById('sopTemplate').innerHTML;
        const html    = tpl.replaceAll('__IDX__', sopIdx++).replaceAll('__STEPNUM__', num);
        builder.insertAdjacentHTML('beforeend', html);
        updatePreview();
    };

    window.removeSop = function (btn) {
        btn.closest('.ef-sop-card').remove();
        renumberSops();
        updatePreview();
    };

    window.moveSop = function (btn, dir) {
        const card  = btn.closest('.ef-sop-card');
        const p     = card.parentElement;
        const cards = [...p.children];
        const i     = cards.indexOf(card);
        const t     = i + dir;
        if (t < 0 || t >= cards.length) return;
        if (dir === -1) p.insertBefore(card, cards[t]);
        else            p.insertBefore(cards[t], card);
        renumberSops();
        updatePreview();
    };

    function renumberSops() {
        document.querySelectorAll('#sopBuilder .ef-sop-card').forEach((card, i) => {
            const n = i + 1;
            card.querySelectorAll('.sop-num-badge').forEach(el => el.textContent = n);
            card.querySelectorAll('.ef-sop-step-lbl').forEach(el => el.textContent = 'Step ' + n);
        });
    }

    let previewTimer;
    window.updatePreview = function () {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(_renderPreview, 280);
    };

    function _renderPreview() {
        const panel    = document.getElementById('recipePreview');
        const name     = document.getElementById('name')?.value?.trim() || '';
        const yieldVal = document.getElementById('yield_per_batch')?.value || '';
        const unit     = document.getElementById('yield_unit')?.value || 'portions';
        const prepTime = parseInt(document.getElementById('prep_time_minutes')?.value) || 0;
        const cookTime = parseInt(document.getElementById('cook_time_minutes')?.value) || 0;
        const category = document.getElementById('category')?.value || '';

        if (!name) { panel.innerHTML = '<div class="ef-preview-empty">Preview updates as you type.</div>'; return; }

        let html = '<div class="ef-preview-name">' + esc(name) + '</div>';
        if (category) html += '<div style="margin-bottom:8px"><span class="ef-recipe-cat-badge ef-cat--' + slugify(category) + '">' + esc(category) + '</span></div>';

        const meta = [];
        if (yieldVal) meta.push('1 batch = ' + yieldVal + ' ' + esc(unit));
        const total = prepTime + cookTime;
        if (total > 0) meta.push(total + ' min total');
        if (meta.length) html += '<div class="ef-preview-meta">' + meta.join(' · ') + '</div>';

        const ingRows = document.querySelectorAll('#ingredientBuilder .ef-ingredient-row');
        if (ingRows.length) {
            html += '<div class="ef-preview-section-lbl">Ingredients</div>';
            ingRows.forEach(row => {
                const n  = row.querySelector('[name$="[ingredient_name]"]')?.value?.trim() || '';
                if (!n) return;
                const q  = row.querySelector('[name$="[quantity_per_batch]"]')?.value?.trim() || '';
                const u  = row.querySelector('[name$="[unit]"]')?.value?.trim() || '';
                const nt = row.querySelector('[name$="[quantity_note]"]')?.value?.trim() || '';
                const d  = q ? (q + (u ? ' ' + u : '')) : (nt || 'As Required');
                html += '<div class="ef-preview-ing-row"><span>' + esc(n) + '</span><span class="ef-preview-ing-qty">' + esc(d) + '</span></div>';
            });
        }

        const sopCards = document.querySelectorAll('#sopBuilder .ef-sop-card');
        if (sopCards.length) {
            html += '<div class="ef-preview-section-lbl">Steps</div>';
            sopCards.forEach((card, i) => {
                const t = card.querySelector('[name$="[title]"]')?.value?.trim() || '';
                const s = card.querySelector('[name$="[instruction]"]')?.value?.trim() || '';
                if (!t && !s) return;
                html += '<div class="ef-preview-sop-item"><div class="ef-preview-sop-num">Step ' + (i+1) + '</div>';
                if (t) html += '<div class="ef-preview-sop-title">' + esc(t) + '</div>';
                if (s) html += '<div class="ef-preview-sop-inst">' + esc(s) + '</div>';
                html += '</div>';
            });
        }

        panel.innerHTML = html;
    }

    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    function slugify(s) { return s.toLowerCase().replace(/\s+/g,'-').replace(/[^a-z0-9-]/g,''); }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('recipeForm').addEventListener('input', updatePreview);
        _renderPreview();
    });
}());
</script>

</x-admin-layout>
