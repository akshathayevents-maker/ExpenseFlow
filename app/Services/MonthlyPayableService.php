<?php

namespace App\Services;

use App\Models\EmployeeAdvance;
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
 *         + approved overtime for the period (EmployeeOvertime.approved_amount
 *           where request_status = 'approved' — the final authorized amount,
 *           which equals calculated_amount unless a manual override was used
 *           at approval time) — the only "adjustment" that already has a
 *           real, existing calculated value to add.
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
 * 2. DEDUCTIONS. There is no installment/schedule concept anywhere in the
 *    codebase for "how much of an outstanding advance should be recovered
 *    from a GIVEN MONTH's payable amount" — an advance's `outstanding_amount`
 *    is a lifetime running balance (advance_transactions 'advance' minus
 *    'recovery', see EmployeeAdvanceService), not tied to any specific
 *    payroll month. Deducting the FULL outstanding balance from a single
 *    month's payable would double/triple-count it in every subsequent month
 *    the balance remains non-zero — that was a bug in a prior version of
 *    this method and has been corrected.
 *
 *    A PRIOR version of this method summed AdvanceTransaction rows of
 *    type='recovery' dated inside the requested month, on the assumption
 *    that a 'recovery' transaction represents money already deducted from
 *    payroll. That assumption was traced and disproven:
 *      - EmployeeAdvanceService::recordRepayment() (app/Services/
 *        EmployeeAdvanceService.php:167) is a plain manual ledger action —
 *        it takes only an amount and an optional free-text `reference`. It
 *        has no payroll-month/deduction-source parameter, is never called
 *        from any payroll process, console command, or scheduled job (the
 *        only call sites are Admin\AdvanceController::recordRepayment and
 *        Manager\AdvanceController::recordRepayment, both plain admin/manager
 *        HTTP actions — see app/Http/Controllers/Admin/AdvanceController.php:60
 *        and app/Http/Controllers/Manager/AdvanceController.php:60), and can
 *        be invoked on any date with no relation to a payroll cycle.
 *      - The advance_transactions schema (database/migrations/
 *        2026_08_24_090012_create_advance_transactions_table.php) has no
 *        payroll_month/deduction_type/deduction_source/payslip_id/
 *        payroll_run column — `type` is a free string, `reference` a
 *        nullable free-text field with no enforced meaning. A codebase-wide
 *        search for payroll_month/deduction_type/payslip/payroll_run/
 *        salary_deduction found no such concept anywhere in the app.
 *      - The repayment UI (resources/views/partials/advance-detail.blade.php:
 *        241-268) only ever labels the action "Record Repayment" against an
 *        "Outstanding balance" — never "salary deduction" — and the
 *        `reference` field is an optional free-text note (its placeholder
 *        text is just an example, not an enforced category).
 *      - MonthlyPayableService itself never persists anything and no other
 *        part of the app actually reduces a paid amount to reflect a
 *        recovery — there is no payroll-run process in this codebase at all
 *        that a 'recovery' transaction could be "the deduction from."
 *    In short: a 'recovery' row records that money was returned by the
 *    employee through some means outside this calculation (cash, bank
 *    transfer, or a manual off-system payroll adjustment an admin chose to
 *    log here) — it is not a fact this service may treat as "this month's
 *    payroll deduction." Subtracting it from net_payable would, at best,
 *    double-count money already returned outside payroll, and at worst
 *    invent a deduction the payroll process never actually applied.
 *
 *    Accordingly, 'advance_deduction_amount' is unconditionally 0.0 — there
 *    is no valid payroll-deduction concept anywhere in this codebase to
 *    compute it from. The lifetime running balance is still surfaced
 *    separately, clearly labelled, as 'advance_outstanding_balance' —
 *    informational only, never subtracted into net_payable.
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
     *   advance_deduction_amount: float,
     *   advance_outstanding_balance: float,
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
            ->sum('approved_amount');

        // No valid payroll-deduction concept exists anywhere in this codebase
        // (see class docblock) — a 'recovery' AdvanceTransaction is a manual,
        // standalone ledger entry with no enforced tie to a payroll run, so
        // it must never be treated as this month's payroll deduction.
        $advanceDeductionAmount = 0.0;

        // Informational only — the employee's CURRENT total outstanding
        // balance across all paid-but-not-fully-recovered advances. Same
        // bucket AdvanceEligibilityService::deductionBuckets reads. Must
        // never be subtracted into net_payable.
        $advanceOutstandingBalance = (float) EmployeeAdvance::where('user_id', $employee->id)
            ->where('payment_status', 'paid')
            ->where('outstanding_amount', '>', 0)
            ->sum('outstanding_amount');

        $netPayable = $payableSalary + $approvedOvertimeAmount - $advanceDeductionAmount;

        return [
            'monthly_salary'              => $monthlySalary,
            'applicable_working_days'     => $applicableWorkingDays,
            'daily_salary'                => round($dailySalary, 2),
            'payable_days'                => $payableDays,
            'payable_salary'              => $payableSalary,
            'approved_overtime_amount'    => round($approvedOvertimeAmount, 2),
            'advance_deduction_amount'    => round($advanceDeductionAmount, 2),
            'advance_outstanding_balance' => round($advanceOutstandingBalance, 2),
            'net_payable'                 => round($netPayable, 2),
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
