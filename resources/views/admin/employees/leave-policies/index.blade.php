<x-admin-layout title="Leave Policy — {{ $employee->name }}">

@push('styles')
<style>
    .lp-wrap { margin: 0 auto; max-width: 1040px; padding: 0 16px; }
    .lp-header { align-items: flex-start; display: flex; flex-wrap: wrap; gap: 12px; padding: 20px 0 4px; }
    .lp-header-main { display: flex; gap: 12px; flex: 1 1 220px; min-width: 0; }
    .lp-title { font-size: 1.25rem; font-weight: 760; letter-spacing: -.02em; line-height: 1.2; margin: 0; overflow-wrap: anywhere; }
    .lp-sub { color: var(--ef-faint, #6b7280); font-size: .82rem; margin: 2px 0 0; overflow-wrap: anywhere; }
    .lp-header-actions { display: flex; gap: 8px; flex-wrap: wrap; }

    .lp-grid { display: flex; flex-direction: column; gap: 14px; margin-top: 14px; }
    @media (min-width: 992px) {
        .lp-grid { flex-direction: row; align-items: flex-start; }
        .lp-grid-main { flex: 1 1 42%; min-width: 0; }
        .lp-grid-side { flex: 1 1 58%; min-width: 0; }
    }
    .lp-grid-main, .lp-grid-side { display: flex; flex-direction: column; gap: 14px; }

    .lp-current-list { display: flex; flex-direction: column; }
    .lp-current-row { padding: 10px 0; border-top: 1px solid var(--ef-border, #e5e7eb); }
    .lp-current-row:first-child { border-top: none; }
    .lp-current-name { font-weight: 700; }
    .lp-current-meta { color: var(--ef-faint, #6b7280); font-size: .78rem; margin-top: 2px; }

    .lp-empty { align-items: center; display: flex; flex-direction: column; gap: 6px; padding: 22px 12px; text-align: center; }
    .lp-empty i { color: var(--ef-faint, #6b7280); font-size: 1.4rem; }
    .lp-empty-title { font-weight: 650; }
    .lp-empty-sub { color: var(--ef-faint, #6b7280); font-size: .82rem; max-width: 320px; }

    .lp-hist-cards { display: flex; flex-direction: column; gap: 8px; }
    .lp-hist-card { border: 1px solid var(--ef-border, #e5e7eb); border-radius: 8px; padding: 10px 12px; }
    .lp-hist-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 12px; margin-top: 8px; }
    .lp-hist-item-lbl { color: var(--ef-faint, #6b7280); font-size: .7rem; font-weight: 700; letter-spacing: .03em; text-transform: uppercase; }
    .lp-hist-item-val { font-size: .84rem; font-weight: 600; }
</style>
@endpush

<div class="lp-wrap">
    <div class="lp-header">
        <div class="lp-header-main">
            <a href="{{ route('admin.employees.show', $employee) }}" class="ef-back" title="Back to {{ $employee->name }}" aria-label="Back to {{ $employee->name }}">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
            </a>
            <div style="min-width:0">
                <div class="lp-sub" style="margin-bottom:2px">Leave Management</div>
                <h1 class="lp-title">{{ $employee->name }}</h1>
                <p class="lp-sub">{{ $employee->email }} &middot; Employee ID #{{ $employee->id }}</p>
            </div>
        </div>
        <div class="lp-header-actions">
            <a href="{{ route('admin.employees.show', $employee) }}" class="ef-btn">
                <i class="bi bi-person"></i> Back to Employee
            </a>
            <a href="{{ route('admin.leave.balances.show', $employee) }}" class="ef-btn">
                <i class="bi bi-calendar-minus"></i> View Balances
            </a>
        </div>
    </div>

    <div class="lp-grid">
        <div class="lp-grid-main">
            <x-ds.card title="Leave Policy Template">
                <p style="color:var(--ef-faint,#6b7280);font-size:.82rem;margin:0 0 12px">
                    Current template: <strong>{{ $employee->leavePolicyTemplate->name ?? 'None assigned' }}</strong>.
                    Assigning a template creates new effective-dated policy rows for every leave type in it — it never
                    edits or removes the employee's existing history, and leave types present in the OLD template but
                    absent from the new one are left exactly as they were (not auto-deactivated).
                </p>
                <form method="POST" action="{{ route('admin.employees.leave-policy-template.assign', $employee) }}">
                    @csrf
                    <div class="ef-form-grid ef-form-grid-2">
                        <div>
                            <label class="ef-label" for="leave_policy_template_id">Template <span style="color:var(--ef-danger)">*</span></label>
                            <select id="leave_policy_template_id" name="leave_policy_template_id" class="ef-select @error('leave_policy_template_id') --error @enderror" required>
                                <option value="">Select template</option>
                                @foreach($leavePolicyTemplates as $template)
                                    <option value="{{ $template->id }}">{{ $template->name }}{{ $template->is_default ? ' (default)' : '' }}</option>
                                @endforeach
                            </select>
                            @error('leave_policy_template_id') <div class="ef-field-error">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="ef-label" for="template_effective_from">Effective From <span style="color:var(--ef-danger)">*</span></label>
                            <input type="date" id="template_effective_from" name="effective_from" class="ef-input @error('effective_from') --error @enderror"
                                   value="{{ old('effective_from', now()->toDateString()) }}" required>
                            @error('effective_from') <div class="ef-field-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <hr class="ef-form-divider">
                    <div class="ef-form-actions">
                        <button type="submit" class="ef-btn ef-btn-dark" style="width:100%;justify-content:center">
                            <i class="bi bi-check-lg"></i> Assign / Change Template
                        </button>
                    </div>
                </form>
            </x-ds.card>

            <x-ds.card title="Current Policies">
                @if($currentPolicies->filter()->isEmpty())
                    <div class="lp-empty">
                        <i class="bi bi-calendar-minus" aria-hidden="true"></i>
                        <div class="lp-empty-title">No leave policy configured</div>
                        <div class="lp-empty-sub">Assign an entitlement below to enable leave allocation for this employee.</div>
                    </div>
                @else
                    <div class="lp-current-list">
                        @foreach($leaveTypes as $type)
                            @php $policy = $currentPolicies[$type->id] ?? null; @endphp
                            @if($policy)
                            <div class="lp-current-row">
                                <div class="lp-current-name">{{ $type->name }}</div>
                                <div class="lp-current-meta">
                                    {{ rtrim(rtrim(number_format((float) $policy->annual_entitlement, 1), '0'), '.') }} day(s)/year
                                    &middot; {{ str_replace('_', ' ', ucfirst($policy->allocation_mode)) }}
                                    &middot; Effective {{ $policy->effective_from->format('d M Y') }}
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </x-ds.card>

            <x-ds.card title="Assign / Update Leave Policy">
                <p style="color:var(--ef-faint,#6b7280);font-size:.82rem;margin:0 0 12px">
                    Saving here never edits history — it closes the current policy for the selected
                    leave type and starts a new one from the chosen effective date.
                </p>
                <form method="POST" action="{{ route('admin.employees.leave-policies.store', $employee) }}">
                    @csrf
                    <div class="ef-form-grid ef-form-grid-1">
                        <div>
                            <label class="ef-label" for="leave_type_id">Leave Type <span style="color:var(--ef-danger)">*</span></label>
                            <select id="leave_type_id" name="leave_type_id" class="ef-select @error('leave_type_id') --error @enderror" required>
                                <option value="">Select leave type</option>
                                @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('leave_type_id') <div class="ef-field-error">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ef-label" for="annual_entitlement">Annual Entitlement (days) <span style="color:var(--ef-danger)">*</span></label>
                            <input type="number" step="0.1" min="0" id="annual_entitlement" name="annual_entitlement"
                                   class="ef-input @error('annual_entitlement') --error @enderror"
                                   value="{{ old('annual_entitlement') }}" required>
                            @error('annual_entitlement') <div class="ef-field-error">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ef-label" for="allocation_mode">Allocation Mode <span style="color:var(--ef-danger)">*</span></label>
                            <select id="allocation_mode" name="allocation_mode"
                                    class="ef-select @error('allocation_mode') --error @enderror"
                                    onchange="document.getElementById('accrual-amount-field').style.display = this.value === 'yearly' ? 'none' : ''">
                                <option value="yearly" {{ old('allocation_mode') === 'yearly' ? 'selected' : '' }}>Yearly (Jan 1 grant)</option>
                                <option value="monthly_accrual" {{ old('allocation_mode') === 'monthly_accrual' ? 'selected' : '' }}>Monthly Accrual</option>
                                <option value="quarterly_accrual" {{ old('allocation_mode') === 'quarterly_accrual' ? 'selected' : '' }}>Quarterly Accrual</option>
                            </select>
                            @error('allocation_mode') <div class="ef-field-error">{{ $message }}</div> @enderror
                        </div>

                        <div id="accrual-amount-field" style="{{ old('allocation_mode', 'yearly') === 'yearly' ? 'display:none' : '' }}">
                            <label class="ef-label" for="monthly_accrual_amount">Amount per Period</label>
                            <input type="number" step="0.01" min="0" id="monthly_accrual_amount" name="monthly_accrual_amount"
                                   class="ef-input @error('monthly_accrual_amount') --error @enderror"
                                   value="{{ old('monthly_accrual_amount') }}">
                            @error('monthly_accrual_amount') <div class="ef-field-error">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ef-label" for="effective_from">Effective From <span style="color:var(--ef-danger)">*</span></label>
                            <input type="date" id="effective_from" name="effective_from"
                                   class="ef-input @error('effective_from') --error @enderror"
                                   value="{{ old('effective_from', now()->toDateString()) }}" required>
                            @error('effective_from') <div class="ef-field-error">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr class="ef-form-divider">
                    <div class="ef-form-actions">
                        <button type="submit" class="ef-btn ef-btn-dark" style="width:100%;justify-content:center">
                            <i class="bi bi-check-lg"></i> Save Leave Policy
                        </button>
                    </div>
                </form>
            </x-ds.card>
        </div>

        <div class="lp-grid-side">
            <x-ds.card title="Current Balances">
                @if(empty($balances))
                    <p style="color:var(--ef-faint,#6b7280);font-size:.82rem;margin:0">No leave types configured.</p>
                @else
                    <div class="lp-current-list">
                        @foreach($balances as $b)
                            <div class="lp-current-row">
                                <div class="lp-current-name">{{ $b['leave_type']->name }}</div>
                                <div class="lp-current-meta">
                                    Available: {{ rtrim(rtrim(number_format((float) $b['available'], 1), '0'), '.') }}
                                    &middot; Allocated: {{ rtrim(rtrim(number_format((float) $b['allocated'], 1), '0'), '.') }}
                                    &middot; Used: {{ rtrim(rtrim(number_format((float) $b['used'], 1), '0'), '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ds.card>

            <x-ds.card title="Policy History" :no-pad="true">
                @if($history->isEmpty())
                    <div class="lp-empty">
                        <i class="bi bi-clock-history" aria-hidden="true"></i>
                        <div class="lp-empty-title">No policy history yet</div>
                        <div class="lp-empty-sub">Every policy change will be recorded here, with nothing overwritten.</div>
                    </div>
                @else
                    <div class="lp-hist-cards" style="padding:16px">
                        @foreach($history as $policy)
                        <div class="lp-hist-card">
                            <div style="font-weight:700">{{ $policy->leaveType->name ?? '—' }}</div>
                            <div class="lp-hist-grid">
                                <div>
                                    <div class="lp-hist-item-lbl">Entitlement</div>
                                    <div class="lp-hist-item-val">{{ rtrim(rtrim(number_format((float) $policy->annual_entitlement, 1), '0'), '.') }} / yr</div>
                                </div>
                                <div>
                                    <div class="lp-hist-item-lbl">Mode</div>
                                    <div class="lp-hist-item-val">{{ str_replace('_', ' ', ucfirst($policy->allocation_mode)) }}</div>
                                </div>
                                <div>
                                    <div class="lp-hist-item-lbl">Effective From</div>
                                    <div class="lp-hist-item-val">{{ $policy->effective_from->format('d M Y') }}</div>
                                </div>
                                <div>
                                    <div class="lp-hist-item-lbl">Status</div>
                                    <div class="lp-hist-item-val">{{ $policy->is_active ? 'Current' : 'Superseded' }}</div>
                                </div>
                                <div>
                                    <div class="lp-hist-item-lbl">Created By</div>
                                    <div class="lp-hist-item-val">{{ $policy->creator->name ?? '—' }}</div>
                                </div>
                                <div>
                                    <div class="lp-hist-item-lbl">Created</div>
                                    <div class="lp-hist-item-val">{{ $policy->created_at->format('d M Y') }}</div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </x-ds.card>
        </div>
    </div>
</div>

</x-admin-layout>
