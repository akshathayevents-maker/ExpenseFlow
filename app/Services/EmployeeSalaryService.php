<?php

namespace App\Services;

use App\Models\EmployeeSalary;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Admin-only salary management. Reuses the existing employee_salaries
 * effective-dated history table as-is — no new schema.
 *
 * Never overwrites a historical row's amount: setSalary() closes whatever
 * salary was applicable immediately before the new effective_from (by
 * setting its effective_to), then inserts a new open-ended row. Both writes
 * happen in one DB::transaction() so a new salary can never exist without
 * its predecessor being correctly closed.
 *
 * ── CURRENT overlap/backdating behavior (documented, NOT yet approved as a
 *    final business rule — see the "conflicting" check below) ─────────────
 *
 * The overlap guard rejects setSalary() whenever ANY existing row for this
 * employee has effective_from >= the requested effective_from. This is
 * broader than pure "don't create two rows covering the same date" overlap
 * prevention — it has two side effects that were introduced as an
 * assumption by this implementation, not requested explicitly:
 *
 *   1. SAME-DAY CORRECTION IS BLOCKED. If a salary was just set effective
 *      today, and the admin immediately tries to set ANOTHER salary also
 *      effective today (to correct a mistake), the just-created row's own
 *      effective_from equals the new one, so the guard treats it as
 *      "already exists on or after this date" and rejects it. There is
 *      currently no same-day amend path — a correction can only take
 *      effect starting tomorrow at the earliest.
 *
 *   2. BACKDATING BEFORE ANY EXISTING HISTORY IS BLOCKED. If an employee
 *      already has a salary row starting on date X, no new row can be
 *      inserted with an effective_from earlier than X, even if it would
 *      not actually overlap in the "two open-ended rows" sense — the
 *      guard is deliberately conservative rather than trying to reason
 *      about splicing a row into the middle of existing history.
 *
 * Backdating a brand-new employee's FIRST salary (no existing rows at all)
 * IS allowed — the guard only fires against existing rows.
 *
 * Do not change this behavior without explicit approval — it is called out
 * here specifically so a future change is a deliberate decision, not a
 * silent side effect of touching this file for something else.
 */
class EmployeeSalaryService
{
    public function __construct(private AuditLogService $auditLogService) {}

    public function setSalary(User $employee, float $monthlySalary, Carbon $effectiveFrom, User $admin): EmployeeSalary
    {
        return DB::transaction(function () use ($employee, $monthlySalary, $effectiveFrom, $admin) {
            // Overlap guard: a salary row already scheduled on or after the
            // requested effective_from would make the history non-linear
            // (two "open" rows, or a backdated row overlapping a future
            // one). Only appending after the latest known change is allowed.
            $conflicting = $employee->salaries()
                ->whereDate('effective_from', '>=', $effectiveFrom->toDateString())
                ->exists();

            if ($conflicting) {
                throw ValidationException::withMessages([
                    'effective_from' => 'A salary change already exists on or after this date.',
                ]);
            }

            $current = $employee->currentSalaryAsOf($effectiveFrom->copy()->subDay());

            if ($current) {
                $current->forceFill([
                    'effective_to' => $effectiveFrom->copy()->subDay()->toDateString(),
                ])->save();
            }

            // effective_to/created_by are excluded from $fillable (server-
            // only fields) — fill() + forceFill() before the single save()
            // call, so the row is inserted complete in one INSERT (created_by
            // is NOT NULL at the schema level, so it must be present before
            // the row is ever written, not patched in afterward).
            $salary = new EmployeeSalary();
            $salary->fill([
                'user_id'        => $employee->id,
                'monthly_salary' => $monthlySalary,
                'effective_from' => $effectiveFrom->toDateString(),
            ]);
            $salary->forceFill([
                'effective_to' => null,
                'created_by'   => $admin->id,
            ]);
            $salary->save();

            $this->auditLogService->log('created', 'employee_salary', $salary->id, $employee->name, [], [
                'monthly_salary' => $monthlySalary,
                'effective_from' => $effectiveFrom->toDateString(),
                'actor_id'       => $admin->id,
            ]);

            return $salary;
        });
    }
}
