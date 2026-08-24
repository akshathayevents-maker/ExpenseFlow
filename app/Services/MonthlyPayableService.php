<?php

namespace App\Services;

use App\Models\EmployeeOvertime;
use App\Models\User;
use Carbon\Carbon;
use DomainException;

/**
 * Monthly payable calculation — money math only, never persists anything.
 *
 * Pipeline implemented here (mirrors OvertimeCalculationService's existing
 * "salary / applicable working days" pattern rather than inventing a new
 * one):
 *
 *   monthly_salary (EmployeeSalary, via User::currentSalaryAsOf())
 *         ÷ applicable_working_days (PayableDaysCalculator::applicableWorkingDays)
 *         × payable_days (PayableDaysCalculator::payableDaysSoFar — already
 *           folds in attendance + approved leave + holidays + weekly offs;
 *           this class does NOT re-derive any of that)
 *         = payable_salary
 *         + approved overtime for the period (EmployeeOvertime.calculated_amount
 *           where request_status = 'approved') — the only "adjustment" that
 *           already has a real, existing calculated value to add.
 *         = net_payable
 *
 * ── Explicitly OUT OF SCOPE, not silently invented ──────────────────────
 *
 * 1. MID-MONTH SALARY CHANGES. If EmployeeSalary has more than one row whose
 *    effective period intersects [monthStart, monthEnd], this throws a
 *    DomainException rather than guessing a proration rule (e.g. "days
 *    before the change at the old rate, days after at the new rate"). No
 *    such rule exists anywhere in the codebase today — it needs an explicit
 *    business decision before it can be implemented.
 *
 * 2. DEDUCTIONS. EmployeeAdvance/AdvanceTransaction outstanding balances are
 *    NOT subtracted here. Nothing in the codebase defines a rule for "how
 *    much of an outstanding advance is recovered from a given month's
 *    payable amount" (recoveries are currently recorded manually via
 *    EmployeeAdvanceService::recordRepayment, independent of payroll). Net
 *    payable in this class is therefore gross of any advance recovery.
 *
 * 3. OTHER ADJUSTMENTS/STATUTORY DEDUCTIONS (tax, PF, etc.) — no schema or
 *    service for these exists anywhere in the app; not invented here.
 */
class MonthlyPayableService
{
    public function __construct(
        private PayableDaysCalculator $payableDaysCalculator,
    ) {}

    /**
     * @return array{
     *   monthly_salary: float,
     *   applicable_working_days: int,
     *   daily_salary: float,
     *   payable_days: float,
     *   payable_salary: float,
     *   approved_overtime_amount: float,
     *   net_payable: float,
     * }
     */
    public function calculate(User $employee, Carbon $monthStart, Carbon $monthEnd): array
    {
        $this->assertSingleSalaryPeriod($employee, $monthStart, $monthEnd);

        $salary = $employee->currentSalaryAsOf($monthEnd);
        if ($salary === null) {
            throw new DomainException('Employee has no salary effective during the requested period.');
        }

        $applicableWorkingDays = $this->payableDaysCalculator->applicableWorkingDays($employee, $monthStart, $monthEnd);
        if ($applicableWorkingDays <= 0) {
            throw new DomainException('No applicable working days in the requested period; cannot derive payable salary.');
        }

        $payableDays = $this->payableDaysCalculator->payableDaysSoFar($employee, $monthStart, $monthEnd);

        $monthlySalary = (float) $salary->monthly_salary;
        $dailySalary   = $monthlySalary / $applicableWorkingDays;
        $payableSalary = round($dailySalary * $payableDays, 2);

        $approvedOvertimeAmount = (float) EmployeeOvertime::where('user_id', $employee->id)
            ->where('request_status', 'approved')
            ->whereDate('ot_date', '>=', $monthStart->toDateString())
            ->whereDate('ot_date', '<=', $monthEnd->toDateString())
            ->sum('calculated_amount');

        return [
            'monthly_salary'           => $monthlySalary,
            'applicable_working_days'  => $applicableWorkingDays,
            'daily_salary'             => round($dailySalary, 2),
            'payable_days'             => $payableDays,
            'payable_salary'           => $payableSalary,
            'approved_overtime_amount' => round($approvedOvertimeAmount, 2),
            'net_payable'              => round($payableSalary + $approvedOvertimeAmount, 2),
        ];
    }

    private function assertSingleSalaryPeriod(User $employee, Carbon $monthStart, Carbon $monthEnd): void
    {
        $changesWithinPeriod = $employee->salaries()
            ->whereDate('effective_from', '>', $monthStart->toDateString())
            ->whereDate('effective_from', '<=', $monthEnd->toDateString())
            ->exists();

        if ($changesWithinPeriod) {
            throw new DomainException(
                'Salary changed mid-period — proration across a salary change is not yet a defined business rule.'
            );
        }
    }
}
