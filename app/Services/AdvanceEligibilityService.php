<?php

namespace App\Services;

use App\Models\EmployeeAdvance;
use App\Models\User;
use Carbon\Carbon;
use DomainException;

/**
 * Advance-eligibility calculation for the employee Request Advance screen.
 *
 * Formula (explicitly specified by the business for this feature — not
 * invented here):
 *
 *   earned_salary    = payable_days (this calendar month, to $asOf) × daily_salary
 *   eligible_advance = max(0, earned_salary − previous_advances_amount − outstanding_amount)
 *
 * Every piece is sourced from EXISTING domain logic, never re-derived:
 *   - salary/daily_salary/payable_days/earned_salary all come straight from
 *     MonthlyPayableService (itself built on PayableDaysCalculator and
 *     User::currentSalaryAsOf() — attendance/leave/holiday/weekly-off/
 *     employment-period handling all live there, not here).
 *   - "earned salary up to the current date" is interpreted as this
 *     calendar month's payable-so-far (MonthlyPayableService's existing,
 *     only scope) — there is no lifetime/since-employment-start payable
 *     calculation anywhere in the codebase, and building one would be a
 *     much larger, unbounded feature outside this task's request.
 *
 * ── Deduction buckets: what reduces eligibility, and why no double count ──
 *
 * The advance workflow (EmployeeAdvanceService/EmployeeAdvance) only ever
 * writes AdvanceTransaction / sets outstanding_amount at DISBURSEMENT time
 * (payment_status = 'paid'). A pending or approved-but-undisbursed advance
 * has outstanding_amount = 0 and no ledger row — no money has actually left
 * the company yet, so it cannot be a deduction bucket here without
 * inventing a new meaning for those columns. (One caveat, NOT resolved
 * here — see the class-level note below on approved-but-unpaid advances.)
 *
 * Among PAID advances, splitting on the existing outstanding_amount column
 * gives two non-overlapping buckets with zero double counting:
 *   - "Previous advances" = original_amount of paid advances that are fully
 *     repaid (outstanding_amount == 0) — settled debt.
 *   - "Outstanding amount" = outstanding_amount of paid advances that still
 *     have a balance (outstanding_amount > 0) — still-owed debt.
 * A single advance can only ever land in exactly one bucket (its
 * outstanding_amount is either 0 or not), so the same rupee is never
 * counted twice.
 *
 * ── UNRESOLVED AMBIGUITY (reported, not silently invented) ───────────────
 * An advance that is APPROVED but not yet DISBURSED (payment_status still
 * 'unpaid') is not blocked by EmployeeAdvanceService's "one pending request
 * at a time" guard, because that guard only checks request_status='pending'
 * — an approved-but-undisbursed advance is no longer 'pending'. Its
 * approved_amount is a real future claim on the employee's earnings, but
 * nothing in the schema marks it as a deduction, and this class does not
 * invent one. In practice this only matters if an admin approves an
 * advance and delays disbursement while the employee submits a second
 * request — a real but narrow gap, flagged here rather than silently
 * "fixed" by guessing a rule.
 */
class AdvanceEligibilityService
{
    public function __construct(
        private MonthlyPayableService $monthlyPayableService,
    ) {}

    /**
     * @return array{
     *   salary_configured: bool,
     *   monthly_salary: ?float,
     *   payable_days: ?float,
     *   daily_salary: ?float,
     *   earned_salary: ?float,
     *   previous_advances_amount: float,
     *   outstanding_amount: float,
     *   eligible_advance_amount: float,
     *   unavailable_reason: ?string,
     *   salary_change_during_period: bool,
     * }
     */
    public function evaluate(User $employee, Carbon $asOf): array
    {
        $salary = $employee->currentSalaryAsOf($asOf);

        if ($salary === null) {
            return [
                'salary_configured'        => false,
                'monthly_salary'           => null,
                'payable_days'             => null,
                'daily_salary'             => null,
                'earned_salary'            => null,
                'previous_advances_amount' => 0.0,
                'outstanding_amount'       => 0.0,
                'eligible_advance_amount'  => 0.0,
                'unavailable_reason'       => 'Advance eligibility is unavailable because your salary has not been configured.',
                'salary_change_during_period' => false,
            ];
        }

        $earnedSalary = null;
        $payableDays  = null;
        $dailySalary  = null;
        $unavailableReason = null;

        $periodStart = $asOf->copy()->startOfMonth();
        $periodEnd   = $asOf->copy()->endOfMonth()->min($asOf);

        // Informational only (see UI's create.blade.php note) — does NOT
        // gate eligibility. MonthlyPayableService::calculate() already
        // handles mid-period salary changes correctly by segmenting the
        // period per EmployeeSalary row; this flag just tells the employee
        // that happened, so the earned-salary figure isn't a surprise.
        $salaryChangedDuringPeriod = $employee->salaries()
            ->whereDate('effective_from', '>', $periodStart->toDateString())
            ->whereDate('effective_from', '<=', $periodEnd->toDateString())
            ->exists();

        try {
            $breakdown = $this->monthlyPayableService->calculate(
                $employee,
                $periodStart,
                $periodEnd,
            );
            $earnedSalary = $breakdown['payable_salary'];
            $payableDays  = $breakdown['payable_days'];
            $dailySalary  = $breakdown['daily_salary'];
        } catch (DomainException $e) {
            $unavailableReason = $e->getMessage();
        }

        [$previousAdvancesAmount, $outstandingAmount] = $this->deductionBuckets($employee);

        $eligibleAmount = $earnedSalary === null
            ? 0.0
            : max(0.0, round($earnedSalary - $previousAdvancesAmount - $outstandingAmount, 2));

        return [
            'salary_configured'        => true,
            'monthly_salary'           => (float) $salary->monthly_salary,
            'payable_days'             => $payableDays,
            'daily_salary'             => $dailySalary,
            'earned_salary'            => $earnedSalary,
            'previous_advances_amount' => $previousAdvancesAmount,
            'outstanding_amount'       => $outstandingAmount,
            'eligible_advance_amount'  => $eligibleAmount,
            'unavailable_reason'       => $unavailableReason,
            'salary_change_during_period' => $salaryChangedDuringPeriod,
        ];
    }

    /**
     * @return array{0: float, 1: float} [previousAdvancesAmount, outstandingAmount]
     */
    private function deductionBuckets(User $employee): array
    {
        $paidAdvances = EmployeeAdvance::where('user_id', $employee->id)
            ->where('payment_status', 'paid')
            ->get(['original_amount', 'outstanding_amount']);

        $previousAdvancesAmount = 0.0;
        $outstandingAmount = 0.0;

        foreach ($paidAdvances as $advance) {
            if ((float) $advance->outstanding_amount > 0.0) {
                $outstandingAmount += (float) $advance->outstanding_amount;
            } else {
                $previousAdvancesAmount += (float) $advance->original_amount;
            }
        }

        return [round($previousAdvancesAmount, 2), round($outstandingAmount, 2)];
    }
}
