<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeavePolicy extends Model
{
    protected $fillable = [
        'leave_type_id', 'annual_entitlement', 'allocation_mode',
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
}
