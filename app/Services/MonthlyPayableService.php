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
 * ── MID-MONTH SALARY CHANGES ─────────────────────────────────────────────
 *
 * If EmployeeSalary has more than one row whose effective period intersects
 * [monthStart, monthEnd], this is handled by segmentedPayableSalary() below:
 * the period is split into EmployeeSalary-effective segments (reusing
 * EmployeeSalaryService's guaranteed invariant that segments are contiguous
 * and non-overlapping — each row's effective_to is exactly the day before
 * the next row's effective_from), and each segment's (that segment's daily
 * rate × that segment's payable days, via the SAME
 * PayableDaysCalculator::payableDaysSoFar() this class always used) is
 * summed. The denominator (applicable_working_days) stays the SAME
 * period-wide value for every segment — only the per-segment rate changes —
 * which is the natural, minimal generalization of the single-salary formula
 * and reduces to it exactly (bit-for-bit) when only one salary row covers
 * the whole period (see regression test in SalaryDiscoveryAndPayableTest).
 *
 * A GAP — dates within the period with NO EmployeeSalary row effective at
 * all (e.g. before the employee's very first salary row) — is not assigned
 * any segment, so those dates simply contribute 0 to payable_salary. This
 * is a deliberate, conservative choice ("no configured rate = nothing
 * earned for that day"), not a silently invented rate.
 *
 * ── Explicitly OUT OF SCOPE, not silently invented ──────────────────────
 *
 * 1. (reserved — mid-month salary changes, formerly listed here as
 *    out-of-scope, are now handled; see above.)
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
        $payableSalary = $this->segmentedPayableSalary($employee, $monthStart, $monthEnd, $applicableWorkingDays);

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

    /**
     * Splits [monthStart, monthEnd] into EmployeeSalary-effective segments
     * and sums each segment's (that segment's daily rate × that segment's
     * payable days). See the class docblock for the full rationale.
     *
     * Reuses PayableDaysCalculator::payableDaysSoFar() completely unchanged
     * — it is simply called once per segment's clipped date range instead
     * of once for the whole period. This is safe because
     * EmployeeSalaryService::setSalary() guarantees salary segments are
     * contiguous and non-overlapping (a superseded row's effective_to is
     * always exactly the day before the next row's effective_from), so
     * summing per-segment payable days is equivalent to the whole-period
     * payable days whenever segments fully cover the period — and when only
     * ONE segment covers the whole period (the pre-existing, single-salary
     * case), this reduces to exactly one call with exactly the same
     * arguments as before, producing a bit-identical result.
     */
    private function segmentedPayableSalary(User $employee, Carbon $monthStart, Carbon $monthEnd, int $applicableWorkingDays): float
    {
        $segments = $employee->salaries()
            ->whereDate('effective_from', '<=', $monthEnd->toDateString())
            ->where(function ($q) use ($monthStart) {
                $q->whereNull('effective_to')
                  ->orWhereDate('effective_to', '>=', $monthStart->toDateString());
            })
            ->orderBy('effective_from')
            ->get();

        $total = 0.0;

        // Compared and clipped purely as 'Y-m-d' strings (never as Carbon
        // instant comparisons) — $monthStart/$monthEnd may carry a caller
        // timezone (e.g. Asia/Kolkata, from AdvanceEligibilityService's
        // $asOf) while effective_from/effective_to come back from Eloquent
        // in the app's default timezone; comparing them as Carbon instants
        // (->gt()/->lt()) silently compares different instants for the
        // "same" calendar date and corrupts the segment boundaries. Plain
        // Y-m-d string comparison sidesteps that entirely, matching how
        // every other date-boundary check in this codebase (whereDate(),
        // toDateString() comparisons in User::currentSalaryAsOf() and
        // PayableDaysCalculator) already avoids the same pitfall.
        $monthStartStr = $monthStart->toDateString();
        $monthEndStr   = $monthEnd->toDateString();

        foreach ($segments as $segment) {
            $segFromStr = Carbon::parse($segment->effective_from)->toDateString();
            $segToStr   = $segment->effective_to !== null
                ? Carbon::parse($segment->effective_to)->toDateString()
                : $monthEndStr;

            $clippedStartStr = $segFromStr > $monthStartStr ? $segFromStr : $monthStartStr;
            $clippedEndStr   = $segToStr < $monthEndStr ? $segToStr : $monthEndStr;

            if ($clippedStartStr > $clippedEndStr) {
                continue;
            }

            $segmentPayableDays = $this->payableDaysCalculator->payableDaysSoFar(
                $employee,
                Carbon::parse($clippedStartStr)->startOfDay(),
                Carbon::parse($clippedEndStr)->startOfDay(),
            );
            $segmentDailyRate = (float) $segment->monthly_salary / $applicableWorkingDays;

            $total += $segmentDailyRate * $segmentPayableDays;
        }

        return round($total, 2);
    }
}
