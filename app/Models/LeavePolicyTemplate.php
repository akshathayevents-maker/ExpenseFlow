<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// A template is a reusable "stamp" of leave-policy items assigned in bulk
// to employees. It is NEVER read directly by LeaveAllocationService,
// LeaveBalanceService, LeaveService, or PayableDaysCalculator — assigning a
// template only ever produces real EmployeeLeavePolicy rows (see
// LeavePolicyAssignmentService). Editing a template's items later has zero
// effect on employees already assigned it.
class LeavePolicyTemplate extends Model
{
    protected $fillable = [
        'name', 'description', 'is_default', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active'  => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(LeavePolicyTemplateItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
