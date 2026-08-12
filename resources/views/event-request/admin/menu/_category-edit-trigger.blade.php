<button type="button" class="{{ $class }} js-edit-category"
        data-action="{{ route('admin.event-request-menu.categories.update', $cat) }}"
        data-name="{{ $cat->name }}"
        data-description="{{ $cat->description }}"
        data-order="{{ $cat->display_order }}"
        data-active="{{ $cat->is_active ? 1 : 0 }}">
    Edit
</button>
