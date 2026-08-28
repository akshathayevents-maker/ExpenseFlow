<x-admin-layout title="Record Overtime">

<div class="ef-form-page --wide">
    <div class="ef-form-page-header">
        <a href="{{ route('admin.overtime.index') }}" class="ef-back" title="Back to Overtime">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="ef-form-page-heading">Record Overtime</h1>
            <p class="ef-form-page-sub">Record and approve overtime for an employee in one step.</p>
        </div>
    </div>

    @if($allowanceMode === 'single')
        <div style="display:flex;gap:8px;align-items:flex-start;background:var(--ef-surface-2,#f8f9fa);border-radius:8px;padding:12px;margin-bottom:16px">
            <i class="bi bi-shield-lock" style="color:var(--ef-faint);margin-top:2px"></i>
            <div style="color:var(--ef-faint);font-size:.84rem">
                Single-allowance mode is enabled: only one admin-recorded overtime allowance is permitted per employee per calendar month.
            </div>
        </div>
    @endif

    <x-ds.card>
        {{-- Single form: choosing the employee or OT date reloads this page
             (GET) so the server can recompute the hourly rate, allowed
             multipliers, pay period, and the existing-entries list below for
             that employee/date — there is only ONE employee selector on the
             whole page. Hours/reason are re-populated via old() on that
             reload. Final submit is POST to store(), which creates AND
             approves the record in a single transaction. --}}
        <form method="POST" action="{{ route('admin.overtime.store') }}" id="otRecordForm">
            @csrf

            <p class="ef-form-section-label">Record Overtime</p>
            <div class="ef-form-grid ef-form-grid-2">
                <div style="grid-column: 1 / -1">
                    <label class="ef-label" for="user_id">Employee <span style="color:var(--ef-danger)">*</span></label>
                    <select id="user_id" name="user_id" class="ef-select @error('user_id') --error @enderror" required
                            onchange="otReloadWith({user_id: this.value})">
                        <option value="">Select employee…</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ (string) old('user_id', $selectedUserId) === (string) $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label" for="ot_date">OT Date <span style="color:var(--ef-danger)">*</span></label>
                    <input type="date" id="ot_date" name="ot_date"
                           class="ef-input @error('ot_date') --error @enderror"
                           value="{{ old('ot_date', $otDate?->toDateString()) }}" max="{{ now()->toDateString() }}" required
                           onchange="otReloadWith({ot_date: this.value})">
                    @error('ot_date') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="ef-label">Pay Period</label>
                    <div class="ef-input" style="background:var(--ef-surface-2,#f8f9fa);color:var(--ef-faint);display:flex;align-items:center" id="ot_pay_period">
                        {{ $month->format('F Y') }} <span style="margin-left:6px;font-size:.72rem">(automatically determined from OT date)</span>
                    </div>
                </div>

                <div style="grid-column: 1 / -1">
                    {{-- No `step` restriction — any decimal (1.5, 2.25,
                         11.76, 12.01…) is accepted, avoiding the browser's
                         "please enter a valid value, nearest allowed values
                         are X/Y" error that a stepped input produces. Server
                         side, AdminRecordOvertimeRequest validates only
                         numeric|gt:0|max:99.99, with no precision cap beyond
                         the column's own decimal(4,2). --}}
                    <label class="ef-label" for="hours">Hours <span style="color:var(--ef-danger)">*</span></label>
                    <input type="number" id="hours" name="hours" step="any" min="0.01" max="99.99"
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
            </div>

            <hr class="ef-form-divider">
            <p class="ef-form-section-label">Payment &amp; Approval</p>

            @if(!$selectedUserId || !$otDate)
                <p style="color:var(--ef-faint);font-size:.84rem;margin:0">
                    Select an employee and OT date to calculate overtime payment.
                </p>
            @elseif($hourlyRate === null)
                <div class="ef-field-error">
                    This employee has no active salary for the selected OT date — recording is unavailable until a salary is set.
                </div>
            @else
                <div class="ef-form-grid ef-form-grid-2">
                    <div>
                        <div class="ef-label" style="margin-bottom:2px">Hourly Rate</div>
                        <div style="font-weight:700" id="ot_salary_per_hour" data-value="{{ $hourlyRate }}">₹{{ number_format($hourlyRate, 2) }}</div>
                    </div>

                    <div>
                        <label class="ef-label" style="margin-bottom:4px">Multiplier <span style="color:var(--ef-danger)">*</span></label>
                        <div id="ot_multiplier_group" style="display:flex;gap:8px;flex-wrap:wrap">
                            @foreach($allowedMultipliers as $m)
                                <label class="ef-btn" style="cursor:pointer;{{ (float) $m === (float) $defaultMultiplier ? 'background:var(--ef-dark,#111827);color:#fff' : '' }}">
                                    <input type="radio" name="multiplier" value="{{ $m }}" data-multiplier-option
                                           {{ (string) old('multiplier', $defaultMultiplier) === (string) $m ? 'checked' : '' }}
                                           style="margin-right:6px">
                                    {{ number_format((float) $m, 2) }}x
                                </label>
                            @endforeach
                        </div>
                        @error('multiplier') <div class="ef-field-error">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <div class="ef-label" style="margin-bottom:2px">Calculated Amount</div>
                        <div style="font-weight:700" id="ot_calculated_amount">—</div>
                    </div>

                    <div>
                        <label class="ef-label" for="manual_amount">Manual Override Amount (optional)</label>
                        <input type="number" step="0.01" min="0.01" id="manual_amount" name="manual_amount"
                               class="ef-input @error('manual_amount') --error @enderror" value="{{ old('manual_amount') }}">
                        <div class="ef-field-hint" style="color:var(--ef-faint,#6b7280);font-size:.8rem;margin-top:4px">
                            Leave blank to use the calculated amount.
                        </div>
                        @error('manual_amount') <div class="ef-field-error">{{ $message }}</div> @enderror
                    </div>

                    <div style="grid-column: 1 / -1;padding-top:6px;border-top:1px solid var(--ef-border,#e5e7eb)">
                        <div class="ef-label" style="margin-bottom:2px">Final Approved Amount</div>
                        <div style="font-weight:800;color:var(--ef-emerald,#0F7B5F)" id="ot_final_amount">—</div>
                    </div>

                    <div style="grid-column: 1 / -1">
                        <label class="ef-label" for="review_note">Admin Note (optional)</label>
                        <textarea id="review_note" name="review_note" rows="2" class="ef-textarea">{{ old('review_note') }}</textarea>
                    </div>
                </div>

                <script>
                (function () {
                    var hoursInput = document.getElementById('hours');
                    var salaryPerHour = {{ (float) $hourlyRate }};
                    var calculatedEl = document.getElementById('ot_calculated_amount');
                    var finalEl = document.getElementById('ot_final_amount');
                    var manualInput = document.getElementById('manual_amount');
                    var multiplierInputs = document.querySelectorAll('[data-multiplier-option]');

                    function selectedMultiplier() {
                        var checked = document.querySelector('[data-multiplier-option]:checked');
                        return checked ? parseFloat(checked.value) : 0;
                    }

                    function formatMoney(n) {
                        if (isNaN(n)) { return '—'; }
                        return '₹' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d)\.)/g, ',');
                    }

                    function recalc() {
                        var hours = parseFloat(hoursInput.value) || 0;
                        var multiplier = selectedMultiplier();
                        var calculated = salaryPerHour * hours * multiplier;
                        calculatedEl.textContent = hours > 0 && multiplier > 0 ? formatMoney(calculated) : '—';

                        var manual = parseFloat(manualInput.value);
                        var final = (!isNaN(manual) && manual > 0) ? manual : calculated;
                        finalEl.textContent = (hours > 0 && multiplier > 0) ? formatMoney(final) : '—';

                        multiplierInputs.forEach(function (input) {
                            var label = input.closest('label');
                            if (!label) return;
                            if (input.checked) {
                                label.style.background = 'var(--ef-dark,#111827)';
                                label.style.color = '#fff';
                            } else {
                                label.style.background = '';
                                label.style.color = '';
                            }
                        });
                    }

                    multiplierInputs.forEach(function (input) {
                        input.addEventListener('change', recalc);
                    });
                    manualInput.addEventListener('input', recalc);
                    hoursInput.addEventListener('input', recalc);

                    recalc();
                })();
                </script>
            @endif

            <hr class="ef-form-divider">
            <div class="ef-form-actions">
                <a href="{{ route('admin.overtime.index') }}" class="ef-btn">Cancel</a>
                <button type="submit" id="otSubmitBtn" class="ef-btn ef-btn-dark" {{ ($selectedUserId && $otDate && $hourlyRate === null) ? 'disabled' : '' }}>
                    <i class="bi bi-check-lg"></i> <span id="otSubmitBtnLabel">Record &amp; Approve Overtime</span>
                </button>
            </div>
        </form>
    </x-ds.card>

    <script>
        // Prevent double-submit: disable the button and swap its label the
        // moment the form is submitted (client-side only — server validation
        // still runs as normal on the POST).
        (function () {
            var form = document.getElementById('otRecordForm');
            var btn = document.getElementById('otSubmitBtn');
            var label = document.getElementById('otSubmitBtnLabel');
            if (!form || !btn || !label) { return; }
            form.addEventListener('submit', function () {
                btn.disabled = true;
                label.textContent = 'Recording...';
            });
        })();
    </script>

    {{-- Existing entries for the SAME employee/pay-period already chosen
         above — no separate employee selector here; it reads whatever the
         main form's employee+date currently resolve to. --}}
    @if($selectedUserId)
    <x-ds.card style="margin-top:16px">
        <div style="margin-bottom:14px">
            <div style="font-weight:760;font-size:1rem">Existing Overtime</div>
            <div style="color:var(--ef-faint);font-size:.82rem;margin-top:2px">
                {{ $month->format('F Y') }} · {{ $employee?->name }}
            </div>
            <div style="margin-top:8px;font-size:.86rem">
                {{ $existingAllowances->count() }} {{ Str::plural('entry', $existingAllowances->count()) }}
                <span style="color:var(--ef-faint)">·</span>
                Total approved: <strong style="color:var(--ef-emerald,#0F7B5F)">₹{{ number_format($runningTotal, 2) }}</strong>
            </div>
        </div>
        @include('partials.overtime-card-list', [
            'records' => $existingAllowances,
            'showEmployee' => false,
            'showOrigin' => false,
            'showDelete' => true,
            'showRoutePrefix' => 'admin',
        ])
    </x-ds.card>
    @endif
</div>

<script>
    // Reloads this page (GET) preserving the current employee/date selection
    // plus whatever the other one already is, so the server can recompute
    // hourly rate / multipliers / pay period / existing-entries list. Hours
    // and Reason are NOT preserved across a reload (matches this page's
    // existing lightweight-inline-script convention — no client-side state
    // framework).
    function otReloadWith(overrides) {
        var params = new URLSearchParams();
        var userId = document.getElementById('user_id').value;
        var otDate = document.getElementById('ot_date').value;
        if (overrides.user_id !== undefined) { userId = overrides.user_id; }
        if (overrides.ot_date !== undefined) { otDate = overrides.ot_date; }
        if (userId) { params.set('user_id', userId); }
        if (otDate) { params.set('ot_date', otDate); }
        window.location.href = '{{ route('admin.overtime.create') }}' + (params.toString() ? ('?' + params.toString()) : '');
    }
</script>

</x-admin-layout>
