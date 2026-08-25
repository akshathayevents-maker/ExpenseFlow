<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = [
        'name', 'code', 'allow_half_day', 'is_active',
        'is_paid', 'allow_carry_forward', 'max_carry_forward',
    ];

    protected function casts(): array
    {
        return [
            'allow_half_day'      => 'boolean',
            'is_active'           => 'boolean',
            'is_paid'             => 'boolean',
            'allow_carry_forward' => 'boolean',
            'max_carry_forward'   => 'decimal:1',
        ];
    }

    public function policies(): HasMany
    {
        return $this->hasMany(EmployeeLeavePolicy::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(EmployeeLeaveAllocation::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(EmployeeLeaveLedger::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
