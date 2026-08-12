<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HallBooking extends Model
{
    protected $fillable = [
        'hall_id', 'meal_plan_id', 'created_by',
        'booking_type', 'service_location',
        'customer_name', 'customer_mobile', 'customer_alt_mobile',
        'event_type', 'booking_date', 'start_time', 'end_time', 'number_of_people',
        'has_breakfast', 'has_lunch', 'has_dinner',
        'hall_cost', 'total_amount', 'advance_amount', 'payment_status', 'status', 'notes',
        'review_requested_at',
        'invoice_number', 'invoice_date', 'cgst_rate', 'sgst_rate',
        'uses_mixed_food',
    ];

    protected $casts = [
        'booking_date'        => 'date',
        'hall_cost'           => 'decimal:2',
        'total_amount'        => 'decimal:2',
        'advance_amount'      => 'decimal:2',
        'has_breakfast'       => 'boolean',
        'has_lunch'           => 'boolean',
        'has_dinner'          => 'boolean',
        'review_requested_at' => 'datetime',
        'invoice_date'        => 'date',
        'cgst_rate'           => 'decimal:2',
        'sgst_rate'           => 'decimal:2',
        'uses_mixed_food'     => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    public function mealPlan(): BelongsTo
    {
        return $this->belongsTo(MealPlan::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function meals(): HasMany
    {
        return $this->hasMany(HallBookingMeal::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BookingPayment::class);
    }

    public function additionalServices(): HasMany
    {
        return $this->hasMany(BookingAdditionalService::class);
    }

    /** Structured veg/non-veg (or any) food split rows — only meaningful when uses_mixed_food is true. */
    public function foodSplits(): HasMany
    {
        return $this->hasMany(HallBookingFoodSplit::class)->orderBy('sort_order');
    }

    // ── Booking type predicates ────────────────────────────────────────────────

    public function isHallOnly(): bool  { return $this->booking_type === 'hall_only'; }
    public function isHallFood(): bool  { return $this->booking_type === 'hall_food'; }
    public function isFoodOnly(): bool  { return $this->booking_type === 'food_only'; }

    /** True when a physical hall must be reserved (conflict checks apply). */
    public function requiresHall(): bool { return in_array($this->booking_type, ['hall_only', 'hall_food']); }

    /** True when catering / kitchen load is involved. */
    public function includesFood(): bool { return in_array($this->booking_type, ['hall_food', 'food_only']); }

    // ── Query scopes ───────────────────────────────────────────────────────────

    public function scopeHallOnly(Builder $query): Builder
    {
        return $query->where('booking_type', 'hall_only');
    }

    public function scopeHallFood(Builder $query): Builder
    {
        return $query->where('booking_type', 'hall_food');
    }

    public function scopeFoodOnly(Builder $query): Builder
    {
        return $query->where('booking_type', 'food_only');
    }

    /** Bookings that occupy a hall slot (used for conflict detection). */
    public function scopeNeedsHall(Builder $query): Builder
    {
        return $query->whereIn('booking_type', ['hall_only', 'hall_food']);
    }

    /** Bookings that contribute to kitchen / catering load. */
    public function scopeHasFood(Builder $query): Builder
    {
        return $query->whereIn('booking_type', ['hall_food', 'food_only']);
    }

    // ── Computed attributes ────────────────────────────────────────────────────

    /**
     * Total food cost — the single authoritative source for every consumer
     * (booking totals, show page, invoice line items). Mixed-food bookings
     * sum their structured split rows; normal bookings use the single
     * meal plan × guest count, exactly as before.
     */
    public function getMealCostAttribute(): float
    {
        if ($this->uses_mixed_food) {
            return $this->relationLoaded('foodSplits')
                ? (float) $this->foodSplits->sum('amount')
                : (float) $this->foodSplits()->sum('amount');
        }

        $pricePerPerson = (float) ($this->mealPlan?->price_per_person ?? 0);
        return $pricePerPerson * (int) $this->number_of_people;
    }

    public function getServicesTotalAttribute(): float
    {
        return $this->relationLoaded('additionalServices')
            ? (float) $this->additionalServices->sum('amount')
            : (float) $this->additionalServices()->sum('amount');
    }

    public function getBalanceAmountAttribute(): float
    {
        $paid = $this->relationLoaded('payments')
            ? $this->payments->sum('amount')
            : $this->payments()->sum('amount');
        return (float) $this->total_amount - (float) $paid;
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->relationLoaded('payments')
            ? (float) $this->payments->sum('amount')
            : (float) $this->payments()->sum('amount');
    }

    // ── Display helpers ────────────────────────────────────────────────────────

    public function isConfirmed(): bool       { return $this->status === 'confirmed'; }
    public function isCancelled(): bool       { return $this->status === 'cancelled'; }
    public function isCompleted(): bool       { return $this->status === 'completed'; }
    public function isFollowUpEligible(): bool { return in_array($this->status, ['completed', 'closed']); }

    /** Human-readable location string for display across calendar, kitchen, invoices. */
    public function getLocationLabelAttribute(): string
    {
        if ($this->isFoodOnly()) {
            return $this->service_location ?? 'External catering';
        }
        return $this->hall?->name ?? '—';
    }

    /**
     * The invoice number to display/use. Falls back to a booking-id-derived
     * default when nothing has been saved yet — this is distinct from (and
     * never overwrites) the booking reference (BK-####).
     */
    public function getEffectiveInvoiceNumberAttribute(): string
    {
        return $this->invoice_number ?: 'INV-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public function getBookingReferenceAttribute(): string
    {
        return 'BK-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    // ── Static lookup tables ───────────────────────────────────────────────────

    public static function bookingTypes(): array
    {
        return [
            'hall_only' => 'Hall Only',
            'hall_food' => 'Hall + Food',
            'food_only' => 'Food Only',
        ];
    }

    public static function bookingTypeIcons(): array
    {
        return [
            'hall_only' => '🏛',
            'hall_food' => '🏛🍽',
            'food_only' => '🍽',
        ];
    }

    /** CSS modifier class per booking type — used by badge component and calendar. */
    public static function bookingTypeColors(): array
    {
        return [
            'hall_only' => 'blue',
            'hall_food' => 'green',
            'food_only' => 'orange',
        ];
    }

    public static function statuses(): array
    {
        return ['confirmed' => 'Confirmed', 'cancelled' => 'Cancelled', 'completed' => 'Completed'];
    }

    public static function paymentStatuses(): array
    {
        return ['pending' => 'Pending', 'partial' => 'Partial', 'paid' => 'Paid'];
    }

    public static function statusColors(): array
    {
        return [
            'confirmed' => 'success',
            'cancelled' => 'danger',
            'completed' => 'primary',
        ];
    }

    public static function paymentStatusColors(): array
    {
        return [
            'pending' => 'warning',
            'partial' => 'info',
            'paid'    => 'success',
        ];
    }

    public static function eventTypes(): array
    {
        return [
            'wedding'       => 'Wedding',
            'birthday'      => 'Birthday',
            'corporate'     => 'Corporate Event',
            'engagement'    => 'Engagement',
            'baby_shower'   => 'Baby Shower',
            'farewell'      => 'Farewell',
            'conference'    => 'Conference',
            'anniversary'   => 'Anniversary',
            'other'         => 'Other',
        ];
    }
}
