<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HallBookingMenuItem extends Model
{
    public const CHANGES = ['plan' => 'From plan', 'added' => 'Added', 'modified' => 'Changed', 'removed' => 'Removed'];

    protected $fillable = ['hall_booking_id', 'name', 'meal_type', 'change_type', 'is_removed', 'notes', 'sort_order', 'updated_by'];

    protected $casts = ['is_removed' => 'boolean'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(HallBooking::class, 'hall_booking_id');
    }
}
