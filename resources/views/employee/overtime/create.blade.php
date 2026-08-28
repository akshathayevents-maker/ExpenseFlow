<x-admin-layout title="Request Overtime">

<div class="ot-akshathay">
<div class="ef-form-page ot-form-page">
    <div class="ef-form-page-header ot-header">
        <a href="{{ route('employee.overtime.index') }}" class="ef-back ot-back" title="Back to Overtime">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="ef-form-page-heading ot-heading">Request Overtime</h1>
            <p class="ef-form-page-sub ot-sub">Submit an overtime claim for a day you already worked</p>
        </div>
    </div>

    <x-ds.card>
        <form method="POST" action="{{ route('employee.overtime.store') }}">
            @csrf

            <div class="ef-form-grid ef-form-grid-2 ot-grid">
                <div>
                    <label class="ef-label ot-label" for="ot_date">OT Date <span class="ot-required">*</span></label>
                    <input type="date" id="ot_date" name="ot_date"
                           class="ef-input ot-input @error('ot_date') --error @enderror"
                           value="{{ old('ot_date') }}" max="{{ now()->toDateString() }}" required autofocus>
                    @error('ot_date') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label ot-label" for="hours_h">Hours <span class="ot-required">*</span></label>
                    <div class="ot-hours-row">
                        <input type="number" id="hours_h" name="hours_h" step="1" min="0" max="99"
                               class="ef-input ot-input @error('hours') --error @enderror"
                               value="{{ old('hours_h', old('hours') !== null ? floor((float) old('hours')) : '') }}"
                               placeholder="Hours" required>
                        <select id="hours_m" name="hours_m" class="ef-select ot-input @error('hours') --error @enderror">
                            @foreach ([0, 15, 30, 45] as $m)
                                <option value="{{ $m }}" {{ (int) old('hours_m', 0) === $m ? 'selected' : '' }}>{{ $m }} min</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ef-field-hint ot-hint">
                        Enter the overtime duration as hours and minutes, e.g. 1 hour 30 min.
                    </div>
                    @error('hours') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div style="grid-column: 1 / -1">
                    <label class="ef-label ot-label" for="reason">Reason <span class="ot-required">*</span></label>
                    <textarea id="reason" name="reason" rows="3" required
                              class="ef-textarea ot-input ot-textarea @error('reason') --error @enderror">{{ old('reason') }}</textarea>
                    @error('reason') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="ot-callout" style="grid-column: 1 / -1">
                    <i class="bi bi-info-circle ot-callout-icon"></i>
                    <div class="ot-callout-text">
                        The hourly rate, multiplier, and amount are not shown here — your admin/manager will choose the multiplier and calculate the compensation when they review this request.
                    </div>
                </div>
            </div>

            <hr class="ef-form-divider ot-divider">
            <div class="ef-form-actions ot-actions">
                <a href="{{ route('employee.overtime.index') }}" class="ef-btn ot-btn-cancel">Cancel</a>
                <button type="submit" class="ef-btn ef-btn-dark ot-btn-submit">
                    <i class="bi bi-send"></i> Submit Request
                </button>
            </div>
        </form>
    </x-ds.card>
</div>
</div>

