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

                        <dt class="col-sm-3">Employment Start</dt>
                        <dd class="col-sm-9">{{ optional($employee->employment_start_date)->format('M d, Y') ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Employment End</dt>
                        <dd class="col-sm-9">{{ optional($employee->employment_end_date)->format('M d, Y') ?? 'N/A' }}</dd>
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

        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius:14px;overflow:hidden">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center" style="border:none">
                    <h5 class="mb-0"><i class="bi bi-cash-coin me-1"></i> Compensation</h5>
                </div>
                <div class="card-body">
                    @if($currentSalary)
                        <div class="text-uppercase text-muted small fw-bold" style="letter-spacing:.04em">Current Monthly Salary</div>
                        <div class="fw-bold" style="font-size:1.7rem">₹{{ number_format((float) $currentSalary->monthly_salary, 2) }}</div>

                        <div class="text-uppercase text-muted small fw-bold mt-3" style="letter-spacing:.04em">Effective From</div>
                        <div class="fw-semibold">{{ $currentSalary->effective_from->format('d M Y') }}</div>

                        <a href="{{ route('admin.employees.salaries.index', $employee) }}" class="btn btn-primary w-100 mt-3">
                            <i class="bi bi-pencil"></i> Change Salary
                        </a>
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-cash-stack text-muted" style="font-size:1.8rem"></i>
                            <p class="text-muted mt-2 mb-3">No salary configured</p>
                            <a href="{{ route('admin.employees.salaries.index', $employee) }}" class="btn btn-primary w-100">
                                <i class="bi bi-plus-lg"></i> Set Employee Salary
                            </a>
                        </div>
                    @endif
                </div>

                @if($salaryHistory->isNotEmpty())
                <div class="card-footer bg-white">
                    <div class="text-uppercase text-muted small fw-bold mb-2" style="letter-spacing:.04em">Salary History</div>
                    <table class="table table-sm mb-0">
                        <tbody>
                            @foreach($salaryHistory as $salary)
                            <tr>
                                <td class="text-muted" style="white-space:nowrap">{{ $salary->effective_from->format('d M Y') }}</td>
                                <td class="text-end fw-semibold">₹{{ number_format((float) $salary->monthly_salary, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($employee->salaries()->count() > $salaryHistory->count())
                    <a href="{{ route('admin.employees.salaries.index', $employee) }}" class="small">View full history &rarr;</a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

</x-admin-layout>
