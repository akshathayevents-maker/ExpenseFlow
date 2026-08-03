<x-admin-layout title="Menu Categories">
<div class="container-fluid py-4" style="max-width:860px">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="text-uppercase small fw-bold" style="color:#B8893E;letter-spacing:.08em;font-size:.7rem">Event Request Portal</div>
            <h1 class="h3 fw-bold mb-0">Menu Categories</h1>
        </div>
        <a href="{{ route('admin.event-request-menu.items.index') }}" class="btn btn-outline-dark">Manage Items</a>
    </div>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <x-premium.card class="mb-4">
        <h2 class="h6 fw-bold mb-3">Add category</h2>
        <form method="POST" action="{{ route('admin.event-request-menu.categories.store') }}" class="row g-2">
            @csrf
            <div class="col-md-4"><input class="form-control" name="name" placeholder="e.g. Main Course" required></div>
            <div class="col-md-5"><input class="form-control" name="description" placeholder="Description (optional)"></div>
            <div class="col-md-2"><input type="number" class="form-control" name="display_order" placeholder="Order" value="0"></div>
            <div class="col-md-1 d-grid"><button class="btn btn-dark">Add</button></div>
        </form>
    </x-premium.card>

    <x-premium.card>
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
                                    <button type="button" class="btn btn-sm btn-outline-dark js-edit-category"
                                            data-action="{{ route('admin.event-request-menu.categories.update', $cat) }}"
                                            data-name="{{ $cat->name }}"
                                            data-description="{{ $cat->description }}"
                                            data-order="{{ $cat->display_order }}"
                                            data-active="{{ $cat->is_active ? 1 : 0 }}">
                                        Edit
                                    </button>
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
