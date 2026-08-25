<?php

namespace App\Services;

use App\Models\EmployeeLeaveAllocation;
use App\Models\EmployeeLeaveLedger;
use App\Models\EmployeeLeavePolicy;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Generates EmployeeLeaveAllocation + EmployeeLeaveLedger rows from active
 * EmployeeLeavePolicy rows. Never computes a balance itself (LeaveBalanceService
 * does that, from the ledger) — this class only decides WHAT to allocate and
 * WHEN, then writes it once.
 *
 * ── Locked business rules (approved, not invented here) ──────────────────
 *
 * - Leave year = calendar year (Jan 1 – Dec 31).
 * - ANNUAL allocations are credited at the START of the leave year (Jan 1),
 *   pro-rated only for the employee's joining year if employment_start_date
 *   falls after Jan 1 of that year:
 *       pro_rata = round(annual_entitlement * remaining_days_in_year / days_in_year, 1)
 *   remaining_days_in_year counts from employment_start_date to Dec 31
 *   inclusive. Every later year gets the full annual_entitlement — no
 *   pro-ration once the employee has a full year of tenure.
 * - MONTHLY/QUARTERLY allocations are credited only AFTER the period fully
 *   COMPLETES (checked from the 1st of the next period onward), and only
 *   for periods the employee was employed for in full — a period the
 *   employee joined partway through is never allocated at all (no partial-
 *   period pro-ration for monthly/quarterly; annual pro-ration is the only
 *   pro-ration rule that exists). The employee's first eligible monthly/
 *   quarterly period is therefore the first one starting on/after
 *   employment_start_date.
 * - `EmployeeLeavePolicy.monthly_accrual_amount` is reused as the generic
 *   "amount per period" for BOTH monthly_accrual and quarterly_accrual
 *   modes — adding a second `quarterly_accrual_amount` column would
 *   duplicate the same concept under a different name for no schema
 *   benefit; the mode itself already says which period the amount applies to.
 * - Idempotency is enforced by the existing DB unique constraint on
 *   employee_leave_allocations (user_id, leave_type_id, period_year,
 *   period_month, source) — this service always attempts the insert and
 *   treats a unique-constraint violation as "already allocated, skip",
 *   rather than pre-checking existence first (avoids a TOCTOU race if this
 *   is ever run concurrently).
 */
class LeaveAllocationService
{
    public function __construct(
        private AuditLogService $auditLogService,
        private LeaveBalanceService $leaveBalanceService,
    ) {}

    /**
     * @return EmployeeLeaveAllocation[] newly created allocations (empty if
     *         everything eligible was already allocated).
     */
    public function generateForUser(User $user, Carbon $asOf): array
    {
        // effective_from <= $asOf excludes any future-dated policy row —
        // a policy scheduled to start next year must never be allocated
        // against today's $asOf. is_active is independent of history
        // selection (see EmployeeLeavePolicy::currentFor()'s docblock): a
        // row the admin has explicitly disabled is excluded regardless of
        // its effective_from.
        $policies = $user->leavePolicies()
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $asOf->toDateString())
            ->with('leaveType')
            ->get();

        // Only the latest (by effective_from) active policy per leave type
        // governs allocation, exactly like EmployeeLeavePolicy::currentFor().
        $currentPerType = $policies->groupBy('leave_type_id')
            ->map(fn ($group) => $group->sortByDesc('effective_from')->first());

        $created = [];
        foreach ($currentPerType as $policy) {
            $created = [...$created, ...match ($policy->allocation_mode) {
                'yearly'             => $this->generateYearly($user, $policy, $asOf),
                'monthly_accrual'    => $this->generatePeriodic($user, $policy, $asOf, 'monthly'),
                'quarterly_accrual'  => $this->generatePeriodic($user, $policy, $asOf, 'quarterly'),
                default              => [],
            }];
        }

