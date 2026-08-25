{{--
    Shared field set for admin/leave-types/create.blade.php and edit.blade.php.
    Expects: $leaveType (LeaveType|null).
--}}
<div class="ef-form-grid ef-form-grid-2">
    <div>
        <label class="ef-label" for="name">Name <span style="color:var(--ef-danger)">*</span></label>
        <input type="text" id="name" name="name" class="ef-input @error('name') --error @enderror"
               value="{{ old('name', $leaveType->name ?? '') }}" required>
        @error('name') <div class="ef-field-error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="ef-label" for="code">Code <span style="color:var(--ef-danger)">*</span></label>
        <input type="text" id="code" name="code" class="ef-input @error('code') --error @enderror"
               value="{{ old('code', $leaveType->code ?? '') }}" required>
        @error('code') <div class="ef-field-error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="ef-label" for="max_carry_forward">Max Carry Forward (days)</label>
        <input type="number" step="0.1" min="0" id="max_carry_forward" name="max_carry_forward"
               class="ef-input @error('max_carry_forward') --error @enderror"
               value="{{ old('max_carry_forward', $leaveType->max_carry_forward ?? '') }}">
        @error('max_carry_forward') <div class="ef-field-error">{{ $message }}</div> @enderror
    </div>

    <div style="display:flex;flex-direction:column;gap:10px;justify-content:center">
        <label style="display:flex;align-items:center;gap:8px;font-weight:600">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $leaveType->is_active ?? true) ? 'checked' : '' }}>
            Active
        </label>
        <label style="display:flex;align-items:center;gap:8px;font-weight:600">
            <input type="checkbox" name="is_paid" value="1" {{ old('is_paid', $leaveType->is_paid ?? true) ? 'checked' : '' }}>
            Paid leave
        </label>
        <label style="display:flex;align-items:center;gap:8px;font-weight:600">
            <input type="checkbox" name="allow_half_day" value="1" {{ old('allow_half_day', $leaveType->allow_half_day ?? false) ? 'checked' : '' }}>
            Allow half-day requests
        </label>
        <label style="display:flex;align-items:center;gap:8px;font-weight:600">
            <input type="checkbox" name="allow_carry_forward" value="1" {{ old('allow_carry_forward', $leaveType->allow_carry_forward ?? false) ? 'checked' : '' }}>
            Allow carry forward
        </label>
    </div>
</div>
