{{-- Link an ingredient to a stock item (needed for issue/costing). $idx row index or '__IDX__', $data row values. --}}
<div class="ef-ing-ta">
    <label class="ef-label" style="margin-bottom:4px" lang="ta">தமிழ் பெயர் <small>Tamil name · display only</small></label>
    <input type="text" name="ingredients[{{ $idx }}][ingredient_name_ta]" class="ef-input" lang="ta" maxlength="200"
           value="{{ $data['ingredient_name_ta'] ?? '' }}" placeholder="{{ ! empty($data['ingredient_name']) ? (\App\Services\Recipes\RecipeLibraryService::tamilIngredient($data['ingredient_name']) ?? 'எ.கா. வெங்காயம்') : 'எ.கா. வெங்காயம்' }}">
</div>
<div class="ef-ing-map {{ ! empty($data['needs_mapping']) ? '--needs' : '' }}" data-ing-map-wrap>
    <label class="ef-label" style="margin-bottom:4px">Stock item <small>for issue &amp; costing</small>
        @if(! empty($data['needs_mapping']))<span class="ef-ing-map-flag">Needs mapping</span>@endif</label>
    <div><input type="text" class="ef-input" data-ing-map value="{{ $data['inventory_item_name'] ?? '' }}"
                placeholder="Link to stock item (English / தமிழ்)" autocomplete="off" aria-label="Stock item"></div>
    <input type="hidden" name="ingredients[{{ $idx }}][inventory_item_id]" value="{{ $data['inventory_item_id'] ?? '' }}" data-ing-map-id>
    <label class="ef-optional-label" style="margin-top:6px">
        <input type="checkbox" name="ingredients[{{ $idx }}][is_approximate]" value="1" @checked(! empty($data['is_approximate']))> Approximate quantity
    </label>
</div>
