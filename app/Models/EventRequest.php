<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventRequest extends Model
{
    protected $fillable = [
        'created_by',
        'client_name', 'client_mobile', 'client_email', 'event_name', 'event_date',
        'meal_type', 'guest_count', 'menu_type', 'special_instructions',
        'status', 'admin_comment',
        'per_person_cost', 'estimated_total',
        'hall_booking_id',
        'submitted_at', 'approved_at', 'rejected_at',
    ];

    protected $casts = [
        'event_date'       => 'date',
        'guest_count'      => 'integer',
        'per_person_cost'  => 'decimal:2',
        'estimated_total'  => 'decimal:2',
        'submitted_at'     => 'datetime',
        'approved_at'      => 'datetime',
        'rejected_at'      => 'datetime',
    ];

    // ── Config ───────────────────────────────────────────────────────────

    public static function mealTypes(): array
    {
        return [
            'breakfast' => 'Breakfast',
            'lunch'     => 'Lunch',
            'dinner'    => 'Dinner',
            'reception' => 'Reception',
            'high_tea'  => 'High Tea',
        ];
    }

    public static function menuTypes(): array
    {
        return [
            'veg'     => 'Veg',
            'non_veg' => 'Non-Veg',
            'both'    => 'Both',
        ];
    }

    public static function statuses(): array
    {
        return [
            'draft'             => 'Draft',
            'submitted'         => 'Submitted',
            'under_review'      => 'Under Review',
            'need_changes'      => 'Need Changes',
            'resubmitted'       => 'Resubmitted',
            'approved'          => 'Approved',
            'rejected'          => 'Rejected',
            'scheduled'         => 'Scheduled',
        ];
    }

    public static function editableStatuses(): array
    {
        return ['draft', 'need_changes'];
    }

    // ── Relationships ────────────────────────────────────────────────────

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(EventRequestItem::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(EventRequestRevision::class)->orderBy('created_at');
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(EventRequestPublicToken::class);
    }

    public function hallBooking(): BelongsTo
    {
        return $this->belongsTo(HallBooking::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    public function activeToken(): ?EventRequestPublicToken
    {
        return $this->tokens()->active()->latest('id')->first();
    }

    public function isEditableByClient(): bool
    {
        return in_array($this->status, self::editableStatuses(), true);
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? ucfirst($this->status);
    }

    public function mealTypeLabel(): string
    {
        return self::mealTypes()[$this->meal_type] ?? (string) $this->meal_type;
    }

    public function menuTypeLabel(): string
    {
        return self::menuTypes()[$this->menu_type] ?? (string) $this->menu_type;
    }

    public function referenceNumber(): string
    {
        return 'ER-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }
}
