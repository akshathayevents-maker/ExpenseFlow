<x-admin-layout title="Add Employee">
    <div class="page-header d-flex align-items-center gap-2">
        <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0 fw-bold">Add Employee</h4>
            <p class="text-muted mb-0 small">Create a new user account</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.employees.store') }}" novalidate>
                        @csrf

                        <div class="row g-3">
                            {{-- Name --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="name">Full Name <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" placeholder="John Doe" autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold" for="email">Email <span class="text-danger">*</span></label>
                                <input type="email" id="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" placeholder="john@example.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Phone --}}
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold" for="phone">Phone</label>
                                <input type="text" id="phone" name="phone"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone') }}" placeholder="9876543210">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Password --}}
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold" for="password">Password <span class="text-danger">*</span></label>
                                <input type="password" id="password" name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Min 8 characters">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Role --}}
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold" for="role">Role <span class="text-danger">*</span></label>
                                <select id="role" name="role"
                                        class="form-select @error('role') is-invalid @enderror">
                                    <option value="">Select role…</option>
                                    <option value="employee" {{ old('role') === 'employee' ? 'selected' : '' }}>Employee</option>
                                    <option value="manager"  {{ old('role') === 'manager'  ? 'selected' : '' }}>Manager</option>
                                    <option value="admin"    {{ old('role') === 'admin'    ? 'selected' : '' }}>Admin</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active"
                                           name="is_active" value="1"
                                           {{ old('is_active', '1') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="is_active">
                                        Active account
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-person-plus me-1"></i> Create Employee
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
