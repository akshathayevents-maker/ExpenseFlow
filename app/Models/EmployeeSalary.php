<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalary extends Model
{
    // SECURITY: effective_to and created_by are intentionally EXCLUDED.
    // effective_to is only ever closed out by EmployeeSalaryService when a
    // later salary supersedes this row; created_by must always come from
    // auth()->id()/the acting admin, never request input. Both are written
    // via forceFill() — mirrors the same pattern already used for
    // EmployeeOvertime's server-only fields and
    // EmployeeAttendanceRegularization's request_status/reviewed_by.
    protected $fillable = [
        'user_id', 'monthly_salary', 'effective_from',
    ];

    protected function casts(): array
    {
        return [
            'monthly_salary' => 'decimal:2',
            'effective_from' => 'date',
            'effective_to'   => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isCurrentAsOf(\Carbon\Carbon $date): bool
    {
        return $this->effective_from->lte($date)
            && ($this->effective_to === null || $this->effective_to->gte($date));
    }
}
