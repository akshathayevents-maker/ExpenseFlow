<x-admin-layout title="Create Purchase Plan">

@push('styles')
<style>
.ef-pp-split { display: grid; gap: 14px; grid-template-columns: 1fr 280px; align-items: start; }
.ef-pp-table { border-collapse: collapse; width: 100%; }
.ef-pp-table th {
    background: var(--ef-bg-subtle);
    border-bottom: 1px solid var(--ef-border);
    color: var(--ef-faint);
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .08em;
    padding: 9px 14px;
    text-align: left;
    text-transform: uppercase;
    white-space: nowrap;
}
.ef-pp-table td {
    border-bottom: 1px solid var(--ef-border);
    font-size: .86rem;
    padding: 10px 14px;
    vertical-align: middle;
}
.ef-pp-table tbody tr:last-child td { border-bottom: none; }
.ef-pp-table tbody tr:hover td { background: var(--ef-bg-subtle); }
.ef-pp-inline-input {
    background: #fbfaf7;
    border: 1px solid var(--ef-border-strong);
    border-radius: 8px;
    color: var(--ef-ink);
    font-size: .84rem;
    padding: 6px 10px;
    width: 100%;
}
.ef-pp-inline-input:focus {
    border-color: rgba(20,20,18,.45);
    outline: 0;
}
.ef-pp-inline-select {
    background: #fbfaf7;
    border: 1px solid var(--ef-border-strong);
    border-radius: 8px;
    color: var(--ef-ink);
    font-size: .84rem;
    padding: 6px 10px;
    min-width: 100px;
}
.ef-pp-item-name { color: var(--ef-ink); font-size: .88rem; font-weight: 600; }
.ef-pp-item-stock { color: var(--ef-faint); font-size: .72rem; margin-top: 2px; }
@media (max-width: 991.98px) { .ef-pp-split { grid-template-columns: 1fr; } }
</style>
@endpush

<div class="ef-form-page-header" style="max-width:none">
    <a href="{{ route('admin.purchase-plans.index') }}" class="ef-back" title="Back to Purchase Plans">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h1 class="ef-form-page-heading">Create Purchase Plan</h1>
        <p class="ef-form-page-sub">Review suggestions and build a procurement plan</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.purchase-plans.store') }}">
    @csrf
    <div class="ef-pp-split">
        <div>
            {{-- Plan details --}}
            <x-ds.card title="Plan Details" style="margin-bottom:14px">
                <div class="ef-form-grid ef-form-grid-2">
                    <div style="grid-column:1/span 1;grid-column-end:span 2">
                        <label class="ef-label" for="title">Plan Title <span style="color:var(--ef-danger)">*</span></label>
                        <input type="text" id="title" name="title"
                               class="ef-input @error('title') --error @enderror"
                               value="{{ old('title', 'Purchase Plan — ' . now()->format('d M Y')) }}" required>
                        @error('title') <div class="ef-field-error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="ef-label" for="planned_date">Planned Date <span style="color:var(--ef-danger)">*</span></label>
                        <input type="date" id="planned_date" name="planned_date"
                               class="ef-input @error('planned_date') --error @enderror"
                               value="{{ old('planned_date', now()->addDay()->toDateString()) }}" required>
                        @error('planned_date') <div class="ef-field-error">{{ $message }}</div> @enderror
                    </div>
                    <div style="grid-column:1/span 2">
                        <label class="ef-label" for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="2" class="ef-textarea">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </x-ds.card>

            {{-- Suggested items --}}
            <x-ds.card :no-pad="true">
                <x-slot:head_right>
                    <x-ds.section-head title="Suggested Items" :count="$suggestions->count()" />
                </x-slot:head_right>

                @if($suggestions->isEmpty())
                    <div style="text-align:center;padding:24px;color:var(--ef-faint);font-size:.84rem">
                        All stock is adequate. You can still create a plan manually.
                    </div>
                @else
                <div style="overflow-x:auto">
                    <table class="ef-pp-table">
                        <thead>
                            <tr>
                                <th style="width:36px">
                                    <input type="checkbox" id="selectAll" checked
                                           style="accent-color:var(--ef-emerald);width:15px;height:15px;cursor:pointer">
                                </th>
                                <th>Item</th>
                                <th>Priority</th>
                                <th>Quantity</th>
                                <th>Unit Cost (₹)</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($suggestions as $i => $item)
                            <tr>
                                <td>
                                    <input type="hidden" name="items[{{ $i }}][inventory_item_id]" value="{{ $item->id }}">
                                    <input type="checkbox" name="items[{{ $i }}][selected]" value="1"
                                           class="item-checkbox" checked
                                           style="accent-color:var(--ef-emerald);width:15px;height:15px;cursor:pointer">
                                </td>
                                <td>
                                    <div class="ef-pp-item-name">{{ $item->name }}</div>
                                    <div class="ef-pp-item-stock">
                                        Current: {{ $item->current_stock }} / Min: {{ $item->minimum_stock }} {{ $item->unit }}
                                    </div>
                                </td>
                                <td>
                                    <select name="items[{{ $i }}][priority]" class="ef-pp-inline-select">
                                        @foreach(['urgent','high','normal','low'] as $p)
                                            <option value="{{ $p }}" {{ $item->priority === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:6px;min-width:130px">
                                        <input type="number" name="items[{{ $i }}][quantity]"
                                               class="ef-pp-inline-input"
                                               value="{{ $item->suggested_quantity }}"
                                               min="0.001" step="0.001" required>
                                        <span style="color:var(--ef-faint);font-size:.8rem;white-space:nowrap">{{ $item->unit }}</span>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $i }}][unit_cost]"
                                           class="ef-pp-inline-input" style="min-width:90px"
                                           value="{{ $item->average_cost }}" min="0" step="0.01">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $i }}][notes]"
                                           class="ef-pp-inline-input" style="min-width:100px" placeholder="Optional">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </x-ds.card>
        </div>

        {{-- Sidebar --}}
        <x-ds.card title="Summary">
            <div style="color:var(--ef-faint);font-size:.82rem;margin-bottom:16px;line-height:1.5">
                Selected items will be added to the plan as a draft for review and approval.
            </div>
            <button type="submit" class="ef-btn ef-btn-dark" style="width:100%;justify-content:center">
                <i class="bi bi-plus-circle"></i> Create Plan
            </button>
            <a href="{{ route('admin.purchase-plans.index') }}" class="ef-btn" style="width:100%;justify-content:center;margin-top:8px">
                Cancel
            </a>
        </x-ds.card>
    </div>
</form>

@push('scripts')
<script>
document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = this.checked);
});
</script>
@endpush

</x-admin-layout>
