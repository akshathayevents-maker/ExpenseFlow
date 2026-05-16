<x-admin-layout title="Vendors">
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Vendors</h4>
            <p class="text-muted mb-0 small">Manage suppliers and vendors</p>
        </div>
        <a href="{{ route('admin.vendors.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Vendor
        </a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="d-flex gap-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search vendors…" value="{{ $search ?? '' }}">
                </div>
                <button class="btn btn-sm btn-outline-primary px-3">Search</button>
                @if($search)
                    <a href="{{ route('admin.vendors.index') }}" class="btn btn-sm btn-outline-secondary px-3">Clear</a>
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
                            <th class="d-none d-md-table-cell">Phone</th>
                            <th class="d-none d-lg-table-cell">Address</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $vendor)
                            <tr>
                                <td class="text-muted small">{{ $vendor->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $vendor->name }}</div>
                                    @if($vendor->notes)
                                        <small class="text-muted">{{ Str::limit($vendor->notes, 40) }}</small>
                                    @endif
                                </td>
                                <td class="d-none d-md-table-cell text-muted small">{{ $vendor->phone ?? '—' }}</td>
                                <td class="d-none d-lg-table-cell text-muted small">{{ Str::limit($vendor->address, 40) ?? '—' }}</td>
                                <td>
                                    @if($vendor->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle role-badge">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle role-badge">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="{{ route('admin.vendors.edit', $vendor) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.vendors.toggle-status', $vendor) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="btn btn-sm {{ $vendor->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                                <i class="bi bi-{{ $vendor->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.vendors.destroy', $vendor) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Delete {{ addslashes($vendor->name) }}?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="bi bi-shop fs-2 d-block mb-2"></i>
                                    No vendors found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($vendors->hasPages())
            <div class="card-footer bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
                <p class="text-muted small mb-0">Showing {{ $vendors->firstItem() }}–{{ $vendors->lastItem() }} of {{ $vendors->total() }}</p>
                {{ $vendors->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</x-admin-layout>
