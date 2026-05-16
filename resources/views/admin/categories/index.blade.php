<x-admin-layout title="Categories">
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Categories</h4>
            <p class="text-muted mb-0 small">Manage expense categories</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Category
        </a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="d-flex gap-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search categories…" value="{{ $search ?? '' }}">
                </div>
                <button class="btn btn-sm btn-outline-primary px-3" type="submit">Search</button>
                @if($search)
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-outline-secondary px-3">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th class="d-none d-md-table-cell">Description</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td class="text-muted small">{{ $category->id }}</td>
                                <td class="fw-semibold">{{ $category->name }}</td>
                                <td class="d-none d-md-table-cell text-muted small">
                                    {{ Str::limit($category->description, 60) ?? '—' }}
                                </td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle role-badge">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle role-badge">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="{{ route('admin.categories.edit', $category) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.categories.toggle-status', $category) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="btn btn-sm {{ $category->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                    title="{{ $category->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="bi bi-{{ $category->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                                    onclick="return confirm('Delete category {{ addslashes($category->name) }}?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="bi bi-tag fs-2 d-block mb-2"></i>
                                    No categories found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($categories->hasPages())
            <div class="card-footer bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
                <p class="text-muted small mb-0">Showing {{ $categories->firstItem() }}–{{ $categories->lastItem() }} of {{ $categories->total() }}</p>
                {{ $categories->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</x-admin-layout>
