<x-admin-layout title="Inventory Categories">
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-0 fw-bold">Inventory Categories</h4>
        <p class="text-muted mb-0 small">Organise inventory items by category</p>
    </div>
    <a href="{{ route('admin.inventory.categories.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> New Category
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th class="text-center">Items</th>
                        <th class="text-center">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr>
                        <td class="fw-semibold">{{ $cat->name }}</td>
                        <td class="text-muted small">{{ $cat->description ?? '—' }}</td>
                        <td class="text-center">{{ $cat->items_count }}</td>
                        <td class="text-center">
                            @if($cat->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('admin.inventory.categories.edit', $cat) }}"
                                   class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.inventory.categories.toggle-status', $cat) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm {{ $cat->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                        <i class="bi bi-{{ $cat->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                @if($cat->items_count === 0)
                                <form method="POST" action="{{ route('admin.inventory.categories.destroy', $cat) }}">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Delete {{ $cat->name }}?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-tags fs-2 d-block mb-2"></i>No categories yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($categories->hasPages())
    <div class="card-footer bg-transparent border-top">{{ $categories->links() }}</div>
    @endif
</div>
</x-admin-layout>
