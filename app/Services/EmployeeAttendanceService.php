<?php

namespace App\Services;

use App\Models\EmployeeAttendance;
use App\Models\EmployeeAttendanceRegularization;
use App\Models\EmployeeAttendanceSegment;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Employee-facing attendance workflow (self-service only — mark today's own
 * attendance, view own history/summary). Never accepts a date from the
 * client: "today" is always resolved server-side in the business timezone.
 *
 * Day classification (weekday/weekend/holiday) is delegated entirely to
 * PayableDaysCalculator::categoryForDate() — this class must never re-derive
 * weekly-off/holiday logic itself.
 *
 * ── Locked business rules (v1) ──────────────────────────────────────────
 *
 * 1. HOLIDAY / WEEKLY-OFF ARE NOT MARKABLE ATTENDANCE DAYS. An employee
 *    working on a holiday/weekly-off is tracked through the separate
 *    Overtime module (which already has its own weekday/weekend/holiday
 *    multiplier), not by repurposing the attendance "present" status.
 *    Therefore markPresent()/markHalfDay() reject the call outright if
 *    today is a holiday or weekly-off — enforced HERE, not just hidden in
 *    Blade, since the browser is never the security boundary.
 *
 * 2. DISPLAY PRIORITY FOR A GIVEN DAY (highest wins):
 *      Holiday  >  Weekly Off  >  actual EmployeeAttendance row  >
 *      approved Leave (no attendance row yet)  >  pending Leave  >
 *      Not Marked.
 *    Holiday/weekly-off outrank everything, including an existing
 *    attendance row, because the calendar fact is fixed and any
 *    conflicting historical row is itself the anomaly worth surfacing as
 *    "Holiday", not silently masking it as "Present". An actual attendance
 *    row outranks approved leave because it reflects what really happened
 *    (e.g. the employee came in despite approved leave, or a correction
 *    was recorded) — approved leave is only a fallback prediction for days
 *    that have no attendance row at all. A PENDING leave request outranks
 *    "Not Marked" too, but sits below approved leave: the employee has
 *    already asked not to be expected at work that day, so the day must
 *    not be silently offered up as "forgot to mark, regularize?" while the
 *    request is still awaiting a decision. Rejected/cancelled leave
 *    requests carry no weight at all — the day falls straight through to
 *    whatever it would have been with no leave request at all.
 *
 * 3. HALF-DAY LEAVE. LeaveRequest.is_half_day distinguishes a half-day
 *    leave request from a full-day one. When no attendance row exists for
 *    a date covered by an approved leave request, the displayed status is
 *    'half_day_leave' if that request is half-day, else 'leave' — mirrors
 *    the two statuses EmployeeAttendance itself already supports.
 *
 * 4. ATTENDANCE REGULARIZATION. Employees never edit employee_attendance
 *    directly — they submit an EmployeeAttendanceRegularization request
 *    (present|half_day only; leave goes through LeaveRequest, holiday/
 *    weekly_off are system-derived, absent has no legitimate self-request
 *    use case). A request is rejected outright, at submission AND again at
 *    approval time (a holiday could be added in between), if the date is a
 *    holiday/weekly-off or already covered by an approved OR PENDING leave
 *    request — a regularization must never silently overwrite an approved
 *    leave day, and must never race a pending leave decision either
 *    (approve the leave after the regularization already claimed the day,
 *    and the day would show two conflicting truths). This is enforced in
 *    assertRegularizable() itself, not just hidden in Blade — the browser
 *    is never the security boundary, so a direct POST to the
 *    regularization endpoint for such a date is rejected the same way.
 *    Approval atomically flips the regularization to 'approved' AND
 *    creates/updates the employee_attendance row in one DB transaction; if
 *    the attendance write fails, the whole approval rolls back.
 */
class EmployeeAttendanceService
{
    private const BUSINESS_TIMEZONE = 'Asia/Kolkata';

    public function __construct(
        private PayableDaysCalculator $payableDaysCalculator,
        private AuditLogService $auditLogService,
        private AttendanceConflictChecker $conflictChecker,
    ) {}

    public function today(): Carbon
    {
        return Carbon::now(self::BUSINESS_TIMEZONE)->startOfDay();
    }

    public function markPresent(User $user, ?string $period = null): EmployeeAttendance|EmployeeAttendanceSegment
    {
        return $this->mark($user, 'present', $period);
    }

    /**
     * A NEW half-day self-mark must always specify which half — an
     * unqualified "half day" with no period is exactly the ambiguous
     * NULL-period row this system has been working to eliminate (see
     * AttendanceConflictChecker's conservative NULL-handling, which exists
     * to safely absorb EXISTING historical NULL rows, not to license new
     * ones). Enforced HERE, at the service boundary, so a direct call
     * (bypassing the HTTP layer/FormRequest entirely) is rejected the same
     * way — the browser is never the security boundary.
     */
    public function markHalfDay(User $user, ?string $halfDayPeriod = null): EmployeeAttendance|EmployeeAttendanceSegment
    {
        if ($halfDayPeriod === null) {
            throw ValidationException::withMessages([
                'half_day_period' => 'Please select which half of the day (First Half or Second Half).',
            ]);
        }

        return $this->mark($user, 'half_day', $halfDayPeriod);
    }

