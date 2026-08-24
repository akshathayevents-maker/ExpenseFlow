<x-admin-layout title="Salary — {{ $employee->name }}">

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('admin.employees.show', $employee) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to {{ $employee->name }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Current Salary</h5>
                </div>
                <div class="card-body">
                    @if($currentSalary)
                        <dl class="row mb-0">
                            <dt class="col-sm-6">Monthly Salary</dt>
                            <dd class="col-sm-6">₹{{ number_format((float) $currentSalary->monthly_salary, 2) }}</dd>
                            <dt class="col-sm-6">Effective From</dt>
                            <dd class="col-sm-6">{{ $currentSalary->effective_from->format('M d, Y') }}</dd>
                        </dl>
                    @else
                        <p class="text-muted mb-0">No salary set yet.</p>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Set / Change Salary</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.employees.salaries.store', $employee) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="monthly_salary">Monthly Salary <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" id="monthly_salary" name="monthly_salary"
                                   class="form-control @error('monthly_salary') is-invalid @enderror"
                                   value="{{ old('monthly_salary') }}" required>
                            @error('monthly_salary') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="effective_from">Effective From <span class="text-danger">*</span></label>
                            <input type="date" id="effective_from" name="effective_from"
                                   class="form-control @error('effective_from') is-invalid @enderror"
                                   value="{{ old('effective_from', now()->toDateString()) }}" required>
                            @error('effective_from') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Save Salary
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Salary History</h5>
                </div>
                <div class="card-body p-0">
                    <div style="overflow-x:auto">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Monthly Salary</th>
                                    <th>Effective From</th>
                                    <th>Effective To</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($history as $salary)
                                <tr>
                                    <td>₹{{ number_format((float) $salary->monthly_salary, 2) }}</td>
                                    <td>{{ $salary->effective_from->format('M d, Y') }}</td>
                                    <td>{{ optional($salary->effective_to)->format('M d, Y') ?? '—' }}</td>
                                    <td>{{ $salary->creator->name ?? '—' }}</td>
                                    <td>{{ $salary->created_at->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No salary history yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</x-admin-layout>
