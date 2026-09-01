<?php

use App\Models\BookingPayment;
use App\Models\HallBooking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Reuses baseBookingData()/makeUser() helpers defined globally in
// HallBookingTypeTest.php.

function payFor(HallBooking $booking, float $amount): void
{
    BookingPayment::create([
        'hall_booking_id' => $booking->id,
        'recorded_by'     => User::factory()->create()->id,
        'amount'          => $amount,
        'payment_method'  => 'cash',
        'payment_type'    => 'advance',
        'paid_at'         => now(),
    ]);
}

// ── Active bookings: normal formula still applies ───────────────────────────

test('active booking with no payment shows full amount due', function () {
    $booking = HallBooking::create(baseBookingData([
        'status' => 'confirmed', 'total_amount' => 10000,
    ]));

    expect($booking->balance_amount)->toBe(10000.0);
});

test('active booking with partial payment shows plan minus paid', function () {
    $booking = HallBooking::create(baseBookingData([
        'status' => 'confirmed', 'total_amount' => 10000,
    ]));
    payFor($booking, 4000);
    $booking->refresh();

    expect($booking->balance_amount)->toBe(6000.0);
});

test('active booking fully paid shows zero due', function () {
    $booking = HallBooking::create(baseBookingData([
        'status' => 'confirmed', 'total_amount' => 10000,
    ]));
    payFor($booking, 10000);
    $booking->refresh();

    expect($booking->balance_amount)->toBe(0.0);
});

// ── Cancelled bookings: always zero, regardless of payment history ─────────

test('cancelled booking with no payment shows zero due', function () {
    $booking = HallBooking::create(baseBookingData([
        'status' => 'cancelled', 'total_amount' => 10000,
    ]));

    expect($booking->balance_amount)->toBe(0.0);
});

test('cancelled booking with partial payment shows zero due but keeps payment history intact', function () {
    $booking = HallBooking::create(baseBookingData([
        'status' => 'confirmed', 'total_amount' => 10000,
    ]));
    payFor($booking, 4000);
    $booking->update(['status' => 'cancelled']);
    $booking->refresh();

    expect($booking->balance_amount)->toBe(0.0)
        ->and($booking->total_paid)->toBe(4000.0)
        ->and((float) $booking->total_amount)->toBe(10000.0)
        ->and($booking->payments()->count())->toBe(1);
});

test('cancelled booking fully paid still shows zero due and preserves paid history', function () {
    $booking = HallBooking::create(baseBookingData([
        'status' => 'confirmed', 'total_amount' => 10000,
    ]));
    payFor($booking, 10000);
    $booking->update(['status' => 'cancelled']);
    $booking->refresh();

    expect($booking->balance_amount)->toBe(0.0)
        ->and($booking->total_paid)->toBe(10000.0);
});

test('cancelled booking rule holds regardless of plan amount', function () {
    foreach ([0, 500, 999999] as $amount) {
        $booking = HallBooking::create(baseBookingData([
            'status' => 'cancelled', 'total_amount' => $amount,
        ]));
        expect($booking->balance_amount)->toBe(0.0);
    }
});

// ── Round trip: cancelled → re-activated restores the real formula ─────────

test('re-activating a cancelled booking restores the normal balance formula intact', function () {
    $booking = HallBooking::create(baseBookingData([
        'status' => 'confirmed', 'total_amount' => 10000,
    ]));
    payFor($booking, 4000);

    $booking->update(['status' => 'cancelled']);
    $booking->refresh();
    expect($booking->balance_amount)->toBe(0.0);

    // Flip back to confirmed — plan_amount/paid_amount were never touched,
    // so the real balance must resume exactly where it left off.
    $booking->update(['status' => 'confirmed']);
    $booking->refresh();

    expect($booking->balance_amount)->toBe(6000.0)
        ->and($booking->total_paid)->toBe(4000.0)
        ->and((float) $booking->total_amount)->toBe(10000.0);
});

// ── Active-outstanding queries must exclude cancelled bookings ─────────────

test('active outstanding query excludes cancelled bookings even though their raw total_amount is unpaid', function () {
    $active = HallBooking::create(baseBookingData([
        'status' => 'confirmed', 'payment_status' => 'pending', 'total_amount' => 5000,
    ]));
    $cancelled = HallBooking::create(baseBookingData([
        'status' => 'cancelled', 'payment_status' => 'pending', 'total_amount' => 7000,
    ]));

    $outstanding = HallBooking::where('payment_status', '!=', 'paid')
        ->where('status', '!=', 'cancelled')
        ->pluck('id');

    expect($outstanding)->toContain($active->id)
        ->and($outstanding)->not->toContain($cancelled->id);
});
