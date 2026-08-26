<x-admin-layout title="Request Overtime">

<div class="ef-form-page">
    <div class="ef-form-page-header">
        <a href="{{ route('employee.overtime.index') }}" class="ef-back" title="Back to Overtime">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="ef-form-page-heading">Request Overtime</h1>
            <p class="ef-form-page-sub">Submit an overtime claim for a day you already worked</p>
        </div>
    </div>

    <x-ds.card>
        <form method="POST" action="{{ route('employee.overtime.store') }}">
            @csrf

            <div class="ef-form-grid ef-form-grid-2">
                <div>
                    <label class="ef-label" for="ot_date">OT Date <span style="color:var(--ef-danger)">*</span></label>
                    <input type="date" id="ot_date" name="ot_date"
                           class="ef-input @error('ot_date') --error @enderror"
                           value="{{ old('ot_date') }}" max="{{ now()->toDateString() }}" required autofocus>
                    @error('ot_date') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label" for="hours_h">Hours <span style="color:var(--ef-danger)">*</span></label>
                    <div style="display:flex;gap:8px;align-items:center">
                        <input type="number" id="hours_h" name="hours_h" step="1" min="0" max="99"
                               class="ef-input @error('hours') --error @enderror"
                               value="{{ old('hours_h', old('hours') !== null ? floor((float) old('hours')) : '') }}"
                               placeholder="Hours" required>
                        <select id="hours_m" name="hours_m" class="ef-select @error('hours') --error @enderror">
                            @foreach ([0, 15, 30, 45] as $m)
                                <option value="{{ $m }}" {{ (int) old('hours_m', 0) === $m ? 'selected' : '' }}>{{ $m }} min</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ef-field-hint" style="color:var(--ef-faint,#6b7280); font-size:.8rem; margin-top:4px;">
                        Enter the overtime duration as hours and minutes, e.g. 1 hour 30 min.
                    </div>
                    @error('hours') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div style="grid-column: 1 / -1">
                    <label class="ef-label" for="reason">Reason <span style="color:var(--ef-danger)">*</span></label>
                    <textarea id="reason" name="reason" rows="3" required
                              class="ef-textarea @error('reason') --error @enderror">{{ old('reason') }}</textarea>
                    @error('reason') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div style="grid-column: 1 / -1;display:flex;gap:8px;align-items:flex-start;background:var(--ef-surface-2,#f8f9fa);border-radius:8px;padding:12px">
                    <i class="bi bi-info-circle" style="color:var(--ef-faint);margin-top:2px"></i>
                    <div style="color:var(--ef-faint);font-size:.84rem">
                        The hourly rate, multiplier, and amount are not shown here — your admin/manager will choose the multiplier and calculate the compensation when they review this request.
                    </div>
                </div>
            </div>

            <hr class="ef-form-divider">
            <div class="ef-form-actions">
                <a href="{{ route('employee.overtime.index') }}" class="ef-btn">Cancel</a>
                <button type="submit" class="ef-btn ef-btn-dark">
                    <i class="bi bi-send"></i> Submit Request
                </button>
            </div>
        </form>
    </x-ds.card>
</div>

</x-admin-layout>
