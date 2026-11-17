<x-admin-layout title="Purchases">
@include('inventory._styles')
<div class="ic-wrap">
    <div class="ic-head">
        <h1>Purchases</h1>
        <a href="{{ route('inventory.purchases.create') }}" class="ic-btn ic-btn--gold"><i class="bi bi-plus-lg"></i> New purchase</a>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.inventory.bills.index') }}" class="ic-btn"><i class="bi bi-upload"></i> Upload bill</a>
        @endif
    </div>

    <div class="ic-tabs">
        <a class="ic-tab {{ ! $status ? '--on' : '' }}" href="{{ route('inventory.purchases.index') }}">All</a>
        @foreach(\App\Models\Purchase::STATUSES as $k => $l)
            @if(($counts[$k] ?? 0) > 0 || $status === $k)
                <a class="ic-tab {{ $status === $k ? '--on' : '' }}" href="{{ route('inventory.purchases.index', ['status' => $k]) }}">{{ $l }} · {{ $counts[$k] ?? 0 }}</a>
            @endif
        @endforeach
    </div>

    @forelse($purchases as $p)
        <a class="ic-list-card" href="{{ route('inventory.purchases.show', $p) }}">
            <div class="ic-list-main">
                <div class="ic-list-title">{{ $p->supplierLabel() }} <span class="ic-note">· {{ $p->reference() }}</span></div>
                <div class="ic-list-sub">{{ $p->purchase_date->format('d M Y') }} · {{ $p->items_count }} item(s) · by {{ $p->creator?->name }}
                    @if($p->invoice_number) · Bill {{ $p->invoice_number }} @endif</div>
            </div>
            <div style="text-align:right">
                <div style="font-weight:800">₹{{ number_format((float) $p->grand_total, 2) }}</div>
                @include('inventory._status', ['label' => $p->statusLabel(), 'tone' => \App\Models\Purchase::statusTone($p->status)])
            </div>
        </a>
    @empty
        <div class="ic-card ic-note" style="text-align:center;padding:32px">No purchases yet.</div>
    @endforelse
    {{ $purchases->links() }}
</div>
</x-admin-layout>
