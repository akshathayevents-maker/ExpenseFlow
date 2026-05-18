<x-admin-layout title="Edit Employee">

<div class="ef-form-page">
    <div class="ef-form-page-header">
        <a href="{{ route('admin.employees.index') }}" class="ef-back" title="Back to Employees">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="ef-form-page-heading">Edit Employee</h1>
            <p class="ef-form-page-sub">{{ $employee->name }}</p>
        </div>
    </div>

    <x-ds.card>
        <form method="POST" action="{{ route('admin.employees.update', $employee) }}" novalidate>
            @csrf @method('PUT')

            <div class="ef-form-grid ef-form-grid-2">
                <div style="grid-column:1/span 2">
                    <label class="ef-label" for="name">Full Name <span style="color:var(--ef-danger)">*</span></label>
                    <input type="text" id="name" name="name"
                           class="ef-input @error('name') --error @enderror"
                           value="{{ old('name', $employee->name) }}" autofocus>
                    @error('name') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label" for="email">Email <span style="color:var(--ef-danger)">*</span></label>
                    <input type="email" id="email" name="email"
                           class="ef-input @error('email') --error @enderror"
                           value="{{ old('email', $employee->email) }}">
                    @error('email') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label" for="phone">Phone</label>
                    <input type="text" id="phone" name="phone"
                           class="ef-input @error('phone') --error @enderror"
                           value="{{ old('phone', $employee->phone) }}">
                    @error('phone') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label" for="password">
                        New Password
                        <span style="color:var(--ef-faint);font-weight:400;text-transform:none;letter-spacing:0">(leave blank to keep)</span>
                    </label>
                    <input type="password" id="password" name="password"
                           class="ef-input @error('password') --error @enderror"
                           placeholder="Min 8 characters">
                    @error('password') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label" for="role">Role <span style="color:var(--ef-danger)">*</span></label>
                    <select id="role" name="role"
                            class="ef-select @error('role') --error @enderror">
                        <option value="employee" {{ old('role', $employee->role) === 'employee' ? 'selected' : '' }}>Employee</option>
                        <option value="manager"  {{ old('role', $employee->role) === 'manager'  ? 'selected' : '' }}>Manager</option>
                        <option value="admin"    {{ old('role', $employee->role) === 'admin'    ? 'selected' : '' }}>Admin</option>
                    </select>
                    @error('role') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div style="grid-column:1/span 2">
                    <label class="ef-switch">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                               {{ old('is_active', $employee->is_active) ? 'checked' : '' }}>
                        <span>Active account</span>
                    </label>
                </div>
            </div>

            <hr class="ef-form-divider">
            <div class="ef-form-actions">
                <a href="{{ route('admin.employees.index') }}" class="ef-btn">Cancel</a>
                <button type="submit" class="ef-btn ef-btn-dark">
                    <i class="bi bi-floppy"></i> Save Changes
                </button>
            </div>
        </form>
    </x-ds.card>
</div>

</x-admin-layout>
