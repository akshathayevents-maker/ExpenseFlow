<x-admin-layout title="Store Stock">
@include('inventory._styles')
@php $q = fn ($n) => rtrim(rtrim(number_format((float) $n, 3, '.', ''), '0'), '.') ?: '0'; @endphp
<div class="ic-wrap">
    <div class="ic-head">
        <a class="ic-back" href="{{ route('inventory.home') }}"><i class="bi bi-arrow-left"></i> Store</a>
        <h1>Central store stock</h1>
    </div>
    <form method="GET" class="ic-card" style="display:flex;gap:10px;flex-wrap:wrap">
        <input class="ic-input" name="q" value="{{ $search }}" placeholder="Search item" style="flex:1;min-width:180px">
        <select class="ic-select" name="show" style="max-width:200px"><option value="">All items</option><option value="low" @selected(request('show') === 'low')>Low / out of stock</option></select>
        <button class="ic-btn" type="submit">Show</button>
    </form>
    <p class="ic-note"><strong>Physical</strong> = on the shelf. <strong>Awaiting handover</strong> = approved, already deducted, not yet collected. <strong>Available</strong> = free to approve.</p>
    <div class="ic-card" style="padding:8px">
        <table class="ic-table">
            <thead><tr><th>Item</th><th class="r">Physical</th><th class="r">Awaiting handover</th><th class="r">Available</th><th class="r">Incoming</th><th class="r">Requested (pending)</th></tr></thead>
            <tbody>
            @forelse($items as $i)
                <tr>
                    <td data-l="Item"><strong>@if(auth()->user()->isAdmin())<a href="{{ route('admin.inventory.items.show', $i) }}">{{ $i->name }}</a>@else{{ $i->name }}@endif</strong>
                        @if($i->isOutOfStock())<span class="ic-chip ic-chip--bad">out</span>@elseif($i->isLowStock())<span class="ic-chip ic-chip--warn">low</span>@endif
                        <div class="ic-note">{{ $i->category?->name }}</div></td>
                    <td data-l="Physical" class="r">{{ $q($i->physical) }} {{ $i->unit }}</td>
                    <td data-l="Awaiting" class="r">{{ $i->awaiting > 0 ? $q($i->awaiting) : '—' }}</td>
                    <td data-l="Available" class="r" style="font-weight:800">{{ $q($i->current_stock) }} {{ $i->unit }}</td>
                    <td data-l="Incoming" class="r">{{ $i->incoming > 0 ? $q($i->incoming) : '—' }}</td>
                    <td data-l="Pending" class="r" style="{{ $i->pending > $i->current_stock ? 'color:var(--ef-danger);font-weight:700' : '' }}">{{ $i->pending > 0 ? $q($i->pending) : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="ic-note" style="text-align:center;padding:24px">No items.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-admin-layout>