    /**
     * True if today is a holiday or weekly-off — attendance cannot be
     * marked on such a day (rule #1). Used by the controller/Blade to
     * hide the mark buttons, but mark() enforces this independently.
     */
    public function isTodayNonWorking(): bool
    {
        return $this->payableDaysCalculator->categoryForDate($this->today()) !== 'weekday';
    }

    public function todayCategory(): string
    {
        return $this->payableDaysCalculator->categoryForDate($this->today());
    }

    /**
     * Attendance-first login gate (EnsureAttendanceMarked middleware):
     * true only when the employee genuinely has something to mark today.
     * Reuses isTodayNonWorking()/getToday()/hasApprovedLeave() — the same
     * pieces getAttendanceDayState() and assertRegularizable() already use
     * — so this is never a second, independently-drifting holiday/leave
     * calculation.
     */
    public function needsAttendanceToday(User $user): bool
    {
        if ($this->isTodayNonWorking()) {
            return false;
        }

        if ($this->hasApprovedLeave($user, $this->today())) {
            return false;
        }

        return $this->getToday($user) === null;
    }

    public function getToday(User $user): ?EmployeeAttendance
    {
        return EmployeeAttendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $this->today()->toDateString())
            ->first();
    }

    /**
     * Reverse self-mark affordance (gap #1): returns the period the
     * employee can still self-mark present TODAY, or null if there is
     * nothing left to mark (no attendance yet at all — the plain
     * full-day buttons cover that case — or the day is already fully
     * occupied). Only ever returns non-null when today's primary
     * EmployeeAttendance row is itself half-day-family with a KNOWN
     * period, and no segment already occupies the complementary period —
     * i.e. exactly the "AM leave, PM open" / "PM leave, AM open" case.
     */
    public function markableOtherHalfToday(User $user): ?string
    {
        $today = $this->getToday($user);
        if ($today === null) {
            return null;
        }

        $isHalfDayFamily = in_array($today->status, ['half_day', 'half_day_leave', 'half_day_lop'], true);
        if (! $isHalfDayFamily || $today->half_day_period === null) {
            return null;
        }

        $otherPeriod = $today->half_day_period === 'first_half' ? 'second_half' : 'first_half';

        $occupied = EmployeeAttendanceSegment::where('user_id', $user->id)
            ->whereDate('attendance_date', $this->today()->toDateString())
            ->where('period', $otherPeriod)
            ->exists();

        return $occupied ? null : $otherPeriod;
    }

    /**
     * Builds a day-by-day view of the month, newest first, clamped so it
     * never shows a date after today (no future attendance is ever
     * fabricated). Each entry:
     *   ['date' => Carbon, 'status' => string, 'can_regularize' => bool]
     * where status is one of: present, half_day, leave, half_day_leave,
     * absent, weekly_off, holiday, not_marked.
     *
     * can_regularize is a NARROWER, additive UI-affordance flag on top of
     * assertRegularizable()'s rules — it only shows the quick "Regularize"
     * action for the common "forgot to mark" case (weekday, unmarked, no
     * approved leave, no existing pending request). It does NOT change what
     * the server actually allows: assertRegularizable() still permits
     * regularizing an already-marked date (e.g. correcting half_day to
     * present) via the manual date-picker form below — this flag only
     * governs whether the inline list-row shortcut button appears. All
     * inputs are the same batch-fetched collections already used for
     * `status` above, plus one additional batched query for pending
     * regularizations across the whole range — no N+1 per row.
     */
    public function getMonthlyHistory(User $user, Carbon $monthStart): Collection
    {
        // Compare by calendar date only, not Carbon instant: this->today()
        // is anchored to BUSINESS_TIMEZONE while $monthStart may be
        // constructed in the app's default timezone (e.g. resolveMonth()'s
        // Carbon::create() in AttendanceController, which is UTC). Comparing
        // instants directly (->gt()/->lt()) can make "today" in the
        // business timezone look like an earlier instant than the UTC
        // representation of the same calendar day (IST is ahead of UTC),
        // wrongly clamping/emptying the whole month. Re-anchoring $today to
        // $monthStart's timezone (by date string, never by instant) keeps
        // every comparison and the day-by-day loop below in one consistent
        // timezone. Same pattern as matchingLeave()'s docblock.
        $today = Carbon::parse($this->today()->toDateString(), $monthStart->getTimezone());
        $end   = $monthStart->copy()->endOfMonth();
        if ($end->toDateString() > $today->toDateString()) {
            $end = $today->copy();
        }

        if ($end->toDateString() < $monthStart->toDateString()) {
            return collect(); // entire requested month is in the future
        }

        $attendanceByDate = EmployeeAttendance::where('user_id', $user->id)
            ->whereDate('attendance_date', '>=', $monthStart->toDateString())
            ->whereDate('attendance_date', '<=', $end->toDateString())
            ->with('leaveRequest.leaveType')
            ->get()
            ->keyBy(fn ($row) => $row->attendance_date->toDateString());

        $approvedLeaveRanges = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $monthStart->toDateString())
            ->with('leaveType')
            ->get(['id', 'leave_type_id', 'start_date', 'end_date', 'is_half_day', 'half_day_period']);

        // Rejected/cancelled requests are deliberately NOT queried here —
        // they carry no weight at all, per the class docblock's precedence
        // rule; only pending/approved ever affect a day's status.
        $pendingLeaveRanges = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $monthStart->toDateString())
            ->with('leaveType')
            ->get(['id', 'leave_type_id', 'start_date', 'end_date', 'is_half_day']);

        $segmentsByDate = EmployeeAttendanceSegment::where('user_id', $user->id)
            ->whereDate('attendance_date', '>=', $monthStart->toDateString())
            ->whereDate('attendance_date', '<=', $end->toDateString())
            ->with('leaveRequest.leaveType')
            ->get()
            ->groupBy(fn ($row) => $row->attendance_date->toDateString());

        $pendingRegularizationDates = EmployeeAttendanceRegularization::where('user_id', $user->id)
            ->where('request_status', 'pending')
            ->whereDate('attendance_date', '>=', $monthStart->toDateString())
            ->whereDate('attendance_date', '<=', $end->toDateString())
            ->get(['attendance_date'])
            ->map(fn ($row) => $row->attendance_date->toDateString())
            ->flip();

        $days = collect();
        for ($date = $monthStart->copy(); $date->lte($end); $date->addDay()) {
            $dateStr  = $date->toDateString();
            $category = $this->payableDaysCalculator->categoryForDate($date);
            $hasAttendance = isset($attendanceByDate[$dateStr]);
            $approvedMatch = $this->matchingLeave($date, $approvedLeaveRanges);
            $pendingMatch  = $approvedMatch === null ? $this->matchingLeave($date, $pendingLeaveRanges) : null;
            $leaveTypeName = null;
            $otherHalfStatus = null;
            $otherHalfLabel = null;

            /** @var \Illuminate\Support\Collection|null $segmentsToday */
            $segmentsToday = $segmentsByDate[$dateStr] ?? null;
            if ($segmentsToday !== null && $segmentsToday->isNotEmpty()) {
                $segment = $segmentsToday->first();
                $otherHalfStatus = $segment->status;
                $otherHalfLabel = match (true) {
                    $segment->status === 'present' => 'Present (other half)',
                    $segment->leaveRequest?->leaveType?->name !== null =>
                        $segment->leaveRequest->leaveType->name . ($segment->status === 'lop' ? ' — LOP (other half)' : ' (other half)'),
                    $segment->status === 'lop' => 'LOP (other half)',
                    default => 'Leave (other half)',
                };
            }

            if ($category === 'holiday') {
                $status = 'holiday';
            } elseif ($category === 'weekend') {
                $status = 'weekly_off';
            } elseif ($hasAttendance) {
                $row = $attendanceByDate[$dateStr];
                $status = $row->status;
                // A real row written by leave approval (source='leave_approval')
                // carries its own leave_request_id — surface that leave
                // type name too, so "On Leave / Casual Leave" still shows
                // once the virtual fallback below is no longer reached.
                $leaveTypeName = $row->leaveRequest?->leaveType?->name;

                // Half-day coexistence: this attendance row only covers ONE
                // half of the date. If an approved leave exists for the
                // date and its period is the COMPLEMENTARY (non-conflicting)
                // half, surface both facts — the attendance status stays as
                // the displayed `status` (it reflects what actually
                // happened), but we append the leave type name so the other
                // half's approved leave isn't silently hidden.
                if ($leaveTypeName === null && $approvedMatch !== null) {
                    $rowIsFullDay = ! in_array($row->status, ['half_day', 'half_day_leave', 'half_day_lop'], true);
                    $conflicts = $this->conflictChecker->periodsOverlap(
                        $rowIsFullDay, $row->half_day_period,
                        ! $approvedMatch->is_half_day, $approvedMatch->half_day_period,
                    );
                    if (! $conflicts) {
                        $leaveTypeName = ($approvedMatch->leaveType?->name ?? 'Leave') . ' (other half)';
                    }
                }
            } elseif ($approvedMatch !== null) {
                $status = $approvedMatch->is_half_day ? 'half_day_leave' : 'leave';
                $leaveTypeName = $approvedMatch->leaveType?->name;
            } elseif ($pendingMatch !== null) {
                $status = 'leave_pending';
                $leaveTypeName = $pendingMatch->leaveType?->name;
            } else {
                $status = 'not_marked';
            }

            $canRegularize = $category === 'weekday'
                && ! $hasAttendance
                && $approvedMatch === null
                && $pendingMatch === null
                && ! isset($pendingRegularizationDates[$dateStr]);

            $days->push([
                'date' => $date->copy(),
                'status' => $status,
                'leave_type_name' => $leaveTypeName,
                'can_regularize' => $canRegularize,
                // Populated only for a genuinely split day that has an
                // independent "other half" fact recorded via
                // EmployeeAttendanceSegment (see LeaveService::writeOneAttendanceRow()
                // and EmployeeAttendanceService::approveRegularization()).
                // Null for the overwhelming majority of (unsplit) days.
                'other_half_status' => $otherHalfStatus,
                'other_half_label'  => $otherHalfLabel,
            ]);
        }

        return $days->reverse()->values();
    }

    public function getMonthlySummary(User $user, Carbon $monthStart): array
    {
        $history = $this->getMonthlyHistory($user, $monthStart);

        // Same calendar-date-only comparison as getMonthlyHistory() above —
        // see that method's docblock. $this->today() must be re-anchored to
        // $monthStart's timezone before comparing, or this clamp suffers
        // the identical instant-vs-timezone bug independently.
        $today = Carbon::parse($this->today()->toDateString(), $monthStart->getTimezone());
        $end = $monthStart->copy()->endOfMonth();
        if ($end->toDateString() > $today->toDateString()) {
            $end = $today->copy();
        }

        return [
            'present'      => $history->where('status', 'present')->count(),
            'half_day'     => $history->where('status', 'half_day')->count(),
            'leave'        => $history->whereIn('status', ['leave', 'half_day_leave'])->count(),
            'weekly_off'   => $history->where('status', 'weekly_off')->count(),
            'holiday'      => $history->where('status', 'holiday')->count(),
            'absent'       => $history->where('status', 'absent')->count(),
            'not_marked'   => $history->where('status', 'not_marked')->count(),
            'payable_days' => $end->toDateString() >= $monthStart->toDateString()
                ? $this->payableDaysCalculator->payableDaysSoFar($user, $monthStart, $end)
                : 0.0,
        ];
    }

    /**
     * Finds the leave request in $ranges (a pre-filtered, single-status
     * collection — approved OR pending, never mixed) whose date range
     * covers $date. Generic over status so the same date-matching logic
     * serves both the approved and pending lookups above without drift.
     */
    private function matchingLeave(Carbon $date, Collection $ranges): ?LeaveRequest
    {
        // Compare by calendar date string, not Carbon instant — start_date/
        // end_date are cast dates at UTC midnight, while $date is midnight in
        // the business timezone; gte()/lte() would compare actual instants
        // and silently mismatch by a few hours for the same calendar day.
        $dateStr = $date->toDateString();

        foreach ($ranges as $range) {
            if ($dateStr >= $range->start_date->toDateString() && $dateStr <= $range->end_date->toDateString()) {
                return $range;
            }
        }

        return null;
    }

    /**
     * ── Period-aware self-mark (reverse workflow) ────────────────────────
     *
     * $halfDayPeriod is nullable and defaults to null for a full-day mark —
     * this preserves every existing caller/test that never passes a
     * period. When a period IS given (either because markHalfDay() was
     * called with one, or markPresent() was called with one to mark just
     * the OTHER half present against an existing half-occupied day), the
     * write actually recorded is always the half-day-family status
     * 'half_day' with that period — 'present' + a period is not a status
     * this table's convention supports (half_day_period is only meaningful
     * on a half_day-family row, per the column's migration docblock); a
     * half-day self-mark IS "present for that half," so this loses no
     * information.
     *
     * Duplicate-guard is period-aware via the shared
     * AttendanceConflictChecker::periodsOverlap() (never a bespoke overlap
     * check here): a conflicting existing EmployeeAttendance row for today
     * blocks the mark UNLESS the existing row and this mark are
     * genuinely complementary halves. In that case the pre-existing row
     * (e.g. an approved half-day leave already written as the primary
     * row) is left completely untouched and this self-mark is written as
     * an independent EmployeeAttendanceSegment for the OTHER period —
     * mirroring exactly how LeaveService::writeOneAttendanceRow() and
     * EmployeeAttendanceService::approveRegularization() already handle
     * the same "which slot is actually free" problem for their own write
     * paths (see AttendanceConflictChecker's docblock, and the invariant
     * enforced by assertHalfNotAlreadyOccupied() below).
     */
    private function mark(User $user, string $status, ?string $halfDayPeriod = null): EmployeeAttendance|EmployeeAttendanceSegment
    {
        $today = $this->today();
        $dateStr = $today->toDateString();
        $isFullDay = $halfDayPeriod === null;

        if ($this->payableDaysCalculator->categoryForDate($today) !== 'weekday') {
            throw ValidationException::withMessages([
                'attendance' => 'Attendance cannot be marked on a holiday or weekly off.',
            ]);
        }

        // A self-mark must never race a leave request for the SAME half of
        // today — whether that request is already approved (in which case
        // it will already have written an EmployeeAttendance row, caught
        // below) or still pending (which writes nothing to attendance yet,
        // so without this check the self-mark would otherwise sail through
        // with no conflict, only for the leave's later approval to fail
        // against the row this created). Reuses the same
        // AttendanceConflictChecker-backed hasApprovedLeave()/hasPendingLeave()
        // helpers assertRegularizable() already uses — never a second,
        // independently-drifting leave-overlap check.
        if ($this->hasApprovedLeave($user, $today, $isFullDay, $halfDayPeriod)
            || $this->hasPendingLeave($user, $today, $isFullDay, $halfDayPeriod)) {
            throw ValidationException::withMessages([
                'attendance' => 'This half of today is already covered by a leave request.',
            ]);
        }

        $existing = EmployeeAttendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $dateStr)
            ->first();

        if ($existing !== null) {
            $existingIsFullDay = ! in_array($existing->status, ['half_day', 'half_day_leave', 'half_day_lop'], true);
            $conflicts = $this->conflictChecker->periodsOverlap(
                $existingIsFullDay, $existing->half_day_period,
                $isFullDay, $halfDayPeriod,
            );

            if ($conflicts) {
                throw ValidationException::withMessages([
                    'attendance' => 'Attendance for today has already been marked.',
                ]);
            }

            // Complementary half is free on the primary row — but the
            // invariant (see #4/assertHalfNotAlreadyOccupied) still forbids
            // a SECOND independently-sourced fact for that same half, so
            // this checks for (and rejects) an already-existing segment
            // before writing a new one.
            $this->conflictChecker->assertHalfNotAlreadyOccupied($user->id, $dateStr, $halfDayPeriod);

            $segment = EmployeeAttendanceSegment::create([
                'user_id'         => $user->id,
                'attendance_date' => $dateStr,
                'period'          => $halfDayPeriod,
                'status'          => 'present',
                'source'          => 'self',
                'marked_by'       => $user->id,
                'marked_at'       => now(),
            ]);

            $this->auditLogService->log('marked', 'employee_attendance_segment', $segment->id, $user->name, [], [
                'status' => 'present',
                'date'   => $dateStr,
                'period' => $halfDayPeriod,
            ]);

            return $segment;
        }

        $statusToWrite = $isFullDay ? $status : 'half_day';

        $attendance = EmployeeAttendance::create([
            'user_id'         => $user->id,
            'attendance_date' => $dateStr,
            'status'          => $statusToWrite,
            'half_day_period' => $isFullDay ? null : $halfDayPeriod,
            'marked_by'       => $user->id,
            'marked_at'       => now(),
            'source'          => 'self',
        ]);

        $this->auditLogService->log('marked', 'employee_attendance', $attendance->id, $user->name, [], [
            'status' => $statusToWrite,
            'date'   => $dateStr,
        ]);

        return $attendance;
    }


    // ── Attendance regularization ────────────────────────────────────────

    public function createRegularization(User $user, array $data): EmployeeAttendanceRegularization
    {
        $date = Carbon::parse($data['attendance_date'])->startOfDay();
        $isFullDay = $data['requested_status'] !== 'half_day';
        $period = $isFullDay ? null : ($data['half_day_period'] ?? null);

        $this->assertRegularizable($user, $date, $isFullDay, $period);

        if (EmployeeAttendanceRegularization::where('user_id', $user->id)
            ->whereDate('attendance_date', $date->toDateString())
            ->where('request_status', 'pending')
            ->exists()) {
            throw ValidationException::withMessages([
                'attendance_date' => 'A pending regularization request already exists for this date.',
            ]);
        }

        // reason is optional — the employee_attendance_regularizations.reason
        // column is NOT NULL text (unchanged schema), so an omitted reason
        // is stored as an empty string, never NULL and never an invented
        // default message.
        $regularization = EmployeeAttendanceRegularization::create([
            'user_id'          => $user->id,
            'attendance_date'  => $date->toDateString(),
            'requested_status' => $data['requested_status'],
            'half_day_period'  => $period,
            'reason'           => $data['reason'] ?? '',
            'created_by'       => $user->id,
        ]);

        $this->auditLogService->log('submitted', 'employee_attendance_regularization', $regularization->id, $user->name, [], [
            'date'             => $date->toDateString(),
            'requested_status' => $data['requested_status'],
        ]);

        return $regularization;
    }

    public function approveRegularization(EmployeeAttendanceRegularization $regularization, User $approver, ?string $note = null): void
    {
        $date = $regularization->attendance_date->copy()->startOfDay();
        $isFullDay = $regularization->requested_status !== 'half_day';
        $period = $isFullDay ? null : $regularization->half_day_period;

        DB::transaction(function () use ($regularization, $approver, $note, $date, $isFullDay, $period) {
            // Re-verified INSIDE the transaction — a holiday could have been
            // added, or a leave request approved, between submission and
            // review. Throwing here rolls back cleanly since nothing has
            // been written yet.
            $this->assertRegularizable($regularization->user, $date, $isFullDay, $period);

            $attendance = EmployeeAttendance::where('user_id', $regularization->user_id)
                ->whereDate('attendance_date', $date->toDateString())
                ->first();

            if ($this->isComplementaryHalfOverlay($attendance, $isFullDay, $period)) {
                // The existing employee_attendance row covers the OPPOSITE
                // half and stays completely untouched — this regularization's
                // own half is written as an independent overlay segment.
                $existingOwnSegment = EmployeeAttendanceSegment::where('user_id', $regularization->user_id)
                    ->whereDate('attendance_date', $date->toDateString())
                    ->where('period', $period)
                    ->where('regularization_id', $regularization->id)
                    ->where('source', 'regularization')
                    ->first();

                $this->conflictChecker->assertHalfNotAlreadyOccupied(
                    $regularization->user_id, $date->toDateString(), $period, $existingOwnSegment?->id,
                );

                $segment = EmployeeAttendanceSegment::updateOrCreate(
                    ['user_id' => $regularization->user_id, 'attendance_date' => $date->toDateString(), 'period' => $period],
                    [
                        // Regularization only ever requests present|half_day
                        // (see EmployeeAttendanceRegularization::requestableStatuses())
                        // — either way, the segment represents a WORKED half.
                        'status'           => 'present',
                        'source'           => 'regularization',
                        'regularization_id'=> $regularization->id,
                        'marked_by'        => $approver->id,
                        'marked_at'        => now(),
                    ],
                );

                $regularization->forceFill([
                    'request_status' => 'approved',
                    'reviewed_by'    => $approver->id,
                    'reviewed_at'    => now(),
                    'review_note'    => $note,
                ])->save();

                $this->auditLogService->log('approved', 'employee_attendance_regularization', $regularization->id, $regularization->user->name, [], [
                    'status'     => $regularization->requested_status,
                    'segment_id' => $segment->id,
                    'actor_id'   => $approver->id,
                ]);

                return;
            }

            $previousStatus = $attendance?->status;

            if ($attendance) {
                $attendance->update([
                    'status'             => $regularization->requested_status,
                    'half_day_period'    => $period,
                    'corrected_by'       => $approver->id,
                    'corrected_at'       => now(),
                    'correction_reason'  => "Regularization #{$regularization->id}: {$regularization->reason}",
                    'previous_status'    => $previousStatus,
                ]);
            } else {
                EmployeeAttendance::create([
                    'user_id'         => $regularization->user_id,
                    'attendance_date' => $date->toDateString(),
                    'status'          => $regularization->requested_status,
                    'half_day_period' => $period,
                    'marked_by'       => $regularization->user_id,
                    'marked_at'       => now(),
                    'source'          => 'admin',
                    'corrected_by'    => $approver->id,
                    'corrected_at'    => now(),
                    'correction_reason' => "Regularization #{$regularization->id}: {$regularization->reason}",
                ]);
            }

            $regularization->forceFill([
                'request_status' => 'approved',
                'reviewed_by'    => $approver->id,
                'reviewed_at'    => now(),
                'review_note'    => $note,
            ])->save();

            $this->auditLogService->log('approved', 'employee_attendance_regularization', $regularization->id, $regularization->user->name, ['status' => $previousStatus], [
                'status'   => $regularization->requested_status,
                'actor_id' => $approver->id,
            ]);
        });
    }

    public function rejectRegularization(EmployeeAttendanceRegularization $regularization, User $approver, string $reason): void
    {
        $regularization->forceFill([
            'request_status' => 'rejected',
            'reviewed_by'    => $approver->id,
            'reviewed_at'    => now(),
            'review_note'    => $reason,
        ])->save();

        $this->auditLogService->log('rejected', 'employee_attendance_regularization', $regularization->id, $regularization->user->name, [], [
            'reason'   => $reason,
            'actor_id' => $approver->id,
        ]);
    }

    /**
     * Cancels a pending OR approved regularization (mirrors
     * LeaveService::cancel()'s pattern for cancel-after-approval).
     *
     * - pending: pure status flip (no attendance was ever written).
     * - approved: reverses the EmployeeAttendance row this regularization's
     *   approval touched — restored to `previous_status` if one was
     *   snapshotted at approval time, or deleted entirely if the row was
     *   freshly created by that approval (previous_status null). The row's
     *   half_day_period is cleared on restore since no prior-period snapshot
     *   is captured (this feature is new; pre-existing rows never had a
     *   period to restore to).
     * - already cancelled/rejected: rejected as a clean idempotency guard.
     *
     * Defensive: if the attendance row was independently modified/deleted
     * since approval, this proceeds without crashing — it simply has
     * nothing to reverse.
     */
    public function cancelRegularization(EmployeeAttendanceRegularization $regularization, User $actor): void
    {
        DB::transaction(function () use ($regularization, $actor) {
            $regularization = EmployeeAttendanceRegularization::whereKey($regularization->id)->lockForUpdate()->firstOrFail();

            if ($regularization->isCancelled() || $regularization->isRejected()) {
                throw ValidationException::withMessages([
                    'request_status' => 'This regularization request has already been ' . $regularization->request_status . '.',
                ]);
            }

            if ($regularization->isPending()) {
                $regularization->forceFill(['request_status' => 'cancelled'])->save();

                $this->auditLogService->log('cancelled', 'employee_attendance_regularization', $regularization->id, $regularization->user->name, [], [
                    'actor_id' => $actor->id,
                ]);

                return;
            }

            $date = $regularization->attendance_date->copy()->startOfDay();

            // If this regularization's approval wrote an overlay segment
            // (complementary-half case) rather than an employee_attendance
            // row, reversing it means deleting ONLY that segment — the
            // complementary half's own employee_attendance row (a different
            // source entirely) is never touched.
            $segment = EmployeeAttendanceSegment::where('regularization_id', $regularization->id)
                ->where('source', 'regularization')
                ->first();

            if ($segment) {
                $segment->delete();

                $regularization->forceFill(['request_status' => 'cancelled'])->save();

                $this->auditLogService->log('cancelled', 'employee_attendance_regularization', $regularization->id, $regularization->user->name, [
                    'segment_status' => 'present',
                ], [
                    'actor_id'       => $actor->id,
                    'segment_status' => null,
                ]);

                return;
            }

            $attendance = EmployeeAttendance::where('user_id', $regularization->user_id)
                ->whereDate('attendance_date', $date->toDateString())
                ->first();

            $attendanceStatusBefore = $attendance?->status;
            $attendanceStatusAfter = null;

            if ($attendance) {
                if ($attendance->previous_status !== null) {
                    $attendance->update([
                        'status'            => $attendance->previous_status,
                        'previous_status'   => null,
                        'half_day_period'   => null,
                        'corrected_by'      => null,
                        'corrected_at'      => null,
                        'correction_reason' => null,
                    ]);
                    $attendanceStatusAfter = $attendance->status;
                } else {
                    $attendance->delete();
                    $attendanceStatusAfter = null;
                }
            }

            $regularization->forceFill(['request_status' => 'cancelled'])->save();

            $this->auditLogService->log('cancelled', 'employee_attendance_regularization', $regularization->id, $regularization->user->name, [
                'attendance_status' => $attendanceStatusBefore,
            ], [
                'actor_id'           => $actor->id,
                'attendance_status'  => $attendanceStatusAfter,
            ]);
        });
    }

    public function listRegularizationsForEmployee(User $user): Collection
    {
        return EmployeeAttendanceRegularization::where('user_id', $user->id)
            ->with('reviewer')
            ->latest('attendance_date')
            ->limit(10)
            ->get();
    }

    public function listRegularizationsForManager(): Collection
    {
        return EmployeeAttendanceRegularization::with(['user', 'reviewer'])
            ->latest('attendance_date')
            ->get();
    }

    /**
     * Read-only day-state summary for the attendance page's date-selection
     * card. Reuses the exact same rules as assertRegularizable()/
     * categoryForDate() — never re-derives holiday/weekly-off/leave logic —
     * so "what the page shows" and "what the server allows" can never drift
     * apart. Returns:
     *   date, is_future, category (weekday|weekend|holiday),
     *   attendance (?EmployeeAttendance), has_approved_leave (bool),
     *   approved_leave (?LeaveRequest), has_pending_leave (bool),
     *   pending_leave (?LeaveRequest),
     *   pending_regularization (?EmployeeAttendanceRegularization),
     *   eligible (bool), block_reason (?string).
     */
    public function getAttendanceDayState(User $user, Carbon $date): array
    {
        // See assertRegularizable() — compare calendar dates only, not
        // instants, since $date's timezone is not guaranteed to match
        // $this->today()'s BUSINESS_TIMEZONE anchor.
        $isFuture      = $date->toDateString() > $this->today()->toDateString();
        $category      = $this->payableDaysCalculator->categoryForDate($date);
        $attendance    = EmployeeAttendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $date->toDateString())
            ->with('leaveRequest.leaveType')
            ->first();
        $approvedLeave = $this->matchingLeaveOnDate($user, $date, 'approved');
        $pendingLeave  = $approvedLeave === null ? $this->matchingLeaveOnDate($user, $date, 'pending') : null;
        $pending       = EmployeeAttendanceRegularization::where('user_id', $user->id)
            ->whereDate('attendance_date', $date->toDateString())
            ->where('request_status', 'pending')
            ->latest('id')
            ->first();

        $blockReason = null;
        if ($isFuture) {
            $blockReason = 'Future dates cannot be regularized.';
        } elseif ($category === 'holiday') {
            $blockReason = 'Holiday — regularization is not available.';
        } elseif ($category === 'weekend') {
            $blockReason = 'Weekly Off — regularization is not available.';
        } elseif ($approvedLeave) {
            $blockReason = 'Approved Leave — regularization is not available.';
        } elseif ($pendingLeave) {
            $blockReason = 'This date has a pending leave request awaiting a decision — regularization is not available until it is resolved.';
        } elseif ($pending) {
            $blockReason = 'Regularization request already submitted.';
        }

        return [
            'date'                   => $date,
            'is_future'              => $isFuture,
            'category'               => $category,
            'attendance'             => $attendance,
            'has_approved_leave'     => $approvedLeave !== null,
            'approved_leave'         => $approvedLeave,
            'has_pending_leave'      => $pendingLeave !== null,
            'pending_leave'          => $pendingLeave,
            'pending_regularization' => $pending,
            'eligible'               => $blockReason === null,
            'block_reason'           => $blockReason,
        ];
    }

    /**
     * $isFullDay/$period describe the attendance/regularization side being
     * checked (default full-day, for callers like needsAttendanceToday()
     * that only ever concern today's plain mark). A period-aware caller
     * (assertRegularizable()) passes the regularization's own half-day
     * period so a half-day request only conflicts with a leave on the
     * SAME half, per the shared AttendanceConflictChecker rule.
     */
    private function hasApprovedLeave(User $user, Carbon $date, bool $isFullDay = true, ?string $period = null): bool
    {
        return $this->leaveConflictsOnDate($user, $date, 'approved', $isFullDay, $period);
    }

    private function hasPendingLeave(User $user, Carbon $date, bool $isFullDay = true, ?string $period = null): bool
    {
        return $this->leaveConflictsOnDate($user, $date, 'pending', $isFullDay, $period);
    }

    private function leaveConflictsOnDate(User $user, Carbon $date, string $status, bool $isFullDay, ?string $period): bool
    {
        $leave = $this->matchingLeaveOnDate($user, $date, $status);
        if ($leave === null) {
            return false;
        }

        return $this->conflictChecker->periodsOverlap($isFullDay, $period, ! $leave->is_half_day, $leave->half_day_period);
    }

    private function matchingLeaveOnDate(User $user, Carbon $date, string $status): ?LeaveRequest
    {
        return LeaveRequest::where('user_id', $user->id)
            ->where('status', $status)
            ->whereDate('start_date', '<=', $date->toDateString())
            ->whereDate('end_date', '>=', $date->toDateString())
            ->with('leaveType')
            ->first();
    }

    /**
     * Shared guard for both createRegularization() and approveRegularization():
     * a regularization must never target a future date, a holiday/weekly-off,
     * or a date already covered by an approved OR pending leave request
     * (case D/E/F from the spec, plus the pending-race case) — enforced
     * here so a direct call to the regularization endpoint is rejected
     * server-side, never relying on the button simply being hidden.
     */
    private function assertRegularizable(User $user, Carbon $date, bool $isFullDay = true, ?string $period = null): void
    {
        // Compare calendar dates only (not instants): $date may have been
        // parsed in the app's default UTC timezone (e.g. from a plain
        // 'Y-m-d' request string), while $this->today() is anchored to
        // BUSINESS_TIMEZONE. Comparing Carbon instants directly (->gt())
        // would make a same-calendar-day submission look "in the future"
        // whenever the business timezone is ahead of UTC.
        if ($date->toDateString() > $this->today()->toDateString()) {
            throw ValidationException::withMessages([
                'attendance_date' => 'Cannot regularize a future date.',
            ]);
        }

        if ($this->payableDaysCalculator->categoryForDate($date) !== 'weekday') {
            throw ValidationException::withMessages([
                'attendance_date' => 'Attendance cannot be regularized on a holiday or weekly off.',
            ]);
        }

        if ($this->hasApprovedLeave($user, $date, $isFullDay, $period)) {
            throw ValidationException::withMessages([
                'attendance_date' => 'This date is already covered by approved leave and cannot be regularized.',
            ]);
        }

        if ($this->hasPendingLeave($user, $date, $isFullDay, $period)) {
            throw ValidationException::withMessages([
                'attendance_date' => 'This date has a pending leave request awaiting a decision and cannot be regularized.',
            ]);
        }
    }

    /**
     * True only for the narrow "genuinely complementary half" case: an
     * existing attendance row that is itself half-day, on a KNOWN period,
     * that DIFFERS from the regularization's own (also half-day, known)
     * period. Every other combination — no existing row, a full-day
     * existing row, a same-period existing row (a legitimate correction,
     * e.g. half_day -> present on the SAME half), or unknown/null periods —
     * is deliberately left to the existing overwrite behavior in
     * approveRegularization(), unchanged from before this feature.
     */
    private function isComplementaryHalfOverlay(?EmployeeAttendance $existing, bool $isFullDay, ?string $period): bool
    {
        if ($existing === null || $isFullDay || $period === null) {
            return false;
        }

        $existingIsHalfDay = in_array($existing->status, ['half_day', 'half_day_leave', 'half_day_lop'], true);
        if (! $existingIsHalfDay || $existing->half_day_period === null) {
            return false;
        }

        return $existing->half_day_period !== $period;
    }
}
