<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchasePlan extends Model
{
    /** Workflow: draft → submitted → approved | rejected → ordered → completed (or cancelled). */
    public const STATUSES = [
        'draft' => 'Draft', 'submitted' => 'Waiting approval', 'approved' => 'Approved – ready to buy',
        'rejected' => 'Sent back', 'ordered' => 'Buying / receiving', 'completed' => 'Completed', 'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'title', 'planned_date', 'status', 'notes',
        'created_by', 'approved_by', 'approved_at',
        'submitted_at', 'rejected_by', 'rejected_at', 'rejection_reason', 'approval_note',
        'closed_by', 'completed_at', 'closing_note', 'shortfall_plan_id',
    ];

    protected $casts = ['planned_date' => 'date', 'approved_at' => 'datetime', 'submitted_at' => 'datetime',
        'rejected_at' => 'datetime', 'completed_at' => 'datetime'];

    public function purchases(): HasMany { return $this->hasMany(Purchase::class); }
    public function rejecter(): BelongsTo { return $this->belongsTo(User::class, 'rejected_by'); }
    public function closer(): BelongsTo { return $this->belongsTo(User::class, 'closed_by'); }
    public function shortfallPlan(): BelongsTo { return $this->belongsTo(self::class, 'shortfall_plan_id'); }

    public function statusLabel(): string { return self::STATUSES[$this->status] ?? ucfirst((string) $this->status); }
    public function reference(): string { return 'PP-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT); }
    /** Lines the creator can still change. */
    public function isEditable(): bool { return in_array($this->status, ['draft', 'rejected'], true); }

    public function creator(): BelongsTo  { return $this->belongsTo(User::class, 'created_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }

    public function items(): HasMany
    {
        return $this->hasMany(PurchasePlanItem::class);
    }

    public function isDraft(): bool     { return $this->status === 'draft'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isOrdered(): bool   { return $this->status === 'ordered'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }

    public function estimatedTotal(): float
    {
        return $this->items->sum(fn ($i) =>
            ($i->estimated_unit_cost ?? 0) * $i->suggested_quantity
        );
    }

    public static function statusColors(): array
    {
        return [
            'draft'     => 'secondary',
            'submitted' => 'warning',
            'rejected'  => 'danger',
            'approved'  => 'success',
            'ordered'   => 'primary',
            'completed' => 'info',
            'cancelled' => 'danger',
        ];
    }
}
