<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use InvalidArgumentException;

/**
 * Money calculation ONLY. Persistence is the caller's job — this service
 * never calls save()/create().
 *
 * Working-day denominator comes exclusively from PayableDaysCalculator —
 * this class must never re-derive weekly-off/holiday logic itself.
 *
 * REDESIGN NOTE: OT compensation is no longer calculated automatically at
 * request-creation time using a company-wide date-category multiplier.
 * Instead, the hourly-rate derivation below is computed at APPROVAL time,
 * combined with a multiplier the Admin/Manager explicitly selects (from the
 * employee's configured allowed multipliers — see EmployeeOvertimeConfig)
 * or an optional manual override amount. The underlying hourly-rate formula
 * is UNCHANGED from the old design; only the timing and the multiplier
 * source have changed.
 */
class OvertimeCalculationService
{
    public function __construct(private PayableDaysCalculator $payableDaysCalculator)
    {
    }

    /**
     * Derives the employee's salary-per-hour AS OF $otDate.
     *
     * Formula (unchanged from the pre-redesign implementation):
     *   daily_salary  = salary / applicable_working_days (month of $otDate)
     *   hourly_salary = daily_salary / standard_working_hours_per_day
     *
     * Throws DomainException if the user has no salary effective on $otDate,
     * or if the OT date's month has no applicable working days.
     */
    public function hourlyRateFor(User $user, Carbon $otDate): float
    {
        $salary = $user->currentSalaryAsOf($otDate);
        if ($salary === null) {
            throw new DomainException('Employee does not have an active salary for the selected OT date.');
        }

        $standardHours = (float) Setting::get('standard_working_hours_per_day');

        $monthStart = $otDate->copy()->startOfMonth();
        $monthEnd   = $otDate->copy()->endOfMonth();
        $applicableWorkingDays = $this->payableDaysCalculator->applicableWorkingDays($user, $monthStart, $monthEnd);

        if ($applicableWorkingDays <= 0) {
            throw new DomainException('No applicable working days in the OT date\'s month; cannot derive a daily rate.');
        }

        $monthlySalary = (float) $salary->monthly_salary;

        $dailySalary  = $monthlySalary / $applicableWorkingDays;
        $hourlySalary = $dailySalary / $standardHours;

        return round($hourlySalary, 2);
    }

    /**
     * Computes the OT financial snapshot AT APPROVAL TIME, using a
     * multiplier the approver explicitly chose (never an automatic
     * date-category lookup). Returns everything needed to persist, but
     * persists nothing itself:
     *   ['hourly_rate_snapshot' => float, 'rate_multiplier' => float,
     *    'calculated_amount' => float]
     *
     * Formula: amount = hourly_salary * hours * multiplier
     *
     * Throws InvalidArgumentException for hours <= 0 or a non-positive
     * multiplier. Throws DomainException if the user has no salary
     * effective on $otDate.
     */
    public function calculateForApproval(User $user, Carbon $otDate, float $hours, float $multiplier): array
    {
        if ($hours <= 0) {
            throw new InvalidArgumentException('OT hours must be greater than zero.');
        }

        if ($multiplier <= 0) {
            throw new InvalidArgumentException('OT multiplier must be greater than zero.');
        }

        $hourlySalary = $this->hourlyRateFor($user, $otDate);
        $amount       = $hourlySalary * $hours * $multiplier;

        return [
            'hourly_rate_snapshot' => $hourlySalary,
            'rate_multiplier'      => round($multiplier, 2),
            'calculated_amount'    => round($amount, 2),
        ];
    }
}
