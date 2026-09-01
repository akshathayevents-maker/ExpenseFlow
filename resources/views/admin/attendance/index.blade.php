<x-admin-layout title="Attendance">

<x-ds.hero eyebrow="People / HR" title="Attendance"
    :meta="[['icon' => 'bi-info-circle', 'text' => 'Track and manage employee attendance'], ['icon' => 'bi-calendar3', 'text' => $today->format('l, d F Y')]]">
</x-ds.hero>

@push('styles')
<style>
    /* ── Month nav ─────────────────────────────────────────────── */
    .att-month-nav { display:flex; align-items:center; gap:8px; }
    .att-month-btn { display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:8px; border:1px solid var(--ef-border,#e5e7eb); color:var(--ef-ink,#1c1612); text-decoration:none; flex-shrink:0; }
    .att-month-btn:hover { background:#f5f3ef; }
    .att-month-btn:focus-visible { outline:2px solid var(--ef-emerald,#0F7B5F); outline-offset:2px; }
    .att-month-label { font-weight:700; font-size:.9rem; min-width:100px; text-align:center; }

    /* ── Section labelling ────────────────────────────────────── */
    .att-section-note { font-size:.75rem; color:var(--ef-faint,#a9a39a); font-weight:600; margin-top:-4px; margin-bottom:14px; }

    /* ── KPI tiles ─────────────────────────────────────────────── */
    .att-kpi-row { display:grid; grid-template-columns:repeat(2,1fr); gap:10px; margin-bottom:16px; }
    .att-kpi { background:#fff; border:1px solid var(--ef-border,#e5e7eb); border-radius:var(--ef-radius-sm,10px); padding:12px 14px; border-top:3px solid var(--ef-border,#e5e7eb); }
    .att-kpi .n { font-size:1.4rem; font-weight:800; line-height:1; color:var(--ef-ink,#141412); }
    .att-kpi .l { font-size:.68rem; color:var(--ef-muted,#77736a); text-transform:uppercase; letter-spacing:.04em; font-weight:700; margin-top:4px; }
    .att-kpi[data-tone="success"] { border-top-color:var(--ef-emerald,#0F7B5F); }
    .att-kpi[data-tone="success"] .n { color:var(--ef-emerald,#0F7B5F); }
    .att-kpi[data-tone="danger"] { border-top-color:var(--ef-danger,#C84B44); }
    .att-kpi[data-tone="danger"] .n { color:var(--ef-danger,#C84B44); }
    .att-kpi[data-tone="warning"] { border-top-color:var(--ef-amber,#D89A3D); }
    .att-kpi[data-tone="warning"] .n { color:var(--ef-amber,#D89A3D); }
    .att-kpi[data-tone="neutral"] { border-top-color:var(--ef-faint,#a9a39a); }
    @media (min-width:640px) { .att-kpi-row { grid-template-columns:repeat(4,1fr); } }

    /* ── Search ───────────────────────────────────────────────── */
    .att-search-wrap { position:relative; margin-bottom:12px; }
    .att-search-wrap i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--ef-faint,#a9a39a); font-size:.85rem; }
    .att-search-input { width:100%; padding:9px 12px 9px 34px; border:1px solid var(--ef-border,#e5e7eb); border-radius:var(--ef-radius-sm,10px); font-size:.85rem; color:var(--ef-ink,#141412); }
    .att-search-input:focus-visible, .att-search-input:focus { outline:2px solid var(--ef-emerald,#0F7B5F); outline-offset:1px; border-color:var(--ef-emerald,#0F7B5F); }

    /* ── Desktop table (min-width 768px) ─────────────────────── */
    .att-table-wrap { overflow-x:auto; }
    .att-table { width:100%; border-collapse:collapse; font-size:.85rem; }
    .att-table th { text-align:left; padding:8px 10px; color:var(--ef-faint,#a9a39a); font-weight:700; text-transform:uppercase; font-size:.66rem; letter-spacing:.04em; border-bottom:1px solid var(--ef-border,#e5e7eb); }
    .att-table td { padding:9px 10px; border-bottom:1px solid #f1efe9; vertical-align:middle; }
    .att-emp-name { text-decoration:none; color:var(--ef-ink,#141412); font-weight:700; font-size:.88rem; }
    .att-emp-name:hover { color:var(--ef-emerald,#0F7B5F); }
    .att-reg-link { display:inline-flex; align-items:center; gap:5px; font-size:.72rem; font-weight:700; color:#7D5218; background:rgba(216,154,61,.13); border-radius:6px; padding:3px 9px; text-decoration:none; }
    .att-reg-link:hover { background:rgba(216,154,61,.22); }
    .att-view-link { font-size:.8rem; text-decoration:none; color:var(--ef-emerald,#0F7B5F); font-weight:700; white-space:nowrap; }
    .att-view-link:hover { color:var(--ef-emerald-dk,#0D5C43); }
    .att-dash { color:var(--ef-faint,#a9a39a); }

    /* ── Mobile compact list (default, below 768px) ───────────── */
    .att-mobile-list { display:flex; flex-direction:column; gap:8px; }
    .att-mrow { border:1px solid var(--ef-border,#e5e7eb); border-radius:var(--ef-radius-sm,10px); padding:10px 12px; }
    .att-mrow-top { display:flex; align-items:center; justify-content:space-between; gap:8px; }
    .att-mrow-name { font-weight:700; font-size:.9rem; color:var(--ef-ink,#141412); text-decoration:none; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .att-mrow-halves { font-size:.78rem; color:var(--ef-muted,#77736a); margin-top:5px; }
    .att-mrow-bottom { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-top:6px; }
    .att-mrow-view { font-size:.78rem; font-weight:700; color:var(--ef-emerald,#0F7B5F); text-decoration:none; min-height:44px; display:inline-flex; align-items:center; }
    .att-mrow-reg { font-size:.7rem; font-weight:700; color:#7D5218; background:rgba(216,154,61,.13); border-radius:6px; padding:4px 8px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; }

    .att-desktop-only { display:none; }
    .att-mobile-only { display:block; }
    @media (min-width:768px) {
        .att-desktop-only { display:block; }
        .att-mobile-only { display:none; }
    }

    /* ── Mobile monthly summary cards ─────────────────────────── */
    .att-msum-card { border:1px solid var(--ef-border,#e5e7eb); border-radius:var(--ef-radius-sm,10px); padding:10px 12px; margin-bottom:8px; }
    .att-msum-top { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:6px; }
    .att-msum-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:4px 10px; font-size:.78rem; }
    .att-msum-grid .kv b { color:var(--ef-ink,#141412); font-weight:700; }
    .att-msum-grid .kv span { color:var(--ef-muted,#77736a); }
    .att-empty { text-align:center; padding:20px 12px; color:var(--ef-faint,#a9a39a); font-size:.85rem; }
</style>
@endpush

<x-ds.card title="Today's Attendance">
    <div class="att-search-wrap">
        <i class="bi bi-search"></i>
        <input type="text" id="attSearch" class="att-search-input" placeholder="Search employee by name…" aria-label="Search today's attendance by employee name">
    </div>

    <div class="att-kpi-row">
        <div class="att-kpi" data-tone="success"><div class="n">{{ $todaySummary['present'] }}</div><div class="l">Present</div></div>
        <div class="att-kpi" data-tone="danger"><div class="n">{{ $todaySummary['absent'] }}</div><div class="l">Absent</div></div>
        <div class="att-kpi" data-tone="neutral"><div class="n">{{ $todaySummary['on_leave'] }}</div><div class="l">On Leave</div></div>
        <div class="att-kpi" data-tone="warning"><div class="n">{{ $todaySummary['not_marked'] }}</div><div class="l">Not Marked</div></div>
    </div>

    {{-- Desktop table --}}
    <div class="att-table-wrap att-desktop-only">
        <table class="att-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Status</th>
                    <th>First Half</th>
                    <th>Second Half</th>
                    <th>Regularization</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="attTodayDesktopBody">
                @forelse($todayRows as $row)
                <tr data-emp-name="{{ strtolower($row['employee']->name) }}">
                    <td data-label="Employee">
                        <a href="{{ route('admin.attendance.show', $row['employee']) }}" class="att-emp-name">
                            {{ $row['employee']->name }}
                        </a>
                    </td>
                    <td data-label="Status"><x-status-badge :status="$row['status']" /></td>
                    <td data-label="First Half">{{ $row['first_half'] }}</td>
                    <td data-label="Second Half">{{ $row['second_half'] }}</td>
                    <td data-label="Regularization">
                        @if($row['has_pending_regularization'])
                            <a href="{{ route('admin.attendance-regularizations.index') }}" class="att-reg-link">
                                <i class="bi bi-hourglass-split"></i> Pending
                            </a>
                        @else
                            <span class="att-dash">—</span>
                        @endif
                    </td>
                    <td data-label="Action">
                        <a href="{{ route('admin.attendance.show', $row['employee']) }}" class="att-view-link">View <i class="bi bi-arrow-right"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="att-empty">No active employees found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile compact list --}}
    <div class="att-mobile-list att-mobile-only" id="attTodayMobileList">
        @forelse($todayRows as $row)
        <div class="att-mrow" data-emp-name="{{ strtolower($row['employee']->name) }}">
            <div class="att-mrow-top">
                <a href="{{ route('admin.attendance.show', $row['employee']) }}" class="att-mrow-name">{{ $row['employee']->name }}</a>
                <x-status-badge :status="$row['status']" />
            </div>
            <div class="att-mrow-halves">1st: {{ $row['first_half'] }} · 2nd: {{ $row['second_half'] }}</div>
            <div class="att-mrow-bottom">
                @if($row['has_pending_regularization'])
                    <a href="{{ route('admin.attendance-regularizations.index') }}" class="att-mrow-reg"><i class="bi bi-hourglass-split"></i> Pending Regularization</a>
                @else
                    <span></span>
                @endif
                <a href="{{ route('admin.attendance.show', $row['employee']) }}" class="att-mrow-view">View <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
        @empty
        <div class="att-empty">No active employees found.</div>
        @endforelse
    </div>

    <div class="att-empty att-mobile-only" id="attTodayMobileNoMatch" hidden>No employees match your search.</div>
    <div class="att-empty att-desktop-only" id="attTodayDesktopNoMatch" hidden>No employees match your search.</div>
</x-ds.card>

<div style="height:20px"></div>

<x-ds.card title="Monthly Summary — {{ $month->format('F Y') }}">
    <x-slot:head_right>
        <div class="att-month-nav">
            <a href="{{ route('admin.attendance.index', ['month' => $prevMonth]) }}" class="att-month-btn" aria-label="Previous month" title="Previous month"><i class="bi bi-chevron-left"></i></a>
            <span class="att-month-label">{{ $month->format('F Y') }}</span>
            <a href="{{ route('admin.attendance.index', ['month' => $nextMonth]) }}" class="att-month-btn" aria-label="Next month" title="Next month"><i class="bi bi-chevron-right"></i></a>
        </div>
    </x-slot:head_right>

    {{-- Desktop table --}}
    <div class="att-table-wrap att-desktop-only">
        <table class="att-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Present</th>
                    <th>Half Days</th>
                    <th>Leave</th>
                    <th>Absent</th>
                    <th>Regularization</th>
                    <th>Payable Days</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($monthRows as $row)
                <tr>
                    <td data-label="Employee"><span class="att-emp-name" style="cursor:default">{{ $row['employee']->name }}</span></td>
                    <td data-label="Present">{{ $row['summary']['present'] }}</td>
                    <td data-label="Half Days">{{ $row['summary']['half_day'] }}</td>
                    <td data-label="Leave">{{ $row['summary']['leave'] }}</td>
                    <td data-label="Absent">{{ $row['summary']['absent'] }}</td>
                    <td data-label="Regularization">
                        @if($row['regularizations'] > 0)
                            <a href="{{ route('admin.attendance-regularizations.index') }}" class="att-reg-link">{{ $row['regularizations'] }}</a>
                        @else
                            <span class="att-dash">—</span>
                        @endif
                    </td>
                    <td data-label="Payable Days">{{ number_format($row['summary']['payable_days'], 1) }}</td>
                    <td data-label="">
                        <a href="{{ route('admin.attendance.show', ['employee' => $row['employee'], 'month' => $month->format('Y-m')]) }}" class="att-view-link">
                            View <i class="bi bi-arrow-right"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="att-empty">No monthly data available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile compact summary cards --}}
    <div class="att-mobile-only">
        @forelse($monthRows as $row)
        <div class="att-msum-card">
            <div class="att-msum-top">
                <span class="att-mrow-name" style="text-decoration:none">{{ $row['employee']->name }}</span>
                <a href="{{ route('admin.attendance.show', ['employee' => $row['employee'], 'month' => $month->format('Y-m')]) }}" class="att-mrow-view">View Details <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="att-msum-grid">
                <div class="kv"><span>Present:</span> <b>{{ $row['summary']['present'] }}</b></div>
                <div class="kv"><span>Half Days:</span> <b>{{ $row['summary']['half_day'] }}</b></div>
                <div class="kv"><span>Leave:</span> <b>{{ $row['summary']['leave'] }}</b></div>
                <div class="kv"><span>Absent:</span> <b>{{ $row['summary']['absent'] }}</b></div>
                <div class="kv"><span>Reg.:</span> <b>{{ $row['regularizations'] }}</b></div>
                <div class="kv"><span>Payable:</span> <b>{{ number_format($row['summary']['payable_days'], 1) }}</b></div>
            </div>
        </div>
        @empty
        <div class="att-empty">No monthly data available.</div>
        @endforelse
    </div>
</x-ds.card>

@push('scripts')
<script>
(function () {
    var input = document.getElementById('attSearch');
    if (!input) return;

    var desktopRows = Array.prototype.slice.call(document.querySelectorAll('#attTodayDesktopBody tr[data-emp-name]'));
    var mobileRows  = Array.prototype.slice.call(document.querySelectorAll('#attTodayMobileList .att-mrow[data-emp-name]'));
    var desktopNoMatch = document.getElementById('attTodayDesktopNoMatch');
    var mobileNoMatch  = document.getElementById('attTodayMobileNoMatch');

    input.addEventListener('input', function () {
        var q = input.value.trim().toLowerCase();
        var desktopVisible = 0, mobileVisible = 0;

        desktopRows.forEach(function (row) {
            var match = row.getAttribute('data-emp-name').indexOf(q) !== -1;
            row.hidden = !match;
            if (match) desktopVisible++;
        });
        mobileRows.forEach(function (row) {
            var match = row.getAttribute('data-emp-name').indexOf(q) !== -1;
            row.hidden = !match;
            if (match) mobileVisible++;
        });

        if (desktopNoMatch) desktopNoMatch.hidden = !(q && desktopVisible === 0 && desktopRows.length > 0);
        if (mobileNoMatch) mobileNoMatch.hidden = !(q && mobileVisible === 0 && mobileRows.length > 0);
    });
})();
</script>
@endpush

</x-admin-layout>
