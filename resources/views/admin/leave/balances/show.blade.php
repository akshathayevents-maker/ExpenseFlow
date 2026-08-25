<x-admin-layout title="Leave Balances — {{ $employee->name }}">

@push('styles')
<style>
    .lb-wrap { margin: 0 auto; max-width: 1040px; padding: 0 16px; }
    .lb-header { align-items: flex-start; display: flex; flex-wrap: wrap; gap: 12px; padding: 20px 0 4px; }
    .lb-header-main { display: flex; gap: 12px; flex: 1 1 220px; min-width: 0; }
    .lb-title { font-size: 1.25rem; font-weight: 760; letter-spacing: -.02em; line-height: 1.2; margin: 0; overflow-wrap: anywhere; }
    .lb-sub { color: var(--ef-faint, #6b7280); font-size: .82rem; margin: 2px 0 0; overflow-wrap: anywhere; }
    .lb-header-actions { display: flex; gap: 8px; flex-wrap: wrap; }

    .lb-grid { display: flex; flex-direction: column; gap: 14px; margin-top: 14px; }
    @media (min-width: 992px) {
        .lb-grid { flex-direction: row; align-items: flex-start; }
        .lb-grid-main { flex: 1 1 55%; min-width: 0; }
        .lb-grid-side { flex: 1 1 45%; min-width: 0; }
    }
    .lb-grid-main, .lb-grid-side { display: flex; flex-direction: column; gap: 14px; }

    .lb-tile-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
    @media (min-width: 480px) { .lb-tile-grid { grid-template-columns: repeat(4, 1fr); } }
    .lb-tile { background: var(--ef-surface-2, #f8f9fa); border-radius: 10px; padding: 10px; text-align: center; }
    .lb-tile-val { font-size: 1.15rem; font-weight: 800; }
    .lb-tile-lbl { font-size: .68rem; color: var(--ef-faint, #6b7280); text-transform: uppercase; letter-spacing: .03em; margin-top: 2px; }

    .lb-type-row { padding: 12px 0; border-top: 1px solid var(--ef-border, #e5e7eb); }
    .lb-type-row:first-child { border-top: none; }
    .lb-type-name { font-weight: 700; margin-bottom: 6px; }
</style>
@endpush

<div class="lb-wrap">
    <div class="lb-header">
        <div class="lb-header-main">
            <a href="{{ route('admin.employees.leave-policies.index', $employee) }}" class="ef-back" title="Back" aria-label="Back">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
            </a>
            <div style="min-width:0">
                <div class="lb-sub" style="margin-bottom:2px">Leave Management</div>
                <h1 class="lb-title">{{ $employee->name }}</h1>
                <p class="lb-sub">{{ $employee->email }} &middot; Leave Balances</p>
            </div>
        </div>
        <div class="lb-header-actions">
            <a href="{{ route('admin.employees.leave-policies.index', $employee) }}" class="ef-btn">
                <i class="bi bi-sliders"></i> Leave Policy
            </a>
        </div>
    </div>

    <div class="lb-grid">
        <div class="lb-grid-main">
            <x-ds.card title="Balances">
                @if(empty($balances))
                    <div style="text-align:center;padding:24px 12px;color:var(--ef-faint,#6b7280)">
                        <i class="bi bi-calendar-minus" style="font-size:1.4rem;display:block;margin-bottom:6px"></i>
                        No active leave types configured.
                    </div>
                @else
                    @foreach($balances as $row)
                        <div class="lb-type-row">
                            <div class="lb-type-name">{{ $row['leave_type']->name }}</div>
                            <div class="lb-tile-grid">
                                <div class="lb-tile">
                                    <div class="lb-tile-val">{{ rtrim(rtrim(number_format($row['allocated'], 1), '0'), '.') }}</div>
                                    <div class="lb-tile-lbl">Allocated</div>
                                </div>
                                <div class="lb-tile">
                                    <div class="lb-tile-val">{{ rtrim(rtrim(number_format($row['used'], 1), '0'), '.') }}</div>
                                    <div class="lb-tile-lbl">Used</div>
                                </div>
                                <div class="lb-tile">
                                    <div class="lb-tile-val">{{ rtrim(rtrim(number_format($row['pending'], 1), '0'), '.') }}</div>
                                    <div class="lb-tile-lbl">Pending</div>
                                </div>
                                <div class="lb-tile">
                                    <div class="lb-tile-val" style="color:var(--ef-emerald,#0F7B5F)">{{ rtrim(rtrim(number_format($row['available'], 1), '0'), '.') }}</div>
                                    <div class="lb-tile-lbl">Available</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </x-ds.card>
        </div>

        <div class="lb-grid-side">
            <x-ds.card title="Manual Adjustment">
                <p style="color:var(--ef-faint,#6b7280);font-size:.82rem;margin:0 0 12px">
                    Use a positive amount to credit, or a negative amount to deduct. A deduction that
                    would take the balance below zero is rejected.
                </p>
                <form method="POST" action="{{ route('admin.leave.adjustments.store', $employee) }}">
                    @csrf
                    <div class="ef-form-grid ef-form-grid-1">
                        <div>
                            <label class="ef-label" for="adj_leave_type_id">Leave Type <span style="color:var(--ef-danger)">*</span></label>
                            <select id="adj_leave_type_id" name="leave_type_id" class="ef-select @error('leave_type_id') --error @enderror" required>
                                <option value="">Select leave type</option>
                                @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('leave_type_id') <div class="ef-field-error">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ef-label" for="amount">Amount (days) <span style="color:var(--ef-danger)">*</span></label>
                            <input type="number" step="0.1" id="amount" name="amount"
                                   class="ef-input @error('amount') --error @enderror"
                                   value="{{ old('amount') }}" placeholder="e.g. 2 or -1.5" required>
                            @error('amount') <div class="ef-field-error">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ef-label" for="reason">Reason <span style="color:var(--ef-danger)">*</span></label>
                            <textarea id="reason" name="reason" rows="3" class="ef-textarea @error('reason') --error @enderror" required>{{ old('reason') }}</textarea>
                            @error('reason') <div class="ef-field-error">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr class="ef-form-divider">
                    <div class="ef-form-actions">
                        <button type="submit" class="ef-btn ef-btn-dark" style="width:100%;justify-content:center">
                            <i class="bi bi-check-lg"></i> Record Adjustment
                        </button>
                    </div>
                </form>
            </x-ds.card>
        </div>
    </div>
</div>

</x-admin-layout>
