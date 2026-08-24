<?php

namespace App\Services;

use App\Models\EmployeeOvertime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * OT workflow/business operations ONLY (create/cancel/approve/reject/list).
 *
 * Money calculation lives in OvertimeCalculationService; working/payable-day
 * counting lives in PayableDaysCalculator. This class must never duplicate
 * either — it only orchestrates persistence, audit, and notifications around
 * a snapshot that OvertimeCalculationService produces.
 */
class OvertimeService
{
    public function __construct(
        private OvertimeCalculationService $calculationService,
        private NotificationService $notificationService,
        private AuditLogService $auditLogService,
    ) {}

    public function createRequest(User $employee, array $data): EmployeeOvertime
    {
        return $this->create($employee, $employee, $data, 'employee_request');
    }

    public function recordHistorical(User $admin, User $employee, array $data): EmployeeOvertime
    {
        return $this->create($employee, $admin, $data, 'admin_recorded');
    }

    private function create(User $employee, User $actor, array $data, string $origin): EmployeeOvertime
    {
        $otDate = Carbon::parse($data['ot_date']);
        $hours  = (float) $data['hours'];

        // Exact-duplicate guard (user_id+ot_date+hours) — app-level only, no
        // unique DB constraint, since distinct OT periods on the same date
        // are legitimate.
        if (EmployeeOvertime::duplicateExists($employee->id, $otDate, $hours)) {
            throw ValidationException::withMessages([
                'hours' => 'An identical overtime request already exists for this date.',
            ]);
        }

        // Financial snapshot computed and frozen NOW, at creation — never
        // recalculated at approval time (locked decision).
        $snapshot = $this->calculationService->calculate($employee, $otDate, $hours);

        return DB::transaction(function () use ($employee, $actor, $data, $origin, $otDate, $hours, $snapshot) {
            // hourly_rate_snapshot/rate_multiplier/calculated_amount are
            // deliberately excluded from $fillable (server-only writes) —
            // create() would silently drop them, so they must be set via
            // forceFill() here, the one legitimate server-side write path.
            $ot = EmployeeOvertime::create([
                'user_id'    => $employee->id,
                'ot_date'    => $otDate->toDateString(),
                'hours'      => $hours,
                'category'   => $snapshot['category'],
                'reason'     => $data['reason'],
                'origin'     => $origin,
                'created_by' => $actor->id,
            ]);

            $ot->forceFill([
                'hourly_rate_snapshot' => $snapshot['hourly_rate_snapshot'],
                'rate_multiplier'      => $snapshot['rate_multiplier'],
                'calculated_amount'    => $snapshot['calculated_amount'],
            ])->save();

            $this->auditLogService->log('created', 'employee_overtime', $ot->id, $employee->name, [], [
                'user_id'  => $employee->id,
                'ot_date'  => $ot->ot_date->toDateString(),
                'hours'    => (float) $ot->hours,
                'amount'   => (float) $ot->calculated_amount,
                'origin'   => $origin,
                'actor_id' => $actor->id,
            ]);

            return $ot;
        });
    }

    public function cancel(EmployeeOvertime $ot, User $actor): void
    {
        $old = $ot->only('request_status');

        $ot->update(['request_status' => 'cancelled']);

        $this->auditLogService->log('cancelled', 'employee_overtime', $ot->id, $ot->user->name, $old, [
            'status'   => 'cancelled',
            'actor_id' => $actor->id,
        ]);
    }

    public function approve(EmployeeOvertime $ot, User $approver, ?string $note = null): void
    {
        $old = $ot->only('request_status');

        $ot->update([
            'request_status' => 'approved',
            'reviewed_by'    => $approver->id,
            'reviewed_at'    => now(),
            'review_note'    => $note,
        ]);

        $this->auditLogService->log('approved', 'employee_overtime', $ot->id, $ot->user->name, $old, [
            'status'   => 'approved',
            'ot_date'  => $ot->ot_date->toDateString(),
            'hours'    => (float) $ot->hours,
            'amount'   => (float) $ot->calculated_amount,
            'actor_id' => $approver->id,
        ]);

        $this->notificationService->send(
            $ot->user,
            'overtime_approved',
            'Overtime Approved',
            "Your overtime claim for {$ot->ot_date->toDateString()} ({$ot->hours}h) was approved. Amount: {$ot->calculated_amount}.",
        );
    }

    public function reject(EmployeeOvertime $ot, User $approver, string $reason): void
    {
        $old = $ot->only('request_status');

        $ot->update([
            'request_status' => 'rejected',
            'reviewed_by'    => $approver->id,
            'reviewed_at'    => now(),
            'review_note'    => $reason,
        ]);

        $this->auditLogService->log('rejected', 'employee_overtime', $ot->id, $ot->user->name, $old, [
            'status'   => 'rejected',
            'ot_date'  => $ot->ot_date->toDateString(),
            'hours'    => (float) $ot->hours,
            'reason'   => $reason,
            'actor_id' => $approver->id,
        ]);

        $this->notificationService->send(
            $ot->user,
            'overtime_rejected',
            'Overtime Rejected',
            "Your overtime claim for {$ot->ot_date->toDateString()} ({$ot->hours}h) was rejected: {$reason}",
        );
    }

    public function listForEmployee(User $user): Collection
    {
        return $user->overtimeRecords()->with(['reviewer'])->latest('ot_date')->get();
    }

    public function listForManager(): Collection
    {
        return EmployeeOvertime::with(['user', 'reviewer'])->latest('ot_date')->get();
    }
}
