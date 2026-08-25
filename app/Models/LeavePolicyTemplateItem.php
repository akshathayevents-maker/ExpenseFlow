<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeavePolicyTemplateItem extends Model
{
    protected $fillable = [
        'leave_policy_template_id', 'leave_type_id',
        'annual_entitlement', 'allocation_mode', 'monthly_accrual_amount',
    ];

    protected function casts(): array
    {
        return [
            'annual_entitlement'     => 'decimal:1',
            'monthly_accrual_amount' => 'decimal:2',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(LeavePolicyTemplate::class, 'leave_policy_template_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }
}
