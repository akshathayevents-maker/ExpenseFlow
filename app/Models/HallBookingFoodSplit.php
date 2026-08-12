<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HallBookingFoodSplit extends Model
{
    protected $fillable = [
        'hall_booking_id', 'meal_plan_id', 'meal_plan_name',
        'guest_count', 'price_per_guest', 'amount', 'sort_order',
    ];

    protected $casts = [
        'guest_count'     => 'integer',
        'price_per_guest' => 'decimal:2',
        'amount'          => 'decimal:2',
        'sort_order'      => 'integer',
    ];

    public function hallBooking(): BelongsTo
    {
        return $this->belongsTo(HallBooking::class);
    }

    public function mealPlan(): BelongsTo
    {
        return $this->belongsTo(MealPlan::class);
    }
}
