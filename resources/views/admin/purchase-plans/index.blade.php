<x-admin-layout title="Purchase Plans">
@include('inventory._styles')
@include('admin.purchase-plans._tone')
<div class="ic-wrap">
    <div class="ic-head">
        <h1>Purchase Plans</h1>
        <a href="{{ route('admin.purchase-plans.suggestions') }}" class="ic-btn"><i class="bi bi-lightbulb"></i> Suggestions</a>
        <a href="{{ route('admin.purchase-plans.create') }}" class="ic-btn ic-btn--gold"><i class="bi bi-plus-lg"></i> New plan</a>
    </div>
    <p class="ic-note" style="margin-top:-8px;margin-bottom:14px">
        A plan is a shopping list built from low-stock items. It does not change stock —
        when goods are bought, record them in <a href="{{ route('inventory.purchases.index') }}">Purchases</a>.
    </p>

    <div class="ic-tabs">
        <a class="ic-tab {{ ! $status ? '--on' : '' }}" href="{{ route('admin.purchase-plans.index') }}">All · {{ $stats['total'] }}</a>
        @foreach(['draft' => 'Draft', 'approved' => 'Approved', 'ordered' => 'Ordered', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $k => $l)
            @if($stats[$k] > 0 || $status === $k)
                <a class="ic-tab {{ $status === $k ? '--on' : '' }}" href="{{ route('admin.purchase-plans.index', ['status' => $k]) }}">{{ $l }} · {{ $stats[$k] }}</a>
            @endif
        @endforeach
    </div>

    @forelse($plans as $plan)
        @php($est = $plan->estimatedTotal())
        <a class="ic-list-card" href="{{ route('admin.purchase-plans.show', $plan) }}">
            <div class="ic-list-main">
                <div class="ic-list-title">{{ $plan->title }}</div>
                <div class="ic-list-sub">Planned {{ $plan->planned_date->format('d M Y') }} · {{ $plan->items->count() }} item(s) · by {{ $plan->creator?->name ?? '—' }}</div>
            </div>
            <div style="text-align:right">
                <div style="font-weight:800">{{ $est > 0 ? '₹'.number_format($est, 2) : '—' }}</div>
                @include('inventory._status', ['label' => ucfirst($plan->status), 'tone' => $planTone[$plan->status] ?? 'neutral'])
            </div>
        </a>
    @empty
        <div class="ic-card ic-note" style="text-align:center;padding:32px">
            {{ $status ? 'No '.$status.' plans.' : 'No purchase plans yet. Create one from low-stock suggestions.' }}
        </div>
    @endforelse
    {{ $plans->links() }}
</div>
</x-admin-layout>
