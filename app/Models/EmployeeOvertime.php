<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeOvertime extends Model
{
    protected $table = 'employee_overtime';

    // SECURITY: hourly_rate_snapshot, rate_multiplier, and calculated_amount
    // are intentionally EXCLUDED from $fillable. The employee submits ONLY
    // ot_date/hours/reason — these three financial columns must only ever be
    // written by a server-side calculation service (never mass-assigned from
    // a request), mirroring the same pattern already used for
    // `employee_advances.outstanding_amount` and `users.role`.
    protected $fillable = [
        'user_id', 'ot_date', 'hours', 'category', 'reason',
        'request_status', 'reviewed_by', 'reviewed_at', 'review_note',
        'paid_at', 'origin', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'ot_date'              => 'date',
            'hours'                => 'decimal:2',
            'hourly_rate_snapshot' => 'decimal:2',
            'rate_multiplier'      => 'decimal:2',
            'calculated_amount'    => 'decimal:2',
            'approved_amount'      => 'decimal:2',
            'used_manual_override' => 'boolean',
            'reviewed_at'          => 'datetime',
            'paid_at'              => 'datetime',
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

    public function isPending(): bool  { return $this->request_status === 'pending'; }
    public function isApproved(): bool { return $this->request_status === 'approved'; }
    public function isRejected(): bool { return $this->request_status === 'rejected'; }
    public function isCancelled(): bool { return $this->request_status === 'cancelled'; }

    public function isPaid(): bool { return $this->paid_at !== null; }

    // Once approved, hourly_rate_snapshot/rate_multiplier/calculated_amount
    // are frozen for good — no code path should ever call save() on this
    // model to change those three columns after this returns true.
    public function isFinancialsLocked(): bool
    {
        return $this->isApproved() || $this->isPaid();
    }

    public static function categories(): array
    {
        return ['weekday', 'weekend', 'holiday'];
    }

    public static function statuses(): array
    {
        return ['pending', 'approved', 'rejected', 'cancelled'];
    }

    // Application-level duplicate check (locked decision: no unique DB
    // constraint — an employee may legitimately log multiple distinct OT
    // periods on the same date, but an exact user_id+ot_date+hours repeat is
    // almost always an accidental resubmission).
    public static function duplicateExists(int $userId, $otDate, float $hours): bool
    {
        return static::where('user_id', $userId)
            ->whereDate('ot_date', \Carbon\Carbon::parse($otDate)->toDateString())
            ->where('hours', $hours)
            ->exists();
    }

    // Guards financial-immutability once a record is approved or paid: the
    // locked fields (hours/category/hourly_rate_snapshot/rate_multiplier/
    // calculated_amount) must never change after that point, from any code
    // path, not just the ones we've built UI for yet. Uses the ORIGINAL
    // (pre-change) attributes to decide lock state, since the request that
    // moves a record INTO 'approved' is itself a legitimate save.
    protected static function booted(): void
    {
        static::saving(function (self $ot) {
            if (! $ot->exists) {
                return;
            }

            $wasLocked = in_array($ot->getOriginal('request_status'), ['approved'], true)
                || $ot->getOriginal('paid_at') !== null;

            if (! $wasLocked) {
                return;
            }

            $lockedFields = [
                'hours', 'category', 'hourly_rate_snapshot', 'rate_multiplier',
                'calculated_amount', 'approved_amount', 'used_manual_override',
            ];
            foreach ($lockedFields as $field) {
                if ($ot->isDirty($field)) {
                    throw new \RuntimeException("Cannot modify '{$field}' on an OT record that is already approved or paid.");
                }
            }
        });
    }
}
