<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveRequest extends Model
{
    // SECURITY: workflow fields (status, reviewed_by/at, review_note) AND
    // the paid/LOP split (paid_leave_days, lop_days, lop_confirmed) are
    // server-controlled only and excluded from $fillable — the split is
    // computed by LeaveService from the employee's real balance, never
    // trusted from request input, and written via forceFill(), mirroring
    // EmployeeAdvance/EmployeeOvertime.
    protected $fillable = [
        'user_id', 'leave_type_id', 'start_date', 'end_date', 'is_half_day',
        'half_day_period', 'days_requested', 'reason',
    ];

    protected function casts(): array
    {
        return [
            'start_date'     => 'date',
            'end_date'       => 'date',
            'is_half_day'    => 'boolean',
            'days_requested' => 'decimal:1',
            'paid_leave_days'=> 'decimal:1',
            'lop_days'       => 'decimal:1',
            'lop_confirmed'  => 'boolean',
            'reviewed_at'    => 'datetime',
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function attendanceRows(): HasMany
    {
        return $this->hasMany(EmployeeAttendance::class);
    }

    public function hasLop(): bool { return (float) $this->lop_days > 0.0; }

    public function isPending(): bool { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }
}
