<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveAllocation extends Model
{
    protected $fillable = [
        'user_id', 'leave_type_id', 'period_year', 'period_month',
        'allocated_amount', 'source', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'allocated_amount' => 'decimal:1',
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

    public function isYearlyGrant(): bool
    {
        return (int) $this->period_month === 0;
    }
}
