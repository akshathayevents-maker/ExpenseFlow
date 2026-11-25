<x-admin-layout title="Create Purchase Plan">
@include('inventory._styles')
<div class="ic-wrap">
    <a class="ic-back" href="{{ route('admin.purchase-plans.index') }}"><i class="bi bi-arrow-left"></i> Purchase plans</a>
    <div class="ic-head" style="margin-top:8px"><h1>New Purchase Plan</h1></div>

    @if($errors->any())
        <div class="ic-err"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('admin.purchase-plans.store') }}">
        @csrf
        <div class="ic-card">
            <h2>Plan details</h2>
            <div class="ic-fields">
                <div style="grid-column:1/-1">
                    <label class="ic-label" for="title">Plan title *</label>
                    <input type="text" id="title" name="title" class="ic-input" value="{{ old('title', 'Purchase Plan — '.now()->format('d M Y')) }}" required maxlength="255">
                </div>
                <div>
                    <label class="ic-label" for="planned_date">Planned date *</label>
                    <input type="date" id="planned_date" name="planned_date" class="ic-input" value="{{ old('planned_date', now()->addDay()->toDateString()) }}" required>
                </div>
                <div style="grid-column:1/-1">
                    <label class="ic-label" for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="2" class="ic-input" maxlength="1000">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="ic-card">
            <h2 style="display:flex;align-items:center;gap:10px">Suggested items <span class="ic-chip ic-chip--neutral">{{ $suggestions->count() }}</span>
                @if($suggestions->isNotEmpty())
                    <label class="ic-note" style="margin-left:auto;font-weight:600;display:flex;align-items:center;gap:6px">
                        <input type="checkbox" id="selectAll" checked style="width:20px;height:20px;accent-color:var(--ef-gold)"> Select all</label>
                @endif
            </h2>
            @if($suggestions->isEmpty())
                <p class="ic-note" style="text-align:center;padding:16px;margin:0">All stock is adequate — nothing to plan right now.</p>
            @else
                <table class="ic-table">
                    <thead><tr><th style="width:40px"></th><th>Item</th><th>Priority</th><th>Quantity</th><th>Unit cost (₹)</th><th>Notes</th></tr></thead>
                    <tbody>
                    @foreach($suggestions as $i => $item)
                        <tr>
                            <td data-l="Include">
                                <input type="hidden" name="items[{{ $i }}][inventory_item_id]" value="{{ $item->id }}">
                                <input type="checkbox" name="items[{{ $i }}][selected]" value="1" class="item-checkbox" checked aria-label="Include {{ $item->name }}" style="width:20px;height:20px;accent-color:var(--ef-gold)">
                            </td>
                            <td data-l="Item"><strong>{{ $item->name }}</strong>
                                <div class="ic-note">Stock {{ $item->current_stock }} / min {{ $item->minimum_stock }} {{ $item->unit }}</div></td>
                            <td data-l="Priority">
                                <select name="items[{{ $i }}][priority]" class="ic-select" style="min-width:110px">
                                    @foreach(['urgent', 'high', 'normal', 'low'] as $p)
                                        <option value="{{ $p }}" @selected($item->priority === $p)>{{ ucfirst($p) }}</option>
                                    @endforeach
                                </select></td>
                            <td data-l="Quantity"><div style="display:flex;align-items:center;gap:6px">
                                <input type="number" name="items[{{ $i }}][quantity]" class="ic-input" style="min-width:100px" value="{{ $item->suggested_quantity }}" min="0.001" step="0.001" inputmode="decimal" required>
                                <span class="ic-note">{{ $item->unit }}</span></div></td>
                            <td data-l="Unit cost"><input type="number" name="items[{{ $i }}][unit_cost]" class="ic-input" style="min-width:100px" value="{{ $item->average_cost }}" min="0" step="0.01" inputmode="decimal"></td>
                            <td data-l="Notes"><input type="text" name="items[{{ $i }}][notes]" class="ic-input" style="min-width:120px" maxlength="255" placeholder="Optional"></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="ic-sticky">
            <span class="ic-note" style="margin-right:auto">Ticked items are saved as a draft plan for approval.</span>
            <a href="{{ route('admin.purchase-plans.index') }}" class="ic-btn">Cancel</a>
            <button type="submit" class="ic-btn ic-btn--dark" @disabled($suggestions->isEmpty())><i class="bi bi-plus-circle"></i> Create plan</button>
        </div>
    </form>
</div>
@push('scripts')
<script>
document.getElementById('selectAll')?.addEventListener('change', function () {
    document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = this.checked);
});
</script>
@endpush
</x-admin-layout>
