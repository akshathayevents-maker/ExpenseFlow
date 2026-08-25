<?php

namespace App\Services;

use App\Models\EmployeeAttendance;
use App\Models\Holiday;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * SINGLE source of truth for working-day and payable-day calculations.
 *
 * Both salary/advance-eligibility calculation and overtime calculation must
 * depend on THIS class rather than each re-deriving working days on their
 * own — that's the entire reason this class exists (see the Phase-2.5
 * architecture review that identified the duplication risk).
 *
 * This class does NOT calculate salary or money. It only counts days.
 * Salary math is a separate responsibility, deliberately kept out of here.
 *
 * All date-range iteration is calendar-DATE based (no time component) and
 * assumes callers already resolved "today" via the business timezone
 * (Asia/Kolkata) — this class never calls now()/today() itself, so it is
 * trivially unit-testable with any fixed date range.
 */
class PayableDaysCalculator
{
    /**
     * Number of calendar dates in [from, to] that are actual working days:
     * within the employee's employment period, not a configured weekly-off
     * weekday, and not an active company holiday.
     *
     * Attendance is NOT consulted here — this is the denominator (how many
     * working days existed), not how many were actually worked/paid.
     */
    public function applicableWorkingDays(User $user, Carbon $from, Carbon $to): int
    {
        return count($this->applicableWorkingDates($user, $from, $to));
    }

    /**
     * The actual calendar dates within [from, to] that are applicable
     * working days (employment period, not weekly-off, not a holiday) —
     * the same rule applicableWorkingDays() counts, exposed as a date list
     * for callers that need to assign something per-date (e.g. LeaveService
     * splitting a multi-day request's paid vs LOP days chronologically).
     *
     * @return Carbon[]
     */
    public function applicableWorkingDates(User $user, Carbon $from, Carbon $to): array
    {
        [$start, $end] = $this->clampToEmploymentPeriod($user, $from, $to);
        if ($start === null) {
            return [];
        }

        $weeklyOffDays = $this->weeklyOffDays();
        $holidayDates  = $this->holidayDateSet($start, $end);

        $dates = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if (! $this->isNonWorkingDay($date, $weeklyOffDays, $holidayDates)) {
                $dates[] = $date->copy();
            }
        }

