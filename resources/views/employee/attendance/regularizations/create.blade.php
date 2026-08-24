<x-admin-layout title="Regularize Attendance">

<div class="ef-form-page">
    <div class="ef-form-page-header">
        <a href="{{ route('employee.attendance.index') }}" class="ef-back" title="Back to Attendance">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="ef-form-page-heading">Regularize Attendance</h1>
            <p class="ef-form-page-sub">Request a correction for a past date — subject to Manager/Admin approval</p>
        </div>
    </div>

    <x-ds.card>
        <form method="POST" action="{{ route('employee.attendance-regularizations.store') }}">
            @csrf

            <div class="ef-form-grid ef-form-grid-2">
                <div>
                    <label class="ef-label" for="attendance_date">Attendance Date <span style="color:var(--ef-danger)">*</span></label>
                    <input type="date" id="attendance_date" name="attendance_date"
                           class="ef-input @error('attendance_date') --error @enderror"
                           value="{{ old('attendance_date') }}" max="{{ now()->toDateString() }}" required autofocus>
                    @error('attendance_date') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label" for="requested_status">Requested Status <span style="color:var(--ef-danger)">*</span></label>
                    <select id="requested_status" name="requested_status" class="ef-select @error('requested_status') --error @enderror" required>
                        <option value="">Select…</option>
                        @foreach(\App\Models\EmployeeAttendanceRegularization::requestableStatuses() as $status)
                            <option value="{{ $status }}" {{ old('requested_status') === $status ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                    @error('requested_status') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div style="grid-column: 1 / -1">
                    <label class="ef-label" for="reason">Reason <span style="color:var(--ef-danger)">*</span></label>
                    <textarea id="reason" name="reason" rows="3"
                              class="ef-textarea @error('reason') --error @enderror" required>{{ old('reason') }}</textarea>
                    @error('reason') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div style="grid-column: 1 / -1;display:flex;gap:8px;align-items:flex-start;background:var(--ef-surface-2,#f8f9fa);border-radius:8px;padding:12px">
                    <i class="bi bi-info-circle" style="color:var(--ef-faint);margin-top:2px"></i>
                    <div style="color:var(--ef-faint);font-size:.84rem">
                        Regularization is not available for future dates, holidays, weekly-offs, or dates already covered by approved leave.
                    </div>
                </div>
            </div>

            <hr class="ef-form-divider">
            <div class="ef-form-actions">
                <a href="{{ route('employee.attendance.index') }}" class="ef-btn">Cancel</a>
                <button type="submit" class="ef-btn ef-btn-dark">
                    <i class="bi bi-send"></i> Submit Request
                </button>
            </div>
        </form>
    </x-ds.card>
</div>

</x-admin-layout>
