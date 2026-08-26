<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single independently-sourced HALF of an attendance date, used only when
 * a date already has a complementary-half fact recorded in
 * EmployeeAttendance (one row per date) and a second, non-conflicting half
 * needs its own status/source/reference — see the creating migration's
 * docblock for the full rationale.
 */
class EmployeeAttendanceSegment extends Model
{
    protected $table = 'employee_attendance_segments';

    protected $fillable = [
        'user_id', 'attendance_date', 'period', 'status', 'source',
        'leave_request_id', 'regularization_id',
        'marked_by', 'marked_at', 'corrected_by', 'corrected_at',
        'correction_reason', 'previous_status',
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

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function regularization(): BelongsTo
    {
        return $this->belongsTo(EmployeeAttendanceRegularization::class, 'regularization_id');
    }

    public static function statuses(): array
    {
        return ['present', 'leave', 'lop', 'absent'];
    }
}
