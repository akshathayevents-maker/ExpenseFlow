<x-admin-layout title="Leave Policy Templates">

<x-ds.hero eyebrow="Leave Management" title="Leave Policy Templates"
    :meta="[['icon' => 'bi-collection', 'text' => 'Reusable leave-entitlement bundles assigned to employees']]">
    <x-slot:actions>
        <a href="{{ route('admin.leave-policy-templates.create') }}" class="ef-ds-btn --primary">
            <i class="bi bi-plus-lg"></i> <span>New Template</span>
        </a>
    </x-slot:actions>
</x-ds.hero>

@push('styles')
<style>
    .lpt-list { display: flex; flex-direction: column; gap: 10px; }
    .lpt-row { border: 1px solid var(--ef-border, #e5e7eb); border-radius: 10px; padding: 12px 14px; }
    .lpt-row-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; flex-wrap: wrap; }
    .lpt-row-title { font-weight: 700; font-size: 1.0rem; }
    .lpt-row-meta { color: var(--ef-faint, #6b7280); font-size: .82rem; margin-top: 2px; }
    .lpt-items { margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px; }
    .lpt-item-chip { font-size: .74rem; background: var(--ef-surface-2, #f1f5f9); border-radius: 6px; padding: 3px 8px; }
    .lpt-actions { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 10px; }
</style>
@endpush

<x-ds.card title="All Templates">
    <div class="lpt-list">
        @forelse($templates as $template)
            <div class="lpt-row">
                <div class="lpt-row-top">
                    <div>
                        <div class="lpt-row-title">
                            {{ $template->name }}
                            @if($template->is_default)
                                <span style="display:inline-flex;align-items:center;border-radius:6px;font-size:.7rem;font-weight:700;padding:2px 8px;background:rgba(15,123,95,.11);color:#0A5240;margin-left:6px">Default</span>
                            @endif
                        </div>
                        @if($template->description)
                            <div class="lpt-row-meta">{{ $template->description }}</div>
                        @endif
                    </div>
                    <span style="display:inline-flex;align-items:center;border-radius:6px;font-size:.72rem;font-weight:700;padding:3px 10px;background:{{ $template->is_active ? 'rgba(15,123,95,.11)' : 'rgba(100,116,139,.11)' }};color:{{ $template->is_active ? '#0A5240' : '#334155' }}">
                        {{ $template->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="lpt-items">
                    @foreach($template->items as $item)
                        <span class="lpt-item-chip">
                            {{ $item->leaveType->name ?? '—' }}:
                            {{ rtrim(rtrim(number_format((float) $item->annual_entitlement, 1), '0'), '.') }}d/yr
                            ({{ str_replace('_', ' ', $item->allocation_mode) }})
                        </span>
                    @endforeach
                </div>

                <div class="lpt-actions">
                    <a href="{{ route('admin.leave-policy-templates.edit', $template) }}" class="ef-btn">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    @if($template->is_default)
                        <form method="POST" action="{{ route('admin.leave-policy-templates.clear-default', $template) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="ef-btn">
                                <i class="bi bi-star"></i> Clear Default
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.leave-policy-templates.set-default', $template) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="ef-btn">
                                <i class="bi bi-star-fill"></i> Set as Default
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div style="text-align:center;padding:40px 16px;color:var(--ef-faint,#6b7280)">
                <i class="bi bi-collection" style="font-size:1.5rem;display:block;margin-bottom:8px"></i>
                No leave policy templates configured yet.
                <div style="margin-top:12px">
                    <a href="{{ route('admin.leave-policy-templates.create') }}" class="ef-btn ef-btn-dark">
                        <i class="bi bi-plus-lg"></i> New Template
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</x-ds.card>

<div class="mt-3">
<x-ds.card title="Bulk-Assign Template to Existing Employees">
    <p style="color:var(--ef-faint,#6b7280);font-size:.82rem;margin:0 0 12px">
        Assigns the selected template's entitlements to every selected employee as new
        effective-dated policy rows — existing history is never edited.
    </p>
    <form method="POST" action="{{ route('admin.leave-policy-templates.bulk-assign') }}">
        @csrf
        <div class="ef-form-grid ef-form-grid-2">
            <div>
                <label class="ef-label" for="bulk_template">Template <span style="color:var(--ef-danger)">*</span></label>
                <select id="bulk_template" name="leave_policy_template_id" class="ef-select @error('leave_policy_template_id') --error @enderror" required>
                    <option value="">Select template</option>
                    @foreach($templates->where('is_active', true) as $template)
                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                    @endforeach
                </select>
                @error('leave_policy_template_id') <div class="ef-field-error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="ef-label" for="bulk_effective_from">Effective From <span style="color:var(--ef-danger)">*</span></label>
                <input type="date" id="bulk_effective_from" name="effective_from" class="ef-input @error('effective_from') --error @enderror"
                       value="{{ old('effective_from', now()->toDateString()) }}" required>
                @error('effective_from') <div class="ef-field-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="margin-top:12px">
            <label class="ef-label">Employees <span style="color:var(--ef-danger)">*</span></label>
            @error('employee_ids') <div class="ef-field-error">{{ $message }}</div> @enderror
            <div style="max-height:220px;overflow-y:auto;border:1px solid var(--ef-border,#e5e7eb);border-radius:8px;padding:8px;margin-top:6px">
                @forelse($allEmployees as $employee)
                    <label style="display:flex;align-items:center;gap:8px;padding:4px 0">
                        <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}">
                        {{ $employee->name }} <span style="color:var(--ef-faint,#6b7280);font-size:.78rem">({{ $employee->email }})</span>
                        @if(!$employee->leave_policy_template_id)
                            <span style="font-size:.7rem;color:var(--ef-faint,#6b7280)">— no template yet</span>
                        @endif
                    </label>
                @empty
                    <p style="color:var(--ef-faint,#6b7280);font-size:.82rem;margin:0">No employees available.</p>
                @endforelse
            </div>
        </div>

        <hr class="ef-form-divider">
        <div class="ef-form-actions">
            <button type="submit" class="ef-btn ef-btn-dark">
                <i class="bi bi-check-lg"></i> Assign to Selected Employees
            </button>
        </div>
    </form>
</x-ds.card>
</div>

</x-admin-layout>
