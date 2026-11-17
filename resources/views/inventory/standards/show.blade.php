<x-admin-layout :title="'Standard · ' . $h['preparation']">
@include('inventory._styles')
@php
    $cur = $h['current'] ? $h['current']->ingredients->pluck('quantity_per_guest', 'inventory_item_id') : collect();
    $q = fn ($n) => rtrim(rtrim(number_format((float) $n, 5, '.', ''), '0'), '.') ?: '0';
@endphp
<div class="ic-wrap">
    <div class="ic-head">
        <a class="ic-back" href="{{ route('inventory.standards.index') }}"><i class="bi bi-arrow-left"></i> Standards</a>
        <h1>{{ $h['preparation'] }}</h1>
        @if($h['current'])<span class="ic-chip ic-chip--ok">Current v{{ $h['current']->version }}</span>@endif
    </div>
    <p class="ic-note">Based on {{ $h['requests'] }} recorded preparation(s) for {{ collect($h['guests'])->join(', ') ?: '—' }} people. “Out per person” = used + wasted + unexplained ÷ people.</p>
    @if($errors->any())<div class="ic-err"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <form method="POST" data-ic-form action="{{ route('inventory.standards.store') }}">
        @csrf
        <input type="hidden" name="name" value="{{ $h['preparation'] }}">
        <div class="ic-card" style="overflow-x:auto">
            <table class="ic-table">
                <thead><tr><th>Item</th><th class="r">Times</th><th class="r">Median out / person</th><th class="r">Min – max</th><th>Standard / person</th><th class="r">100</th><th class="r">250</th><th class="r">500</th></tr></thead>
                <tbody>
                @foreach($h['rows'] as $r)
                    @php $val = $cur[$r['item_id']] ?? $r['median']; @endphp
                    <tr data-std>
                        <td data-l="Item"><strong>{{ $r['item'] }}</strong> <span class="ic-note">{{ $r['unit'] }}</span></td>
                        <td data-l="Times" class="r">{{ $r['times'] }}</td>
                        <td data-l="Median" class="r">{{ $q($r['median']) }}</td>
                        <td data-l="Range" class="r">{{ $q($r['min']) }} – {{ $q($r['max']) }}</td>
                        <td data-l="Standard"><input class="ic-input" style="max-width:140px" type="number" step="0.00001" min="0" name="lines[{{ $r['item_id'] }}]" value="{{ $q($val) }}" data-pg></td>
                        <td data-l="100" class="r" data-n="100"></td><td data-l="250" class="r" data-n="250"></td><td data-l="500" class="r" data-n="500"></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="ic-fields" style="margin-top:12px">
                <div><label class="ic-label">Add another item</label>
                    <select class="ic-select" name="extra_item_id"><option value="">—</option>@foreach($items as $i)<option value="{{ $i->id }}">{{ $i->name }} ({{ $i->unit }})</option>@endforeach</select></div>
                <div><label class="ic-label">Per person</label><input class="ic-input" type="number" step="0.00001" min="0" name="extra_per_guest"></div>
                <div style="grid-column:1/-1"><label class="ic-label">Notes (method, batch size…)</label><input class="ic-input" name="notes" maxlength="500"></div>
            </div>
        </div>
        <div class="ic-sticky">
            <span class="ic-total" style="font-size:1rem">Saving creates version {{ ($h['current']?->version ?? 0) + 1 }}</span>
            <button class="ic-btn ic-btn--gold" type="submit"><i class="bi bi-save"></i> Save standard</button>
        </div>
    </form>
</div>
@push('scripts')
<script>
document.querySelectorAll('[data-std]').forEach(row => {
    const inp = row.querySelector('[data-pg]');
    const paint = () => row.querySelectorAll('[data-n]').forEach(td => { const v = (parseFloat(inp.value) || 0) * parseInt(td.dataset.n, 10); td.textContent = Math.round(v * 1000) / 1000; });
    inp.addEventListener('input', paint); paint();
});
</script>
@endpush
</x-admin-layout>
