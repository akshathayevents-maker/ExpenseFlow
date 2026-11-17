<x-admin-layout title="Loss Control">
@include('inventory._styles')
<div class="ic-wrap">
    <div class="ic-head">
        <a class="ic-back" href="{{ route('inventory.home') }}"><i class="bi bi-arrow-left"></i> Store</a>
        <h1>Loss control</h1>
    </div>
    @include('inventory.reports._filters')
    <div class="ic-tiles">
        <div class="ic-tile"><i class="bi bi-cart"></i><b>₹{{ number_format($totals['purchased']) }}</b><span>Purchased</span></div>
        <div class="ic-tile"><i class="bi bi-box-arrow-up"></i><b>₹{{ number_format($totals['issued']) }}</b><span>Issued to kitchen (net)</span></div>
        <div class="ic-tile"><i class="bi bi-trash3"></i><b>₹{{ number_format($totals['wasted']) }}</b><span>Recorded wastage</span></div>
        <div class="ic-tile"><i class="bi bi-question-octagon"></i><b>₹{{ number_format($totals['unexplained']) }}</b><span>Unexplained</span></div>
    </div>
    <div class="ic-card">
        <h2>Worth checking</h2>
        <p class="ic-note">These are patterns to look into, not conclusions. Open each to see the supporting transactions.</p>
        @foreach($signals as $s)
            <a class="ic-list-card" href="{{ route('inventory.reports.show', [$s['report']] + array_filter($f)) }}">
                <div class="ic-list-main"><div class="ic-list-title">{{ $s['label'] }}</div></div>
                <span class="ic-chip ic-chip--{{ $s['count'] > 0 ? 'warn' : 'ok' }}">{{ $s['count'] }}</span>
            </a>
        @endforeach
    </div>
    <div class="ic-card">
        <h2>All reports</h2>
        <div class="ic-tabs" style="flex-wrap:wrap">
            @foreach($reports as $k => $l)<a class="ic-tab" href="{{ route('inventory.reports.show', [$k] + array_filter($f)) }}">{{ $l }}</a>@endforeach
        </div>
    </div>
</div>
</x-admin-layout>