@push('styles')
<style>
    /* Page-scoped Akshathay teal palette — mirrors the Leave/Login page-local
       wrapper convention. Presentation only; no field names/behaviour touched. */
    .ot-akshathay {
        --ot-primary: #0F766E;
        --ot-primary-hover: #0D5F59;
        --ot-accent: #14B8A6;
        --ot-dark: #0F172A;
        --ot-page-bg: #F6F7F5;
        --ot-card-bg: #FFFFFF;
        --ot-text: #111827;
        --ot-text-secondary: #64748B;
        --ot-border: #E5E7EB;
        --ot-divider: #EEF0ED;
        --ot-success: #15803D;
        --ot-warning: #B45309;
        --ot-danger: #B91C1C;
    }

    .ot-form-page { max-width: 640px; }

    .ot-header { gap: 12px; margin-bottom: 20px; }
    .ot-back {
        display: inline-flex; align-items: center; justify-content: center;
        width: 38px; height: 38px; border-radius: 10px;
        background: rgba(15,118,110,.08);
        color: var(--ot-primary);
        margin-bottom: 0;
        flex-shrink: 0;
    }
    .ot-back:hover { background: rgba(15,118,110,.15); color: var(--ot-primary-hover); }
    .ot-heading { color: var(--ot-text); font-size: 1.3rem; font-weight: 700; letter-spacing: -.01em; }
    .ot-sub { color: var(--ot-text-secondary); font-size: .8rem; margin-top: 2px; }

    /* Card look — matches Leave page card treatment */
    .ot-akshathay .ef-ds-card {
        background: var(--ot-card-bg);
        border: 1px solid var(--ot-border);
        border-radius: 14px;
        box-shadow: 0 1px 2px rgba(15,23,42,.04);
    }
    .ot-akshathay .ef-ds-card-body { padding: 20px; }
    @media (max-width: 575.98px) {
        .ot-akshathay .ef-ds-card-body { padding: 16px; }
    }

    .ot-grid { gap: 16px; }

    .ot-label { color: #374151; font-size: .72rem; font-weight: 700; letter-spacing: .02em; text-transform: none; margin-bottom: 6px; }
    .ot-required { color: var(--ot-danger); }

    .ot-akshathay .ot-input {
        background: #FFFFFF;
        border: 1px solid #CBD5E1;
        border-radius: 10px;
        color: var(--ot-text);
        min-height: 45px;
        font-size: .92rem;
    }
    .ot-akshathay .ot-input::placeholder { color: var(--ot-text-secondary); }
    .ot-akshathay .ot-input:focus {
        background: #FFFFFF;
        border-color: var(--ot-accent);
        box-shadow: 0 0 0 3px rgba(20,184,166,.18);
        outline: 0;
    }
    .ot-akshathay .ot-input.--error { border-color: var(--ot-danger); box-shadow: 0 0 0 3px rgba(185,28,28,.10); }

    .ot-hours-row { display: flex; gap: 8px; align-items: center; }
    .ot-hours-row .ot-input { flex: 1; min-width: 0; }

    .ot-hint { color: var(--ot-text-secondary); font-size: .68rem; line-height: 1.5; margin-top: 4px; }

    .ot-akshathay .ot-textarea { min-height: 96px; resize: vertical; }

    .ot-callout {
        display: flex; gap: 8px; align-items: flex-start;
        background: rgba(20,184,166,.06);
        border: 1px solid rgba(15,118,110,.16);
        border-radius: 12px;
        padding: 12px 14px;
    }
    .ot-callout-icon { color: var(--ot-primary); margin-top: 2px; font-size: .85rem; flex-shrink: 0; }
    .ot-callout-text { color: var(--ot-text-secondary); font-size: .78rem; line-height: 1.5; }

    .ot-divider { border-top: 1px solid var(--ot-divider); margin: 20px 0 16px; }

    .ot-actions { gap: 12px; }

    .ot-akshathay .ot-btn-submit {
        background: var(--ot-primary);
        border-color: var(--ot-primary);
        color: #fff;
        min-height: 46px;
        border-radius: 10px;
        font-weight: 700;
        font-size: .92rem;
    }
    .ot-akshathay .ot-btn-submit:hover { background: var(--ot-primary-hover); border-color: var(--ot-primary-hover); color: #fff; }

    .ot-akshathay .ot-btn-cancel {
        background: #FFFFFF;
        border: 1px solid #CBD5E1;
        color: #374151;
        min-height: 46px;
        border-radius: 10px;
        font-weight: 600;
        font-size: .92rem;
    }
    .ot-akshathay .ot-btn-cancel:hover { background: #F8FAFC; border-color: #CBD5E1; color: #374151; box-shadow: none; transform: none; }

    @media (max-width: 575.98px) {
        .ot-actions { flex-direction: column-reverse; gap: 12px; }
        .ot-actions .ot-btn-submit,
        .ot-actions .ot-btn-cancel { width: 100%; }
    }
</style>
@endpush

</x-admin-layout>
