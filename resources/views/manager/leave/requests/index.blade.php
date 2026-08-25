<x-admin-layout title="Leave Requests">

<x-ds.hero eyebrow="Leave Management" title="Leave Requests"
    :meta="[['icon' => 'bi-calendar-minus', 'text' => 'Review, approve, and track employee leave']]">
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
    .lvr-filter-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 10px; }
    .lvr-filter-chip {
        display: inline-flex; align-items: center; gap: 5px;
        min-height: 34px; padding: 6px 14px; border-radius: 20px;
        border: 1.5px solid var(--ef-border, #e5e7eb); background: var(--ef-surface-2, #f8f9fa);
        color: var(--ef-muted, #475569); font-size: .82rem; font-weight: 650; text-decoration: none;
        white-space: nowrap;
    }
    .lvr-filter-chip.--active { background: var(--ef-emerald, #0F7B5F); border-color: var(--ef-emerald, #0F7B5F); color: #fff; }
    .lvr-list { display: flex; flex-direction: column; gap: 10px; }
    .lvr-row {
        display: flex; flex-direction: column; gap: 8px;
        padding: 12px 14px; border: 1px solid var(--ef-border, #e5e7eb); border-radius: 10px;
        text-decoration: none; color: inherit;
    }
    .lvr-row-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; flex-wrap: wrap; }
    .lvr-row-title { font-weight: 700; font-size: 1.0rem; overflow-wrap: anywhere; }
    .lvr-row-meta { color: var(--ef-faint, #6b7280); font-size: .82rem; margin-top: 2px; }
    @media (min-width: 576px) {
        .lvr-row { flex-direction: row; align-items: center; }
        .lvr-row-top { flex: 1; }
    }
</style>
@endpush

<div class="lvr-filter-row">
    <a href="{{ route('manager.leave.requests.index') }}" class="lvr-filter-chip {{ $status === '' ? '--active' : '' }}">All</a>
    <a href="{{ route('manager.leave.requests.index', ['status' => 'pending']) }}" class="lvr-filter-chip {{ $status === 'pending' ? '--active' : '' }}">Pending</a>
    <a href="{{ route('manager.leave.requests.index', ['status' => 'approved']) }}" class="lvr-filter-chip {{ $status === 'approved' ? '--active' : '' }}">Approved</a>
    <a href="{{ route('manager.leave.requests.index', ['status' => 'rejected']) }}" class="lvr-filter-chip {{ $status === 'rejected' ? '--active' : '' }}">Rejected</a>
    <a href="{{ route('manager.leave.requests.index', ['status' => 'cancelled']) }}" class="lvr-filter-chip {{ $status === 'cancelled' ? '--active' : '' }}">Cancelled</a>
</div>

<x-ds.card title="Leave Requests" :no-pad="true">
    <div class="lvr-list" style="padding:14px">
        @forelse($requests as $leaveRequest)
            @php $chip = $statusChips[$leaveRequest->status] ?? $statusChips['pending']; @endphp
            <a href="{{ route('manager.leave.requests.show', $leaveRequest) }}" class="lvr-row">
                <div class="lvr-row-top">
                    <div>
                        <div class="lvr-row-title">{{ $leaveRequest->user->name ?? 'Unknown' }} — {{ $leaveRequest->leaveType->name ?? 'Leave' }}</div>
                        <div class="lvr-row-meta">
                            {{ $leaveRequest->start_date->format('d M Y') }}
                            @if(!$leaveRequest->start_date->equalTo($leaveRequest->end_date))
                                &ndash; {{ $leaveRequest->end_date->format('d M Y') }}
                            @endif
                            &middot; {{ rtrim(rtrim(number_format((float) $leaveRequest->days_requested, 1), '0'), '.') }} day(s)
                            @if($leaveRequest->hasLop())
                                &middot; <span style="color:#9B2C2C">{{ rtrim(rtrim(number_format((float) $leaveRequest->lop_days, 1), '0'), '.') }} LOP</span>
                            @endif
                        </div>
                    </div>
                    <span style="display:inline-flex;align-items:center;border-radius:6px;font-size:.72rem;font-weight:700;padding:3px 10px;background:{{ $chip['bg'] }};color:{{ $chip['color'] }};text-transform:capitalize;white-space:nowrap">
                        {{ $leaveRequest->status }}
                    </span>
                </div>
            </a>
        @empty
            <div style="text-align:center;padding:40px 16px;color:var(--ef-faint,#6b7280)">
                <i class="bi bi-calendar-minus" style="font-size:1.5rem;display:block;margin-bottom:8px"></i>
                No leave requests found.
            </div>
        @endforelse
    </div>
</x-ds.card>

@if($requests->hasPages())
    <div style="display:flex;justify-content:center;margin-top:14px">{{ $requests->links() }}</div>
@endif

</x-admin-layout>
