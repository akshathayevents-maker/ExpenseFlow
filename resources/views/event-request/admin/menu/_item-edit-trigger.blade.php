<button type="button" class="{{ $class }} js-edit-item"
        data-action="{{ route('admin.event-request-menu.items.update', $item) }}"
        data-id="{{ $item->id }}"
        data-category-id="{{ $item->category_id }}"
        data-name="{{ $item->name }}"
        data-description="{{ $item->description }}"
        data-price="{{ $item->price_per_person }}"
        data-veg="{{ $item->is_veg ? 1 : 0 }}"
        data-popular="{{ $item->is_popular ? 1 : 0 }}"
        data-chef="{{ $item->is_chef_recommended ? 1 : 0 }}"
        data-active="{{ $item->is_active ? 1 : 0 }}">
    Edit
</button>
