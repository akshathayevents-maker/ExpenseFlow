<x-admin-layout title="Employees">
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Employees</h4>
            <p class="text-muted mb-0 small">Manage all employees and managers</p>
        </div>
        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-person-plus me-1"></i> Add Employee
        </a>
    </div>

    {{-- Search --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('admin.employees.index') }}" class="d-flex gap-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email or phone…"
                           value="{{ $search ?? '' }}">
                </div>
                <button class="btn btn-sm btn-outline-primary px-3" type="submit">Search</button>
                @if($search)
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-outline-secondary px-3">Clear</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th class="d-none d-md-table-cell">Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                            <tr>
                                <td class="text-muted small">{{ $employee->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $employee->name }}</div>
                                </td>
                                <td class="text-muted small">{{ $employee->email }}</td>
                                <td class="d-none d-md-table-cell text-muted small">
                                    {{ $employee->phone ?? '—' }}
                                </td>
                                <td>
                                    @php
                                        $roleColors = [
                                            'admin'    => 'danger',
                                            'manager'  => 'warning',
                                            'employee' => 'primary',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $roleColors[$employee->role] ?? 'secondary' }} role-badge">
                                        {{ ucfirst($employee->role) }}
                                    </span>
                                </td>
                                <td>
                                    @if($employee->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle role-badge">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle role-badge">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="{{ route('admin.employees.edit', $employee) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        {{-- Toggle status --}}
                                        <form method="POST"
                                              action="{{ route('admin.employees.toggle-status', $employee) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="btn btn-sm {{ $employee->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                    title="{{ $employee->is_active ? 'Deactivate' : 'Activate' }}"
                                                    onclick="return confirm('{{ $employee->is_active ? 'Deactivate' : 'Activate' }} this employee?')">
                                                <i class="bi bi-{{ $employee->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                            </button>
                                        </form>

                                        {{-- Delete --}}
                                        <form method="POST"
                                              action="{{ route('admin.employees.destroy', $employee) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Delete"
                                                    onclick="return confirm('Permanently delete {{ addslashes($employee->name) }}? This cannot be undone.')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-people fs-2 d-block mb-2"></i>
                                    No employees found.
                                    @if($search)
                                        <br><a href="{{ route('admin.employees.index') }}">Clear search</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($employees->hasPages())
            <div class="card-footer bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
                <p class="text-muted small mb-0">
                    Showing {{ $employees->firstItem() }}–{{ $employees->lastItem() }} of {{ $employees->total() }}
                </p>
                {{ $employees->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</x-admin-layout>
