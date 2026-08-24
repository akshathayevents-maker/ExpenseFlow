<x-admin-layout title="Request Advance">

@php
    $eligibleAmount = (float) $eligibility['eligible_advance_amount'];
    $canRequest = $eligibility['salary_configured'] && $eligibleAmount > 0;
@endphp

@push('styles')
<style>
    .adv-elig-card { border: 1px solid var(--ef-border, #e5e7eb); border-radius: 12px; padding: 16px; margin-bottom: 16px; background: var(--ef-surface-2, #f8f9fa); }
    .adv-elig-title { font-size: .72rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--ef-faint, #6b7280); margin-bottom: 10px; }
    .adv-elig-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 6px 0; font-size: .86rem; }
    .adv-elig-lbl { color: var(--ef-faint, #6b7280); }
    .adv-elig-val { font-weight: 650; font-variant-numeric: tabular-nums; }
    .adv-elig-divider { border: none; border-top: 1px solid var(--ef-border, #e5e7eb); margin: 8px 0; }
    .adv-elig-final { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; }
    .adv-elig-final-lbl { font-weight: 700; }
    .adv-elig-final-val { font-size: 1.4rem; font-weight: 800; letter-spacing: -.02em; }
    .adv-elig-final-val.--zero { color: var(--ef-danger, #C84B44); }
    .adv-elig-empty { display: flex; align-items: flex-start; gap: 10px; padding: 4px 0; color: var(--ef-faint, #6b7280); font-size: .88rem; }
    .adv-elig-empty i { font-size: 1.1rem; margin-top: 1px; }
    .adv-max-hint { font-size: .78rem; color: var(--ef-faint, #6b7280); margin-top: 4px; }
    .adv-live-error { font-size: .78rem; color: var(--ef-danger, #C84B44); margin-top: 4px; display: none; }
    .adv-live-error.--show { display: block; }
</style>
@endpush

<div class="ef-form-page">
    <div class="ef-form-page-header">
        <a href="{{ route('employee.advances.index') }}" class="ef-back" title="Back to Advances">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="ef-form-page-heading">Request Advance</h1>
            <p class="ef-form-page-sub">Submit a request for admin/manager approval</p>
        </div>
    </div>

    {{-- ── Advance Eligibility ─────────────────────────────────────── --}}
    <div class="adv-elig-card">
        <div class="adv-elig-title">Advance Eligibility</div>

        @if(!$eligibility['salary_configured'])
            <div class="adv-elig-empty">
                <i class="bi bi-exclamation-circle"></i>
                <span>{{ $eligibility['unavailable_reason'] }}</span>
            </div>
        @else
            @if($eligibility['unavailable_reason'])
                <div class="adv-elig-empty">
                    <i class="bi bi-exclamation-circle"></i>
                    <span>{{ $eligibility['unavailable_reason'] }}</span>
                </div>
            @else
                <div class="adv-elig-row">
                    <span class="adv-elig-lbl">Payable days</span>
                    <span class="adv-elig-val">{{ rtrim(rtrim(number_format((float) $eligibility['payable_days'], 1), '0'), '.') ?: '0' }}</span>
                </div>
                <div class="adv-elig-row">
                    <span class="adv-elig-lbl">Daily salary</span>
                    <span class="adv-elig-val">₹{{ number_format((float) $eligibility['daily_salary'], 2) }}</span>
                </div>
                <div class="adv-elig-row">
                    <span class="adv-elig-lbl">Earned salary</span>
                    <span class="adv-elig-val">₹{{ number_format((float) $eligibility['earned_salary'], 2) }}</span>
                </div>
                <div class="adv-elig-row">
                    <span class="adv-elig-lbl">Previous advances</span>
                    <span class="adv-elig-val">₹{{ number_format((float) $eligibility['previous_advances_amount'], 2) }}</span>
                </div>
                <div class="adv-elig-row">
                    <span class="adv-elig-lbl">Outstanding amount</span>
                    <span class="adv-elig-val">₹{{ number_format((float) $eligibility['outstanding_amount'], 2) }}</span>
                </div>
                <hr class="adv-elig-divider">
                <div class="adv-elig-final">
                    <span class="adv-elig-final-lbl">Eligible advance</span>
                    <span class="adv-elig-final-val {{ $eligibleAmount <= 0 ? '--zero' : '' }}">₹{{ number_format($eligibleAmount, 2) }}</span>
                </div>
            @endif

            @if($eligibility['salary_configured'] && !$eligibility['unavailable_reason'] && $eligibleAmount <= 0)
                <div class="adv-elig-empty" style="margin-top:10px">
                    <i class="bi bi-info-circle"></i>
                    <span>You currently have no advance amount available.</span>
                </div>
            @endif
        @endif
    </div>

    <x-ds.card>
        <form method="POST" action="{{ route('employee.advances.store') }}" id="advRequestForm">
            @csrf

            <div class="ef-form-grid ef-form-grid-2">
                <div style="grid-column: 1 / -1">
                    <label class="ef-label" for="requested_amount">Requested Amount <span style="color:var(--ef-danger)">*</span></label>
                    <input type="number" step="0.01" min="0.01"
                           @if($canRequest) max="{{ $eligibleAmount }}" @endif
                           id="requested_amount" name="requested_amount"
                           class="ef-input @error('requested_amount') --error @enderror"
                           value="{{ old('requested_amount') }}"
                           {{ $canRequest ? 'required autofocus' : 'disabled' }}>
                    @error('requested_amount') <div class="ef-field-error">{{ $message }}</div> @enderror
                    @if($canRequest)
                        <div class="adv-max-hint">Maximum eligible: ₹{{ number_format($eligibleAmount, 2) }}</div>
                        <div class="adv-live-error" id="advLiveError">Maximum eligible advance is ₹{{ number_format($eligibleAmount, 2) }}.</div>
                    @endif
                </div>

                <div style="grid-column: 1 / -1">
                    <label class="ef-label" for="notes">Notes <span style="color:var(--ef-faint,#6b7280);font-weight:400;text-transform:none;letter-spacing:0">(optional)</span></label>
                    <textarea id="notes" name="notes" rows="3"
                              class="ef-textarea @error('notes') --error @enderror">{{ old('notes') }}</textarea>
                    @error('notes') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <hr class="ef-form-divider">
            <div class="ef-form-actions">
                <a href="{{ route('employee.advances.index') }}" class="ef-btn">Cancel</a>
                <button type="submit" class="ef-btn ef-btn-dark" id="advSubmitBtn" {{ $canRequest ? '' : 'disabled' }}>
                    <i class="bi bi-send"></i> Submit Request
                </button>
            </div>
        </form>
    </x-ds.card>
</div>

@if($canRequest)
@push('scripts')
<script>
(function () {
    'use strict';
    var input   = document.getElementById('requested_amount');
    var error   = document.getElementById('advLiveError');
    var submit  = document.getElementById('advSubmitBtn');
    var maxEligible = {{ $eligibleAmount }};

    function validateAmount() {
        var val = parseFloat(input.value);
        var invalid = isNaN(val) || val <= 0 || val > maxEligible;
        error.classList.toggle('--show', !isNaN(val) && val > maxEligible);
        // Server-side validation (StoreAdvanceRequest) is the source of
        // truth — this only blocks the obviously-invalid case for UX.
        submit.disabled = invalid;
        return !invalid;
    }

    input.addEventListener('input', validateAmount);
    validateAmount();

    // Mobile keyboard: bring the field clear of the fixed topbar on focus
    // only — never on every keystroke (same principle as the Kitchen
    // Calculator's search-input focus handling).
    input.addEventListener('focus', function () {
        if (window.matchMedia('(max-width: 767.98px)').matches) {
            var rect = input.getBoundingClientRect();
            if (rect.top < 72) {
                input.scrollIntoView({ block: 'center', behavior: 'smooth' });
            }
        }
    });
})();
</script>
@endpush
@endif

</x-admin-layout>
