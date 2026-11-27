<x-admin-layout title="{{ $purchasePlan->title }}">
@include('inventory._styles')
@include('admin.purchase-plans._tone')
@php($p = $purchasePlan)
<div class="ic-wrap">
    <a class="ic-back" href="{{ route('admin.purchase-plans.index') }}"><i class="bi bi-arrow-left"></i> Purchase plans</a>
    <div class="ic-head" style="margin-top:8px">
        <h1>{{ $p->title }}</h1>
        @include('inventory._status', ['label' => ucfirst($p->status), 'tone' => $planTone[$p->status] ?? 'neutral'])
    </div>


    <div class="ic-banner"><i class="bi bi-diagram-3"></i> Approve, buy, receive and close this plan on the workflow page.
        <a href="{{ route('inventory.plans.show', $p) }}" class="ic-btn ic-btn--sm" style="margin-left:auto">Open workflow</a></div>

    <div class="ic-card">
        <div class="ic-kv">
            <div><small>Planned date</small><strong>{{ $p->planned_date->format('d M Y') }}</strong></div>
            <div><small>Created by</small><strong>{{ $p->creator?->name ?? '—' }}</strong></div>
            @if($p->approver)
                <div><small>Approved by</small><strong>{{ $p->approver->name }}</strong></div>
                <div><small>Approved at</small><strong>{{ $p->approved_at?->format('d M Y, h:i A') }}</strong></div>
            @endif
            <div><small>Items</small><strong>{{ $p->items->count() }}</strong></div>
            <div><small>Estimated total</small><strong>{{ $p->estimatedTotal() > 0 ? '₹'.number_format($p->estimatedTotal(), 2) : '—' }}</strong></div>
        </div>
        @if($p->notes)<p class="ic-note" style="margin:12px 0 0"><i class="bi bi-sticky"></i> {{ $p->notes }}</p>@endif
    </div>

    <div class="ic-card">
        <h2>Items</h2>
        <table class="ic-table">
            <thead><tr><th>Item</th><th>Category</th><th>Priority</th><th class="r">Qty</th><th class="r">Unit cost</th><th class="r">Total</th><th>Notes</th></tr></thead>
            <tbody>
            @foreach($p->items as $item)
                <tr>
                    <td data-l="Item"><a href="{{ route('admin.inventory.items.show', $item->inventoryItem) }}" style="font-weight:700;color:var(--ef-ink);text-decoration:none">{{ $item->inventoryItem->name }}</a>
                        <div class="ic-note">Stock {{ $item->inventoryItem->current_stock }} {{ $item->inventoryItem->unit }}</div></td>
                    <td data-l="Category" class="ic-note">{{ $item->inventoryItem->category?->name }}</td>
                    <td data-l="Priority">@include('inventory._status', ['label' => ucfirst($item->priority), 'tone' => ['urgent' => 'bad', 'high' => 'warn', 'normal' => 'info', 'low' => 'neutral'][$item->priority] ?? 'neutral'])</td>
                    <td data-l="Qty" class="r" style="font-weight:700">{{ $item->suggested_quantity }} {{ $item->inventoryItem->unit }}</td>
                    <td data-l="Unit cost" class="r ic-note">{{ $item->estimated_unit_cost ? '₹'.number_format($item->estimated_unit_cost, 2) : '—' }}</td>
                    <td data-l="Total" class="r" style="font-weight:700">{{ $item->estimatedTotal() > 0 ? '₹'.number_format($item->estimatedTotal(), 2) : '—' }}</td>
                    <td data-l="Notes" class="ic-note">{{ $item->notes ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @if(in_array($p->status, ['approved', 'ordered'], true))
        <div class="ic-banner"><i class="bi bi-info-circle"></i>
            Buying these goods? Record them as a purchase so stock is updated.
            <a href="{{ route('inventory.purchases.create') }}" class="ic-btn ic-btn--sm" style="margin-left:auto">New purchase</a></div>
    @endif

    @if(in_array($p->status, ['draft', 'approved', 'ordered'], true))
        <div class="ic-sticky">
            <button type="button" class="ic-btn ic-btn--danger" data-bs-toggle="modal" data-bs-target="#ppCancel"><i class="bi bi-x-circle"></i> Cancel plan</button>
            <span style="margin-right:auto"></span>
            @php($next = ['draft' => [route('admin.purchase-plans.approve', $p), null, 'Approve plan', 'bi-check-circle'],
                          'approved' => [route('admin.purchase-plans.status', $p), 'ordered', 'Mark ordered', 'bi-truck'],
                          'ordered' => [route('admin.purchase-plans.status', $p), 'completed', 'Mark completed', 'bi-check2-all']][$p->status])
            <form method="POST" action="{{ $next[0] }}">
                @csrf @method('PATCH')
                @if($next[1])<input type="hidden" name="status" value="{{ $next[1] }}">@endif
                <button class="ic-btn ic-btn--dark"><i class="bi {{ $next[3] }}"></i> {{ $next[2] }}</button>
            </form>
        </div>

        <div class="modal fade" id="ppCancel" tabindex="-1" aria-labelledby="ppCancelTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
                <div class="modal-header"><h5 class="modal-title" id="ppCancelTitle">Cancel this plan?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">“{{ $p->title }}” will be marked cancelled. Stock is not affected. This cannot be undone.</div>
                <div class="modal-footer">
                    <button type="button" class="ic-btn ic-btn--sm" data-bs-dismiss="modal">Keep plan</button>
                    <form method="POST" action="{{ route('admin.purchase-plans.status', $p) }}">
                        @csrf @method('PATCH')<input type="hidden" name="status" value="cancelled">
                        <button class="ic-btn ic-btn--sm ic-btn--danger">Cancel plan</button>
                    </form>
                </div>
            </div></div>
        </div>
    @endif
</div>
</x-admin-layout>
