<x-admin-layout title="Employee Details">

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>{{ $employee->name }}</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Name</dt>
                        <dd class="col-sm-9">{{ $employee->name }}</dd>

                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9">{{ $employee->email }}</dd>

                        <dt class="col-sm-3">Phone</dt>
                        <dd class="col-sm-9">{{ $employee->phone ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Role</dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-{{ $employee->role === 'admin' ? 'danger' : ($employee->role === 'manager' ? 'warning' : 'info') }}">
                                {{ ucfirst($employee->role) }}
                            </span>
                        </dd>

                        <dt class="col-sm-3">Status</dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-{{ $employee->is_active ? 'success' : 'secondary' }}">
                                {{ $employee->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>

                        <dt class="col-sm-3">Joined</dt>
                        <dd class="col-sm-9">{{ $employee->created_at->format('M d, Y') }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5>Actions</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    @if($employee->is_active)
                        <form method="POST" action="{{ route('admin.employees.toggle-status', $employee) }}" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-lock"></i> Deactivate
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.employees.toggle-status', $employee) }}" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-unlock"></i> Activate
                            </button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</x-admin-layout>
