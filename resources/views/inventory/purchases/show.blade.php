<x-admin-layout :title="$purchase->reference()">
@include('inventory._styles')
@php $u = auth()->user(); @endphp
<div class="ic-wrap">
    <div class="ic-head">
        <a class="ic-back" href="{{ route('inventory.purchases.index') }}"><i class="bi bi-arrow-left"></i> Purchases</a>
        <h1>{{ $purchase->reference() }} · {{ $purchase->supplierLabel() }}</h1>
        @include('inventory._status', ['label' => $purchase->statusLabel(), 'tone' => \App\Models\Purchase::statusTone($purchase->status)])
    </div>

    <div class="ic-card">
        <div class="ic-kv">
            <div><small>Purchase date</small><strong>{{ $purchase->purchase_date->format('d M Y') }}</strong></div>
            <div><small>Bill no.</small><strong>{{ $purchase->invoice_number ?: '—' }}</strong></div>
            <div><small>Entered by</small><strong>{{ $purchase->creator?->name }}</strong></div>
            <div><small>Approved by</small><strong>{{ $purchase->approver?->name ?? '—' }}</strong>
                @if($purchase->approved_at)<span class="ic-note"> {{ $purchase->approved_at->format('d M, h:i A') }}</span>@endif</div>
            <div><small>Payment</small><strong>{{ \App\Models\Purchase::PAYMENT_MODES[$purchase->payment_mode] ?? '—' }} · {{ ucfirst($purchase->payment_status) }}</strong></div>
            <div><small>Total</small><strong>₹{{ number_format((float) $purchase->grand_total, 2) }}</strong></div>
        </div>
        @if($purchase->rejection_reason)
            <div class="ic-err" style="margin:12px 0 0">Rejected by {{ $purchase->rejecter?->name }}: {{ $purchase->rejection_reason }}</div>
        @endif
        @if($purchase->bill)
            <p class="ic-note" style="margin:12px 0 0"><i class="bi bi-file-earmark-text"></i> Supported by uploaded bill
                @if($u->isAdmin())<a href="{{ route('admin.inventory.bills.show', $purchase->bill) }}">{{ $purchase->bill->original_filename }}</a>@else{{ $purchase->bill->original_filename }}@endif</p>
        @endif
        @if($purchase->notes)<p class="ic-note" style="margin:8px 0 0">{{ $purchase->notes }}</p>@endif
    </div>

    <div class="ic-card">
        <h2>Items</h2>
        <table class="ic-table">
            <thead><tr><th>Item</th><th class="r">Qty</th><th class="r">Rate</th><th class="r">Amount</th><th class="r">Received</th></tr></thead>
            <tbody>
            @foreach($purchase->items as $i)
                @php $factor = $i->quantity > 0 ? $i->base_quantity / $i->quantity : 1; @endphp
                <tr>
                    <td data-l="Item"><strong>{{ $i->item->name }}</strong>@if($i->description === \App\Services\Inventory\GoodsReceiptService::ADDED_ON_RECEIPT) <span class="ic-chip ic-chip--warn" style="font-size:.7rem">Added on receipt</span>@endif @if($i->description && strcasecmp($i->description, $i->item->name) && $i->description !== \App\Services\Inventory\GoodsReceiptService::ADDED_ON_RECEIPT)<div class="ic-note">Bill: {{ $i->description }}</div>@endif</td>
                    <td data-l="Qty" class="r">{{ $i->quantity + 0 }} {{ $i->unit }}@if($i->unit !== $i->item->unit)<div class="ic-note">= {{ $i->base_quantity + 0 }} {{ $i->item->unit }}</div>@endif</td>
                    <td data-l="Rate" class="r">₹{{ number_format($i->rate, 2) }}@if($i->tax_percent > 0)<div class="ic-note">+{{ $i->tax_percent + 0 }}% tax</div>@endif</td>
                    <td data-l="Amount" class="r">₹{{ number_format($i->amount, 2) }}</td>
                    <td data-l="Received" class="r">{{ round($i->received_base_quantity / $factor, 3) + 0 }} / {{ $i->quantity + 0 }} {{ $i->unit }}@if($i->closed_base_quantity > 0)<div class="ic-note" style="color:#7D5218">{{ round($i->closed_base_quantity / $factor, 3) + 0 }} not coming</div>@endif</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="ic-kv" style="margin-top:12px">
            <div><small>Items</small><strong>₹{{ number_format((float) $purchase->subtotal, 2) }}</strong></div>
            <div><small>Tax</small><strong>₹{{ number_format((float) $purchase->tax_amount, 2) }}</strong></div>
            <div><small>Discount</small><strong>₹{{ number_format((float) $purchase->discount_amount, 2) }}</strong></div>
            <div><small>Grand total</small><strong>₹{{ number_format((float) $purchase->grand_total, 2) }}</strong></div>
        </div>
    </div>

    @if($purchase->receipts->isNotEmpty())
        <div class="ic-card">
            <h2>Goods receipts</h2>
            @foreach($purchase->receipts as $r)
                @php
                    $value = $r->items->sum(function ($ri) {
                        $pi = $ri->purchaseItem; $f = $pi->quantity > 0 ? $pi->base_quantity / $pi->quantity : 1;
                        return ($ri->received_base_quantity / $f) * ($ri->actual_rate ?? $pi->rate) * (1 + $pi->tax_percent / 100);
                    });
                @endphp
                <div style="padding:10px 0;border-bottom:1px solid var(--ef-border)">
                    <div style="display:flex;flex-wrap:wrap;gap:6px 12px;align-items:center">
                        <strong>{{ $r->received_at->format('d M Y, h:i A') }}</strong>
                        <span class="ic-note" style="margin:0">received by {{ $r->receiver?->name }}</span>
                        @if($r->bill_path)
                            <a class="ic-btn ic-btn--sm" style="margin-left:auto" target="_blank" rel="noopener" href="{{ route('inventory.purchases.receipt-bill', [$purchase, $r]) }}">
                                <i class="bi {{ $r->billIsPdf() ? 'bi-file-earmark-pdf' : 'bi-image' }}"></i> View bill</a>
                        @endif
                    </div>
                    @if($r->bill_number || $r->bill_amount !== null)
                        <div class="ic-note">Bill {{ $r->bill_number ?: '—' }}@if($r->bill_amount !== null) · ₹{{ number_format($r->bill_amount, 2) }}@if(abs($r->bill_amount - $value) >= 1) <span style="color:#7D5218;font-weight:700">(goods ₹{{ number_format($value, 2) }})</span>@endif @endif</div>
                    @endif
                    <ul class="ic-note" style="margin:4px 0 0;padding-left:18px">
                        @foreach($r->items as $ri)
                            @php $pi = $ri->purchaseItem; @endphp
                            <li>{{ $pi->item->name }}: +{{ $ri->received_base_quantity + 0 }} {{ $pi->item->unit }}
                                @if($ri->actual_rate !== null && abs($ri->actual_rate - $pi->rate) >= 0.005) · rate ₹{{ number_format($ri->actual_rate, 2) }} (approved ₹{{ number_format($pi->rate, 2) }})@endif
                                @if($ri->rejected_base_quantity > 0) · rejected {{ $ri->rejected_base_quantity + 0 }}@endif
                                @if($ri->closed_base_quantity > 0) · {{ $ri->closed_base_quantity + 0 }} {{ $pi->item->unit }} not coming @endif
                                @if($ri->remarks) — {{ $ri->remarks }}@endif</li>
                        @endforeach
                    </ul>
                    @if($r->notes)<div class="ic-note">{{ $r->notes }}</div>@endif
                </div>
            @endforeach
        </div>
    @endif

    @can('receive', $purchase)
        @include('inventory.purchases._quick-receive')
    @endcan

    <div class="ic-card" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-start">
        @can('update', $purchase)
            <a class="ic-btn" href="{{ route('inventory.purchases.edit', $purchase) }}"><i class="bi bi-pencil"></i> Edit</a>
        @endcan
        @can('submit', $purchase)
            <form method="POST" data-ic-form action="{{ route('inventory.purchases.submit', $purchase) }}">@csrf
                <button class="ic-btn ic-btn--gold" type="submit"><i class="bi bi-send"></i> Submit for approval</button></form>
        @endcan
        @can('approve', $purchase)
            <form method="POST" data-ic-form action="{{ route('inventory.purchases.approve', $purchase) }}">@csrf
                <button class="ic-btn ic-btn--gold" type="submit"><i class="bi bi-check2-circle"></i> Approve</button></form>
            <form method="POST" data-ic-form action="{{ route('inventory.purchases.reject', $purchase) }}" style="display:flex;gap:8px;flex-wrap:wrap;flex:1;min-width:260px">@csrf
                <input class="ic-input" name="reason" placeholder="Reason for rejecting" required minlength="3" maxlength="500" style="flex:1">
                <button class="ic-btn ic-btn--danger" type="submit">Reject</button></form>
        @endcan
        @can('complete', $purchase)
            <form method="POST" data-ic-form action="{{ route('inventory.purchases.complete', $purchase) }}">@csrf
                <button class="ic-btn" type="submit"><i class="bi bi-flag"></i> Close purchase</button></form>
        @endcan
        @can('cancel', $purchase)
            <form method="POST" data-ic-form action="{{ route('inventory.purchases.cancel', $purchase) }}">@csrf
                <button class="ic-btn ic-btn--danger" type="submit"><i class="bi bi-x-circle"></i> Cancel purchase</button></form>
        @endcan
    </div>
</div>
</x-admin-layout>
