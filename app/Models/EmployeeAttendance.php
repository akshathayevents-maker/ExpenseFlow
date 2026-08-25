<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAttendance extends Model
{
    protected $table = 'employee_attendance';

    protected $fillable = [
        'user_id', 'attendance_date', 'status', 'marked_by', 'marked_at', 'source',
        'corrected_by', 'corrected_at', 'correction_reason', 'previous_status',
        'leave_request_id',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'marked_at'       => 'datetime',
            'corrected_at'    => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function correctedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function isPresent(): bool { return $this->status === 'present'; }
    public function isHalfDay(): bool { return $this->status === 'half_day'; }
    public function isLeave(): bool { return in_array($this->status, ['leave', 'half_day_leave'], true); }
    public function isLop(): bool { return in_array($this->status, ['lop', 'half_day_lop'], true); }
    public function isAbsent(): bool { return $this->status === 'absent'; }

    public static function statuses(): array
    {
        return ['present', 'half_day', 'leave', 'half_day_leave', 'lop', 'half_day_lop', 'absent'];
    }
}
