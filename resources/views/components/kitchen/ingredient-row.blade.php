@props(['index', 'data' => []])
{{-- Used on create/edit when old() data exists (validation failure repopulation) --}}
<div class="ef-ingredient-row" data-ing-index="{{ $index }}">
    <div class="ef-ing-grid">
        <div>
            <label class="ef-label">Ingredient Name <span style="color:var(--ef-danger)">*</span></label>
            <input type="text"
                   name="ingredients[{{ $index }}][ingredient_name]"
                   class="ef-input @error("ingredients.$index.ingredient_name") --error @enderror"
                   value="{{ $data['ingredient_name'] ?? '' }}"
                   placeholder="e.g. Rava, Salt, Green Chilli" required>
            @error("ingredients.$index.ingredient_name")
                <div class="ef-field-error">{{ $message }}</div>
            @enderror
        </div>
        <div>
            <label class="ef-label">Quantity</label>
            <input type="number"
                   name="ingredients[{{ $index }}][quantity_per_batch]"
                   class="ef-input ing-qty"
                   value="{{ $data['quantity_per_batch'] ?? '' }}"
                   placeholder="e.g. 8" min="0" step="any"
                   oninput="toggleQtyNote(this)">
        </div>
        <div>
            <label class="ef-label">Unit</label>
            <input type="text"
                   name="ingredients[{{ $index }}][unit]"
                   class="ef-input"
                   value="{{ $data['unit'] ?? '' }}"
                   placeholder="kg, L, nos, g…"
                   list="unitSuggestions">
        </div>
        <button type="button" class="ef-ing-remove" onclick="removeIngredient(this)" aria-label="Remove ingredient">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="ef-ing-footer">
        <div class="ef-qty-note-wrap {{ empty($data['quantity_per_batch'] ?? '') ? '--show' : '' }}">
            <label class="ef-label" style="margin-bottom:4px">Display as <span style="color:var(--ef-muted);font-weight:400;font-size:.78rem">— shown when quantity is blank</span></label>
            <input type="text"
                   name="ingredients[{{ $index }}][quantity_note]"
                   class="ef-input"
                   value="{{ $data['quantity_note'] ?? '' }}"
                   placeholder="As Required · To Taste · Approx 20 L">
        </div>
        <div>
            <label class="ef-label" style="margin-bottom:4px">Prep note <span style="color:var(--ef-muted);font-weight:400;font-size:.78rem">(optional)</span></label>
            <input type="text"
                   name="ingredients[{{ $index }}][prep_note]"
                   class="ef-input"
                   value="{{ $data['prep_note'] ?? '' }}"
                   placeholder="finely chopped, sifted, marinated…">
        </div>
        <label class="ef-optional-label">
            <input type="checkbox"
                   name="ingredients[{{ $index }}][is_optional]"
                   value="1"
                   @checked(!empty($data['is_optional']))>
            Optional
        </label>
        <input type="hidden" name="ingredients[{{ $index }}][inventory_item_id]"
               value="{{ $data['inventory_item_id'] ?? '' }}">
    </div>
</div>
