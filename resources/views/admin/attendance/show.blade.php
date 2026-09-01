<x-admin-layout title="Attendance · {{ $employee->name }}">

<x-ds.hero eyebrow="People / HR · Attendance" title="{{ $employee->name }}"
    :meta="[['icon' => 'bi-calendar3', 'text' => $month->format('F Y')]]">
    <x-slot:actions>
        <a href="{{ route('admin.attendance.index') }}" class="ef-ds-btn"><i class="bi bi-arrow-left"></i> <span>Back to Attendance</span></a>
    </x-slot:actions>
</x-ds.hero>

@push('styles')
<style>
    .att-month-nav { display:flex; align-items:center; gap:10px; }
    .att-month-btn { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid var(--ef-border,#e5e7eb); color:var(--ef-ink,#1c1612); text-decoration:none; }
    .att-month-btn:hover { background:#f5f3ef; }
    .att-month-label { font-weight:700; font-size:.95rem; min-width:110px; text-align:center; }
    .att-summary-row { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:16px; }
    .att-summary-chip { flex:1 1 110px; background:#fff; border:1px solid var(--ef-border,#e5e7eb); border-radius:10px; padding:10px 12px; }
    .att-summary-chip .n { font-size:1.2rem; font-weight:800; }
    .att-summary-chip .l { font-size:.68rem; color:var(--ef-faint,#6b7280); text-transform:uppercase; letter-spacing:.03em; }
    .att-table-wrap { overflow-x:auto; }
    .att-table { width:100%; border-collapse:collapse; font-size:.85rem; }
    .att-table th { text-align:left; padding:8px 10px; color:var(--ef-faint,#6b7280); font-weight:700; text-transform:uppercase; font-size:.68rem; letter-spacing:.04em; border-bottom:1px solid var(--ef-border,#e5e7eb); }
    .att-table td { padding:9px 10px; border-bottom:1px solid #f1efe9; vertical-align:middle; }

    @media (max-width: 640px) {
        .att-table thead { display:none; }
        .att-table, .att-table tbody, .att-table tr, .att-table td { display:block; width:100%; }
        .att-table tr { border:1px solid var(--ef-border,#e5e7eb); border-radius:10px; margin-bottom:10px; padding:8px 10px; }
        .att-table td { border:none; padding:4px 0; }
        .att-table td[data-label]:before { content: attr(data-label); display:block; font-size:.65rem; text-transform:uppercase; color:var(--ef-faint,#6b7280); font-weight:700; }
    }
</style>
@endpush

<x-ds.card title="Month Summary">
    <x-slot:head_right>
        <div class="att-month-nav">
            <a href="{{ route('admin.attendance.show', ['employee' => $employee, 'month' => $prevMonth]) }}" class="att-month-btn" aria-label="Previous month"><i class="bi bi-chevron-left"></i></a>
            <span class="att-month-label">{{ $month->format('F Y') }}</span>
            <a href="{{ route('admin.attendance.show', ['employee' => $employee, 'month' => $nextMonth]) }}" class="att-month-btn" aria-label="Next month"><i class="bi bi-chevron-right"></i></a>
        </div>
    </x-slot:head_right>

    <div class="att-summary-row">
        <div class="att-summary-chip"><div class="n">{{ $summary['present'] }}</div><div class="l">Present</div></div>
        <div class="att-summary-chip"><div class="n">{{ $summary['half_day'] }}</div><div class="l">Half Days</div></div>
        <div class="att-summary-chip"><div class="n">{{ $summary['leave'] }}</div><div class="l">Leave</div></div>
        <div class="att-summary-chip"><div class="n">{{ $summary['absent'] }}</div><div class="l">Absent</div></div>
        <div class="att-summary-chip"><div class="n">{{ $summary['not_marked'] }}</div><div class="l">Not Marked</div></div>
        <div class="att-summary-chip"><div class="n">{{ number_format($summary['payable_days'], 1) }}</div><div class="l">Payable Days</div></div>
    </div>
</x-ds.card>

<div style="height:20px"></div>

<x-ds.card title="Day-by-Day">
    <div class="att-table-wrap">
        <table class="att-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $day)
                <tr>
                    <td data-label="Date">{{ $day['date']->format('D, d M Y') }}</td>
                    <td data-label="Status"><x-status-badge :status="$day['status']" /></td>
                    <td data-label="Details">
                        {{ $day['leave_type_name'] ?? ($day['other_half_label'] ?? '—') }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" style="text-align:center;padding:24px;color:var(--ef-faint,#6b7280)">No attendance data for this month.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ds.card>

</x-admin-layout>
