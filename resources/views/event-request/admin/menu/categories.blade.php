<x-admin-layout title="Menu Categories">
<x-event-request.admin-responsive-styles />
<div class="container-fluid py-4" style="max-width:860px">
    <div class="erm-header">
        <div class="erm-header-text">
            <div class="text-uppercase small fw-bold" style="color:#B8893E;letter-spacing:.08em;font-size:.7rem">Event Request Portal</div>
            <h1 class="h3 fw-bold mb-0">Menu Categories</h1>
        </div>
        <div class="erm-header-actions">
            <a href="{{ route('admin.event-request-menu.items.index') }}" class="btn btn-outline-dark">Manage Items</a>
        </div>
    </div>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <x-premium.card class="mb-4">
        <h2 class="h6 fw-bold mb-3">Add category</h2>
        <form method="POST" action="{{ route('admin.event-request-menu.categories.store') }}" class="row g-2 erm-form-1col">
            @csrf
            <div class="col-md-4"><input class="form-control" name="name" placeholder="e.g. Main Course" required></div>
            <div class="col-md-5"><input class="form-control" name="description" placeholder="Description (optional)"></div>
            <div class="col-md-2"><input type="number" class="form-control" name="display_order" placeholder="Order" value="0"></div>
            <div class="col-md-1 d-grid"><button class="btn btn-dark">Add</button></div>
        </form>
    </x-premium.card>

    <x-premium.card>
        @if($categories->isEmpty())
            <div class="erm-empty">
                <div class="glyph"><i class="bi bi-tags"></i></div>
                <div class="title">No categories yet.</div>
                <div class="body">Add your first category using the form above.</div>
            </div>
        @else
            {{-- Desktop table (>=768px) --}}
            <div class="erm-desktop-table">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr class="small text-uppercase text-muted"><th>Order</th><th>Name</th><th>Description</th><th>Items</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @foreach($categories as $cat)
                                <tr>
                                    <td>{{ $cat->display_order }}</td>
                                    <td class="fw-bold">{{ $cat->name }}</td>
                                    <td class="text-muted small">{{ $cat->description }}</td>
                                    <td>{{ $cat->items_count }}</td>
                                    <td><span class="badge {{ $cat->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $cat->is_active ? 'Active' : 'Inactive' }}</span></td>
                                    <td class="text-end">
                                        <div class="d-flex gap-2 justify-content-end">
                                            @include('event-request.admin.menu._category-edit-trigger', ['cat' => $cat, 'class' => 'btn btn-sm btn-outline-dark'])
                                            <form method="POST" action="{{ route('admin.event-request-menu.categories.destroy', $cat) }}" onsubmit="return confirm('Delete this category and its items?')">
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
            <div class="erm-mobile-cards">
                @foreach($categories as $cat)
                    <div class="erm-card">
                        <div class="erm-card-top">
                            <div>
                                <div class="erm-card-title">{{ $cat->name }}</div>
                                @if($cat->description)
                                    <div class="erm-card-subtitle">{{ $cat->description }}</div>
                                @endif
                            </div>
                            <span class="badge {{ $cat->is_active ? 'text-bg-success' : 'text-bg-secondary' }} flex-shrink-0">{{ $cat->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                        <div class="erm-card-grid">
                            <div class="erm-card-field">
                                <div class="k">Items</div>
                                <div class="v">{{ $cat->items_count }}</div>
                            </div>
                            <div class="erm-card-field">
                                <div class="k">Order</div>
                                <div class="v">{{ $cat->display_order }}</div>
                            </div>
                        </div>
                        <div class="erm-card-footer">
                            <a href="{{ route('admin.event-request-menu.items.index', ['category_id' => $cat->id]) }}" class="small text-muted text-decoration-none">View items <i class="bi bi-arrow-right"></i></a>
                            <div class="erm-card-actions">
                                @include('event-request.admin.menu._category-edit-trigger', ['cat' => $cat, 'class' => 'btn btn-outline-dark'])
                                <div class="dropdown">
                                    <button class="btn btn-outline-dark erm-more-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More actions">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <form method="POST" action="{{ route('admin.event-request-menu.categories.destroy', $cat) }}" onsubmit="return confirm('Delete this category and its items?')">
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
</div>

{{-- Shared edit modal — populated per row via JS. --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="editCategoryForm" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-bold">Name</label>
                        <input class="form-control" name="name" id="editCategoryName" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Description</label>
                        <input class="form-control" name="description" id="editCategoryDescription">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Display Order</label>
                        <input type="number" class="form-control" name="display_order" id="editCategoryOrder">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Status</label>
                        <select class="form-select" name="is_active" id="editCategoryActive">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
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
document.querySelectorAll('.js-edit-category').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.getElementById('editCategoryForm').action = btn.dataset.action;
        document.getElementById('editCategoryName').value = btn.dataset.name;
        document.getElementById('editCategoryDescription').value = btn.dataset.description || '';
        document.getElementById('editCategoryOrder').value = btn.dataset.order;
        document.getElementById('editCategoryActive').value = btn.dataset.active;
        new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
    });
});
</script>
</x-admin-layout>