        return $created;
    }

    /**
     * @return EmployeeLeaveAllocation[]
     */
    private function generateYearly(User $user, EmployeeLeavePolicy $policy, Carbon $asOf): array
    {
        $created = [];
        $firstEligibleYear = max(
            (int) $policy->effective_from->year,
            $user->employment_start_date ? (int) Carbon::parse($user->employment_start_date)->year : $policy->effective_from->year,
        );

        for ($year = $firstEligibleYear; $year <= $asOf->year; $year++) {
            if ($policy->leaveType->allow_carry_forward && $year > $firstEligibleYear) {
                $carryForward = $this->generateCarryForward($user, $policy->leaveType, $year);
                if ($carryForward) {
                    $created[] = $carryForward;
                }
            }

            $amount = $this->annualAmountForYear($user, $policy, $year);
            if ($amount <= 0) {
                continue;
            }

            $allocation = $this->attemptAllocation(
                $user, $policy->leaveType, $year, 0, 'yearly_grant', $amount,
                Carbon::create($year, 1, 1),
            );
            if ($allocation) {
                $created[] = $allocation;
            }
        }

        return $created;
    }

    /**
     * Carries the employee's unused balance from Dec 31 of ($forYear - 1)
     * into $forYear, capped at LeaveType.max_carry_forward. Posted as its
     * own ledger 'allocation' entry (source='carry_forward', distinct from
     * the year's own 'yearly_grant') dated Jan 1 of $forYear — the prior
     * year's ledger rows are never touched or deleted, only referenced.
     */
    private function generateCarryForward(User $user, LeaveType $leaveType, int $forYear): ?EmployeeLeaveAllocation
    {
        $priorYearEnd = Carbon::create($forYear - 1, 12, 31)->endOfDay();
        $availableAtYearEnd = $this->leaveBalanceService->availableFor($user, $leaveType, $priorYearEnd);

        if ($availableAtYearEnd <= 0) {
            return null;
        }

        $cap = $leaveType->max_carry_forward !== null ? (float) $leaveType->max_carry_forward : null;
        $carryAmount = $cap !== null ? min($availableAtYearEnd, $cap) : $availableAtYearEnd;

        if ($carryAmount <= 0) {
            return null;
        }

        return $this->attemptAllocation(
            $user, $leaveType, $forYear, 0, 'carry_forward', round($carryAmount, 1),
            Carbon::create($forYear, 1, 1),
        );
    }

    private function annualAmountForYear(User $user, EmployeeLeavePolicy $policy, int $year): float
    {
        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd   = Carbon::create($year, 12, 31)->startOfDay();
        $joinDate  = $user->employment_start_date ? Carbon::parse($user->employment_start_date)->startOfDay() : null;

        $entitlement = (float) $policy->annual_entitlement;

        if ($joinDate === null || $joinDate->lte($yearStart)) {
            return $entitlement; // employed for the whole year — full entitlement
        }

        if ($joinDate->gt($yearEnd)) {
            return 0.0; // not yet joined during this year at all
        }

        $daysInYear         = $yearStart->diffInDays($yearEnd) + 1; // 365 or 366
        $remainingFromJoin  = $joinDate->diffInDays($yearEnd) + 1;

        return round($entitlement * $remainingFromJoin / $daysInYear, 1);
    }

    /**
     * @return EmployeeLeaveAllocation[]
     */
    private function generatePeriodic(User $user, EmployeeLeavePolicy $policy, Carbon $asOf, string $frequency): array
    {
        $amount = (float) $policy->monthly_accrual_amount;
        if ($amount <= 0) {
            return [];
        }

        $joinDate = $user->employment_start_date ? Carbon::parse($user->employment_start_date)->startOfDay() : null;

        $created = [];
        $cursor = $policy->effective_from->copy()->startOfMonth();
        $cursorPeriodEnd = $this->periodEndFor($cursor, $frequency);

        while ($cursorPeriodEnd->lte($asOf) && $cursorPeriodEnd->lte(Carbon::create($asOf->year, 12, 31))) {
            $periodStart = $frequency === 'quarterly'
                ? $cursorPeriodEnd->copy()->subMonths(2)->startOfMonth()
                : $cursorPeriodEnd->copy()->startOfMonth();

            $employedForWholePeriod = $joinDate === null || $joinDate->lte($periodStart);
            $withinPolicyEffectivePeriod = $periodStart->gte($policy->effective_from->copy()->startOfMonth());

            if ($employedForWholePeriod && $withinPolicyEffectivePeriod) {
                $allocation = $this->attemptAllocation(
                    $user, $policy->leaveType, $cursorPeriodEnd->year, $cursorPeriodEnd->month,
                    $frequency === 'quarterly' ? 'quarterly_accrual' : 'monthly_accrual',
                    $amount, $periodStart,
                );
                if ($allocation) {
                    $created[] = $allocation;
                }
            }

            $cursor = $cursorPeriodEnd->copy()->addDay()->startOfMonth();
            $cursorPeriodEnd = $this->periodEndFor($cursor, $frequency);
        }

        return $created;
    }

    private function periodEndFor(Carbon $monthStart, string $frequency): Carbon
    {
        if ($frequency === 'monthly') {
            return $monthStart->copy()->endOfMonth()->startOfDay();
        }

        // Quarterly: snap forward to the end of the quarter containing $monthStart.
        $quarterEndMonth = (int) (ceil($monthStart->month / 3) * 3);

        return Carbon::create($monthStart->year, $quarterEndMonth, 1)->endOfMonth()->startOfDay();
    }

    private function attemptAllocation(
        User $user, LeaveType $leaveType, int $periodYear, int $periodMonth,
        string $source, float $amount, Carbon $entryDate,
    ): ?EmployeeLeaveAllocation {
        try {
            return DB::transaction(function () use ($user, $leaveType, $periodYear, $periodMonth, $source, $amount, $entryDate) {
                $allocation = EmployeeLeaveAllocation::create([
                    'user_id'          => $user->id,
                    'leave_type_id'    => $leaveType->id,
                    'period_year'      => $periodYear,
                    'period_month'     => $periodMonth,
                    'allocated_amount' => $amount,
                    'source'           => $source,
                    'created_by'       => $user->id, // system-generated; attributed to the employee's own record for traceability
                ]);

                EmployeeLeaveLedger::create([
                    'user_id'        => $user->id,
                    'leave_type_id'  => $leaveType->id,
                    'entry_date'     => $entryDate->toDateString(),
                    'type'           => 'allocation',
                    'amount'         => $amount,
                    'reference_type' => EmployeeLeaveAllocation::class,
                    'reference_id'   => $allocation->id,
                    'created_by'     => $user->id,
                    'notes'          => "Auto-generated {$source} allocation for {$leaveType->name}.",
                ]);

                return $allocation;
            });
        } catch (QueryException $e) {
            // Unique constraint violation = already allocated for this
            // period — the intended, silent, idempotent no-op.
            if ($this->isUniqueViolation($e)) {
                return null;
            }
            throw $e;
        }
    }

    private function isUniqueViolation(QueryException $e): bool
    {
        // SQLSTATE 23000 = integrity constraint violation on every driver
        // this app supports (Postgres, SQLite).
        return $e->getCode() === '23000';
    }

    /**
     * Admin-driven manual adjustment — always explicit, always audited,
     * never a substitute for policy-driven accrual. Positive or negative;
     * a negative adjustment that would drive the ledger balance below zero
     * is rejected (employees may never carry a negative leave balance —
     * approved business rule).
     */
    public function manualAdjustment(User $employee, LeaveType $leaveType, float $amount, string $reason, User $admin): EmployeeLeaveLedger
    {
        if ($amount === 0.0) {
            throw ValidationException::withMessages(['amount' => 'Adjustment amount cannot be zero.']);
        }

        return DB::transaction(function () use ($employee, $leaveType, $amount, $reason, $admin) {
            if ($amount < 0) {
                $currentBalance = (float) EmployeeLeaveLedger::where('user_id', $employee->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->sum('amount');

                if ($currentBalance + $amount < 0) {
                    throw ValidationException::withMessages([
                        'amount' => "This adjustment would take {$leaveType->name} balance below zero, which is not allowed.",
                    ]);
                }
            }

            // The employee_leave_allocations unique constraint is
            // (user_id, leave_type_id, period_year, period_month, source) —
            // designed to make AUTOMATIC accrual idempotent (one allocation
            // per real period). Manual adjustments have no such "one per
            // month" concept — an admin may legitimately make several in
            // the same calendar month — so a second one in the same month
            // would collide on that constraint. The ledger entry (the
            // actual source of truth for balance) must never be blocked or
            // silently dropped because of this; the allocation-table row is
            // supplementary bookkeeping, so on a collision we simply omit
            // it rather than fail or lose the adjustment.
            $allocation = null;
            try {
                $allocation = EmployeeLeaveAllocation::create([
                    'user_id'          => $employee->id,
                    'leave_type_id'    => $leaveType->id,
                    'period_year'      => now()->year,
                    'period_month'     => now()->month,
                    'allocated_amount' => $amount,
                    'source'           => 'manual_adjustment',
                    'created_by'       => $admin->id,
                ]);
            } catch (QueryException $e) {
                if (! $this->isUniqueViolation($e)) {
                    throw $e;
                }
            }

            $ledger = EmployeeLeaveLedger::create([
                'user_id'        => $employee->id,
                'leave_type_id'  => $leaveType->id,
                'entry_date'     => now()->toDateString(),
                'type'           => 'adjustment',
                'amount'         => $amount,
                'reference_type' => $allocation ? EmployeeLeaveAllocation::class : null,
                'reference_id'   => $allocation?->id,
                'created_by'     => $admin->id,
                'notes'          => $reason,
            ]);

            $this->auditLogService->log('manual_leave_adjustment', 'leave_ledger', $ledger->id, $employee->name, [], [
                'leave_type' => $leaveType->name,
                'amount'     => $amount,
                'reason'     => $reason,
                'actor_id'   => $admin->id,
            ]);

            return $ledger;
        });
    }
}
