<?php

namespace App\Services;

/**
 * Single shared implementation of "do these two half-day-aware date spans
 * conflict on the same calendar date" — used by both EmployeeAttendanceService
 * (attendance/regularization vs. leave) and LeaveService (leave vs. leave,
 * leave vs. attendance) so the overlap rule is never duplicated/drifted.
 *
 * Rule:
 *   - If either side is full-day, they always conflict (a full day leaves no
 *     room for anything else that date).
 *   - If both sides are half-day but either side's period is unknown/null
 *     (legacy data, or intentionally unspecified), treat conservatively as
 *     conflicting — never silently allow a conflict just because period
 *     data is missing.
 *   - If both sides are half-day with a known period, they conflict only if
 *     the periods are the same; complementary halves (first_half vs.
 *     second_half) do not conflict.
 */
class AttendanceConflictChecker
{
    public function periodsOverlap(bool $isFullDayA, ?string $periodA, bool $isFullDayB, ?string $periodB): bool
    {
        if ($isFullDayA || $isFullDayB) {
            return true;
        }

        if ($periodA === null || $periodB === null) {
            return true;
        }

        return $periodA === $periodB;
    }

    /**
     * ── Overlay invariant guard (single home for gap #4) ─────────────────
     * For one user+date, a given half may be represented by AT MOST ONE
     * independently-sourced EmployeeAttendanceSegment. Every write path
     * that could create a segment for a specific period — self-mark
     * (EmployeeAttendanceService::mark()), leave-approval's complementary-
     * half write (LeaveService::writeOneAttendanceRow()), and
     * regularization-approval's complementary-half write
     * (EmployeeAttendanceService::approveRegularization()) — calls this
     * BEFORE writing, rather than each re-implementing its own existence
     * check or relying on updateOrCreate() to silently clobber a prior
     * fact. A null $period is a no-op: segments only ever represent a
     * single known half, so a full-day write never reaches here as a
     * segment write in the first place.
     *
     * @throws ValidationException if the half is already occupied by a
     *   segment (optionally ignoring one specific segment id, so an
     *   approval can safely re-run/idempotently touch its OWN prior
     *   segment without tripping over itself).
     */
    public function assertHalfNotAlreadyOccupied(int $userId, string $dateStr, ?string $period, ?int $ignoreSegmentId = null): void
    {
        if ($period === null) {
            return;
        }

        $query = \App\Models\EmployeeAttendanceSegment::where('user_id', $userId)
            ->whereDate('attendance_date', $dateStr)
            ->where('period', $period);

        if ($ignoreSegmentId !== null) {
            $query->where('id', '!=', $ignoreSegmentId);
        }

        if ($query->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'attendance' => 'This half of the day already has a recorded attendance fact.',
            ]);
        }
    }
}
