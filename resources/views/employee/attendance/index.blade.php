<x-admin-layout title="Attendance">

<x-ds.hero eyebrow="Employee Self-Service" title="Attendance"
    :meta="[['icon' => 'bi-calendar3', 'text' => $monthStart->format('F Y')]]">
    <x-slot:actions>
        <a href="#regularize-date-card" class="ef-ds-btn --primary">
            <i class="bi bi-pencil-square"></i> <span>Regularize a Date</span>
        </a>
    </x-slot:actions>
</x-ds.hero>

{{-- Leave entry point — Apply Leave is the primary action, My Leave secondary --}}
<div class="at-leave-cta-row">
    <a href="{{ route('employee.leave.create') }}" class="ef-btn ef-btn-dark">
        <i class="bi bi-calendar-plus"></i> Apply Leave
    </a>
    <a href="{{ route('employee.leave.index') }}" class="ef-btn">
        <i class="bi bi-calendar-minus"></i> My Leave
    </a>
</div>

@php
$statusChips = [
    'present'    => ['label' => 'Present',    'bg' => 'rgba(15,123,95,.11)',  'color' => '#0A5240'],
    'half_day'   => ['label' => 'Half Day',    'bg' => 'rgba(216,154,61,.13)', 'color' => '#7D5218'],
    'leave'      => ['label' => 'Leave',       'bg' => 'rgba(47,111,237,.10)', 'color' => '#1E4DB7'],
    'half_day_leave' => ['label' => 'Half Day (Leave)', 'bg' => 'rgba(47,111,237,.10)', 'color' => '#1E4DB7'],
    'absent'     => ['label' => 'Absent',      'bg' => 'rgba(200,75,68,.11)',  'color' => '#9B2C2C'],
    'weekly_off' => ['label' => 'Weekly Off',  'bg' => 'rgba(100,116,139,.11)','color' => '#334155'],
    'holiday'    => ['label' => 'Holiday',     'bg' => 'rgba(184,137,62,.12)', 'color' => '#6B4A12'],
    'not_marked' => ['label' => 'Not Marked',  'bg' => 'rgba(100,116,139,.08)','color' => '#64748B'],
];
$todayChip = $statusChips[$today->status ?? 'not_marked'] ?? $statusChips['not_marked'];
@endphp

