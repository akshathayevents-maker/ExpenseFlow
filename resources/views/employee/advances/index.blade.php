<x-admin-layout title="My Advances">

<x-ds.hero eyebrow="Employee Self-Service" title="My Advances"
    :meta="[['icon' => 'bi-cash-coin', 'text' => 'Track your advance requests and repayments']]">
    <x-slot:actions>
        <a href="{{ route('employee.advances.create') }}" class="ef-ds-btn --primary">
            <i class="bi bi-plus-lg"></i> <span>Request Advance</span>
        </a>
    </x-slot:actions>
</x-ds.hero>

@php
$statusChips = [
    'pending'   => ['bg' => 'rgba(216,154,61,.13)', 'color' => '#7D5218'],
    'approved'  => ['bg' => 'rgba(15,123,95,.11)',  'color' => '#0A5240'],
    'rejected'  => ['bg' => 'rgba(200,75,68,.11)',  'color' => '#9B2C2C'],
    'cancelled' => ['bg' => 'rgba(100,116,139,.11)','color' => '#334155'],
];
@endphp

@push('styles')
<style>
    .adv-list { display: flex; flex-direction: column; gap: 10px; }
    .adv-row {
        display: flex; flex-direction: column; gap: 8px;
        padding: 12px 14px; border: 1px solid var(--ef-border, #e5e7eb); border-radius: 10px;
        text-decoration: none; color: inherit;
    }
    .adv-row-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; flex-wrap: wrap; }
    .adv-row-amount { font-weight: 700; font-size: 1.05rem; }
    .adv-row-meta { color: var(--ef-faint, #6b7280); font-size: .82rem; margin-top: 2px; }
    .adv-row-outstanding { font-size: .84rem; font-weight: 600; }
    @media (min-width: 576px) {
        .adv-row { flex-direction: row; align-items: center; }
        .adv-row-top { flex: 1; }
    }
</style>
@endpush

<x-ds.card title="Advance Requests">
    <div class="adv-list">
        @forelse($advances as $advance)
            @php $chip = $statusChips[$advance->request_status] ?? $statusChips['pending']; @endphp
            <a href="{{ route('employee.advances.show', $advance) }}" class="adv-row">
                <div class="adv-row-top">
                    <div>
                        <div class="adv-row-amount">₹{{ number_format((float) $advance->requested_amount, 2) }}</div>
                        <div class="adv-row-meta">
                            Requested {{ $advance->created_at->format('d M Y') }}
                            @if($advance->isPaid())
                                · Outstanding: <span class="adv-row-outstanding" style="color:var(--ef-danger)">₹{{ number_format((float) $advance->outstanding_amount, 2) }}</span>
                            @endif
                        </div>
                    </div>
                    <span style="display:inline-flex;align-items:center;border-radius:6px;font-size:.72rem;font-weight:700;padding:3px 10px;background:{{ $chip['bg'] }};color:{{ $chip['color'] }};text-transform:capitalize;white-space:nowrap">
                        {{ $advance->request_status }}
                    </span>
                </div>
            </a>
        @empty
            <div style="text-align:center;padding:40px 16px;color:var(--ef-faint,#6b7280)">
                <i class="bi bi-cash-coin" style="font-size:1.5rem;display:block;margin-bottom:8px"></i>
                No advance requests yet.
                <div style="margin-top:12px">
                    <a href="{{ route('employee.advances.create') }}" class="ef-btn ef-btn-dark">
                        <i class="bi bi-plus-lg"></i> Request Advance
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</x-ds.card>

</x-admin-layout>
