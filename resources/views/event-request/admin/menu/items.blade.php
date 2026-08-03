<x-admin-layout title="Menu Items">
<div class="container-fluid py-4" style="max-width:1100px">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="text-uppercase small fw-bold" style="color:#B8893E;letter-spacing:.08em;font-size:.7rem">Event Request Portal</div>
            <h1 class="h3 fw-bold mb-0">Menu Items</h1>
        </div>
        <a href="{{ route('admin.event-request-menu.categories.index') }}" class="btn btn-outline-dark">Manage Categories</a>
    </div>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <x-premium.card class="mb-4">
        <h2 class="h6 fw-bold mb-3">Add menu item</h2>
        <form method="POST" action="{{ route('admin.event-request-menu.items.store') }}" class="row g-2">
            @csrf
            <div class="col-md-3">
                <select class="form-select" name="category_id" required>
                    <option value="">Category</option>
                    @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3"><input class="form-control" name="name" placeholder="Item name" required></div>
            <div class="col-md-3"><input class="form-control" name="description" placeholder="Description"></div>
            <div class="col-md-1"><input type="number" step="0.01" class="form-control" name="price_per_person" placeholder="₹/person" required></div>
            <div class="col-md-1">
                <select class="form-select" name="is_veg">
                    <option value="1">Veg</option>
                    <option value="0">Non-Veg</option>
                </select>
            </div>
            <div class="col-md-1 d-grid"><button class="btn btn-dark">Add</button></div>
            <div class="col-12 d-flex gap-3 mt-1">
                <label class="form-check small"><input type="checkbox" class="form-check-input" name="is_popular" value="1"> Popular</label>
                <label class="form-check small"><input type="checkbox" class="form-check-input" name="is_chef_recommended" value="1"> Chef Recommended</label>
            </div>
        </form>
    </x-premium.card>

    <x-premium.card class="mb-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search by name...">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Type</label>
                <select name="is_veg" class="form-select">
                    <option value="">All</option>
                    <option value="1" @selected(request('is_veg') === '1')>Veg</option>
                    <option value="0" @selected(request('is_veg') === '0')>Non-Veg</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Status</label>
                <select name="is_active" class="form-select">
                    <option value="">All</option>
                    <option value="1" @selected(request('is_active') === '1')>Active</option>
                    <option value="0" @selected(request('is_active') === '0')>Inactive</option>
                </select>
            </div>
            <div class="col-md-1 d-grid"><button class="btn btn-outline-dark">Go</button></div>
            @if(request()->hasAny(['search','category_id','is_veg','is_active']))
                <div class="col-12">
                    <a href="{{ route('admin.event-request-menu.items.index') }}" class="small text-muted">Clear filters</a>
                </div>
            @endif
        </form>
    </x-premium.card>

    <x-premium.card>
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="text-muted small">{{ $items->total() }} item{{ $items->total() === 1 ? '' : 's' }} found</div>
            <form method="GET" class="d-flex align-items-center gap-2">
                @foreach(request()->except('per_page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <label class="small fw-bold text-muted mb-0">Per page</label>
                <select name="per_page" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                    @foreach([10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" @selected($items->perPage() == $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr class="small text-uppercase text-muted"><th>Category</th><th>Item</th><th>Type</th><th>Price</th><th>Badges</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td class="text-muted small">{{ $item->category->name }}</td>
                            <td class="fw-bold">{{ $item->name }}</td>
                            <td><span class="badge {{ $item->is_veg ? 'text-bg-success' : 'text-bg-danger' }}">{{ $item->is_veg ? 'Veg' : 'Non-Veg' }}</span></td>
                            <td>₹{{ number_format($item->price_per_person, 0) }}</td>
                            <td>
                                @if($item->is_popular)<span class="badge text-bg-warning">Popular</span>@endif
                                @if($item->is_chef_recommended)<span class="badge" style="background:#B8893E;color:#fff">Chef Rec.</span>@endif
                            </td>
                            <td><span class="badge {{ $item->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $item->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="button" class="btn btn-sm btn-outline-dark js-edit-item"
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
                                    <form method="POST" action="{{ route('admin.event-request-menu.items.destroy', $item) }}" onsubmit="return confirm('Delete this item?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No menu items match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-premium.card>

    <div class="mt-3">{{ $items->links() }}</div>
</div>

{{-- Shared edit modal — one instance, populated per row via JS so we don't
     render a modal per item (there can be hundreds). --}}
<div class="modal fade" id="editItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" id="editItemForm" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Category</label>
                        <select class="form-select" name="category_id" id="editItemCategory" required>
                            @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Name</label>
                        <input class="form-control" name="name" id="editItemName" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Description</label>
                        <input class="form-control" name="description" id="editItemDescription">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Price / Person</label>
                        <input type="number" step="0.01" class="form-control" name="price_per_person" id="editItemPrice" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Type</label>
                        <select class="form-select" name="is_veg" id="editItemVeg">
                            <option value="1">Veg</option>
                            <option value="0">Non-Veg</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Status</label>
                        <select class="form-select" name="is_active" id="editItemActive">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-3">
                        <label class="form-check small"><input type="checkbox" class="form-check-input" name="is_popular" value="1" id="editItemPopular"> Popular</label>
                        <label class="form-check small"><input type="checkbox" class="form-check-input" name="is_chef_recommended" value="1" id="editItemChef"> Chef Recommended</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-dark">Save changes</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.js-edit-item').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.getElementById('editItemForm').action = btn.dataset.action;
        document.getElementById('editItemCategory').value = btn.dataset.categoryId;
        document.getElementById('editItemName').value = btn.dataset.name;
        document.getElementById('editItemDescription').value = btn.dataset.description || '';
        document.getElementById('editItemPrice').value = btn.dataset.price;
        document.getElementById('editItemVeg').value = btn.dataset.veg;
        document.getElementById('editItemActive').value = btn.dataset.active;
        document.getElementById('editItemPopular').checked = btn.dataset.popular === '1';
        document.getElementById('editItemChef').checked = btn.dataset.chef === '1';
        new bootstrap.Modal(document.getElementById('editItemModal')).show();
    });
});
</script>
</x-admin-layout>
