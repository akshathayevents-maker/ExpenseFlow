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
        [$start, $end] = $this->clampToEmploymentPeriod($user, $from, $to);
        if ($start === null) {
            return 0; // no overlap between the requested range and employment period
        }

        $weeklyOffDays = $this->weeklyOffDays();
        $holidayDates  = $this->holidayDateSet($start, $end);

        $count = 0;
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if (! $this->isNonWorkingDay($date, $weeklyOffDays, $holidayDates)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Payable days within [from, to], clamped to employment period:
     *   present            = 1.0
     *   half_day           = 0.5
     *   leave              = 1.0   (all approved leave is payable — see note below)
     *   half_day_leave     = 0.5
     *   absent / unmarked  = 0.0
     *   weekly-off/holiday = 0.0   (excluded entirely, never contribute)
     *
     * NOTE on paid/unpaid leave: leave_types/employee_leave_policies carry
     * no paid/unpaid distinction in the current schema (verified — no such
     * column exists). This is not a gap being papered over: it matches the
     * already-locked business decision that ALL approved leave (full or
     * half) is payable. If a paid/unpaid split is introduced later, this is
     * the one method that needs to change — nowhere else.
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
                'half_day', 'half_day_leave' => 1,
                default => 0, // absent, or no row at all (unmarked)
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
