<?php

use App\Models\HallBooking;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Reuses baseBookingData()/makeUser() helpers defined globally in
// HallBookingTypeTest.php.

test('dashboard defaults to the current month when no month param is given', function () {
    $user = makeUser('manager');

    $thisMonthBooking = HallBooking::create(baseBookingData([
        'status' => 'confirmed',
        'booking_date' => now()->startOfMonth()->addDays(2)->format('Y-m-d'),
        'total_amount' => 15000,
    ]));

    $response = $this->actingAs($user)->get('/hall/dashboard');

    $response->assertOk();
    $response->assertSee(now()->format('F Y'));
    $response->assertViewHas('operations', function ($operations) {
        return $operations['month_revenue'] == 15000;
    });
});

test('an explicit month query param loads that month\'s data', function () {
    $user = makeUser('manager');

    $julyBooking = HallBooking::create(baseBookingData([
        'status' => 'confirmed',
        'booking_date' => '2026-07-15',
        'total_amount' => 20000,
    ]));

    // A booking in a different month must not leak into July's figures.
    HallBooking::create(baseBookingData([
        'status' => 'confirmed',
        'booking_date' => '2026-08-15',
        'total_amount' => 99999,
    ]));

    $response = $this->actingAs($user)->get('/hall/dashboard?month=2026-07');

    $response->assertOk();
    $response->assertSee('July 2026');
    $response->assertViewHas('operations', function ($operations) {
        return $operations['month_revenue'] == 20000
            && $operations['month_bookings_count'] === 1;
    });
});

test('previous and next navigation links point to the adjacent month in Y-m format', function () {
    $user = makeUser('manager');

    $response = $this->actingAs($user)->get('/hall/dashboard?month=2026-07');

    $response->assertOk();
    $response->assertViewHas('prevMonth', '2026-06');
    $response->assertViewHas('nextMonth', '2026-08');
    $response->assertSee('month=2026-06', false);
    $response->assertSee('month=2026-08', false);
});

test('an invalid month param falls back to the current month instead of erroring', function () {
    $user = makeUser('manager');

    $response = $this->actingAs($user)->get('/hall/dashboard?month=not-a-month');

    $response->assertOk();
    $response->assertSee(now()->format('F Y'));
});

test('a month with zero bookings renders an empty state without error', function () {
    $user = makeUser('manager');

    $response = $this->actingAs($user)->get('/hall/dashboard?month=2031-01');

    $response->assertOk();
    $response->assertViewHas('operations', function ($operations) {
        return $operations['month_revenue'] == 0
            && $operations['month_bookings_count'] === 0
            && $operations['month_payment_due'] == 0;
    });
});

test('cancelled bookings within the selected month do not inflate month payment due', function () {
    $user = makeUser('manager');

    HallBooking::create(baseBookingData([
        'status' => 'cancelled',
        'payment_status' => 'pending',
        'booking_date' => '2026-09-10',
        'total_amount' => 50000,
        'advance_amount' => 0,
    ]));
    $active = HallBooking::create(baseBookingData([
        'status' => 'confirmed',
        'payment_status' => 'partial',
        'booking_date' => '2026-09-12',
        'total_amount' => 10000,
        'advance_amount' => 4000,
    ]));

    $response = $this->actingAs($user)->get('/hall/dashboard?month=2026-09');

    $response->assertOk();
    $response->assertViewHas('operations', function ($operations) use ($active) {
        // Only the active booking's balance (10000) should count; the
        // cancelled booking's 50000 must not appear via
        // HallBooking::getBalanceAmountAttribute()'s cancellation rule.
        return $operations['month_bookings_count'] === 1
            && $operations['month_payment_due'] == $active->balance_amount;
    });
});
