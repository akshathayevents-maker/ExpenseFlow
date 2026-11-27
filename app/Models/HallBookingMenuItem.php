<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HallBookingMenuItem extends Model
{
    public const CHANGES = ['plan' => 'From plan', 'added' => 'Added', 'replaced' => 'Replaced', 'modified' => 'Changed', 'removed' => 'Removed'];

    public const CHARGES = ['included' => 'Included', 'per_person' => 'Per person', 'fixed' => 'Fixed amount'];

    protected $fillable = ['hall_booking_id', 'name', 'meal_type', 'change_type', 'is_removed', 'notes', 'sort_order', 'updated_by',
        'replaces', 'servings', 'charge_type', 'charge_amount'];

    protected $casts = ['is_removed' => 'boolean', 'servings' => 'integer', 'charge_amount' => 'decimal:2'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(HallBooking::class, 'hall_booking_id');
    }
}
