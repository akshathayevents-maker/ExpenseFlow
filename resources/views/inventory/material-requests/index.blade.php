<x-admin-layout title="Material Requests">
@include('inventory._styles')
<div class="ic-wrap">
    <div class="ic-head">
        <h1>Material requests</h1>
        <a href="{{ route('inventory.material-requests.create') }}" class="ic-btn ic-btn--gold"><i class="bi bi-plus-lg"></i> Request materials</a>
    </div>
    <div class="ic-tabs">
        @foreach($tabs as $k => $l)
            <a class="ic-tab {{ $tab === $k ? '--on' : '' }}" href="{{ route('inventory.material-requests.index', ['tab' => $k]) }}">{{ $l }}@if(($counts[$k] ?? 0) > 0) · {{ $counts[$k] }}@endif</a>
        @endforeach
    </div>
    @forelse($requests as $r)
        <a class="ic-list-card" href="{{ route('inventory.material-requests.show', $r) }}">
            <div class="ic-list-main">
                <div class="ic-list-title">{{ $r->preparation_name ?: $r->forLabel() }} <span class="ic-note">· {{ $r->reference() }}</span></div>
                <div class="ic-list-sub">{{ $r->forLabel() }} · needed {{ $r->required_date->format('D d M') }}{{ $r->required_time ? ' ' . \Carbon\Carbon::parse($r->required_time)->format('h:i A') : '' }}
                    · {{ $r->items_count }} item(s) · {{ $r->requester?->name }}@if($r->guest_count) · {{ $r->guest_count }} people @endif</div>
            </div>
            <div style="text-align:right;display:flex;flex-direction:column;gap:4px;align-items:flex-end">
                @include('inventory._status', ['label' => $r->statusLabel(), 'tone' => \App\Models\MaterialRequest::statusTone($r->status)])
                @if($r->reconciliation_status === 'pending_review')<span class="ic-chip ic-chip--warn">review</span>@endif
            </div>
        </a>
    @empty
        <div class="ic-card ic-note" style="text-align:center;padding:32px">Nothing here.</div>
    @endforelse
    {{ $requests->links() }}
</div>
</x-admin-layout>
