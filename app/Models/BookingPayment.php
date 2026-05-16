<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPayment extends Model
{
    protected $fillable = [
        'hall_booking_id', 'recorded_by', 'amount',
        'payment_method', 'reference_number', 'payment_type', 'paid_at', 'notes',
    ];

    protected $casts = [
        'amount'   => 'decimal:2',
        'paid_at'  => 'date',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(HallBooking::class, 'hall_booking_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public static function methods(): array
    {
        return [
            'cash'          => 'Cash',
            'upi'           => 'UPI',
            'card'          => 'Card',
            'bank_transfer' => 'Bank Transfer',
        ];
    }

    public static function types(): array
    {
        return ['advance' => 'Advance', 'balance' => 'Balance', 'full' => 'Full Payment'];
    }
}
