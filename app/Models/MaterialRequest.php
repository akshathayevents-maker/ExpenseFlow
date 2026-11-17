<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialRequest extends Model
{
    public const STATUSES = [
        'draft' => 'Draft',
        'submitted' => 'Waiting approval',
        'approved' => 'Approved – ready for handover',
        'issued' => 'Handed over',
        'reconciled' => 'Usage recorded',
        'rejected' => 'Rejected',
        'cancelled' => 'Cancelled',
    ];

    public const DEPARTMENTS = ['kitchen' => 'Kitchen', 'hall' => 'Hall catering', 'bakery' => 'Bakery / sweets', 'other' => 'Other'];

    protected $fillable = [
        'department', 'source', 'meal_entry_id', 'meal_type', 'hall_booking_id',
        'required_date', 'required_time', 'preparation_name', 'guest_count', 'purpose', 'notes',
        'menu_snapshot', 'status', 'requested_by', 'submitted_at',
        'approved_by', 'approved_at', 'approval_remarks',
        'handed_over_by', 'collected_by', 'collected_by_name', 'handed_over_at',
        'cancelled_by', 'cancelled_at', 'cancel_reason',
        'reconciled_by', 'reconciled_at', 'reconciliation_status',
        'reviewed_by', 'reviewed_at', 'review_remarks',
        'revision_of_id', 'revision_no', 'client_token', 'recipe_id', 'recipe_version',
    ];

    protected $casts = [
        'required_date' => 'date',
        'guest_count' => 'integer',
        'menu_snapshot' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'handed_over_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'reconciled_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MaterialRequestItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function handedOverBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handed_over_by');
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function reconciler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function mealEntry(): BelongsTo
    {
        return $this->belongsTo(MealEntry::class);
    }

    public function hallBooking(): BelongsTo
    {
        return $this->belongsTo(HallBooking::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function revisionOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'revision_of_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(self::class, 'revision_of_id');
    }

    public function reference(): string
    {
        return 'MR-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT).($this->revision_no > 1 ? '-R'.$this->revision_no : '');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public static function statusTone(string $status): string
    {
        return match ($status) {
            'draft' => 'neutral', 'submitted' => 'warn', 'approved' => 'info',
            'issued', 'reconciled' => 'ok', 'rejected', 'cancelled' => 'bad', default => 'neutral',
        };
    }

    public function forLabel(): string
    {
        if ($this->source === 'meal' && $this->mealEntry) {
            return ucfirst((string) $this->meal_type).' · '.($this->mealEntry->client?->name ?? 'Meal').' · '.$this->mealEntry->entry_date->format('d M');
        }
        if ($this->source === 'hall' && $this->hallBooking) {
            return ($this->hallBooking->event_type ? ucfirst(str_replace('_', ' ', $this->hallBooking->event_type)).' · ' : '')
                .$this->hallBooking->customer_name.' · '.$this->hallBooking->booking_reference;
        }

        return $this->purpose ?: (self::DEPARTMENTS[$this->department] ?? 'General');
    }

    /** Total cost of what left the store (issued − returned) at approval-time average cost. */
    public function issuedCost(): float
    {
        return round($this->items->sum(fn ($i) => max(0, ($i->issued_base_quantity ?? $i->approved_base_quantity ?? 0) - $i->returned_base_quantity) * ($i->unit_cost ?? 0)), 2);
    }
}
