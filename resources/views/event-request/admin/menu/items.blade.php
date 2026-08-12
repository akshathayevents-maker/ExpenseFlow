<x-admin-layout title="Menu Items">
<x-event-request.admin-responsive-styles />
<div class="container-fluid py-4" style="max-width:1100px">
    <div class="erm-header">
        <div class="erm-header-text">
            <div class="text-uppercase small fw-bold" style="color:#B8893E;letter-spacing:.08em;font-size:.7rem">Event Request Portal</div>
            <h1 class="h3 fw-bold mb-0">Menu Items</h1>
        </div>
        <div class="erm-header-actions">
            <a href="{{ route('admin.event-request-menu.categories.index') }}" class="btn btn-outline-dark">Manage Categories</a>
        </div>
    </div>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <x-premium.card class="mb-4">
        <h2 class="h6 fw-bold mb-3">Add menu item</h2>
        <form method="POST" action="{{ route('admin.event-request-menu.items.store') }}" class="row g-2 erm-form-1col">
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
        <form method="GET" class="erm-toolbar">
            <div class="erm-field erm-field-search">
                <label class="form-label small fw-bold">Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search menu items...">
            </div>
            <div class="erm-field">
                <label class="form-label small fw-bold">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="erm-field">
                <label class="form-label small fw-bold">Type</label>
                <select name="is_veg" class="form-select">
                    <option value="">All</option>
                    <option value="1" @selected(request('is_veg') === '1')>Veg</option>
                    <option value="0" @selected(request('is_veg') === '0')>Non-Veg</option>
                </select>
            </div>
            <div class="erm-field">
                <label class="form-label small fw-bold">Status</label>
                <select name="is_active" class="form-select">
                    <option value="">All</option>
                    <option value="1" @selected(request('is_active') === '1')>Active</option>
                    <option value="0" @selected(request('is_active') === '0')>Inactive</option>
                </select>
            </div>
            <button class="btn btn-outline-dark">Go</button>
            @if(request()->hasAny(['search','category_id','is_veg','is_active']))
                <a href="{{ route('admin.event-request-menu.items.index') }}" class="btn btn-link btn-sm text-muted">Clear</a>
            @endif
        </form>
    </x-premium.card>

    <x-premium.card>
        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
            <div class="text-muted small">{{ $items->total() }} item{{ $items->total() === 1 ? '' : 's' }}</div>
            <form method="GET" class="d-flex align-items-center gap-2">
                @foreach(request()->except('per_page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <select name="per_page" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()" aria-label="Items per page">
                    @foreach([10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" @selected($items->perPage() == $size)>{{ $size }} / page</option>
                    @endforeach
                </select>
            </form>
        </div>

        @if($items->isEmpty())
            <div class="erm-empty">
                <div class="glyph"><i class="bi bi-egg-fried"></i></div>
                <div class="title">No menu items yet.</div>
                <div class="body">{{ request()->hasAny(['search','category_id','is_veg','is_active']) ? 'No items match these filters.' : 'Add your first item using the form above.' }}</div>
                @if(request()->hasAny(['search','category_id','is_veg','is_active']))
                    <a href="{{ route('admin.event-request-menu.items.index') }}" class="btn btn-outline-dark btn-sm">Clear Filters</a>
                @endif
            </div>
        @else
            {{-- Desktop table (>=768px) --}}
            <div class="erm-desktop-table">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr class="small text-uppercase text-muted"><th>Category</th><th>Item</th><th>Type</th><th>Price</th><th>Badges</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @foreach($items as $item)
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
                                            @include('event-request.admin.menu._item-edit-trigger', ['item' => $item, 'class' => 'btn btn-sm btn-outline-dark'])
                                            <form method="POST" action="{{ route('admin.event-request-menu.items.destroy', $item) }}" onsubmit="return confirm('Delete this item?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile cards (<768px) --}}
            <div class="erm-mobile-cards erm-cards-compact">
                @foreach($items as $item)
                    <div class="erm-card erm-item-card">
                        <div class="erm-card-top">
                            <div class="erm-card-title">{{ $item->name }}</div>
                            <span class="erm-badge erm-badge-status {{ $item->is_active ? 'is-active' : 'is-inactive' }} flex-shrink-0">{{ $item->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                        @if($item->category)
                            <div class="erm-card-subtitle">{{ $item->category->name }}</div>
                        @endif

                        <div class="erm-item-meta">
                            <span class="erm-badge {{ $item->is_veg ? 'erm-badge-veg' : 'erm-badge-nonveg' }}">{{ $item->is_veg ? 'Veg' : 'Non-Veg' }}</span>
                            <span class="erm-item-meta-price">₹{{ number_format($item->price_per_person, 0) }}</span>
                            @if($item->is_popular)<span class="erm-badge erm-badge-popular">Popular</span>@endif
                            @if($item->is_chef_recommended)<span class="erm-badge erm-badge-chef">Chef Rec.</span>@endif
                        </div>

                        <div class="erm-item-footer">
                            <span class="erm-item-id">#{{ $item->id }}</span>
                            <div class="erm-item-actions">
                                @include('event-request.admin.menu._item-edit-trigger', ['item' => $item, 'class' => 'btn btn-outline-dark erm-btn-edit'])
                                <div class="dropdown">
                                    <button class="btn btn-outline-dark erm-more-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More actions">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <form method="POST" action="{{ route('admin.event-request-menu.items.destroy', $item) }}" onsubmit="return confirm('Delete this item?')">
                                                @csrf @method('DELETE')
                                                <button class="dropdown-item text-danger">Delete</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
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
                <div class="row g-3 erm-form-1col">
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
