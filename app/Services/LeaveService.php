<?php

namespace App\Services;

use App\Models\EmployeeAttendance;
use App\Models\EmployeeLeaveLedger;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Leave request lifecycle: create (with paid/LOP split) → approve/reject →
 * cancel (pending, or approved-with-reversal). Balance comes exclusively
 * from LeaveBalanceService (which reads the ledger); day-counting comes
 * exclusively from PayableDaysCalculator. This class never re-derives
 * either — it only orchestrates the transaction and the resulting
 * ledger/attendance writes.
 *
 * ── LOP is NOT a leave type ────────────────────────────────────────────
 * It is the unpaid remainder of a request that exceeds the employee's
 * available paid balance for that leave type. A request always has ONE
 * leave_type_id; paid_leave_days + lop_days = days_requested.
 *
 * ── Explicit LOP confirmation (never silent) ──────────────────────────
 * If the requested days exceed available paid balance, createRequest()
 * throws a ValidationException UNLESS the caller has already set
 * lop_confirmed=true — the controller's first submit attempt always stops
 * here and shows the employee the exact split before anything is created;
 * only a second, explicit submit (with lop_confirmed=1) proceeds.
 */
class LeaveService
{
    public function __construct(
        private AuditLogService $auditLogService,
        private PayableDaysCalculator $payableDaysCalculator,
        private LeaveBalanceService $leaveBalanceService,
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
            // PayableDaysCalculator used for payroll/overtime denominators.
            $daysRequested = $this->payableDaysCalculator->applicableWorkingDays($employee, $start, $end);

            if ($daysRequested <= 0) {
                throw ValidationException::withMessages([
                    'end_date' => 'The selected date range contains no working days.',
                ]);
            }
        }

        // ── Paid leave / LOP split ──────────────────────────────────────
        $available = $leaveType->is_paid
            ? $this->leaveBalanceService->availableFor($employee, $leaveType)
            : 0.0; // an unpaid-by-design leave type never has paid balance to draw from

        $paidLeaveDays = min($daysRequested, max(0.0, $available));
        $lopDays = round($daysRequested - $paidLeaveDays, 1);
        $lopConfirmed = (bool) ($data['lop_confirmed'] ?? false);

        if ($lopDays > 0 && ! $lopConfirmed) {
            throw ValidationException::withMessages([
                'lop_confirmation' => "Requested: {$daysRequested} day(s). Paid leave available: {$paidLeaveDays} day(s). "
                    . "{$lopDays} day(s) will be treated as Loss of Pay — confirm to proceed.",
            ]);
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
        $leaveRequest->forceFill([
            'status'          => 'pending',
            'paid_leave_days' => $paidLeaveDays,
            'lop_days'        => $lopDays,
            'lop_confirmed'   => $lopConfirmed,
        ]);
        $leaveRequest->save();

        $this->auditLogService->log('requested', 'leave_request', $leaveRequest->id, $employee->name, [], [
            'leave_type_id'   => $leaveRequest->leave_type_id,
            'start_date'      => $data['start_date'],
            'end_date'        => $leaveRequest->end_date->toDateString(),
            'days_requested'  => $daysRequested,
            'paid_leave_days' => $paidLeaveDays,
            'lop_days'        => $lopDays,
        ]);

        return $leaveRequest;
    }

    /**
     * Approve a pending request: writes the paid-portion usage ledger
     * entry, writes real EmployeeAttendance rows for every date in range
     * (leave/half_day_leave for paid days, lop/half_day_lop for LOP days),
     * and marks the request approved. All in one transaction.
     */
    public function approve(LeaveRequest $leaveRequest, User $approver): void
    {
        DB::transaction(function () use ($leaveRequest, $approver) {
            $leaveRequest = LeaveRequest::whereKey($leaveRequest->id)->lockForUpdate()->firstOrFail();

            if (! $leaveRequest->isPending()) {
                throw ValidationException::withMessages([
                    'status' => 'Only a pending leave request can be approved.',
                ]);
            }

            $employee = $leaveRequest->user;
            $leaveType = $leaveRequest->leaveType;

            // Defensive re-check: available balance EXCLUDING this
            // request's own pending reservation (it's about to stop being
            // pending) must still cover the paid portion. In a single-actor
            // admin flow this can't normally fail, but the check is cheap
            // and closes a theoretical race between request and approval.
            if ((float) $leaveRequest->paid_leave_days > 0) {
                $availableExcludingSelf = $this->leaveBalanceService->availableFor($employee, $leaveType)
                    + (float) $leaveRequest->paid_leave_days; // add back this request's own reservation
                if ($availableExcludingSelf < (float) $leaveRequest->paid_leave_days) {
                    throw ValidationException::withMessages([
                        'status' => 'Leave balance is no longer sufficient to approve the paid portion of this request.',
                    ]);
                }

                EmployeeLeaveLedger::create([
                    'user_id'        => $employee->id,
                    'leave_type_id'  => $leaveType->id,
                    'entry_date'     => now()->toDateString(),
                    'type'           => 'usage',
                    'amount'         => -1 * (float) $leaveRequest->paid_leave_days,
                    'reference_type' => LeaveRequest::class,
                    'reference_id'   => $leaveRequest->id,
                    'created_by'     => $approver->id,
                    'notes'          => "Leave used: {$leaveType->name}, {$leaveRequest->start_date->toDateString()} to {$leaveRequest->end_date->toDateString()}.",
                ]);
            }

            $this->writeApprovedAttendance($leaveRequest, $approver);

            $leaveRequest->forceFill([
                'status'      => 'approved',
                'reviewed_by' => $approver->id,
                'reviewed_at' => now(),
            ])->save();

            $this->auditLogService->log('approved', 'leave_request', $leaveRequest->id, $employee->name, [], [
                'actor_id'        => $approver->id,
                'paid_leave_days' => (float) $leaveRequest->paid_leave_days,
                'lop_days'        => (float) $leaveRequest->lop_days,
            ]);
        });
    }

