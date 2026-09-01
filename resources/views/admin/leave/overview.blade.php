<x-admin-layout title="Leave Overview">

<x-ds.hero eyebrow="People / HR" title="Leave Overview"
    :meta="[['icon' => 'bi-calendar-range', 'text' => now()->format('l, d F Y')]]">
    <x-slot:actions>
        <a href="{{ route('admin.leave.requests.index') }}" class="ef-ds-btn"><i class="bi bi-list-check"></i> <span>Leave Requests</span></a>
    </x-slot:actions>
</x-ds.hero>

@push('styles')
<style>
    .lo-month-nav { display:flex; align-items:center; gap:10px; }
    .lo-month-btn { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid var(--ef-border,#e5e7eb); color:var(--ef-ink,#1c1612); text-decoration:none; }
    .lo-month-btn:hover { background:#f5f3ef; }
    .lo-month-label { font-weight:700; font-size:.95rem; min-width:110px; text-align:center; }
    .lo-table-wrap { overflow-x:auto; }
    .lo-table { width:100%; border-collapse:collapse; font-size:.85rem; }
    .lo-table th { text-align:left; padding:8px 10px; color:var(--ef-faint,#6b7280); font-weight:700; text-transform:uppercase; font-size:.68rem; letter-spacing:.04em; border-bottom:1px solid var(--ef-border,#e5e7eb); }
    .lo-table td { padding:9px 10px; border-bottom:1px solid #f1efe9; vertical-align:middle; }
    .lo-status-group { margin-bottom:18px; }
    .lo-status-head { display:flex; align-items:center; gap:8px; font-weight:700; font-size:.82rem; text-transform:capitalize; margin-bottom:8px; }

    @media (max-width: 640px) {
        .lo-table thead { display:none; }
        .lo-table, .lo-table tbody, .lo-table tr, .lo-table td { display:block; width:100%; }
        .lo-table tr { border:1px solid var(--ef-border,#e5e7eb); border-radius:10px; margin-bottom:10px; padding:8px 10px; }
        .lo-table td { border:none; padding:4px 0; }
        .lo-table td[data-label]:before { content: attr(data-label); display:block; font-size:.65rem; text-transform:uppercase; color:var(--ef-faint,#6b7280); font-weight:700; }
    }
</style>
@endpush

<x-ds.card title="Today's Leave">
    <div class="lo-table-wrap">
        <table class="lo-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Leave Type</th>
                    <th>Duration</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($todaysLeave as $leave)
                <tr>
                    <td data-label="Employee">{{ $leave->user->name }}</td>
                    <td data-label="Leave Type">{{ $leave->leaveType->name ?? '—' }}</td>
                    <td data-label="Duration">
                        @if($leave->is_half_day)
                            Half Day ({{ str_replace('_', ' ', $leave->half_day_period) }})
                        @else
                            {{ $leave->start_date->format('d M') }} – {{ $leave->end_date->format('d M') }}
                        @endif
                    </td>
                    <td data-label="Status"><x-status-badge :status="$leave->status" /></td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;padding:24px;color:var(--ef-faint,#6b7280)">No employees are on leave today.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ds.card>

<div style="height:20px"></div>

<x-ds.card title="Leave Requests This Month">
    <x-slot:head_right>
        <div class="lo-month-nav">
            <a href="{{ route('admin.leave.overview', ['month' => $prevMonth]) }}" class="lo-month-btn" aria-label="Previous month"><i class="bi bi-chevron-left"></i></a>
            <span class="lo-month-label">{{ $month->format('F Y') }}</span>
            <a href="{{ route('admin.leave.overview', ['month' => $nextMonth]) }}" class="lo-month-btn" aria-label="Next month"><i class="bi bi-chevron-right"></i></a>
        </div>
    </x-slot:head_right>

    @if($monthTotal === 0)
        <div style="text-align:center;padding:24px;color:var(--ef-faint,#6b7280)">No leave requests for this month.</div>
    @else
        @foreach(['pending', 'approved', 'rejected', 'cancelled'] as $statusKey)
            @if($monthByStatus[$statusKey]->isNotEmpty())
            <div class="lo-status-group">
                <div class="lo-status-head">
                    <x-status-badge :status="$statusKey" /> {{ $monthByStatus[$statusKey]->count() }} request(s)
                </div>
                <div class="lo-table-wrap">
                    <table class="lo-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Dates</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthByStatus[$statusKey] as $leave)
                            <tr>
                                <td data-label="Employee">{{ $leave->user->name }}</td>
                                <td data-label="Leave Type">{{ $leave->leaveType->name ?? '—' }}</td>
                                <td data-label="Dates">{{ $leave->start_date->format('d M') }} – {{ $leave->end_date->format('d M Y') }}</td>
                                <td data-label="">
                                    <a href="{{ route('admin.leave.requests.show', $leave) }}" style="font-size:.8rem;text-decoration:none;color:var(--ef-emerald,#0F7B5F);font-weight:600">
                                        View <i class="bi bi-arrow-right"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        @endforeach
    @endif
</x-ds.card>

</x-admin-layout>
