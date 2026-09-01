<x-admin-layout title="Attendance · {{ $employee->name }}">

<x-ds.hero eyebrow="People / HR · Attendance" title="{{ $employee->name }}"
    :meta="[['icon' => 'bi-calendar3', 'text' => $month->format('F Y')]]">
    <x-slot:actions>
        <a href="{{ route('admin.attendance.index') }}" class="ef-ds-btn"><i class="bi bi-arrow-left"></i> <span>Back to Attendance</span></a>
    </x-slot:actions>
</x-ds.hero>

@push('styles')
<style>
    .att-month-nav { display:flex; align-items:center; gap:8px; }
    .att-month-btn { display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:8px; border:1px solid var(--ef-border,#e5e7eb); color:var(--ef-ink,#1c1612); text-decoration:none; flex-shrink:0; }
    .att-month-btn:hover { background:#f5f3ef; }
    .att-month-btn:focus-visible { outline:2px solid var(--ef-emerald,#0F7B5F); outline-offset:2px; }
    .att-month-label { font-weight:700; font-size:.9rem; min-width:100px; text-align:center; }

    .att-kpi-row { display:grid; grid-template-columns:repeat(2,1fr); gap:10px; margin-bottom:4px; }
    .att-kpi { background:#fff; border:1px solid var(--ef-border,#e5e7eb); border-radius:var(--ef-radius-sm,10px); padding:12px 14px; border-top:3px solid var(--ef-border,#e5e7eb); }
    .att-kpi .n { font-size:1.3rem; font-weight:800; line-height:1; color:var(--ef-ink,#141412); }
    .att-kpi .l { font-size:.66rem; color:var(--ef-muted,#77736a); text-transform:uppercase; letter-spacing:.04em; font-weight:700; margin-top:4px; }
    .att-kpi[data-tone="success"] { border-top-color:var(--ef-emerald,#0F7B5F); }
    .att-kpi[data-tone="success"] .n { color:var(--ef-emerald,#0F7B5F); }
    .att-kpi[data-tone="danger"] { border-top-color:var(--ef-danger,#C84B44); }
    .att-kpi[data-tone="danger"] .n { color:var(--ef-danger,#C84B44); }
    .att-kpi[data-tone="warning"] { border-top-color:var(--ef-amber,#D89A3D); }
    .att-kpi[data-tone="warning"] .n { color:var(--ef-amber,#D89A3D); }
    .att-kpi[data-tone="neutral"] { border-top-color:var(--ef-faint,#a9a39a); }
    @media (min-width:640px) { .att-kpi-row { grid-template-columns:repeat(3,1fr); } }
    @media (min-width:900px) { .att-kpi-row { grid-template-columns:repeat(6,1fr); } }

    .att-table-wrap { overflow-x:auto; }
    .att-table { width:100%; border-collapse:collapse; font-size:.85rem; }
    .att-table th { text-align:left; padding:8px 10px; color:var(--ef-faint,#a9a39a); font-weight:700; text-transform:uppercase; font-size:.66rem; letter-spacing:.04em; border-bottom:1px solid var(--ef-border,#e5e7eb); }
    .att-table td { padding:9px 10px; border-bottom:1px solid #f1efe9; vertical-align:middle; }

    .att-desktop-only { display:none; }
    .att-mobile-only { display:block; }
    @media (min-width:768px) {
        .att-desktop-only { display:block; }
        .att-mobile-only { display:none; }
    }

    .att-day-row { border:1px solid var(--ef-border,#e5e7eb); border-radius:var(--ef-radius-sm,10px); padding:9px 12px; margin-bottom:7px; }
    .att-day-top { display:flex; align-items:center; justify-content:space-between; gap:8px; }
    .att-day-date { font-weight:700; font-size:.85rem; color:var(--ef-ink,#141412); }
    .att-day-detail { font-size:.78rem; color:var(--ef-muted,#77736a); margin-top:4px; }
    .att-empty { text-align:center; padding:20px 12px; color:var(--ef-faint,#a9a39a); font-size:.85rem; }
</style>
@endpush

<x-ds.card title="Month Summary — {{ $month->format('F Y') }}">
    <x-slot:head_right>
        <div class="att-month-nav">
            <a href="{{ route('admin.attendance.show', ['employee' => $employee, 'month' => $prevMonth]) }}" class="att-month-btn" aria-label="Previous month" title="Previous month"><i class="bi bi-chevron-left"></i></a>
            <span class="att-month-label">{{ $month->format('F Y') }}</span>
            <a href="{{ route('admin.attendance.show', ['employee' => $employee, 'month' => $nextMonth]) }}" class="att-month-btn" aria-label="Next month" title="Next month"><i class="bi bi-chevron-right"></i></a>
        </div>
    </x-slot:head_right>

    <div class="att-kpi-row">
        <div class="att-kpi" data-tone="success"><div class="n">{{ $summary['present'] }}</div><div class="l">Present</div></div>
        <div class="att-kpi" data-tone="neutral"><div class="n">{{ $summary['half_day'] }}</div><div class="l">Half Days</div></div>
        <div class="att-kpi" data-tone="neutral"><div class="n">{{ $summary['leave'] }}</div><div class="l">Leave</div></div>
        <div class="att-kpi" data-tone="danger"><div class="n">{{ $summary['absent'] }}</div><div class="l">Absent</div></div>
        <div class="att-kpi" data-tone="warning"><div class="n">{{ $summary['not_marked'] }}</div><div class="l">Not Marked</div></div>
        <div class="att-kpi" data-tone="success"><div class="n">{{ number_format($summary['payable_days'], 1) }}</div><div class="l">Payable Days</div></div>
    </div>
</x-ds.card>

<div style="height:20px"></div>

<x-ds.card title="Day-by-Day">
    {{-- Desktop table --}}
    <div class="att-table-wrap att-desktop-only">
        <table class="att-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $day)
                <tr>
                    <td data-label="Date">{{ $day['date']->format('d M Y') }}</td>
                    <td data-label="Day">{{ $day['date']->format('D') }}</td>
                    <td data-label="Status"><x-status-badge :status="$day['status']" /></td>
                    <td data-label="Details">{{ $day['leave_type_name'] ?? ($day['other_half_label'] ?? '—') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="att-empty">No attendance data for this month.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile list --}}
    <div class="att-mobile-only">
        @forelse($history as $day)
        <div class="att-day-row">
            <div class="att-day-top">
                <span class="att-day-date">{{ $day['date']->format('D, d M Y') }}</span>
                <x-status-badge :status="$day['status']" />
            </div>
            @php $detail = $day['leave_type_name'] ?? ($day['other_half_label'] ?? null); @endphp
            @if($detail)
                <div class="att-day-detail">{{ $detail }}</div>
            @endif
        </div>
        @empty
        <div class="att-empty">No attendance data for this month.</div>
        @endforelse
    </div>
</x-ds.card>

</x-admin-layout>
