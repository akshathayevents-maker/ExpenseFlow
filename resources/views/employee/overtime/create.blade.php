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
                    <label class="ef-label" for="hours">Hours <span style="color:var(--ef-danger)">*</span></label>
                    <input type="number" id="hours" name="hours" step="0.25" min="0.01" max="99.99"
                           class="ef-input @error('hours') --error @enderror"
                           value="{{ old('hours') }}" required>
                    @error('hours') <div class="ef-field-error">{{ $message }}</div> @enderror
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
                        The hourly rate, multiplier, and amount are calculated automatically based on your salary and the OT category (weekday/weekend/holiday) — you don't set these.
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
