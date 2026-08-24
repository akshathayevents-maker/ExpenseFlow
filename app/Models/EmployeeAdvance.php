<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeAdvance extends Model
{
    // SECURITY: only fields legitimately supplied by an employee's request
    // form (or an admin's admin_recorded entry) are fillable. Every
    // server-controlled workflow field — approval, payment, status, and the
    // cached outstanding_amount — is excluded and written exclusively via
    // forceFill() inside EmployeeAdvanceService, mirroring the same
    // hardening already applied to EmployeeSalary/EmployeeOvertime/
    // EmployeeAttendanceRegularization.
    protected $fillable = [
        'user_id', 'origin', 'requested_amount', 'eligibility_breakdown', 'reference', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'requested_amount'      => 'decimal:2',
            'eligibility_breakdown' => 'array',
            'approved_amount'       => 'decimal:2',
            'approved_at'           => 'datetime',
            'paid_at'               => 'datetime',
            'original_amount'       => 'decimal:2',
            'outstanding_amount'    => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(AdvanceTransaction::class);
    }

    public function isPending(): bool { return $this->request_status === 'pending'; }
    public function isApproved(): bool { return $this->request_status === 'approved'; }
    public function isRejected(): bool { return $this->request_status === 'rejected'; }
    public function isCancelled(): bool { return $this->request_status === 'cancelled'; }
    public function isPaid(): bool { return $this->payment_status === 'paid'; }
    public function isUnpaid(): bool { return $this->payment_status === 'unpaid'; }
    public function isFullyRecovered(): bool { return $this->isPaid() && (float) $this->outstanding_amount <= 0.0; }
}
