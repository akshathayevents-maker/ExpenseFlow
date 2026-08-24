<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = ['name', 'code', 'allow_half_day', 'is_active'];

    protected function casts(): array
    {
        return [
            'allow_half_day' => 'boolean',
            'is_active'      => 'boolean',
        ];
    }

    public function policies(): HasMany
    {
        return $this->hasMany(EmployeeLeavePolicy::class);
    }

    // $date is required (not defaulted to now()) — the business-timezone
    // "today" is decided by BusinessClock in the service layer (Phase 3+),
    // not assumed here at the model layer.
    public function currentPolicyAsOf(\Carbon\Carbon $date): ?EmployeeLeavePolicy
    {
        return $this->policies()
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $date->toDateString())
            ->orderByDesc('effective_from')
            ->first();
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
