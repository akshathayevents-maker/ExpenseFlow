<?php

namespace App\Services;

use App\Models\EmployeeLeaveLedger;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;

/**
 * Reads EmployeeLeaveLedger (the sole source of truth for balance) and
 * pending LeaveRequest rows. Never stores or caches a running balance —
 * every call re-derives it from the current ledger + pending state, so a
 * second concurrent request always sees the first one's reservation.
 *
 * Balance formula (approved):
 *   allocated = SUM(ledger.amount WHERE type='allocation')
 *   used      = -SUM(ledger.amount WHERE type='usage')      (usage rows are negative)
 *   net       = SUM(ledger.amount)  — allocation - usage + reversals + adjustments,
 *               all already netted by the ledger itself
 *   pending   = SUM(leave_requests.paid_leave_days WHERE status='pending')
 *   available = net - pending
 */
class LeaveBalanceService
{
    /**
     * @return array{allocated: float, used: float, pending: float, available: float}
     */
    public function balanceFor(User $user, LeaveType $leaveType, ?Carbon $asOf = null): array
    {
        $ledger = EmployeeLeaveLedger::where('user_id', $user->id)
            ->where('leave_type_id', $leaveType->id)
            ->when($asOf, fn ($q) => $q->whereDate('entry_date', '<=', $asOf->toDateString()));

        $allocated = (float) (clone $ledger)->where('type', 'allocation')->sum('amount');
        $used      = -1 * (float) (clone $ledger)->where('type', 'usage')->sum('amount');
        $net       = (float) (clone $ledger)->sum('amount');

        $pending = (float) LeaveRequest::where('user_id', $user->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'pending')
            ->sum('paid_leave_days');

        $available = round($net - $pending, 1);

        return [
            'allocated' => round($allocated, 1),
            'used'      => round($used, 1),
            'pending'   => round($pending, 1),
            'available' => max(0.0, $available), // never surface a negative available balance
        ];
    }

    /**
     * Available balance only, as a plain float — the one number
     * LeaveService needs to decide the paid/LOP split.
     */
    public function availableFor(User $user, LeaveType $leaveType, ?Carbon $asOf = null): float
    {
        return $this->balanceFor($user, $leaveType, $asOf)['available'];
    }

    /**
     * @return array<int, array{leave_type: LeaveType, allocated: float, used: float, pending: float, available: float}>
     */
    public function balancesForAllTypes(User $user, ?Carbon $asOf = null): array
    {
        return LeaveType::active()
            ->orderBy('name')
            ->get()
            ->map(fn (LeaveType $type) => [
                'leave_type' => $type,
                ...$this->balanceFor($user, $type, $asOf),
            ])
            ->all();
    }
}
