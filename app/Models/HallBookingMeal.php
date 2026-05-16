<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HallBookingMeal extends Model
{
    protected $fillable = ['hall_booking_id', 'meal_type', 'guest_count', 'special_requirements'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(HallBooking::class, 'hall_booking_id');
    }
}
