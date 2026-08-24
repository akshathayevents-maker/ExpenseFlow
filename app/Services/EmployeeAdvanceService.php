<?php

namespace App\Services;

use App\Models\AdvanceTransaction;
use App\Models\EmployeeAdvance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Advance / advance-payment workflow. Reuses the existing employee_advances
 * (two-axis: request_status + payment_status) and advance_transactions
 * (immutable, append-only ledger) tables exactly as designed — no schema
 * change.
 *
 * ── SCHEMA LIMITATION, DOCUMENTED (not silently worked around) ──────────
 * There is no dedicated "review_note"/"rejection_reason" column on
 * employee_advances (unlike employee_overtime and
 * employee_attendance_regularizations, which do have one). The only actor/
 * timestamp columns available are `approved_by`/`approved_at`, which this
 * service reuses for BOTH approve and reject (i.e. "reviewed by/at", not
 * literally "approved by/at" on a rejected row) — this is the schema's only
 * available reviewer trail, not an invented field. No rejection reason is
 * ever stored, because there is nowhere schema-appropriate to put it
 * without overwriting the employee's own `notes` field. If a rejection
 * reason becomes a hard requirement, that needs a small migration
 * (`review_note` nullable text) — flagged here rather than reusing
 * `notes` and silently overwriting employee input.
 *
 * ── Outstanding balance ───────────────────────────────────────────────
 * `employee_advances.outstanding_amount` is a cached column, excluded from
 * $fillable, always recomputed from advance_transactions (SUM of 'advance'
 * minus SUM of 'recovery') and written via forceFill() inside the same
 * DB::transaction() that inserts the new ledger row — matches the exact
 * pattern already established for EmployeeSalary/EmployeeOvertime/
 * EmployeeAttendanceRegularization server-only fields.
 */
class EmployeeAdvanceService
{
    public function __construct(private AuditLogService $auditLogService) {}

    public function createRequest(User $employee, array $data): EmployeeAdvance
    {
        if (EmployeeAdvance::where('user_id', $employee->id)->where('request_status', 'pending')->exists()) {
            throw ValidationException::withMessages([
                'requested_amount' => 'You already have a pending advance request.',
            ]);
        }

        $advance = new EmployeeAdvance();
        $advance->fill([
            'user_id'          => $employee->id,
            'origin'           => 'employee_request',
            'requested_amount' => $data['requested_amount'],
            'notes'            => $data['notes'] ?? null,
        ]);
        $advance->forceFill(['created_by' => $employee->id]);
        $advance->save();

        $this->auditLogService->log('requested', 'employee_advance', $advance->id, $employee->name, [], [
            'requested_amount' => $data['requested_amount'],
        ]);

        return $advance;
    }

    public function approve(EmployeeAdvance $advance, User $approver, float $approvedAmount): void
    {
        if (! $advance->isPending()) {
            throw ValidationException::withMessages([
                'approved_amount' => 'Only a pending advance request can be approved.',
            ]);
        }

        $advance->forceFill([
            'approved_amount' => $approvedAmount,
            'approved_by'     => $approver->id,
            'approved_at'     => now(),
            'request_status'  => 'approved',
        ])->save();

        $this->auditLogService->log('approved', 'employee_advance', $advance->id, $advance->user->name, [], [
            'approved_amount' => $approvedAmount,
            'actor_id'        => $approver->id,
        ]);
    }

    public function reject(EmployeeAdvance $advance, User $approver): void
    {
        if (! $advance->isPending()) {
            throw ValidationException::withMessages([
                'request_status' => 'Only a pending advance request can be rejected.',
            ]);
        }

        // approved_by/approved_at reused as the reviewer trail — see class
        // docblock. No rejection reason column exists to populate.
        $advance->forceFill([
            'approved_by'    => $approver->id,
            'approved_at'    => now(),
            'request_status' => 'rejected',
        ])->save();

        $this->auditLogService->log('rejected', 'employee_advance', $advance->id, $advance->user->name, [], [
            'actor_id' => $approver->id,
        ]);
    }

    public function cancel(EmployeeAdvance $advance, User $actor): void
    {
        if (! $advance->isPending()) {
            throw ValidationException::withMessages([
                'request_status' => 'Only a pending advance request can be cancelled.',
            ]);
        }

        $advance->forceFill(['request_status' => 'cancelled'])->save();

        $this->auditLogService->log('cancelled', 'employee_advance', $advance->id, $advance->user->name, [], [
            'actor_id' => $actor->id,
        ]);
    }

    public function disburse(EmployeeAdvance $advance, User $actor): void
    {
        if (! $advance->isApproved()) {
            throw ValidationException::withMessages([
                'payment_status' => 'Only an approved advance can be disbursed.',
            ]);
        }

        if ($advance->isPaid()) {
            throw ValidationException::withMessages([
                'payment_status' => 'This advance has already been disbursed.',
            ]);
        }

        DB::transaction(function () use ($advance, $actor) {
            $amount = (float) $advance->approved_amount;

            AdvanceTransaction::create([
                'employee_advance_id' => $advance->id,
                'user_id'             => $advance->user_id,
                'transaction_date'    => now()->toDateString(),
                'type'                => 'advance',
                'amount'              => $amount,
                'balance_after'       => $amount,
                'created_by'          => $actor->id,
            ]);

            $advance->forceFill([
                'payment_status'     => 'paid',
                'paid_at'            => now(),
                'paid_by'            => $actor->id,
                'original_amount'    => $amount,
                'outstanding_amount' => $amount,
            ])->save();

            $this->auditLogService->log('disbursed', 'employee_advance', $advance->id, $advance->user->name, [], [
                'amount'   => $amount,
                'actor_id' => $actor->id,
            ]);
        });
    }

    public function recordRepayment(EmployeeAdvance $advance, User $actor, float $amount, ?string $reference = null): AdvanceTransaction
    {
        if (! $advance->isPaid()) {
            throw ValidationException::withMessages([
                'amount' => 'Repayments can only be recorded against a disbursed advance.',
            ]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Repayment amount must be greater than zero.',
            ]);
        }

        return DB::transaction(function () use ($advance, $actor, $amount, $reference) {
            $outstanding = $this->recomputeOutstanding($advance->fresh());

            if ($amount > $outstanding) {
                throw ValidationException::withMessages([
                    'amount' => 'Repayment amount cannot exceed the outstanding balance of ' . number_format($outstanding, 2) . '.',
                ]);
            }

            $newBalance = round($outstanding - $amount, 2);

            $transaction = AdvanceTransaction::create([
                'employee_advance_id' => $advance->id,
                'user_id'             => $advance->user_id,
                'transaction_date'    => now()->toDateString(),
                'type'                => 'recovery',
                'amount'              => $amount,
                'reference'           => $reference,
                'balance_after'       => $newBalance,
                'created_by'          => $actor->id,
            ]);

            $advance->forceFill(['outstanding_amount' => $newBalance])->save();

            $this->auditLogService->log('repayment_recorded', 'employee_advance', $advance->id, $advance->user->name, [], [
                'amount'      => $amount,
                'new_balance' => $newBalance,
                'actor_id'    => $actor->id,
            ]);

            return $transaction;
        });
    }

    private function recomputeOutstanding(EmployeeAdvance $advance): float
    {
        $advanced  = (float) $advance->transactions()->where('type', 'advance')->sum('amount');
        $recovered = (float) $advance->transactions()->where('type', 'recovery')->sum('amount');

        return round($advanced - $recovered, 2);
    }
}
