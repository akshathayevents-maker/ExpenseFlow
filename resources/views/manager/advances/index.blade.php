<x-admin-layout title="Advances">

<x-ds.hero eyebrow="Approvals" title="Employee Advances"
    :meta="[['icon' => 'bi-cash-coin', 'text' => 'Review, approve, and track advance repayments']]">
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
            <a href="{{ route('manager.advances.show', $advance) }}" class="adv-row">
                <div class="adv-row-top">
                    <div>
                        <div class="adv-row-amount">{{ $advance->user->name }} — ₹{{ number_format((float) $advance->requested_amount, 2) }}</div>
                        <div class="adv-row-meta">
                            Requested {{ $advance->created_at->format('d M Y') }}
                            @if($advance->approved_amount !== null)
                                · Approved: ₹{{ number_format((float) $advance->approved_amount, 2) }}
                            @endif
                            @if($advance->isPaid())
                                · Outstanding: <strong style="color:var(--ef-danger)">₹{{ number_format((float) $advance->outstanding_amount, 2) }}</strong>
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
                No advance requests found.
            </div>
        @endforelse
    </div>
</x-ds.card>

</x-admin-layout>