@push('styles')
<style>
    .at-leave-cta-row { display: flex; gap: 8px; flex-wrap: wrap; margin: 12px 0; }
    .at-leave-cta-row .ef-btn, .at-leave-cta-row .ef-btn-dark { flex: 1 1 140px; }
    .at-today-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
    .at-today-date { font-weight: 700; font-size: 1.05rem; }
    .at-today-sub { color: var(--ef-faint, #6b7280); font-size: .84rem; margin-top: 2px; }
    .at-today-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .at-stat-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
    @media (min-width: 576px) { .at-stat-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (min-width: 992px) { .at-stat-grid { grid-template-columns: repeat(6, 1fr); } }
    .at-stat-tile { background: var(--ef-surface-2, #f8f9fa); border-radius: 10px; padding: 12px; text-align: center; }
    .at-stat-val { font-size: 1.3rem; font-weight: 800; }
    .at-stat-lbl { font-size: .72rem; color: var(--ef-faint, #6b7280); text-transform: uppercase; letter-spacing: .03em; margin-top: 2px; }
    .at-list { display: flex; flex-direction: column; gap: 8px; }
    /* Mobile-first: date block stacked above status+action. From 576px up,
       everything sits on one row (date left, status+action right) — this
       is the exact structure requested: no collision between the status
       badge and the Regularize button at narrow widths. */
    .at-list-row {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 10px 12px;
        border: 1px solid var(--ef-border, #e5e7eb);
        border-radius: 8px;
    }
    .at-list-row-meta { min-width: 0; }
    .at-list-row-aside {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
    }
    @media (min-width: 576px) {
        .at-list-row { flex-direction: row; align-items: center; justify-content: space-between; }
        .at-list-row-aside { justify-content: flex-end; flex-wrap: nowrap; }
    }
    .at-list-date { font-weight: 600; overflow-wrap: anywhere; }
    .at-list-day { color: var(--ef-faint, #6b7280); font-size: .82rem; }
    .at-list-empty { text-align: center; padding: 40px 16px; color: var(--ef-faint, #6b7280); }
    .at-month-nav { display: flex; align-items: center; gap: 6px; }
    .at-month-label { font-weight: 600; font-size: .86rem; text-align: center; flex: 1 1 auto; }
    .at-reg-date-row { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; }
    .at-reg-date-field { flex: 1 1 200px; min-width: 0; }
    .at-reg-status-block { margin-top: 14px; padding: 12px; border-radius: 8px; background: var(--ef-surface-2, #f8f9fa); }
    .at-reg-block-msg { color: var(--ef-faint, #6b7280); font-size: .86rem; }
</style>
@endpush

{{-- Today ─────────────────────────────────────────────────── --}}
<x-ds.card title="Today">
    <div class="at-today-row">
        <div>
            <div class="at-today-date">{{ $todayDate->format('d M Y (l)') }}</div>
            @if($today)
                <div class="at-today-sub">Marked at {{ $today->marked_at?->format('h:i A') ?? $today->created_at->format('h:i A') }}</div>
            @elseif($todayIsNonWorking)
                <div class="at-today-sub">{{ $todayCategory === 'holiday' ? 'Holiday — attendance is not applicable today.' : 'Weekly off — attendance is not applicable today.' }}</div>
            @else
                <div class="at-today-sub">Attendance not marked yet</div>
            @endif
        </div>

        @if($today)
            <span style="display:inline-flex;align-items:center;border-radius:6px;font-size:.78rem;font-weight:700;padding:4px 12px;background:{{ $todayChip['bg'] }};color:{{ $todayChip['color'] }}">
                {{ $todayChip['label'] }}
            </span>
        @elseif($todayIsNonWorking)
            @php $nonWorkingChip = $statusChips[$todayCategory === 'holiday' ? 'holiday' : 'weekly_off']; @endphp
            <span style="display:inline-flex;align-items:center;border-radius:6px;font-size:.78rem;font-weight:700;padding:4px 12px;background:{{ $nonWorkingChip['bg'] }};color:{{ $nonWorkingChip['color'] }}">
                {{ $nonWorkingChip['label'] }}
            </span>
        @else
            <div class="at-today-actions">
                <form method="POST" action="{{ route('employee.attendance.mark-present') }}">
                    @csrf
                    <button type="submit" class="ef-btn ef-btn-dark">
                        <i class="bi bi-check-lg"></i> Mark Present
                    </button>
                </form>
                <form method="POST" action="{{ route('employee.attendance.mark-half-day') }}">
                    @csrf
                    <button type="submit" class="ef-btn">
                        <i class="bi bi-clock-history"></i> Mark Half Day
                    </button>
                </form>
            </div>
        @endif
    </div>
</x-ds.card>

{{-- Monthly summary ───────────────────────────────────────── --}}
<div class="mt-3">
<x-ds.card title="This Month">
    <div class="at-stat-grid">
        <div class="at-stat-tile">
            <div class="at-stat-val">{{ $summary['present'] }}</div>
            <div class="at-stat-lbl">Present</div>
        </div>
        <div class="at-stat-tile">
            <div class="at-stat-val">{{ $summary['half_day'] }}</div>
            <div class="at-stat-lbl">Half Day</div>
        </div>
        <div class="at-stat-tile">
            <div class="at-stat-val">{{ $summary['leave'] }}</div>
            <div class="at-stat-lbl">Leave</div>
        </div>
        <div class="at-stat-tile">
            <div class="at-stat-val">{{ $summary['weekly_off'] }}</div>
            <div class="at-stat-lbl">Weekly Off</div>
        </div>
        <div class="at-stat-tile">
            <div class="at-stat-val">{{ $summary['holiday'] }}</div>
            <div class="at-stat-lbl">Holiday</div>
        </div>
        <div class="at-stat-tile">
            <div class="at-stat-val">{{ $summary['not_marked'] }}</div>
            <div class="at-stat-lbl">Not Marked</div>
        </div>
    </div>
    <div style="margin-top:12px;color:var(--ef-faint,#6b7280);font-size:.84rem">
        <i class="bi bi-info-circle"></i> Payable days so far this month: <strong>{{ rtrim(rtrim(number_format($summary['payable_days'], 1), '0'), '.') }}</strong>
    </div>
</x-ds.card>
</div>

{{-- History ────────────────────────────────────────────────── --}}
<div class="mt-3">
<x-ds.card>
    <x-slot:head_right>
        <div class="at-month-nav">
            <a href="{{ route('employee.attendance.index', ['month' => $prevMonth]) }}" class="ef-btn ef-btn-icon" title="Previous month" aria-label="View previous month">
                <i class="bi bi-chevron-left" aria-hidden="true"></i>
            </a>
            <span class="at-month-label">{{ $monthStart->format('F Y') }}</span>
            @if($canGoNext)
                <a href="{{ route('employee.attendance.index', ['month' => $nextMonth]) }}" class="ef-btn ef-btn-icon" title="Next month" aria-label="View next month">
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </a>
            @else
                <span class="ef-btn ef-btn-icon" style="opacity:.35;pointer-events:none" aria-disabled="true" aria-hidden="true" title="No future months"><i class="bi bi-chevron-right"></i></span>
            @endif
        </div>
    </x-slot:head_right>

    <div class="at-list">
        @forelse($history as $day)
            @php $chip = $statusChips[$day['status']] ?? $statusChips['not_marked']; $dayDateStr = $day['date']->toDateString(); @endphp
            <div class="at-list-row">
                <div class="at-list-row-meta">
                    <div class="at-list-date">{{ $day['date']->format('d M Y') }}</div>
                    <div class="at-list-day">{{ $day['date']->format('l') }}</div>
                </div>
                <div class="at-list-row-aside">
                    <span style="display:inline-flex;align-items:center;border-radius:6px;font-size:.74rem;font-weight:700;padding:3px 10px;background:{{ $chip['bg'] }};color:{{ $chip['color'] }};white-space:nowrap">
                        {{ $chip['label'] }}
                    </span>
                    @if($day['can_regularize'])
                        <a href="{{ route('employee.attendance.index', ['date' => $dayDateStr]) }}#regularize-date-card"
                           class="ef-btn-xs --primary js-regularize-link"
                           data-date="{{ $dayDateStr }}"
                           data-label="{{ $day['date']->format('d M Y') }}"
                           style="white-space:nowrap;min-height:32px">
                            <i class="bi bi-pencil-square" aria-hidden="true"></i> Regularize
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="at-list-empty">
                <i class="bi bi-calendar3" style="font-size:1.5rem;display:block;margin-bottom:8px"></i>
                No attendance history for this month yet.
            </div>
        @endforelse
    </div>
</x-ds.card>
</div>

{{-- Regularize a Date ──────────────────────────────────────── --}}
<div class="mt-3" id="regularize-date-card">
<x-ds.card title="Regularize a Date">
    <form method="GET" action="{{ route('employee.attendance.index') }}" class="at-reg-date-row">
        <div class="at-reg-date-field">
            <label class="ef-label" for="reg_date">Select Date</label>
            <input type="date" id="reg_date" name="date" class="ef-input"
                   value="{{ $selectedDate->toDateString() }}"
                   max="{{ $todayDate->toDateString() }}"
                   onchange="this.form.submit()">
        </div>
        <noscript><button type="submit" class="ef-btn">View</button></noscript>
    </form>

    @php
        $regAttendance = $dayState['attendance'];
        $regChipForDate = $statusChips[$regAttendance->status ?? 'not_marked'] ?? $statusChips['not_marked'];
    @endphp

    <div class="at-reg-status-block">
        <div style="font-weight:700;margin-bottom:2px">{{ $selectedDate->format('d M Y (l)') }}</div>

        @if($regAttendance)
            <div style="margin-bottom:8px">Current status:
                <span style="display:inline-flex;align-items:center;border-radius:6px;font-size:.74rem;font-weight:700;padding:3px 10px;background:{{ $regChipForDate['bg'] }};color:{{ $regChipForDate['color'] }}">
                    {{ $regChipForDate['label'] }}
                </span>
            </div>
        @elseif($dayState['category'] !== 'weekday')
            <div style="margin-bottom:8px">
                <span style="display:inline-flex;align-items:center;border-radius:6px;font-size:.74rem;font-weight:700;padding:3px 10px;background:{{ $statusChips[$dayState['category'] === 'holiday' ? 'holiday' : 'weekly_off']['bg'] }};color:{{ $statusChips[$dayState['category'] === 'holiday' ? 'holiday' : 'weekly_off']['color'] }}">
                    {{ $dayState['category'] === 'holiday' ? 'Holiday' : 'Weekly Off' }}
                </span>
            </div>
        @elseif($dayState['has_approved_leave'])
            <div style="margin-bottom:8px">
                <span style="display:inline-flex;align-items:center;border-radius:6px;font-size:.74rem;font-weight:700;padding:3px 10px;background:{{ $statusChips['leave']['bg'] }};color:{{ $statusChips['leave']['color'] }}">
                    Approved Leave
                </span>
            </div>
        @else
            <div style="margin-bottom:8px">
                <span style="display:inline-flex;align-items:center;border-radius:6px;font-size:.74rem;font-weight:700;padding:3px 10px;background:{{ $statusChips['not_marked']['bg'] }};color:{{ $statusChips['not_marked']['color'] }}">
                    Not Marked
                </span>
            </div>
        @endif

        @if(!$dayState['eligible'])
            @if($dayState['pending_regularization'])
                <div class="at-reg-block-msg">
                    Regularization request already submitted —
                    <span style="text-transform:capitalize;font-weight:600">{{ $dayState['pending_regularization']->request_status }}</span>.
                    <a href="{{ route('employee.attendance-regularizations.show', $dayState['pending_regularization']) }}">View Request</a>
                </div>
            @else
                <div class="at-reg-block-msg">{{ $dayState['block_reason'] }}</div>
            @endif
        @else
            <p class="at-reg-block-msg" style="margin-bottom:10px">You can request a correction for this date.</p>
            <form method="POST" action="{{ route('employee.attendance-regularizations.store') }}">
                @csrf
                <input type="hidden" name="attendance_date" value="{{ $selectedDate->toDateString() }}">

                <div class="ef-form-grid ef-form-grid-2">
                    <div>
                        <label class="ef-label" for="reg_requested_status">Requested Status</label>
                        <select id="reg_requested_status" name="requested_status" class="ef-select" required>
                            @foreach(\App\Models\EmployeeAttendanceRegularization::requestableStatuses() as $status)
                                <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="margin-top:10px">
                    <label class="ef-label" for="reg_reason">Reason <span style="color:var(--ef-faint,#6b7280);font-weight:400;text-transform:none;letter-spacing:0">(optional)</span></label>
                    <textarea id="reg_reason" name="reason" rows="2" class="ef-textarea"></textarea>
                </div>

                <div style="margin-top:10px">
                    <button type="submit" class="ef-btn ef-btn-dark">
                        <i class="bi bi-send"></i> Submit Regularization
                    </button>
                </div>
            </form>
        @endif
    </div>
</x-ds.card>
</div>

{{-- My Regularization Requests ─────────────────────────────── --}}
@php
$regularizationChips = [
    'pending'   => ['bg' => 'rgba(216,154,61,.13)', 'color' => '#7D5218'],
    'approved'  => ['bg' => 'rgba(15,123,95,.11)',  'color' => '#0A5240'],
    'rejected'  => ['bg' => 'rgba(200,75,68,.11)',  'color' => '#9B2C2C'],
    'cancelled' => ['bg' => 'rgba(100,116,139,.11)','color' => '#334155'],
];
@endphp
<div class="mt-3">
<x-ds.card title="My Regularization Requests">
    <div class="at-list">
        @forelse($regularizations as $reg)
            @php $regChip = $regularizationChips[$reg->request_status] ?? $regularizationChips['pending']; @endphp
            <a href="{{ route('employee.attendance-regularizations.show', $reg) }}" class="at-list-row" style="text-decoration:none;color:inherit">
                <div class="at-list-row-meta">
                    <div class="at-list-date">{{ $reg->attendance_date->format('d M Y') }} — <span style="text-transform:capitalize">{{ str_replace('_', ' ', $reg->requested_status) }}</span> requested</div>
                    @if($reg->reason)
                        <div class="at-list-day" style="overflow-wrap:anywhere">{{ Illuminate\Support\Str::limit($reg->reason, 60) }}</div>
                    @endif
                    @if($reg->review_note)
                        <div class="at-list-day" style="overflow-wrap:anywhere">Manager note: {{ $reg->review_note }}</div>
                    @endif
                </div>
                <div class="at-list-row-aside">
                    <span style="display:inline-flex;align-items:center;border-radius:6px;font-size:.74rem;font-weight:700;padding:3px 10px;background:{{ $regChip['bg'] }};color:{{ $regChip['color'] }};text-transform:capitalize;white-space:nowrap">
                        {{ $reg->request_status }}
                    </span>
                </div>
            </a>
        @empty
            <div class="at-list-empty">
                <i class="bi bi-pencil-square" style="font-size:1.5rem;display:block;margin-bottom:8px"></i>
                No regularization requests yet.
            </div>
        @endforelse
    </div>
</x-ds.card>
</div>

@push('scripts')
<script>
(function () {
    // Progressive enhancement only — the "Regularize" links are real <a
    // href="...?date=X#regularize-date-card"> links that work with no JS at
    // all (full page navigation lands on the correctly-eligible form for
    // that date, pre-filled server-side). When JS is available AND the
    // clicked date is already the one loaded on this page, skip the reload
    // and just scroll+focus for a smoother feel — no date is ever
    // client-side "computed" here, this is purely a UX shortcut.
    var currentSelectedDate = @json($selectedDate->toDateString());

    document.querySelectorAll('.js-regularize-link').forEach(function (link) {
        link.addEventListener('click', function (e) {
            if (link.dataset.date !== currentSelectedDate) {
                return; // let the normal link navigation happen
            }

            var card = document.getElementById('regularize-date-card');
            if (!card) {
                return;
            }

            e.preventDefault();
            card.scrollIntoView({ behavior: 'smooth', block: 'start' });

            var reason = document.getElementById('reg_reason');
            if (reason) {
                window.setTimeout(function () { reason.focus(); }, 300);
            }
        });
    });
})();
</script>
@endpush

</x-admin-layout>
