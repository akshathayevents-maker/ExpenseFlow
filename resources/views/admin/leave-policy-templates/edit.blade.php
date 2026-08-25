<x-admin-layout title="Edit Leave Policy Template">

<div class="ef-form-page">
    <div class="ef-form-page-header">
        <a href="{{ route('admin.leave-policy-templates.index') }}" class="ef-back" title="Back to Leave Policy Templates">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="ef-form-page-heading">Edit Leave Policy Template</h1>
            <p class="ef-form-page-sub">Changing this template never affects employees already assigned it — only future assignments.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.leave-policy-templates.update', $template) }}">
        @csrf
        @method('PUT')

        <x-ds.card title="Policy Information">
            <div class="ef-form-grid ef-form-grid-1">
                <div>
                    <label class="ef-label" for="name">Name <span style="color:var(--ef-danger)">*</span></label>
                    <input type="text" id="name" name="name" class="ef-input @error('name') --error @enderror"
                           value="{{ old('name', $template->name) }}" required>
                    @error('name') <div class="ef-field-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="ef-label" for="description">Description</label>
                    <textarea id="description" name="description" class="ef-input" rows="2">{{ old('description', $template->description) }}</textarea>
                </div>
                <div>
                    <label style="display:flex;align-items:center;gap:8px;font-weight:600">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                        Active (available for new assignments)
                    </label>
                    @if($template->is_default)
                        <div style="color:var(--ef-faint);font-size:.78rem;margin-top:4px;margin-left:24px">
                            This is currently the default template for new employees. Manage default status from the templates list.
                        </div>
                    @endif
                </div>
            </div>
        </x-ds.card>

        <div style="margin-top:14px">
        <x-ds.card title="Leave Types">
            <div id="items-container">
                @php
                    $oldItems = old('items');
                    $items = $oldItems ?: $template->items->map(fn ($i) => [
                        'leave_type_id' => $i->leave_type_id,
                        'annual_entitlement' => $i->annual_entitlement,
                        'allocation_mode' => $i->allocation_mode,
                        'monthly_accrual_amount' => $i->monthly_accrual_amount,
                    ])->all();
                @endphp
                @foreach($items as $i => $item)
                    @include('admin.leave-policy-templates._item-fields', ['leaveTypes' => $leaveTypes, 'index' => $i, 'item' => $item])
                @endforeach
            </div>
            <button type="button" class="ef-btn" id="add-item-btn">
                <i class="bi bi-plus-lg"></i> Add Leave Type
            </button>
        </x-ds.card>
        </div>

        <div class="ef-form-actions" style="margin-top:16px">
            <a href="{{ route('admin.leave-policy-templates.index') }}" class="ef-btn">Cancel</a>
            <button type="submit" class="ef-btn ef-btn-dark">
                <i class="bi bi-check-lg"></i> Save Template
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('add-item-btn').addEventListener('click', function () {
    const container = document.getElementById('items-container');
    const template = document.querySelector('[data-item-row]').cloneNode(true);
    const idx = container.querySelectorAll('[data-item-row]').length;
    template.querySelectorAll('select, input').forEach(function (el) {
        el.name = el.name.replace(/items\[\d+\]/, 'items[' + idx + ']');
        if (el.tagName === 'SELECT') { el.selectedIndex = 0; } else { el.value = ''; }
    });
    container.appendChild(template);
});
</script>
@endpush

</x-admin-layout>
