<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * NO leave-balance validation is performed here on purpose.
 *
 * EmployeeLeaveLedger is documented as "the sole source of truth for
 * balance = SUM(amount)", but nothing in the codebase ever writes to it or
 * to EmployeeLeaveAllocation — there is no accrual/allocation command, no
 * seeder, and no other service that inserts ledger/allocation rows. A
 * balance computed from those tables today would always be 0 regardless of
 * an employee's real entitlement, which would incorrectly block every leave
 * request. Until an accrual mechanism exists to populate those tables, a
 * "check balance" step here would be enforcing a number that is not real.
 */
class LeaveService
{
    public function __construct(
        private AuditLogService $auditLogService,
        private PayableDaysCalculator $payableDaysCalculator,
    ) {}

    // "Active" = still consumes/could consume calendar time: pending
    // (not yet decided) or approved. Rejected/cancelled requests never
    // block a new request for the same dates.
    private const ACTIVE_STATUSES = ['pending', 'approved'];

    public function createRequest(User $employee, array $data): LeaveRequest
    {
        $start = \Carbon\Carbon::parse($data['start_date']);
        $end   = \Carbon\Carbon::parse($data['end_date']);
        $isHalfDay = (bool) ($data['is_half_day'] ?? false);
        $effectiveEnd = $isHalfDay ? $start : $end;

        $leaveType = LeaveType::findOrFail($data['leave_type_id']);
        if ($isHalfDay && ! $leaveType->allow_half_day) {
            throw ValidationException::withMessages([
                'is_half_day' => 'This leave type does not allow half-day requests.',
            ]);
        }

        $this->assertNoOverlap($employee, $start, $effectiveEnd);

        if ($isHalfDay) {
            $daysRequested = 0.5;
        } else {
            // Days actually consumed exclude weekly offs/holidays — the same
            // PayableDaysCalculator used for payroll/overtime denominators,
            // and consistent with how EmployeeAttendanceService already
            // treats a holiday/weekly-off date inside an approved leave
            // range as 'holiday'/'weekly_off', not 'leave' (see
            // getMonthlyHistory()). This is the existing app convention,
            // not a new business rule invented here.
            $daysRequested = $this->payableDaysCalculator->applicableWorkingDays($employee, $start, $end);

            if ($daysRequested <= 0) {
                throw ValidationException::withMessages([
                    'end_date' => 'The selected date range contains no working days.',
                ]);
            }
        }

        $leaveRequest = new LeaveRequest();
        $leaveRequest->fill([
            'user_id'         => $employee->id,
            'leave_type_id'   => $data['leave_type_id'],
            'start_date'      => $data['start_date'],
            'end_date'        => $isHalfDay ? $data['start_date'] : $data['end_date'],
            'is_half_day'     => $isHalfDay,
            'half_day_period' => $isHalfDay ? ($data['half_day_period'] ?? null) : null,
            'days_requested'  => $daysRequested,
            'reason'          => $data['reason'],
        ]);
        $leaveRequest->forceFill(['status' => 'pending']);
        $leaveRequest->save();

        $this->auditLogService->log('requested', 'leave_request', $leaveRequest->id, $employee->name, [], [
            'leave_type_id'  => $leaveRequest->leave_type_id,
            'start_date'     => $data['start_date'],
            'end_date'       => $leaveRequest->end_date->toDateString(),
            'days_requested' => $daysRequested,
        ]);

        return $leaveRequest;
    }

    public function cancel(LeaveRequest $leaveRequest, User $actor): void
    {
        if (! $leaveRequest->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Only a pending leave request can be cancelled.',
            ]);
        }

        $leaveRequest->forceFill(['status' => 'cancelled'])->save();

        $this->auditLogService->log('cancelled', 'leave_request', $leaveRequest->id, $leaveRequest->user->name, [], [
            'actor_id' => $actor->id,
        ]);
    }

    private function assertNoOverlap(User $employee, \Carbon\Carbon $start, \Carbon\Carbon $end): void
    {
        $overlaps = LeaveRequest::where('user_id', $employee->id)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString())
            ->exists();

        if ($overlaps) {
            throw ValidationException::withMessages([
                'start_date' => 'This date range overlaps an existing pending or approved leave request.',
            ]);
        }
    }
}
