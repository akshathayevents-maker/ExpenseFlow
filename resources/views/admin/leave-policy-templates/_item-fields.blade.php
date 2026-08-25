{{--
    Repeatable leave-type item row for admin/leave-policy-templates/create.blade.php
    and edit.blade.php. Expects: $leaveTypes (Collection<LeaveType>), $index (int),
    $item (array|null, e.g. ['leave_type_id'=>.., 'annual_entitlement'=>.., 'allocation_mode'=>.., 'monthly_accrual_amount'=>..]).
--}}
@php $item = $item ?? []; @endphp
<div class="lpt-item" data-item-row>
    <div class="ef-form-grid ef-form-grid-2">
        <div>
            <label class="ef-label">Leave Type <span style="color:var(--ef-danger)">*</span></label>
            <select name="items[{{ $index }}][leave_type_id]" class="ef-select" required>
                <option value="">Select leave type</option>
                @foreach($leaveTypes as $type)
                    <option value="{{ $type->id }}" {{ (string) ($item['leave_type_id'] ?? '') === (string) $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="ef-label">Annual Entitlement (days) <span style="color:var(--ef-danger)">*</span></label>
            <input type="number" step="0.1" min="0" name="items[{{ $index }}][annual_entitlement]"
                   class="ef-input" value="{{ $item['annual_entitlement'] ?? '' }}" required>
        </div>
        <div>
            <label class="ef-label">Accrual Frequency <span style="color:var(--ef-danger)">*</span></label>
            <select name="items[{{ $index }}][allocation_mode]" class="ef-select"
                    onchange="this.closest('[data-item-row]').querySelector('[data-accrual-field]').style.display = this.value === 'yearly' ? 'none' : ''">
                <option value="yearly" {{ ($item['allocation_mode'] ?? 'yearly') === 'yearly' ? 'selected' : '' }}>Yearly — full entitlement credited annually</option>
                <option value="monthly_accrual" {{ ($item['allocation_mode'] ?? '') === 'monthly_accrual' ? 'selected' : '' }}>Monthly — credited every month</option>
                <option value="quarterly_accrual" {{ ($item['allocation_mode'] ?? '') === 'quarterly_accrual' ? 'selected' : '' }}>Quarterly — credited every quarter</option>
            </select>
        </div>
        <div data-accrual-field style="{{ ($item['allocation_mode'] ?? 'yearly') === 'yearly' ? 'display:none' : '' }}">
            <label class="ef-label">Amount per Period</label>
            <input type="number" step="0.01" min="0" name="items[{{ $index }}][monthly_accrual_amount]"
                   class="ef-input" value="{{ $item['monthly_accrual_amount'] ?? '' }}">
        </div>
    </div>
    <div style="margin-top:8px;text-align:right">
        <button type="button" class="ef-btn" onclick="this.closest('[data-item-row]').remove()">
            <i class="bi bi-trash"></i> Remove
        </button>
    </div>
    <hr class="ef-form-divider">
</div>
