<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeavePolicy extends Model
{
    // Sentinel allocation_mode used to represent "this leave type is no
    // longer part of the employee's policy from effective_from onward" —
    // e.g. when an admin switches the employee to a template that doesn't
    // include a leave type the old template did. It is a REAL, effective-
    // dated EmployeeLeavePolicy row (so currentFor() correctly returns it
    // instead of falling back to the still-is_active=true predecessor —
    // that fallback is exactly the bug this sentinel avoids), it is simply
    // not one of the modes LeaveAllocationService::generateForUser()
    // recognizes — its `match` already has a `default => []` arm, so a
    // 'removed' row silently produces zero future allocations with no
    // change to that service. Historical rows/ledger/allocations before
    // effective_from are completely untouched.
    public const ALLOCATION_MODE_REMOVED = 'removed';

    protected $fillable = [
        'user_id', 'leave_type_id', 'annual_entitlement', 'allocation_mode',
        'monthly_accrual_amount', 'effective_from', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'annual_entitlement'     => 'decimal:1',
            'monthly_accrual_amount' => 'decimal:2',
            'effective_from'         => 'date',
            'is_active'              => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isYearly(): bool
    {
        return $this->allocation_mode === 'yearly';
    }

    public function isMonthlyAccrual(): bool
    {
        return $this->allocation_mode === 'monthly_accrual';
    }

    public function isQuarterlyAccrual(): bool
    {
        return $this->allocation_mode === 'quarterly_accrual';
    }

    public function isRemoved(): bool
    {
        return $this->allocation_mode === self::ALLOCATION_MODE_REMOVED;
    }

    // Effective-dated lookup. History/current-version selection is driven
    // SOLELY by effective_from — the latest row whose effective_from is
    // on/before $date wins. There is deliberately no `effective_to`:
    // inserting a new row never touches any prior row, so a future-dated
    // change can never create a gap (the previous row simply keeps winning
    // for every date before the new one's effective_from).
    //
    // `is_active` here means something ENTIRELY different and orthogonal:
    // "this employee is currently permitted to use/accrue this leave
    // policy at all" — an independent admin on/off switch, e.g. to
    // explicitly disable a leave type for one employee. It is NEVER
    // flipped automatically when a new policy row is inserted (see
    // LeavePolicyController::store()) and must NOT be read as "is this the
    // latest version" — effective_from alone answers that question.
    public static function currentFor(User $user, LeaveType $leaveType, \Carbon\Carbon $date): ?self
    {
        return static::where('user_id', $user->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $date->toDateString())
            ->orderByDesc('effective_from')
            ->first();
    }
}