    public function reject(LeaveRequest $leaveRequest, User $approver, ?string $reviewNote = null): void
    {
        DB::transaction(function () use ($leaveRequest, $approver, $reviewNote) {
            $leaveRequest = LeaveRequest::whereKey($leaveRequest->id)->lockForUpdate()->firstOrFail();

            if (! $leaveRequest->isPending()) {
                throw ValidationException::withMessages([
                    'status' => 'Only a pending leave request can be rejected.',
                ]);
            }

            // Rejecting a pending request has no ledger/attendance effect —
            // a pending request never wrote a usage entry, only reserved
            // balance via LeaveBalanceService's live pending-sum query,
            // which stops counting it the moment status changes.
            $leaveRequest->forceFill([
                'status'      => 'rejected',
                'reviewed_by' => $approver->id,
                'reviewed_at' => now(),
                'review_note' => $reviewNote,
            ])->save();

            $this->auditLogService->log('rejected', 'leave_request', $leaveRequest->id, $leaveRequest->user->name, [], [
                'actor_id' => $approver->id,
            ]);
        });
    }

    public function cancel(LeaveRequest $leaveRequest, User $actor): void
    {
        DB::transaction(function () use ($leaveRequest, $actor) {
            $leaveRequest = LeaveRequest::whereKey($leaveRequest->id)->lockForUpdate()->firstOrFail();

            if ($leaveRequest->isPending()) {
                // Never wrote usage/attendance — cancelling is a pure status change.
                $leaveRequest->forceFill(['status' => 'cancelled'])->save();
                $this->auditLogService->log('cancelled', 'leave_request', $leaveRequest->id, $leaveRequest->user->name, [], [
                    'actor_id' => $actor->id,
                ]);

                return;
            }

            if (! $leaveRequest->isApproved()) {
                throw ValidationException::withMessages([
                    'status' => 'Only a pending or approved leave request can be cancelled.',
                ]);
            }

            $employee = $leaveRequest->user;
            $leaveType = $leaveRequest->leaveType;

            if ((float) $leaveRequest->paid_leave_days > 0) {
                EmployeeLeaveLedger::create([
                    'user_id'        => $employee->id,
                    'leave_type_id'  => $leaveType->id,
                    'entry_date'     => now()->toDateString(),
                    'type'           => 'reversal',
                    'amount'         => (float) $leaveRequest->paid_leave_days,
                    'reference_type' => LeaveRequest::class,
                    'reference_id'   => $leaveRequest->id,
                    'created_by'     => $actor->id,
                    'notes'          => "Leave cancelled after approval — balance restored: {$leaveType->name}.",
                ]);
            }

            // Only revert rows THIS approval wrote — a row later corrected
            // by an admin (source would no longer be 'leave_approval') is
            // never touched, matching the migration's documented intent.
            EmployeeAttendance::where('leave_request_id', $leaveRequest->id)
                ->where('source', 'leave_approval')
                ->delete();

            $leaveRequest->forceFill(['status' => 'cancelled'])->save();

            $this->auditLogService->log('cancelled', 'leave_request', $leaveRequest->id, $employee->name, [], [
                'actor_id'                => $actor->id,
                'reversed_paid_leave_days'=> (float) $leaveRequest->paid_leave_days,
            ]);
        });
    }

    private function writeApprovedAttendance(LeaveRequest $leaveRequest, User $approver): void
    {
        $employee = $leaveRequest->user;

        if ($leaveRequest->is_half_day) {
            $status = (float) $leaveRequest->paid_leave_days > 0 ? 'half_day_leave' : 'half_day_lop';
            $this->writeOneAttendanceRow($employee, $leaveRequest->start_date, $status, $leaveRequest, $approver);

            return;
        }

        $applicableDates = $this->payableDaysCalculator->applicableWorkingDates(
            $employee, $leaveRequest->start_date, $leaveRequest->end_date,
        );

        $paidDaysRemaining = (float) $leaveRequest->paid_leave_days;

        foreach ($applicableDates as $date) {
            $status = $paidDaysRemaining > 0 ? 'leave' : 'lop';
            $paidDaysRemaining -= 1.0;
            $this->writeOneAttendanceRow($employee, $date, $status, $leaveRequest, $approver);
        }
    }

    private function writeOneAttendanceRow(User $employee, $date, string $status, LeaveRequest $leaveRequest, User $approver): void
    {
        $dateStr = \Carbon\Carbon::parse($date)->toDateString();

        $existing = EmployeeAttendance::where('user_id', $employee->id)
            ->whereDate('attendance_date', $dateStr)
            ->first();

        if ($existing && ! ($existing->leave_request_id === $leaveRequest->id && $existing->source === 'leave_approval')) {
            // Never silently overwrite a Present/regularized/other-leave day
            // — the same date must never carry conflicting attendance.
            throw ValidationException::withMessages([
                'start_date' => "Cannot approve — {$dateStr} already has a conflicting attendance record.",
            ]);
        }

        EmployeeAttendance::updateOrCreate(
            ['user_id' => $employee->id, 'attendance_date' => $dateStr],
            [
                'status'           => $status,
                'source'           => 'leave_approval',
                'leave_request_id' => $leaveRequest->id,
                'marked_by'        => $approver->id,
                'marked_at'        => now(),
            ],
        );
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
