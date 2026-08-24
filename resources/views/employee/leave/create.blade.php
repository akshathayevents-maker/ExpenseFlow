<x-admin-layout title="Apply Leave">

<div class="ef-form-page">
    <div class="ef-form-page-header">
        <a href="{{ route('employee.leave.index') }}" class="ef-back" title="Back to Leave">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="ef-form-page-heading">Apply Leave</h1>
            <p class="ef-form-page-sub">Submit a leave request for approval</p>
        </div>
    </div>

    <x-ds.card>
        <form method="POST" action="{{ route('employee.leave.store') }}">
            @csrf

            <div class="ef-form-grid ef-form-grid-2">
                <div>
                    <label class="ef-label" for="leave_type_id">Leave Type <span style="color:var(--ef-danger)">*</span></label>
                    <select id="leave_type_id" name="leave_type_id" class="ef-select @error('leave_type_id') --error @enderror" required>
                        <option value="">Select type</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @error('leave_type_id') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label" for="is_half_day">Duration</label>
                    <select id="is_half_day" name="is_half_day" class="ef-select" onchange="document.getElementById('half-day-period-field').style.display = this.value === '1' ? '' : 'none'; document.getElementById('end-date-field').style.display = this.value === '1' ? 'none' : '';">
                        <option value="0">Full day(s)</option>
                        <option value="1" {{ old('is_half_day') ? 'selected' : '' }}>Half day</option>
                    </select>
                </div>

                <div>
                    <label class="ef-label" for="start_date">{{ old('is_half_day') ? 'Date' : 'From Date' }} <span style="color:var(--ef-danger)">*</span></label>
                    <input type="date" id="start_date" name="start_date" class="ef-input @error('start_date') --error @enderror" value="{{ old('start_date') }}" required>
                    @error('start_date') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div id="end-date-field" style="{{ old('is_half_day') ? 'display:none' : '' }}">
                    <label class="ef-label" for="end_date">To Date <span style="color:var(--ef-danger)">*</span></label>
                    <input type="date" id="end_date" name="end_date" class="ef-input @error('end_date') --error @enderror" value="{{ old('end_date') }}">
                    @error('end_date') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div id="half-day-period-field" style="{{ old('is_half_day') ? '' : 'display:none' }}">
                    <label class="ef-label" for="half_day_period">Half Day Period</label>
                    <select id="half_day_period" name="half_day_period" class="ef-select @error('half_day_period') --error @enderror">
                        <option value="first_half" {{ old('half_day_period') === 'first_half' ? 'selected' : '' }}>First Half</option>
                        <option value="second_half" {{ old('half_day_period') === 'second_half' ? 'selected' : '' }}>Second Half</option>
                    </select>
                    @error('half_day_period') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div style="grid-column: 1 / -1">
                    <label class="ef-label" for="reason">Reason <span style="color:var(--ef-danger)">*</span></label>
                    <textarea id="reason" name="reason" rows="3" class="ef-textarea @error('reason') --error @enderror" required>{{ old('reason') }}</textarea>
                    @error('reason') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <hr class="ef-form-divider">
            <div class="ef-form-actions">
                <a href="{{ route('employee.leave.index') }}" class="ef-btn">Cancel</a>
                <button type="submit" class="ef-btn ef-btn-dark">
                    <i class="bi bi-send"></i> Submit Request
                </button>
            </div>
        </form>
    </x-ds.card>
</div>

</x-admin-layout>
