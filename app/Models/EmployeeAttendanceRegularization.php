<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAttendanceRegularization extends Model
{
    protected $table = 'employee_attendance_regularizations';

    // SECURITY: request_status/reviewed_by/reviewed_at/review_note are
    // intentionally EXCLUDED. These are server-only writes made during
    // approve()/reject()/cancel() via forceFill() — never mass-assignable
    // from employee input. Mirrors the same pattern used by
    // EmployeeOvertime for its server-only financial snapshot fields.
    protected $fillable = [
        'user_id', 'attendance_date', 'requested_status', 'reason', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'reviewed_at'     => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPending(): bool { return $this->request_status === 'pending'; }
    public function isApproved(): bool { return $this->request_status === 'approved'; }
    public function isRejected(): bool { return $this->request_status === 'rejected'; }
    public function isCancelled(): bool { return $this->request_status === 'cancelled'; }

    // The only statuses an employee may request via regularization — a
    // deliberate subset of EmployeeAttendance::statuses(): leave/half_day_leave
    // must go through LeaveRequest, holiday/weekly_off are system-derived,
    // and absent has no legitimate self-correction use case.
    public static function requestableStatuses(): array
    {
        return ['present', 'half_day'];
    }

    public static function statuses(): array
    {
        return ['pending', 'approved', 'rejected', 'cancelled'];
    }
}
