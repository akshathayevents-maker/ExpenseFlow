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

    @php
        $availableByType = collect($balances)->mapWithKeys(fn ($row) => [$row['leave_type']->id => $row['available']]);
    @endphp

    <x-ds.card>
        <form method="POST" action="{{ route('employee.leave.store') }}">
            @csrf

            @if ($errors->any() && ! $errors->has('lop_confirmation'))
                {{-- Belt-and-suspenders: every field-level error already
                     renders next to its own input via @error() below, but a
                     field whose container is conditionally hidden by JS
                     (e.g. end-date-field/half-day-period-field, toggled by
                     the Duration select) must never let its error go
                     unseen — the employee must always see SOME visible
                     indication a submit failed, never a page that silently
                     looks unchanged. --}}
                <div style="margin-bottom:16px;padding:14px;border:1px solid rgba(220,38,38,.35);background:rgba(220,38,38,.06);border-radius:10px">
                    <div style="font-weight:700;color:#B91C1C;margin-bottom:6px">
                        <i class="bi bi-exclamation-circle"></i> This request could not be submitted
                    </div>
                    <ul style="margin:0;padding-left:20px;color:var(--ef-ink,#1f2937);font-size:.88rem">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="ef-form-grid ef-form-grid-2">
                <div>
                    <label class="ef-label" for="leave_type_id">Leave Type <span style="color:var(--ef-danger)">*</span></label>
                    <select id="leave_type_id" name="leave_type_id" class="ef-select @error('leave_type_id') --error @enderror" required
                            onchange="const el = document.getElementById('available-balance-note'); const bal = ({!! $availableByType->toJson() !!})[this.value]; el.style.display = this.value ? '' : 'none'; el.textContent = this.value ? ('Available balance: ' + (bal ?? 0) + ' day(s)') : '';">
                        <option value="">Select type</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                    <div id="available-balance-note" style="font-size:.78rem;color:var(--ef-faint,#6b7280);margin-top:4px;{{ old('leave_type_id') ? '' : 'display:none' }}">
                        @if(old('leave_type_id'))
                            Available balance: {{ $availableByType[(int) old('leave_type_id')] ?? 0 }} day(s)
                        @endif
                    </div>
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
                        <option value="first_half" {{ old('half_day_period') === 'first_half' ? 'selected' : '' }}>First Half{{ ($todayOccupiedHalf ?? null) === 'first_half' ? ' — attendance already marked today' : '' }}</option>
                        <option value="second_half" {{ old('half_day_period') === 'second_half' ? 'selected' : '' }}>Second Half{{ ($todayOccupiedHalf ?? null) === 'second_half' ? ' — attendance already marked today' : '' }}</option>
                    </select>
                    @error('half_day_period') <div class="ef-field-error">{{ $message }}</div> @enderror
                    @if(($todayOccupiedHalf ?? null) === 'full_day')
                        <div class="ef-field-hint" style="color:var(--ef-warning, #b45309); font-size:.8rem; margin-top:4px;">
                            Note: today already has full-day attendance marked — a half-day request for today will not be approvable.
                        </div>
                    @endif
                </div>

                <div style="grid-column: 1 / -1">
                    <label class="ef-label" for="reason">Reason <span style="color:var(--ef-faint,#6b7280);font-weight:400;text-transform:none;letter-spacing:0">(optional)</span></label>
                    <textarea id="reason" name="reason" rows="3" class="ef-textarea @error('reason') --error @enderror">{{ old('reason') }}</textarea>
                    @error('reason') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>
            </div>

            @error('lop_confirmation')
                {{-- The employee's first submit attempt stops here — LeaveService::createRequest()
                     throws this exact message (with the precise day split) instead of silently
                     applying LOP. Nothing is created until the box below is checked and the form
                     is explicitly resubmitted with lop_confirmed=1. --}}
                <div id="lop-confirm-banner" tabindex="-1" style="margin-top:16px;padding:14px;border:1px solid rgba(216,154,61,.4);background:rgba(216,154,61,.08);border-radius:10px">
                    <div style="display:flex;gap:10px;align-items:flex-start">
                        <i class="bi bi-exclamation-triangle" style="color:#7D5218;font-size:1.1rem;margin-top:1px"></i>
                        <div style="flex:1">
                            <div style="font-weight:700;color:#7D5218;margin-bottom:4px">Loss of Pay confirmation required</div>
                            <div style="font-size:.88rem;color:var(--ef-ink,#1f2937)">{{ $message }}</div>
                            <label style="display:flex;align-items:flex-start;gap:8px;margin-top:12px;font-weight:600;font-size:.88rem">
                                <input type="checkbox" name="lop_confirmed" value="1" style="margin-top:3px" required>
                                <span>I understand — apply the remaining days as Loss of Pay and submit this request.</span>
                            </label>
                        </div>
                    </div>
                </div>
                <script>
                    // Bring the confirmation banner into view — on a small
                    // screen it can render below the fold after the page
                    // reloads with validation errors.
                    document.getElementById('lop-confirm-banner')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                </script>
            @enderror

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
