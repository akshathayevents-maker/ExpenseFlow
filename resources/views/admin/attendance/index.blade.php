<x-admin-layout title="Attendance">

<x-ds.hero eyebrow="People / HR" title="Attendance"
    :meta="[['icon' => 'bi-calendar3', 'text' => $today->format('l, d F Y')]]">
</x-ds.hero>

@push('styles')
<style>
    .att-month-nav { display:flex; align-items:center; gap:10px; margin-bottom:16px; }
    .att-month-btn { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid var(--ef-border,#e5e7eb); color:var(--ef-ink,#1c1612); text-decoration:none; }
    .att-month-btn:hover { background:#f5f3ef; }
    .att-month-label { font-weight:700; font-size:.95rem; min-width:110px; text-align:center; }
    .att-table-wrap { overflow-x:auto; }
    .att-table { width:100%; border-collapse:collapse; font-size:.85rem; }
    .att-table th { text-align:left; padding:8px 10px; color:var(--ef-faint,#6b7280); font-weight:700; text-transform:uppercase; font-size:.68rem; letter-spacing:.04em; border-bottom:1px solid var(--ef-border,#e5e7eb); }
    .att-table td { padding:9px 10px; border-bottom:1px solid #f1efe9; vertical-align:middle; }
    .att-summary-row { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:16px; }
    .att-summary-chip { flex:1 1 120px; background:#fff; border:1px solid var(--ef-border,#e5e7eb); border-radius:10px; padding:10px 12px; }
    .att-summary-chip .n { font-size:1.25rem; font-weight:800; }
    .att-summary-chip .l { font-size:.72rem; color:var(--ef-faint,#6b7280); text-transform:uppercase; letter-spacing:.03em; }

    @media (max-width: 640px) {
        .att-table thead { display:none; }
        .att-table, .att-table tbody, .att-table tr, .att-table td { display:block; width:100%; }
        .att-table tr { border:1px solid var(--ef-border,#e5e7eb); border-radius:10px; margin-bottom:10px; padding:8px 10px; }
        .att-table td { border:none; padding:4px 0; }
        .att-table td[data-label]:before { content: attr(data-label); display:block; font-size:.65rem; text-transform:uppercase; color:var(--ef-faint,#6b7280); font-weight:700; }
    }
</style>
@endpush

<x-ds.card title="Today's Attendance">
    <div class="att-summary-row">
        <div class="att-summary-chip"><div class="n">{{ $todaySummary['present'] }}</div><div class="l">Present</div></div>
        <div class="att-summary-chip"><div class="n">{{ $todaySummary['absent'] }}</div><div class="l">Absent</div></div>
        <div class="att-summary-chip"><div class="n">{{ $todaySummary['on_leave'] }}</div><div class="l">On Leave</div></div>
        <div class="att-summary-chip"><div class="n">{{ $todaySummary['not_marked'] }}</div><div class="l">Not Marked</div></div>
    </div>

    <div class="att-table-wrap">
        <table class="att-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Status</th>
                    <th>First Half</th>
                    <th>Second Half</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($todayRows as $row)
                <tr>
                    <td data-label="Employee">
                        <a href="{{ route('admin.attendance.show', $row['employee']) }}" style="text-decoration:none;color:var(--ef-ink,#1c1612);font-weight:600">
                            {{ $row['employee']->name }}
                        </a>
                    </td>
                    <td data-label="Status"><x-status-badge :status="$row['status']" /></td>
                    <td data-label="First Half">{{ $row['first_half'] }}</td>
                    <td data-label="Second Half">{{ $row['second_half'] }}</td>
                    <td data-label="Action">
                        @if($row['has_pending_regularization'])
                            <a href="{{ route('admin.attendance-regularizations.index') }}" class="ef-btn" style="font-size:.75rem;padding:4px 10px">
                                <i class="bi bi-hourglass-split"></i> Pending Regularization
                            </a>
                        @else
                            <span style="color:var(--ef-faint,#6b7280)">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;padding:24px;color:var(--ef-faint,#6b7280)">No active employees found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ds.card>

<div style="height:20px"></div>

<x-ds.card title="Monthly Summary">
    <x-slot:head_right>
        <div class="att-month-nav">
            <a href="{{ route('admin.attendance.index', ['month' => $prevMonth]) }}" class="att-month-btn" aria-label="Previous month"><i class="bi bi-chevron-left"></i></a>
            <span class="att-month-label">{{ $month->format('F Y') }}</span>
            <a href="{{ route('admin.attendance.index', ['month' => $nextMonth]) }}" class="att-month-btn" aria-label="Next month"><i class="bi bi-chevron-right"></i></a>
        </div>
    </x-slot:head_right>

    <div class="att-table-wrap">
        <table class="att-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Present</th>
                    <th>Half Days</th>
                    <th>Leave</th>
                    <th>Absent</th>
                    <th>Regularizations</th>
                    <th>Payable Days</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($monthRows as $row)
                <tr>
                    <td data-label="Employee">{{ $row['employee']->name }}</td>
                    <td data-label="Present">{{ $row['summary']['present'] }}</td>
                    <td data-label="Half Days">{{ $row['summary']['half_day'] }}</td>
                    <td data-label="Leave">{{ $row['summary']['leave'] }}</td>
                    <td data-label="Absent">{{ $row['summary']['absent'] }}</td>
                    <td data-label="Regularizations">{{ $row['regularizations'] }}</td>
                    <td data-label="Payable Days">{{ number_format($row['summary']['payable_days'], 1) }}</td>
                    <td data-label="">
                        <a href="{{ route('admin.attendance.show', ['employee' => $row['employee'], 'month' => $month->format('Y-m')]) }}" style="font-size:.8rem;text-decoration:none;color:var(--ef-emerald,#0F7B5F);font-weight:600">
                            View <i class="bi bi-arrow-right"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:24px;color:var(--ef-faint,#6b7280)">No active employees found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ds.card>

</x-admin-layout>
