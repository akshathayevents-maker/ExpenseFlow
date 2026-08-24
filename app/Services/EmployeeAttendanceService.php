<?php

namespace App\Services;

use App\Models\EmployeeAttendance;
use App\Models\EmployeeAttendanceRegularization;
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
 *      approved Leave (no attendance row yet)  >  Not Marked.
 *    Holiday/weekly-off outrank everything, including an existing
 *    attendance row, because the calendar fact is fixed and any
 *    conflicting historical row is itself the anomaly worth surfacing as
 *    "Holiday", not silently masking it as "Present". An actual attendance
 *    row outranks approved leave because it reflects what really happened
 *    (e.g. the employee came in despite approved leave, or a correction
 *    was recorded) — approved leave is only a fallback prediction for days
 *    that have no attendance row at all.
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
 *    holiday/weekly-off or already covered by approved leave — a
 *    regularization must never silently overwrite an approved leave day.
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
    ) {}

    public function today(): Carbon
    {
        return Carbon::now(self::BUSINESS_TIMEZONE)->startOfDay();
    }

    public function markPresent(User $user): EmployeeAttendance
    {
        return $this->mark($user, 'present');
    }

    public function markHalfDay(User $user): EmployeeAttendance
    {
        return $this->mark($user, 'half_day');
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
        $today = $this->today();
        $end   = $monthStart->copy()->endOfMonth();
        if ($end->gt($today)) {
            $end = $today->copy();
        }

        if ($end->lt($monthStart)) {
            return collect(); // entire requested month is in the future
        }

        $attendanceByDate = EmployeeAttendance::where('user_id', $user->id)
            ->whereDate('attendance_date', '>=', $monthStart->toDateString())
            ->whereDate('attendance_date', '<=', $end->toDateString())
            ->get()
            ->keyBy(fn ($row) => $row->attendance_date->toDateString());

        $approvedLeaveRanges = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $monthStart->toDateString())
            ->get(['start_date', 'end_date', 'is_half_day']);

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
            $leaveMatch = $this->matchingApprovedLeave($date, $approvedLeaveRanges);

            if ($category === 'holiday') {
                $status = 'holiday';
            } elseif ($category === 'weekend') {
                $status = 'weekly_off';
            } elseif ($hasAttendance) {
                $status = $attendanceByDate[$dateStr]->status;
            } elseif ($leaveMatch !== null) {
                $status = $leaveMatch->is_half_day ? 'half_day_leave' : 'leave';
            } else {
                $status = 'not_marked';
            }

            $canRegularize = $category === 'weekday'
                && ! $hasAttendance
                && $leaveMatch === null
                && ! isset($pendingRegularizationDates[$dateStr]);

            $days->push(['date' => $date->copy(), 'status' => $status, 'can_regularize' => $canRegularize]);
        }

        return $days->reverse()->values();
    }

    public function getMonthlySummary(User $user, Carbon $monthStart): array
    {
        $history = $this->getMonthlyHistory($user, $monthStart);

        $end = $monthStart->copy()->endOfMonth();
        if ($end->gt($this->today())) {
            $end = $this->today()->copy();
        }

        return [
            'present'      => $history->where('status', 'present')->count(),
            'half_day'     => $history->where('status', 'half_day')->count(),
            'leave'        => $history->whereIn('status', ['leave', 'half_day_leave'])->count(),
            'weekly_off'   => $history->where('status', 'weekly_off')->count(),
            'holiday'      => $history->where('status', 'holiday')->count(),
            'absent'       => $history->where('status', 'absent')->count(),
            'not_marked'   => $history->where('status', 'not_marked')->count(),
            'payable_days' => $end->gte($monthStart)
                ? $this->payableDaysCalculator->payableDaysSoFar($user, $monthStart, $end)
                : 0.0,
        ];
    }

    private function matchingApprovedLeave(Carbon $date, Collection $ranges): ?LeaveRequest
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

    private function mark(User $user, string $status): EmployeeAttendance
    {
        $today = $this->today();

        if ($this->payableDaysCalculator->categoryForDate($today) !== 'weekday') {
            throw ValidationException::withMessages([
                'attendance' => 'Attendance cannot be marked on a holiday or weekly off.',
            ]);
        }

        if (EmployeeAttendance::where('user_id', $user->id)->whereDate('attendance_date', $today->toDateString())->exists()) {
            throw ValidationException::withMessages([
                'attendance' => 'Attendance for today has already been marked.',
            ]);
        }

        $attendance = EmployeeAttendance::create([
            'user_id'         => $user->id,
            'attendance_date' => $today->toDateString(),
            'status'          => $status,
            'marked_by'       => $user->id,
            'marked_at'       => now(),
            'source'          => 'self',
        ]);

        $this->auditLogService->log('marked', 'employee_attendance', $attendance->id, $user->name, [], [
            'status' => $status,
            'date'   => $today->toDateString(),
        ]);

        return $attendance;
    }

    // ── Attendance regularization ────────────────────────────────────────

    public function createRegularization(User $user, array $data): EmployeeAttendanceRegularization
    {
        $date = Carbon::parse($data['attendance_date'])->startOfDay();

        $this->assertRegularizable($user, $date);

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

        DB::transaction(function () use ($regularization, $approver, $note, $date) {
            // Re-verified INSIDE the transaction — a holiday could have been
            // added, or a leave request approved, between submission and
            // review. Throwing here rolls back cleanly since nothing has
            // been written yet.
            $this->assertRegularizable($regularization->user, $date);

            $attendance = EmployeeAttendance::where('user_id', $regularization->user_id)
                ->whereDate('attendance_date', $date->toDateString())
                ->first();

            $previousStatus = $attendance?->status;

            if ($attendance) {
                $attendance->update([
                    'status'             => $regularization->requested_status,
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

    public function cancelRegularization(EmployeeAttendanceRegularization $regularization, User $actor): void
    {
        $regularization->forceFill(['request_status' => 'cancelled'])->save();

        $this->auditLogService->log('cancelled', 'employee_attendance_regularization', $regularization->id, $regularization->user->name, [], [
            'actor_id' => $actor->id,
        ]);
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
     *   pending_regularization (?EmployeeAttendanceRegularization),
     *   eligible (bool), block_reason (?string).
     */
    public function getAttendanceDayState(User $user, Carbon $date): array
    {
        $isFuture         = $date->gt($this->today());
        $category         = $this->payableDaysCalculator->categoryForDate($date);
        $attendance       = EmployeeAttendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $date->toDateString())
            ->first();
        $hasApprovedLeave = $this->hasApprovedLeave($user, $date);
        $pending          = EmployeeAttendanceRegularization::where('user_id', $user->id)
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
        } elseif ($hasApprovedLeave) {
            $blockReason = 'Approved Leave — regularization is not available.';
        } elseif ($pending) {
            $blockReason = 'Regularization request already submitted.';
        }

        return [
            'date'                   => $date,
            'is_future'              => $isFuture,
            'category'               => $category,
            'attendance'             => $attendance,
            'has_approved_leave'     => $hasApprovedLeave,
            'pending_regularization' => $pending,
            'eligible'               => $blockReason === null,
            'block_reason'           => $blockReason,
        ];
    }

    private function hasApprovedLeave(User $user, Carbon $date): bool
    {
        return LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date->toDateString())
            ->whereDate('end_date', '>=', $date->toDateString())
            ->exists();
    }

    /**
     * Shared guard for both createRegularization() and approveRegularization():
     * a regularization must never target a future date, a holiday/weekly-off,
     * or a date already covered by approved leave (case D/E/F from the spec).
     */
    private function assertRegularizable(User $user, Carbon $date): void
    {
        if ($date->gt($this->today())) {
            throw ValidationException::withMessages([
                'attendance_date' => 'Cannot regularize a future date.',
            ]);
        }

        if ($this->payableDaysCalculator->categoryForDate($date) !== 'weekday') {
            throw ValidationException::withMessages([
                'attendance_date' => 'Attendance cannot be regularized on a holiday or weekly off.',
            ]);
        }

        if ($this->hasApprovedLeave($user, $date)) {
            throw ValidationException::withMessages([
                'attendance_date' => 'This date is already covered by approved leave and cannot be regularized.',
            ]);
        }
    }
}
