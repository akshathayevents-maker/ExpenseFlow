@php $isSelected = in_array($item->id, $selectedItemIds ?? []); @endphp
<div class="erp-item-card {{ $isSelected ? 'selected' : '' }}"
     data-price="{{ $item->price_per_person }}"
     role="checkbox"
     aria-checked="{{ $isSelected ? 'true' : 'false' }}"
     tabindex="0">
    <input type="checkbox" name="menu_item_ids[]" value="{{ $item->id }}" class="visually-hidden" {{ $isSelected ? 'checked' : '' }} tabindex="-1">

    <div class="erp-item-media">
        @if($item->image_path)
            <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->name }}" loading="lazy">
        @else
            <i class="bi bi-egg-fried"></i>
        @endif
    </div>

    <div class="erp-item-body">
        <div class="erp-item-top">
            <span class="erp-veg-dot {{ $item->is_veg ? 'veg' : 'non_veg' }}"></span>
            <div style="flex:1;min-width:0">
                <div class="erp-item-name">{{ $item->name }}</div>
                @if($item->description)
                    <div class="erp-item-desc">{{ $item->description }}</div>
                @endif
                @if($item->is_popular || $item->is_chef_recommended)
                    <div class="erp-item-badges">
                        @if($item->is_popular)<span class="erp-badge erp-badge-popular"><i class="bi bi-star-fill"></i> Popular</span>@endif
                        @if($item->is_chef_recommended)<span class="erp-badge erp-badge-chef"><i class="bi bi-award-fill"></i> Chef Recommended</span>@endif
                    </div>
                @endif
            </div>
        </div>
        <div class="erp-item-bottom">
            <span class="erp-item-price">₹{{ number_format($item->price_per_person, 0) }}/person</span>
            <span class="erp-add-btn"><i class="bi {{ $isSelected ? 'bi-check-lg' : 'bi-plus-lg' }}"></i> {{ $isSelected ? 'Added' : 'Add' }}</span>
        </div>
    </div>
</div>
