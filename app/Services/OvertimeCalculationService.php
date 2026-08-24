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
 * Financial values are computed against the salary/settings state AS OF
 * $otDate (or, for settings, as of "now" at calculation time — settings are
 * not effective-dated, so the value used is whatever is live when calculate()
 * runs, which must only ever be called at OT-request-creation time, never at
 * approval time — see the frozen-snapshot rule below).
 */
class OvertimeCalculationService
{
    public function __construct(private PayableDaysCalculator $payableDaysCalculator)
    {
    }

    /**
     * Computes the OT financial snapshot for a single claim. Returns
     * everything needed to persist, but persists nothing itself:
     *   ['category' => string, 'hourly_rate_snapshot' => float,
     *    'rate_multiplier' => float, 'calculated_amount' => float]
     *
     * Formula:
     *   daily_salary   = salary / applicable_working_days (month of $otDate)
     *   hourly_salary  = daily_salary / standard_working_hours_per_day
     *   amount         = hourly_salary * hours * category_multiplier
     *
     * Throws InvalidArgumentException for hours <= 0.
     * Throws DomainException if the user has no salary effective on $otDate.
     */
    public function calculate(User $user, Carbon $otDate, float $hours): array
    {
        if ($hours <= 0) {
            throw new InvalidArgumentException('OT hours must be greater than zero.');
        }

        $salary = $user->currentSalaryAsOf($otDate);
        if ($salary === null) {
            throw new DomainException('Employee does not have an active salary for the selected OT date.');
        }

        $category = $this->payableDaysCalculator->categoryForDate($otDate);

        $multipliers = Setting::get('ot_multipliers', []);
        if (! isset($multipliers[$category])) {
            throw new DomainException("No OT multiplier configured for category '{$category}'.");
        }
        $multiplier = (float) $multipliers[$category];

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
        $amount       = $hourlySalary * $hours * $multiplier;

        return [
            'category'              => $category,
            'hourly_rate_snapshot'  => round($hourlySalary, 2),
            'rate_multiplier'       => round($multiplier, 2),
            'calculated_amount'     => round($amount, 2),
        ];
    }
}
