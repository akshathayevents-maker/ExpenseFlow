<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HallBooking extends Model
{
    protected $fillable = [
        'hall_id', 'meal_plan_id', 'created_by',
        'customer_name', 'customer_mobile', 'customer_alt_mobile',
        'event_type', 'booking_date', 'start_time', 'end_time', 'number_of_people',
        'has_breakfast', 'has_lunch', 'has_dinner',
        'total_amount', 'advance_amount', 'payment_status', 'status', 'notes',
    ];

    protected $casts = [
        'booking_date'   => 'date',
        'total_amount'   => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'has_breakfast'  => 'boolean',
        'has_lunch'      => 'boolean',
        'has_dinner'     => 'boolean',
    ];

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

    public function isConfirmed(): bool  { return $this->status === 'confirmed'; }
    public function isCancelled(): bool  { return $this->status === 'cancelled'; }
    public function isCompleted(): bool  { return $this->status === 'completed'; }

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
