<x-admin-layout title="Leave">

<x-ds.hero eyebrow="Employee Self-Service" title="Leave"
    :meta="[['icon' => 'bi-calendar-minus', 'text' => 'Track your leave requests']]">
    <x-slot:actions>
        <a href="{{ route('employee.leave.create') }}" class="ef-ds-btn --primary">
            <i class="bi bi-plus-lg"></i> <span>Apply Leave</span>
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
    .lv-summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
    .lv-summary-tile { background: var(--ef-surface-2, #f8f9fa); border-radius: 10px; padding: 12px; text-align: center; }
    .lv-summary-val { font-size: 1.3rem; font-weight: 800; }
    .lv-summary-lbl { font-size: .72rem; color: var(--ef-faint, #6b7280); text-transform: uppercase; letter-spacing: .03em; margin-top: 2px; }
    .lv-list { display: flex; flex-direction: column; gap: 10px; }
    .lv-row {
        display: flex; flex-direction: column; gap: 8px;
        padding: 12px 14px; border: 1px solid var(--ef-border, #e5e7eb); border-radius: 10px;
        text-decoration: none; color: inherit;
    }
    .lv-row-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; flex-wrap: wrap; }
    .lv-row-title { font-weight: 700; font-size: 1.0rem; overflow-wrap: anywhere; }
    .lv-row-meta { color: var(--ef-faint, #6b7280); font-size: .82rem; margin-top: 2px; }
    @media (min-width: 576px) {
        .lv-row { flex-direction: row; align-items: center; }
        .lv-row-top { flex: 1; }
    }
    .lv-bal-list { display: flex; flex-direction: column; }
    .lv-bal-row { padding: 10px 0; border-top: 1px solid var(--ef-border, #e5e7eb); }
    .lv-bal-row:first-child { border-top: none; }
    .lv-bal-name { font-weight: 700; margin-bottom: 6px; }
    .lv-bal-tiles { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .lv-bal-tile { background: var(--ef-surface-2, #f8f9fa); border-radius: 8px; padding: 8px; text-align: center; }
    .lv-bal-tile-val { font-size: 1.0rem; font-weight: 800; }
    .lv-bal-tile-lbl { font-size: .64rem; color: var(--ef-faint, #6b7280); text-transform: uppercase; letter-spacing: .03em; margin-top: 1px; }
</style>
@endpush

<x-ds.card title="Summary">
    <div class="lv-summary-grid">
        <div class="lv-summary-tile">
            <div class="lv-summary-val">{{ $summary['pending'] }}</div>
            <div class="lv-summary-lbl">Pending</div>
        </div>
        <div class="lv-summary-tile">
            <div class="lv-summary-val">{{ $summary['approved'] }}</div>
            <div class="lv-summary-lbl">Approved</div>
        </div>
        <div class="lv-summary-tile">
            <div class="lv-summary-val">{{ $summary['rejected'] }}</div>
            <div class="lv-summary-lbl">Rejected</div>
        </div>
    </div>
</x-ds.card>

<div class="mt-3">
<x-ds.card title="My Leave Balances">
    @if(empty($balances))
        <div style="text-align:center;padding:20px 12px;color:var(--ef-faint,#6b7280)">
            No active leave types configured yet.
        </div>
    @else
        <div class="lv-bal-list">
            @foreach($balances as $row)
                <div class="lv-bal-row">
                    <div class="lv-bal-name">{{ $row['leave_type']->name }}</div>
                    <div class="lv-bal-tiles">
                        <div class="lv-bal-tile">
                            <div class="lv-bal-tile-val">{{ rtrim(rtrim(number_format($row['allocated'], 1), '0'), '.') }}</div>
                            <div class="lv-bal-tile-lbl">Allocated</div>
                        </div>
                        <div class="lv-bal-tile">
                            <div class="lv-bal-tile-val">{{ rtrim(rtrim(number_format($row['used'], 1), '0'), '.') }}</div>
                            <div class="lv-bal-tile-lbl">Used</div>
                        </div>
                        <div class="lv-bal-tile">
                            <div class="lv-bal-tile-val">{{ rtrim(rtrim(number_format($row['pending'], 1), '0'), '.') }}</div>
                            <div class="lv-bal-tile-lbl">Pending</div>
                        </div>
                        <div class="lv-bal-tile">
                            <div class="lv-bal-tile-val" style="color:var(--ef-emerald,#0F7B5F)">{{ rtrim(rtrim(number_format($row['available'], 1), '0'), '.') }}</div>
                            <div class="lv-bal-tile-lbl">Available</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-ds.card>
</div>

<div class="mt-3">
<x-ds.card title="My Leave Requests">
    <div class="lv-list">
        @forelse($requests as $leaveRequest)
            @php $chip = $statusChips[$leaveRequest->status] ?? $statusChips['pending']; @endphp
            <a href="{{ route('employee.leave.show', $leaveRequest) }}" class="lv-row">
                <div class="lv-row-top">
                    <div>
                        <div class="lv-row-title">{{ $leaveRequest->leaveType->name ?? 'Leave' }}</div>
                        <div class="lv-row-meta">
                            {{ $leaveRequest->start_date->format('d M Y') }}
                            @if(!$leaveRequest->start_date->equalTo($leaveRequest->end_date))
                                &ndash; {{ $leaveRequest->end_date->format('d M Y') }}
                            @endif
                            &middot; {{ rtrim(rtrim(number_format((float) $leaveRequest->days_requested, 1), '0'), '.') }} day(s)
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
                No leave requests yet.
                <div style="margin-top:12px">
                    <a href="{{ route('employee.leave.create') }}" class="ef-btn ef-btn-dark">
                        <i class="bi bi-plus-lg"></i> Apply Leave
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</x-ds.card>
</div>

</x-admin-layout>
