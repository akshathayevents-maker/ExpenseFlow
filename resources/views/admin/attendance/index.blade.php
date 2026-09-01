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

    /* ── KPI tiles (also client-side filter buttons) ─────────────── */
    .att-kpi-row { display:grid; grid-template-columns:repeat(2,1fr); gap:10px; margin-bottom:12px; }
    .att-kpi { background:#fff; border:1px solid var(--ef-border,#e5e7eb); border-radius:var(--ef-radius-sm,10px); padding:12px 14px; border-top:3px solid var(--ef-border,#e5e7eb); text-align:left; cursor:pointer; width:100%; font:inherit; transition:box-shadow .12s,background-color .12s; }
    .att-kpi .n { font-size:1.6rem; font-weight:800; line-height:1; color:var(--ef-ink,#141412); }
    .att-kpi .l { display:flex; align-items:center; gap:5px; font-size:.68rem; color:var(--ef-muted,#77736a); text-transform:uppercase; letter-spacing:.04em; font-weight:700; margin-top:5px; }
    .att-kpi .l .dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
    .att-kpi[data-tone="success"] { border-top-color:var(--ef-emerald,#0F7B5F); }
    .att-kpi[data-tone="success"] .n { color:var(--ef-emerald,#0F7B5F); }
    .att-kpi[data-tone="success"] .dot { background:var(--ef-emerald,#0F7B5F); }
    .att-kpi[data-tone="danger"] { border-top-color:var(--ef-danger,#C84B44); }
    .att-kpi[data-tone="danger"] .n { color:var(--ef-danger,#C84B44); }
    .att-kpi[data-tone="danger"] .dot { background:var(--ef-danger,#C84B44); }
    .att-kpi[data-tone="warning"] { border-top-color:var(--ef-amber,#D89A3D); }
    .att-kpi[data-tone="warning"] .n { color:var(--ef-amber,#D89A3D); }
    .att-kpi[data-tone="warning"] .dot { background:var(--ef-amber,#D89A3D); }
    .att-kpi[data-tone="neutral"] { border-top-color:#2F6FED; }
    .att-kpi[data-tone="neutral"] .n { color:#1E4DB7; }
    .att-kpi[data-tone="neutral"] .dot { background:#2F6FED; }
    .att-kpi:hover, .att-kpi:focus-visible { box-shadow:0 2px 8px rgba(20,20,18,.08); outline:none; }
    .att-kpi:focus-visible { outline:2px solid var(--ef-emerald,#0F7B5F); outline-offset:2px; }
    .att-kpi.is-active { background:#f5f3ef; box-shadow:inset 0 0 0 2px currentColor; }
    .att-kpi[data-tone="success"].is-active { box-shadow:inset 0 0 0 2px var(--ef-emerald,#0F7B5F); }
    .att-kpi[data-tone="danger"].is-active { box-shadow:inset 0 0 0 2px var(--ef-danger,#C84B44); }
    .att-kpi[data-tone="warning"].is-active { box-shadow:inset 0 0 0 2px var(--ef-amber,#D89A3D); }
    .att-kpi[data-tone="neutral"].is-active { box-shadow:inset 0 0 0 2px #2F6FED; }
    .att-kpi:active { transform:scale(.98); }
    @media (min-width:640px) { .att-kpi-row { grid-template-columns:repeat(4,1fr); } }

    /* ── Active filter indicator ─────────────────────────────────── */
    .att-filter-bar { display:none; align-items:center; gap:8px; font-size:.78rem; color:var(--ef-muted,#77736a); margin-bottom:10px; flex-wrap:wrap; }
    .att-filter-bar.is-visible { display:flex; }
    .att-filter-bar b { color:var(--ef-ink,#141412); }
    .att-filter-clear { font-size:.75rem; font-weight:700; color:var(--ef-emerald,#0F7B5F); background:none; border:none; cursor:pointer; padding:2px 6px; text-decoration:underline; }

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
    .att-mobile-list { display:flex; flex-direction:column; gap:8px; padding-bottom:env(safe-area-inset-bottom,0px); }
    .att-mrow { border:1px solid var(--ef-border,#e5e7eb); border-left-width:4px; border-radius:var(--ef-radius-sm,10px); padding:10px 12px; }
    .att-mrow[data-status="present"], .att-mrow[data-status="half_day"] { border-left-color:var(--ef-emerald,#0F7B5F); }
    .att-mrow[data-status="absent"], .att-mrow[data-status="half_day_lop"] { border-left-color:var(--ef-danger,#C84B44); }
    .att-mrow[data-status="leave"], .att-mrow[data-status="half_day_leave"] { border-left-color:#2F6FED; }
    .att-mrow[data-status="not_marked"] { border-left-color:var(--ef-amber,#D89A3D); }
    .att-mrow[data-status="holiday"], .att-mrow[data-status="weekly_off"] { border-left-color:var(--ef-faint,#a9a39a); }
    .att-mrow-top { display:flex; align-items:center; justify-content:space-between; gap:8px; }
    .att-mrow-name { font-weight:700; font-size:.9rem; color:var(--ef-ink,#141412); text-decoration:none; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .att-mrow-halves { display:grid; grid-template-columns:1fr 1fr; gap:6px; margin-top:8px; }
    .att-mrow-half { background:#faf9f6; border-radius:7px; padding:5px 8px; font-size:.74rem; color:var(--ef-muted,#77736a); }
    .att-mrow-half b { display:block; font-size:.78rem; color:var(--ef-ink,#141412); font-weight:700; margin-top:1px; }
    .att-mrow-half .glyph { margin-right:3px; }
    .att-mrow-bottom { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-top:6px; }
    .att-mrow-view { font-size:.78rem; font-weight:700; color:var(--ef-emerald,#0F7B5F); text-decoration:none; min-height:44px; display:inline-flex; align-items:center; }
    .att-mrow-reg { font-size:.74rem; font-weight:700; color:#7D5218; background:rgba(216,154,61,.13); border-radius:6px; padding:4px 8px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; margin-top:6px; }

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

<div id="attTodayCard">
<x-ds.card title="Today's Attendance">
    <div class="att-search-wrap">
        <i class="bi bi-search"></i>
        <input type="text" id="attSearch" class="att-search-input" placeholder="Search employee by name…" aria-label="Search today's attendance by employee name">
    </div>

    <div class="att-kpi-row" role="group" aria-label="Filter today's attendance by status">
        <button type="button" class="att-kpi" data-tone="success" data-filter="present" aria-pressed="false">
            <div class="n">{{ $todaySummary['present'] }}</div><div class="l"><span class="dot"></span>Present</div>
        </button>
        <button type="button" class="att-kpi" data-tone="danger" data-filter="absent" aria-pressed="false">
            <div class="n">{{ $todaySummary['absent'] }}</div><div class="l"><span class="dot"></span>Absent</div>
        </button>
        <button type="button" class="att-kpi" data-tone="neutral" data-filter="on_leave" aria-pressed="false">
            <div class="n">{{ $todaySummary['on_leave'] }}</div><div class="l"><span class="dot"></span>On Leave</div>
        </button>
        <button type="button" class="att-kpi" data-tone="warning" data-filter="not_marked" aria-pressed="false">
            <div class="n">{{ $todaySummary['not_marked'] }}</div><div class="l"><span class="dot"></span>Not Marked</div>
        </button>
    </div>

    <div class="att-filter-bar" id="attFilterBar">
        <span>Showing: <b id="attFilterLabel"></b></span>
        <button type="button" class="att-filter-clear" id="attFilterClear">Clear</button>
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
                @php
                    $filterGroup = match(true) {
                        in_array($row['status'], ['present', 'half_day']) => 'present',
                        $row['status'] === 'absent' => 'absent',
                        in_array($row['status'], ['leave', 'half_day_leave']) => 'on_leave',
                        $row['status'] === 'not_marked' => 'not_marked',
                        default => 'other',
                    };
                @endphp
                <tr data-emp-name="{{ strtolower($row['employee']->name) }}" data-filter-group="{{ $filterGroup }}">
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
        @php
            $filterGroup = match(true) {
                in_array($row['status'], ['present', 'half_day']) => 'present',
                $row['status'] === 'absent' => 'absent',
                in_array($row['status'], ['leave', 'half_day_leave']) => 'on_leave',
                $row['status'] === 'not_marked' => 'not_marked',
                default => 'other',
            };
            $firstGlyph = $row['first_half'] === '—' ? '—' : '✓';
            $secondGlyph = $row['second_half'] === '—' ? '—' : '✓';
        @endphp
        <div class="att-mrow" data-emp-name="{{ strtolower($row['employee']->name) }}" data-filter-group="{{ $filterGroup }}" data-status="{{ $row['status'] }}">
            <div class="att-mrow-top">
                <a href="{{ route('admin.attendance.show', $row['employee']) }}" class="att-mrow-name">{{ $row['employee']->name }}</a>
                <x-status-badge :status="$row['status']" />
            </div>
            <div class="att-mrow-halves">
                <div class="att-mrow-half"><span class="glyph">{{ $firstGlyph }}</span>First Half<b>{{ $row['first_half'] }}</b></div>
                <div class="att-mrow-half"><span class="glyph">{{ $secondGlyph }}</span>Second Half<b>{{ $row['second_half'] }}</b></div>
            </div>
            @if($row['has_pending_regularization'])
                <a href="{{ route('admin.attendance-regularizations.index') }}" class="att-mrow-reg"><i class="bi bi-exclamation-triangle-fill"></i> Pending regularization</a>
            @endif
            <div class="att-mrow-bottom">
                <span></span>
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
</div>

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
    var kpiButtons = Array.prototype.slice.call(document.querySelectorAll('.att-kpi[data-filter]'));
    var filterBar = document.getElementById('attFilterBar');
    var filterLabel = document.getElementById('attFilterLabel');
    var filterClear = document.getElementById('attFilterClear');
    var cardTitleEl = document.querySelector('#attTodayCard .ef-ds-card-title');
    var defaultTitle = cardTitleEl ? cardTitleEl.textContent : "Today's Attendance";

    var emptyMessages = {
        present: 'No employees are marked present today.',
        absent: 'No employees are currently absent.',
        on_leave: 'No employees are on leave today.',
        not_marked: 'All employees have marked their attendance today.',
    };
    var filterLabels = {
        present: 'Present',
        absent: 'Absent',
        on_leave: 'On Leave',
        not_marked: 'Not Marked',
    };

    var activeFilter = null;

    function applyFilters() {
        var q = input.value.trim().toLowerCase();
        var desktopVisible = 0, mobileVisible = 0;

        desktopRows.forEach(function (row) {
            var nameMatch = row.getAttribute('data-emp-name').indexOf(q) !== -1;
            var statusMatch = !activeFilter || row.getAttribute('data-filter-group') === activeFilter;
            var match = nameMatch && statusMatch;
            row.hidden = !match;
            if (match) desktopVisible++;
        });
        mobileRows.forEach(function (row) {
            var nameMatch = row.getAttribute('data-emp-name').indexOf(q) !== -1;
            var statusMatch = !activeFilter || row.getAttribute('data-filter-group') === activeFilter;
            var match = nameMatch && statusMatch;
            row.hidden = !match;
            if (match) mobileVisible++;
        });

        var noMatchMessage = null;
        if (desktopVisible === 0 && desktopRows.length > 0) {
            noMatchMessage = activeFilter && !q ? emptyMessages[activeFilter] : 'No employees match your search.';
        }
        if (desktopNoMatch) {
            desktopNoMatch.hidden = noMatchMessage === null;
            if (noMatchMessage !== null) desktopNoMatch.textContent = noMatchMessage;
        }
        var noMatchMessageM = null;
        if (mobileVisible === 0 && mobileRows.length > 0) {
            noMatchMessageM = activeFilter && !q ? emptyMessages[activeFilter] : 'No employees match your search.';
        }
        if (mobileNoMatch) {
            mobileNoMatch.hidden = noMatchMessageM === null;
            if (noMatchMessageM !== null) mobileNoMatch.textContent = noMatchMessageM;
        }

        // Section heading + active-filter indicator
        if (activeFilter) {
            var count = Math.max(desktopVisible, mobileVisible);
            if (cardTitleEl) cardTitleEl.textContent = filterLabels[activeFilter] + ' — ' + count + ' employee' + (count === 1 ? '' : 's');
            if (filterBar) filterBar.classList.add('is-visible');
            if (filterLabel) filterLabel.textContent = filterLabels[activeFilter] + ' · ' + count + ' employee' + (count === 1 ? '' : 's');
        } else {
            if (cardTitleEl) cardTitleEl.textContent = defaultTitle;
            if (filterBar) filterBar.classList.remove('is-visible');
        }

        kpiButtons.forEach(function (btn) {
            var isActive = btn.getAttribute('data-filter') === activeFilter;
            btn.classList.toggle('is-active', isActive);
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    }

    kpiButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var filter = btn.getAttribute('data-filter');
            activeFilter = activeFilter === filter ? null : filter;
            applyFilters();
        });
    });

    if (filterClear) {
        filterClear.addEventListener('click', function () {
            activeFilter = null;
            applyFilters();
        });
    }

    input.addEventListener('input', applyFilters);

    applyFilters();
})();
</script>
@endpush

</x-admin-layout>