        return $dates;
    }

    /**
     * Payable days within [from, to], clamped to employment period:
     *   present            = 1.0
     *   half_day           = 0.5
     *   leave              = 1.0   (approved PAID leave — see note below)
     *   half_day_leave     = 0.5
     *   lop                = 0.0   (Loss of Pay — unpaid by definition)
     *   half_day_lop       = 0.5   (the worked half is payable, the LOP half isn't)
     *   absent / unmarked  = 0.0
     *   weekly-off/holiday = 0.0   (excluded entirely, never contribute)
     *
     * NOTE on paid/unpaid leave: 'leave'/'half_day_leave' attendance rows
     * are only ever written for the PAID portion of an approved leave
     * request (LeaveService splits paid_leave_days/lop_days at request
     * time); the unpaid portion is written as 'lop'/'half_day_lop' instead.
     * This method does not know anything about leave types, policies, or
     * balances — it only reacts to the attendance status string, exactly
     * as it always has. LOP is deliberately NOT a leave type (see
     * LeaveService) — it is a property of how a request was fulfilled.
     *
     * Uses half-unit integer arithmetic internally (values doubled, summed
     * as integers, halved once at the end) rather than accumulating floats
     * across a loop, so no float-precision drift is possible regardless of
     * range length.
     */
    public function payableDaysSoFar(User $user, Carbon $from, Carbon $to): float
    {
        [$start, $end] = $this->clampToEmploymentPeriod($user, $from, $to);
        if ($start === null) {
            return 0.0;
        }

        $weeklyOffDays = $this->weeklyOffDays();
        $holidayDates  = $this->holidayDateSet($start, $end);
        $attendanceByDate = $this->attendanceStatusByDate($user, $start, $end);

        $halfUnits = 0; // running total in units of 0.5 days, as an integer
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($this->isNonWorkingDay($date, $weeklyOffDays, $holidayDates)) {
                continue;
            }

            $status = $attendanceByDate[$date->toDateString()] ?? null;
            $halfUnits += match ($status) {
                'present', 'leave' => 2,
                // half_day_lop: the worked half of the day is payable, the
                // LOP half is not — same 0.5-day contribution as
                // half_day/half_day_leave, for the same reason.
                'half_day', 'half_day_leave', 'half_day_lop' => 1,
                // 'lop' (full-day LOP) is, by definition, unpaid — 0,
                // same bucket as absent/unmarked.
                default => 0, // absent, lop, or no row at all (unmarked)
            };
        }

        return $halfUnits / 2;
    }

    /**
     * Classifies a single date as weekday|weekend|holiday, for OT category
     * snapshotting. Holiday wins over weekly-off when a date is both
     * (locked business decision) — reuses the same holiday/weekly-off
     * lookups as the day-counting methods so this never drifts from them.
     */
    public function categoryForDate(Carbon $date): string
    {
        $day = $date->copy()->startOfDay();

        $holidayDates = $this->holidayDateSet($day, $day);
        if (isset($holidayDates[$day->toDateString()])) {
            return 'holiday';
        }

        if (in_array($day->dayOfWeek, $this->weeklyOffDays(), true)) {
            return 'weekend';
        }

        return 'weekday';
    }

    // ── Internal helpers ─────────────────────────────────────────────────

    /**
     * Clamps [from, to] to the employee's employment period. NULL
     * employment_start_date is treated as "always employed" (no lower
     * clamp). NULL employment_end_date is open-ended (no upper clamp).
     * Returns [null, null] if the requested range doesn't overlap the
     * employment period at all, or if $to is before $from.
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    private function clampToEmploymentPeriod(User $user, Carbon $from, Carbon $to): array
    {
        if ($to->lt($from)) {
            throw new InvalidArgumentException('PayableDaysCalculator: $to must not be before $from.');
        }

        $start = $from->copy()->startOfDay();
        $end   = $to->copy()->startOfDay();

        if ($user->employment_start_date !== null) {
            $empStart = Carbon::parse($user->employment_start_date)->startOfDay();
            if ($empStart->gt($start)) {
                $start = $empStart;
            }
        }

        if ($user->employment_end_date !== null) {
            $empEnd = Carbon::parse($user->employment_end_date)->startOfDay();
            if ($empEnd->lt($end)) {
                $end = $empEnd;
            }
        }

        if ($start->gt($end)) {
            return [null, null]; // no overlap — e.g. requested range entirely before joining or after leaving
        }

        return [$start, $end];
    }

    /**
     * Weekly-off weekdays as configured in settings.weekly_off_days.
     * Convention: 0=Sunday .. 6=Saturday — the same numbering Carbon's
     * dayOfWeek/Carbon::SUNDAY use natively, so no translation is needed at
     * the comparison site. This equivalence is verified by a dedicated test
     * rather than assumed.
     *
     * @return int[]
     */
    private function weeklyOffDays(): array
    {
        $days = Setting::get('weekly_off_days', []);

        return array_map('intval', is_array($days) ? $days : []);
    }

    /**
     * Set of active holiday dates (as 'Y-m-d' strings) within [start, end].
     * Multiple holiday rows on the same date collapse to one entry — a date
     * is either a holiday or it isn't, never "double" excluded.
     *
     * whereDate (not whereBetween) — some drivers (SQLite, used by the test
     * suite) store a DATE-cast column with a "00:00:00" time suffix, which
     * silently breaks whereBetween's plain string bounds. whereDate wraps
     * both sides in SQL date() and is correct on every supported driver —
     * same fix already applied to User::currentSalaryAsOf() in Phase 2.
     *
     * @return array<string, true>
     */
    private function holidayDateSet(Carbon $start, Carbon $end): array
    {
        return Holiday::active()
            ->whereDate('holiday_date', '>=', $start->toDateString())
            ->whereDate('holiday_date', '<=', $end->toDateString())
            ->pluck('holiday_date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->unique()
            ->flip()
            ->map(fn () => true)
            ->all();
    }

    /**
     * @param  int[]  $weeklyOffDays
     * @param  array<string, true>  $holidayDates
     */
    private function isNonWorkingDay(Carbon $date, array $weeklyOffDays, array $holidayDates): bool
    {
        // Holiday takes classification priority over weekly-off when a date
        // is both (locked business decision), but for THIS class the two
        // conditions are combined with OR — the date is excluded once,
        // never subtracted twice, regardless of which reason(s) apply.
        return in_array($date->dayOfWeek, $weeklyOffDays, true)
            || isset($holidayDates[$date->toDateString()]);
    }

    /**
     * Attendance status for the user across [start, end], keyed by
     * 'Y-m-d'. A date with no row is simply absent from the map (caller
     * treats a missing key as unmarked/0, per payableDaysSoFar's match()
     * default branch).
     *
     * @return array<string, string>
     */
    private function attendanceStatusByDate(User $user, Carbon $start, Carbon $end): array
    {
        return EmployeeAttendance::where('user_id', $user->id)
            ->whereDate('attendance_date', '>=', $start->toDateString())
            ->whereDate('attendance_date', '<=', $end->toDateString())
            ->get(['attendance_date', 'status'])
            ->mapWithKeys(fn ($row) => [$row->attendance_date->toDateString() => $row->status])
            ->all();
    }
}
