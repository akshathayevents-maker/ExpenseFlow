<x-admin-layout title="Purchase Suggestions">

<x-ds.hero eyebrow="Purchase Plans" title="Purchase Suggestions"
    :meta="[['icon' => 'bi-lightbulb', 'text' => 'Items below minimum stock — auto-generated list']]">
    @if($suggestions->isNotEmpty())
    <x-slot:actions>
        <a href="{{ route('admin.purchase-plans.create') }}" class="ef-btn ef-btn-dark">
            <i class="bi bi-plus-lg"></i> Create Purchase Plan
        </a>
    </x-slot:actions>
    @endif
</x-ds.hero>

@if($suggestions->isEmpty())
<x-ds.card>
    <div style="text-align:center;padding:32px;color:var(--ef-faint)">
        <i class="bi bi-check-circle" style="font-size:1.5rem;display:block;margin-bottom:8px;color:var(--ef-emerald)"></i>
        All stock levels are healthy. No purchases needed right now.
    </div>
</x-ds.card>
@else
<x-ds.card :no-pad="true">
    <div style="overflow-x:auto">
        <table class="ef-an-trend-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Category</th>
                    <th class="r">Current</th>
                    <th class="r">Minimum</th>
                    <th class="r">Deficit</th>
                    <th class="r">Suggested Order</th>
                    <th style="text-align:center">Priority</th>
                    <th class="r">Est. Cost</th>
                </tr>
            </thead>
            <tbody>
                @foreach($suggestions as $item)
                <tr>
                    <td>
                        <a href="{{ route('admin.inventory.items.show', $item) }}"
                           style="color:var(--ef-ink-2);text-decoration:none;font-weight:600">
                            {{ $item->name }}
                        </a>
                        @if($item->isOutOfStock())
                            <span style="background:rgba(220,53,69,.1);border-radius:4px;color:var(--ef-danger);font-size:.62rem;font-weight:700;margin-left:6px;padding:1px 6px;text-transform:uppercase">OUT</span>
                        @endif
                    </td>
                    <td style="color:var(--ef-faint);font-size:.84rem">{{ $item->category->name }}</td>
                    <td class="r" style="{{ $item->isOutOfStock() ? 'color:var(--ef-danger);font-weight:700' : 'color:var(--ef-amber);font-weight:600' }}">
                        {{ $item->current_stock }} {{ $item->unit }}
                    </td>
                    <td class="r" style="color:var(--ef-faint);font-size:.84rem">{{ $item->minimum_stock }} {{ $item->unit }}</td>
                    <td class="r" style="color:var(--ef-danger);font-weight:680">{{ $item->deficit }} {{ $item->unit }}</td>
                    <td class="r fw" style="color:var(--ef-emerald)">{{ $item->suggested_quantity }} {{ $item->unit }}</td>
                    <td style="text-align:center">
                        <span class="ef-ds-priority --{{ $item->priority }}">{{ $item->priority }}</span>
                    </td>
                    <td class="r" style="color:var(--ef-faint);font-size:.84rem">
                        @if($item->average_cost)
                            ₹{{ number_format($item->average_cost * $item->suggested_quantity, 2) }}
                        @else —
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-ds.card>
@endif

</x-admin-layout>
