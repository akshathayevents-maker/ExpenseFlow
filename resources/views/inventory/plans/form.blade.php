<x-admin-layout :title="$plan ? 'Edit '.$plan->reference() : 'Create Purchase Plan'">
@include('inventory._styles')
@include('inventory._item-picker')
{{-- One plan form for everyone: tick the suggested (low-stock) items, adjust them, add any other item, then save or send for approval. --}}
@php
    $isAdmin = auth()->user()->isAdmin();
    $suggested = collect($rows)->where('suggested', true)->count();
@endphp
<div class="ic-wrap">
    <a class="ic-back" href="{{ $plan ? route('inventory.plans.show', $plan) : route('inventory.plans.index') }}"><i class="bi bi-arrow-left"></i> {{ $plan ? $plan->reference() : 'Purchase plans' }}</a>
    <div class="ic-head" style="margin-top:8px"><h1>{{ $plan ? 'Edit purchase plan' : 'New Purchase Plan' }}</h1></div>

    @if($plan?->status === 'rejected')<div class="ic-err"><strong>Sent back:</strong> {{ $plan->rejection_reason }}</div>@endif
    @if($errors->any())
        <div class="ic-err"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ $plan ? route('inventory.plans.update', $plan) : route('inventory.plans.store') }}" id="ppForm">
        @csrf @if($plan) @method('PUT') @endif
        <div class="ic-card">
            <h2>Plan details</h2>
            <div class="ic-fields">
                <div style="grid-column:1/-1">
                    <label class="ic-label" for="title">Plan title *</label>
                    <input type="text" id="title" name="title" class="ic-input" value="{{ old('title', $plan?->title ?? 'Purchase Plan — '.now()->format('d M Y')) }}" required maxlength="150">
                </div>
                <div>
                    <label class="ic-label" for="planned_date">Needed by *</label>
                    <input type="date" id="planned_date" name="planned_date" class="ic-input" value="{{ old('planned_date', $plan?->planned_date?->toDateString() ?? now()->addDay()->toDateString()) }}" required>
                </div>
                <div style="grid-column:1/-1">
                    <label class="ic-label" for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="2" class="ic-input" maxlength="1000" placeholder="Why / for which event — optional">{{ old('notes', $plan?->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="ic-card">
            <h2 style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">Items
                @if($suggested)<span class="ic-chip ic-chip--neutral">{{ $suggested }} suggested — low stock</span>@endif
                @if(count($rows))
                    <label class="ic-note" style="margin-left:auto;font-weight:600;display:flex;align-items:center;gap:6px">
                        <input type="checkbox" id="selectAll" style="width:20px;height:20px;accent-color:var(--ef-gold)"> Select all</label>
                @endif
            </h2>
            <p class="ic-note" style="margin:-4px 0 10px">Tick only what you want to buy and adjust the quantity (stock unit). Unticked rows are ignored.</p>
            @if(count($rows) > 8)
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:10px">
                    <input type="search" id="ppFilter" class="ic-input" style="flex:1 1 220px;min-width:0" placeholder="Find in this list…" aria-label="Find in this list" autocomplete="off">
                    <label class="ic-note" style="display:flex;align-items:center;gap:6px;font-weight:600"><input type="checkbox" id="ppOnlyTicked" style="width:18px;height:18px;accent-color:var(--ef-gold)"> Show ticked only</label>
                </div>
            @endif
            <table class="ic-table" id="ppRows">
                <thead><tr><th style="width:40px"></th><th>Item</th><th>Priority</th><th>Quantity</th><th>Unit cost (₹)</th><th>Notes</th></tr></thead>
                <tbody>
                @foreach($rows as $i => $r)
                    <tr data-row>
                        <td data-l="Include">
                            <input type="hidden" name="items[{{ $i }}][inventory_item_id]" value="{{ $r['inventory_item_id'] }}">
                            <input type="checkbox" name="items[{{ $i }}][selected]" value="1" class="item-checkbox" @checked($r['selected']) aria-label="Include {{ $r['name'] }}" style="width:20px;height:20px;accent-color:var(--ef-gold)">
                        </td>
                        <td data-l="Item"><strong>{{ $r['name'] }}</strong>
                            <div class="ic-note">Stock {{ rtrim(rtrim(number_format($r['stock'], 3, '.', ''), '0'), '.') ?: '0' }}@if($r['min'] > 0) / min {{ rtrim(rtrim(number_format($r['min'], 3, '.', ''), '0'), '.') }}@endif {{ $r['unit'] }}</div></td>
                        <td data-l="Priority">
                            <select name="items[{{ $i }}][priority]" class="ic-select" style="min-width:110px">
                                @foreach(['urgent', 'high', 'normal', 'low'] as $p)<option value="{{ $p }}" @selected(($r['priority'] ?? 'normal') === $p)>{{ ucfirst($p) }}</option>@endforeach
                            </select></td>
                        <td data-l="Quantity"><div style="display:flex;align-items:center;gap:6px">
                            <input type="number" name="items[{{ $i }}][quantity]" class="ic-input" style="min-width:100px" value="{{ $r['quantity'] }}" min="0" step="0.001" inputmode="decimal">
                            <span class="ic-note">{{ $r['unit'] }}</span></div></td>
                        <td data-l="Unit cost"><input type="number" name="items[{{ $i }}][unit_cost]" class="ic-input" style="min-width:100px" value="{{ $r['unit_cost'] }}" min="0" step="0.01" inputmode="decimal"></td>
                        <td data-l="Notes"><input type="text" name="items[{{ $i }}][notes]" class="ic-input" style="min-width:120px" maxlength="255" value="{{ $r['notes'] }}" placeholder="Optional"></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            @if(! count($rows))<p class="ic-note" style="text-align:center;padding:10px;margin:0">All stock is adequate — add the items you need below.</p>@endif

            <div style="display:flex;gap:8px;align-items:flex-start;margin-top:12px;flex-wrap:wrap">
                <div style="flex:1 1 260px;min-width:0">
                    <label class="ic-label" for="ppAdd">Add another item</label>
                    <input type="text" id="ppAdd" class="ic-input" placeholder="Search item — English / தமிழ்" autocomplete="off">
                </div>
            </div>
        </div>

        <div class="ic-sticky">
            <span class="ic-note" style="margin-right:auto"><b data-count>0</b> ticked · estimate <b data-est>—</b></span>
            <a href="{{ $plan ? route('inventory.plans.show', $plan) : route('inventory.plans.index') }}" class="ic-btn">Cancel</a>
            <button type="submit" class="ic-btn" name="submit" value="0"><i class="bi bi-save"></i> Save draft</button>
            <button type="submit" class="ic-btn ic-btn--dark" name="submit" value="1"><i class="bi bi-send"></i> {{ $isAdmin ? 'Create & send for approval' : 'Send for approval' }}</button>
        </div>
    </form>
</div>

<template id="ppRowTpl">
    <tr data-row>
        <td data-l="Include"><input type="hidden" data-f="inventory_item_id"><input type="checkbox" data-f="selected" value="1" class="item-checkbox" checked style="width:20px;height:20px;accent-color:var(--ef-gold)"></td>
        <td data-l="Item"><strong data-name></strong><div class="ic-note" data-stock></div></td>
        <td data-l="Priority"><select data-f="priority" class="ic-select" style="min-width:110px"><option value="urgent">Urgent</option><option value="high">High</option><option value="normal" selected>Normal</option><option value="low">Low</option></select></td>
        <td data-l="Quantity"><div style="display:flex;align-items:center;gap:6px"><input type="number" data-f="quantity" class="ic-input" style="min-width:100px" min="0" step="0.001" inputmode="decimal"><span class="ic-note" data-unit></span></div></td>
        <td data-l="Unit cost"><input type="number" data-f="unit_cost" class="ic-input" style="min-width:100px" min="0" step="0.01" inputmode="decimal"></td>
        <td data-l="Notes"><input type="text" data-f="notes" class="ic-input" style="min-width:120px" maxlength="255" placeholder="Optional"></td>
    </tr>
</template>

@push('scripts')
<script>
(() => {
    const form = document.getElementById('ppForm'), body = document.querySelector('#ppRows tbody'), all = document.getElementById('selectAll');
    let n = {{ count($rows) }};
    function sum() {
        let c = 0, est = 0;
        body.querySelectorAll('[data-row]').forEach(r => {
            if (!r.querySelector('.item-checkbox').checked) return;
            c++; est += (parseFloat(r.querySelector('[name$="[quantity]"]').value) || 0) * (parseFloat(r.querySelector('[name$="[unit_cost]"]').value) || 0);
        });
        form.querySelector('[data-count]').textContent = c;
        form.querySelector('[data-est]').textContent = est ? '₹' + Math.round(est).toLocaleString('en-IN') : '—';
        if (all) { const boxes = body.querySelectorAll('[data-row]:not([hidden]) .item-checkbox'); all.checked = boxes.length > 0 && [...boxes].every(b => b.checked); }
    }
    // "Select all" acts on the rows currently shown (after filtering)
    all?.addEventListener('change', () => { body.querySelectorAll('[data-row]:not([hidden]) .item-checkbox').forEach(cb => cb.checked = all.checked); sum(); });
    const flt = document.getElementById('ppFilter'), only = document.getElementById('ppOnlyTicked');
    function filterRows() {
        const t = (flt?.value || '').trim().toLowerCase(), o = only?.checked;
        body.querySelectorAll('[data-row]').forEach(r => {
            const name = (r.querySelector('strong')?.textContent || '').toLowerCase();
            r.hidden = (t && !name.includes(t)) || (o && !r.querySelector('.item-checkbox').checked);
        });
    }
    flt?.addEventListener('input', filterRows); only?.addEventListener('change', filterRows);
    form.addEventListener('input', sum); form.addEventListener('change', sum);
    const addIn = document.getElementById('ppAdd');
    window.IcItemPicker.attach(addIn, {
        onPick(it) {
            const have = [...body.querySelectorAll('input[name$="[inventory_item_id]"]')].find(h => String(h.value) === String(it.id));
            if (have) { const row = have.closest('tr'); row.querySelector('.item-checkbox').checked = true; row.querySelector('[name$="[quantity]"]').focus(); sum(); return; }
            const tr = document.getElementById('ppRowTpl').content.firstElementChild.cloneNode(true), i = n++;
            tr.querySelectorAll('[data-f]').forEach(f => f.name = `items[${i}][${f.dataset.f}]`);
            tr.querySelector('[data-f=inventory_item_id]').value = it.id;
            tr.querySelector('[data-name]').textContent = it.name;
            tr.querySelector('[data-unit]').textContent = it.unit;
            tr.querySelector('[data-stock]').textContent = 'Added by you';
            body.appendChild(tr);
            document.querySelector('#ppRows').hidden = false;
            setTimeout(() => { addIn.value = ''; addIn.dispatchEvent(new Event('input')); tr.querySelector('[data-f=quantity]').focus(); }, 0);
            sum();
        },
    });
    sum();
})();
</script>
@endpush
</x-admin-layout>
