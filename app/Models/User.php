<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // SECURITY: `role` and `is_active` are intentionally EXCLUDED from $fillable.
    // If mass-assignable, an attacker could POST role=admin or is_active=1
    // through any form that calls User::create() or $user->fill().
    // Role and active-status changes must go through explicit model assignments:
    //   $user->role = 'admin';  $user->save();
    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'employment_start_date',
        'employment_end_date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'     => 'datetime',
            'password'              => 'hashed',
            'is_active'             => 'boolean',
            'employment_start_date' => 'date',
            'employment_end_date'   => 'date',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function expenseRequests(): HasMany
    {
        return $this->hasMany(ExpenseRequest::class, 'requested_by');
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(AppNotification::class)->latest();
    }

    public function unreadNotificationsCount(): int
    {
        return $this->hasMany(AppNotification::class)->whereNull('read_at')->count();
    }

    // ── Employee Self-Service ────────────────────────────────────────────

    public function salaries(): HasMany
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    // $date required deliberately — "today" is a business-timezone decision
    // made by BusinessClock in the service layer, never assumed here.
    public function currentSalaryAsOf(\Carbon\Carbon $date): ?EmployeeSalary
    {
        // whereDate (not where) — some drivers (SQLite) store a DATE-cast
        // column with a "00:00:00" time suffix, which breaks plain string
        // <=/>= comparison against a bare Y-m-d value. whereDate wraps both
        // sides in SQL date() and is correct on every supported driver.
        return $this->salaries()
            ->whereDate('effective_from', '<=', $date->toDateString())
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                  ->orWhereDate('effective_to', '>=', $date->toDateString());
            })
            ->orderByDesc('effective_from')
            ->first();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(EmployeeAttendance::class);
    }

    public function leaveAllocations(): HasMany
    {
        return $this->hasMany(EmployeeLeaveAllocation::class);
    }

    public function leaveLedgerEntries(): HasMany
    {
        return $this->hasMany(EmployeeLeaveLedger::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function advances(): HasMany
    {
        return $this->hasMany(EmployeeAdvance::class);
    }

    public function overtimeRecords(): HasMany
    {
        return $this->hasMany(EmployeeOvertime::class);
    }

    public function attendanceRegularizations(): HasMany
    {
        return $this->hasMany(EmployeeAttendanceRegularization::class);
    }
}
