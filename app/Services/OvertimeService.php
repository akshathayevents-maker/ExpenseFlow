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
        private PayableDaysCalculator $payableDaysCalculator,
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

        // REDESIGN: no financial snapshot is computed at creation time.
        // hourly_rate_snapshot/rate_multiplier/calculated_amount/
        // approved_amount all stay NULL until approve() runs — compensation
        // is now calculated at APPROVAL time using a multiplier the
        // Admin/Manager explicitly chooses (see OvertimeCalculationService
        // and EmployeeOvertimeConfig). `category` is retained purely as an
        // informational date-type label (weekday/weekend/holiday) — it no
        // longer drives any automatic multiplier lookup.
        $category = $this->payableDaysCalculator->categoryForDate($otDate);

        return DB::transaction(function () use ($employee, $actor, $data, $origin, $otDate, $hours, $category) {
            $ot = EmployeeOvertime::create([
                'user_id'    => $employee->id,
                'ot_date'    => $otDate->toDateString(),
                'hours'      => $hours,
                'category'   => $category,
                // reason is optional — employee_overtime.reason is NOT NULL
                // text (unchanged schema), so an omitted reason is stored as
                // an empty string, never NULL. Mirrors
                // EmployeeAttendanceService::createRegularization().
                'reason'     => $data['reason'] ?? '',
                'origin'     => $origin,
                'created_by' => $actor->id,
            ]);

            $this->auditLogService->log('created', 'employee_overtime', $ot->id, $employee->name, [], [
                'user_id'  => $employee->id,
                'ot_date'  => $ot->ot_date->toDateString(),
                'hours'    => (float) $ot->hours,
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

    /**
     * REDESIGN (Part 3): compensation is calculated HERE, at approval time,
     * never trusting any client-submitted amount. $multiplier must already
     * have been validated by the caller (ApproveOvertimeRequest) against
     * EmployeeOvertimeConfig::allowedMultipliersFor($ot->user) — this method
     * does not re-validate multiplier membership, only recomputes the
     * amount independently server-side.
     *
     * $manualAmount, when non-null, becomes approved_amount verbatim
     * (used_manual_override = true); otherwise approved_amount equals the
     * freshly computed calculated_amount.
     */
    public function approve(EmployeeOvertime $ot, User $approver, float $multiplier, ?float $manualAmount = null, ?string $note = null): void
    {
        $old = $ot->only('request_status');

        $snapshot = $this->calculationService->calculateForApproval(
            $ot->user,
            $ot->ot_date,
            (float) $ot->hours,
            $multiplier,
        );

        $approvedAmount = $manualAmount ?? $snapshot['calculated_amount'];

        DB::transaction(function () use ($ot, $approver, $note, $snapshot, $approvedAmount, $manualAmount) {
            $ot->forceFill([
                'hourly_rate_snapshot' => $snapshot['hourly_rate_snapshot'],
                'rate_multiplier'      => $snapshot['rate_multiplier'],
                'calculated_amount'    => $snapshot['calculated_amount'],
                'approved_amount'      => round($approvedAmount, 2),
                'used_manual_override' => $manualAmount !== null,
            ]);

            $ot->update([
                'request_status' => 'approved',
                'reviewed_by'    => $approver->id,
                'reviewed_at'    => now(),
                'review_note'    => $note,
            ]);
        });

        $this->auditLogService->log('approved', 'employee_overtime', $ot->id, $ot->user->name, $old, [
            'status'               => 'approved',
            'ot_date'              => $ot->ot_date->toDateString(),
            'hours'                => (float) $ot->hours,
            'rate_multiplier'      => (float) $ot->rate_multiplier,
            'calculated_amount'    => (float) $ot->calculated_amount,
            'approved_amount'      => (float) $ot->approved_amount,
            'used_manual_override' => (bool) $ot->used_manual_override,
            'actor_id'             => $approver->id,
        ]);

        $this->notificationService->send(
            $ot->user,
            'overtime_approved',
            'Overtime Approved',
            "Your overtime claim for {$ot->ot_date->toDateString()} ({$ot->hours}h) was approved. Amount: {$ot->approved_amount}.",
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
