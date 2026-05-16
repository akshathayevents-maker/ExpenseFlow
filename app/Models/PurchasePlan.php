<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchasePlan extends Model
{
    protected $fillable = [
        'title', 'planned_date', 'status', 'notes',
        'created_by', 'approved_by', 'approved_at',
    ];

    protected $casts = ['planned_date' => 'date', 'approved_at' => 'datetime'];

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
            'approved'  => 'success',
            'ordered'   => 'primary',
            'completed' => 'info',
            'cancelled' => 'danger',
        ];
    }
}
