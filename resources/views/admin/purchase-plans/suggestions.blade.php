<x-admin-layout title="Purchase Suggestions">
@include('inventory._styles')
<div class="ic-wrap">
    <a class="ic-back" href="{{ route('admin.purchase-plans.index') }}"><i class="bi bi-arrow-left"></i> Purchase plans</a>
    <div class="ic-head" style="margin-top:8px">
        <h1>Purchase Suggestions</h1>
        @if($suggestions->isNotEmpty())
            <a href="{{ route('admin.purchase-plans.create') }}" class="ic-btn ic-btn--gold"><i class="bi bi-plus-lg"></i> Create plan</a>
        @endif
    </div>
    <p class="ic-note" style="margin-top:-8px;margin-bottom:14px">Items below their minimum stock level.</p>

    @if($suggestions->isEmpty())
        <div class="ic-card ic-note" style="text-align:center;padding:32px">
            <i class="bi bi-check-circle" style="font-size:1.5rem;display:block;margin-bottom:8px;color:var(--ef-emerald)"></i>
            All stock levels are healthy. No purchases needed right now.
        </div>
    @else
        <div class="ic-card">
            <table class="ic-table">
                <thead><tr><th>Item</th><th>Category</th><th class="r">Current</th><th class="r">Minimum</th><th class="r">Short by</th><th class="r">Suggested</th><th>Priority</th><th class="r">Est. cost</th></tr></thead>
                <tbody>
                @foreach($suggestions as $item)
                    <tr>
                        <td data-l="Item"><a href="{{ route('admin.inventory.items.show', $item) }}" style="font-weight:700;color:var(--ef-ink);text-decoration:none">{{ $item->name }}</a>
                            @if($item->isOutOfStock()) @include('inventory._status', ['label' => 'Out', 'tone' => 'bad']) @endif</td>
                        <td data-l="Category" class="ic-note">{{ $item->category?->name }}</td>
                        <td data-l="Current" class="r">{{ $item->current_stock }} {{ $item->unit }}</td>
                        <td data-l="Minimum" class="r ic-note">{{ $item->minimum_stock }} {{ $item->unit }}</td>
                        <td data-l="Short by" class="r" style="color:var(--ef-danger);font-weight:700">{{ $item->deficit }} {{ $item->unit }}</td>
                        <td data-l="Suggested" class="r" style="font-weight:800">{{ $item->suggested_quantity }} {{ $item->unit }}</td>
                        <td data-l="Priority">@include('inventory._status', ['label' => ucfirst($item->priority), 'tone' => ['urgent' => 'bad', 'high' => 'warn', 'normal' => 'info', 'low' => 'neutral'][$item->priority] ?? 'neutral'])</td>
                        <td data-l="Est. cost" class="r ic-note">{{ $item->average_cost ? '₹'.number_format($item->average_cost * $item->suggested_quantity, 2) : '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
</x-admin-layout>
