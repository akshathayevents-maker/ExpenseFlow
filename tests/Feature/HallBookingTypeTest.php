<?php

use App\Models\Hall;
use App\Models\HallBooking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Helpers ────────────────────────────────────────────────────────────────────

function makeUser(string $role = 'manager'): User
{
    $user = User::factory()->create();
    $user->role = $role;
    $user->save();
    return $user;
}

function makeHall(): Hall
{
    return Hall::create(['name' => 'Test Hall', 'capacity' => 200, 'is_active' => true]);
}

function baseBookingData(array $overrides = []): array
{
    // Create a real user for created_by so FK constraint is satisfied
    $defaultCreatedBy = User::factory()->create()->id;

    return array_merge([
        'booking_type'     => 'hall_food',
        'created_by'       => $defaultCreatedBy,
        'hall_id'          => null, // tests that need a hall will set this
        'customer_name'    => 'Test Customer',
        'customer_mobile'  => '9876543210',
        'event_type'       => 'wedding',
        'booking_date'     => now()->addDays(10)->format('Y-m-d'),
        'start_time'       => '10:00',
        'end_time'         => '18:00',
        'number_of_people' => 100,
        'hall_cost'        => 5000,
        'total_amount'     => 5000,
        'advance_amount'   => 2000,
        'payment_status'   => 'partial',
        'status'           => 'confirmed',
    ], $overrides);
}

// ── 1. Model predicates ────────────────────────────────────────────────────────

test('model predicates return correct values for each booking type', function () {
    $hallOnly = new HallBooking(['booking_type' => 'hall_only']);
    $hallFood = new HallBooking(['booking_type' => 'hall_food']);
    $foodOnly = new HallBooking(['booking_type' => 'food_only']);

    expect($hallOnly->isHallOnly())->toBeTrue()
        ->and($hallOnly->isFoodOnly())->toBeFalse()
        ->and($hallOnly->requiresHall())->toBeTrue()
        ->and($hallOnly->includesFood())->toBeFalse();

    expect($hallFood->isHallFood())->toBeTrue()
        ->and($hallFood->requiresHall())->toBeTrue()
        ->and($hallFood->includesFood())->toBeTrue();

    expect($foodOnly->isFoodOnly())->toBeTrue()
        ->and($foodOnly->requiresHall())->toBeFalse()
        ->and($foodOnly->includesFood())->toBeTrue();
});

// ── 2. Existing rows default to hall_food ──────────────────────────────────────

test('existing bookings have booking_type hall_food by default', function () {
    $hall = makeHall();
    $booking = HallBooking::create(baseBookingData(['booking_type' => 'hall_food', 'hall_id' => $hall->id]));
    expect($booking->booking_type)->toBe('hall_food');
});

// ── 3. Create hall_food booking (store) ───────────────────────────────────────

test('manager can create a hall_food booking', function () {
    $user = makeUser('manager');
    $hall = makeHall();

    $this->actingAs($user)->post(route('hall.bookings.store'), baseBookingData([
        'booking_type' => 'hall_food',
        'hall_id'      => $hall->id,
    ]))->assertRedirect();

    $this->assertDatabaseHas('hall_bookings', [
        'booking_type' => 'hall_food',
        'hall_id'      => $hall->id,
    ]);
});

// ── 4. Create hall_only booking ───────────────────────────────────────────────

test('manager can create a hall_only booking', function () {
    $user = makeUser('manager');
    $hall = makeHall();

    $this->actingAs($user)->post(route('hall.bookings.store'), baseBookingData([
        'booking_type' => 'hall_only',
        'hall_id'      => $hall->id,
    ]))->assertRedirect();

    $this->assertDatabaseHas('hall_bookings', ['booking_type' => 'hall_only']);
});

// ── 5. Create food_only booking — no hall required ────────────────────────────

test('manager can create a food_only booking without a hall', function () {
    $user = makeUser('manager');

    $this->actingAs($user)->post(route('hall.bookings.store'), baseBookingData([
        'booking_type'     => 'food_only',
        'hall_id'          => null,
        'service_location' => 'TCS Office Block C',
        'hall_cost'        => 0,
    ]))->assertRedirect();

    $this->assertDatabaseHas('hall_bookings', [
        'booking_type'     => 'food_only',
        'hall_id'          => null,
        'service_location' => 'TCS Office Block C',
    ]);
});

// ── 6. food_only requires service_location ────────────────────────────────────

test('food_only booking fails validation without service_location', function () {
    $user = makeUser('manager');

    $this->actingAs($user)->post(route('hall.bookings.store'), baseBookingData([
        'booking_type'     => 'food_only',
        'hall_id'          => null,
        'service_location' => '',
    ]))->assertSessionHasErrors('service_location');
});

// ── 7. hall_only and hall_food require hall_id ────────────────────────────────

test('hall_food booking fails validation without hall_id', function () {
    $user = makeUser('manager');

    $this->actingAs($user)->post(route('hall.bookings.store'), baseBookingData([
        'booking_type' => 'hall_food',
        'hall_id'      => null,
    ]))->assertSessionHasErrors('hall_id');
});

test('hall_only booking fails validation without hall_id', function () {
    $user = makeUser('manager');

    $this->actingAs($user)->post(route('hall.bookings.store'), baseBookingData([
        'booking_type' => 'hall_only',
        'hall_id'      => null,
    ]))->assertSessionHasErrors('hall_id');
});

// ── 8. Conflict check only applies to needsHall() bookings ───────────────────

test('two food_only bookings on same date and time do not conflict', function () {
    $user = makeUser('manager');

    $date = now()->addDays(5)->format('Y-m-d');

    HallBooking::create(baseBookingData([
        'booking_type'     => 'food_only',
        'hall_id'          => null,
        'service_location' => 'Location A',
        'booking_date'     => $date,
        'hall_cost'        => 0,
    ]));

    $this->actingAs($user)->post(route('hall.bookings.store'), baseBookingData([
        'booking_type'     => 'food_only',
        'hall_id'          => null,
        'service_location' => 'Location B',
        'booking_date'     => $date,
        'hall_cost'        => 0,
    ]))->assertRedirect();

    expect(HallBooking::where('booking_type', 'food_only')->count())->toBe(2);
});

test('two hall bookings on same hall, date, and overlapping time conflict', function () {
    $user = makeUser('manager');
    $hall = makeHall();
    $date = now()->addDays(5)->format('Y-m-d');

    HallBooking::create(baseBookingData([
        'booking_type' => 'hall_food',
        'hall_id'      => $hall->id,
        'booking_date' => $date,
        'start_time'   => '10:00',
        'end_time'     => '18:00',
    ]));

    $this->actingAs($user)->post(route('hall.bookings.store'), baseBookingData([
        'booking_type' => 'hall_food',
        'hall_id'      => $hall->id,
        'booking_date' => $date,
        'start_time'   => '12:00',
        'end_time'     => '16:00',
    ]))->assertSessionHasErrors();
});

// ── 9. Check availability short-circuits for food_only ────────────────────────

test('check-availability returns available=true for food_only', function () {
    $user = makeUser('manager');

    $this->actingAs($user)
        ->getJson(route('hall.bookings.check-availability') . '?booking_type=food_only&booking_date=' . now()->addDays(3)->format('Y-m-d') . '&start_time=10:00&end_time=18:00')
        ->assertOk()
        ->assertJsonPath('available', true);
});

// ── 10. location_label accessor ───────────────────────────────────────────────

test('location_label returns hall name for hall types and service_location for food_only', function () {
    $hall = makeHall();

    $hallFood = HallBooking::create(baseBookingData([
        'booking_type' => 'hall_food',
        'hall_id'      => $hall->id,
    ]));
    $hallFood->load('hall');

    $foodOnly = HallBooking::create(baseBookingData([
        'booking_type'     => 'food_only',
        'hall_id'          => null,
        'service_location' => 'Murugan Temple',
        'hall_cost'        => 0,
    ]));

    expect($hallFood->location_label)->toBe('Test Hall')
        ->and($foodOnly->location_label)->toBe('Murugan Temple');
});

// ── 11. Calendar events include booking_type ──────────────────────────────────

test('calendar events API returns booking_type in extendedProps', function () {
    $user = makeUser('manager');
    $hall = makeHall();

    HallBooking::create(baseBookingData([
        'booking_type' => 'hall_food',
        'hall_id'      => $hall->id,
        'booking_date' => now()->format('Y-m-d'),
    ]));

    $start = now()->startOfMonth()->format('Y-m-d');
    $end   = now()->endOfMonth()->format('Y-m-d');

    $this->actingAs($user)
        ->getJson(route('hall.bookings.calendar-events') . "?start={$start}&end={$end}")
        ->assertOk()
        ->assertJsonFragment(['booking_type' => 'hall_food']);
});

// ── 12. Kitchen filter works for all three variants ───────────────────────────

test('kitchen filter all returns hall_food and food_only bookings', function () {
    $user = makeUser('manager');
    $hall = makeHall();
    $date = now()->format('Y-m-d');

    HallBooking::create(baseBookingData(['booking_type' => 'hall_food', 'hall_id' => $hall->id, 'booking_date' => $date]));
    HallBooking::create(baseBookingData(['booking_type' => 'food_only', 'hall_id' => null, 'service_location' => 'Loc', 'booking_date' => $date, 'hall_cost' => 0]));
    HallBooking::create(baseBookingData(['booking_type' => 'hall_only', 'hall_id' => $hall->id, 'booking_date' => $date]));

    $this->actingAs($user)
        ->get(route('hall.bookings.kitchen', ['date' => $date]))
        ->assertOk()
        ->assertSeeText('Test Customer');
});

test('kitchen filter external returns only food_only bookings', function () {
    $user = makeUser('manager');
    $hall = makeHall();
    $date = now()->format('Y-m-d');

    HallBooking::create(baseBookingData(['booking_type' => 'hall_food', 'hall_id' => $hall->id, 'customer_name' => 'Hall Guy', 'booking_date' => $date]));
    HallBooking::create(baseBookingData(['booking_type' => 'food_only', 'hall_id' => null, 'service_location' => 'Loc', 'customer_name' => 'Food Guy', 'booking_date' => $date, 'hall_cost' => 0]));

    $response = $this->actingAs($user)
        ->get(route('hall.bookings.kitchen', ['date' => $date, 'catering_type' => 'external']))
        ->assertOk();

    $content = $response->getContent();
    expect($content)->toContain('Food Guy')
        ->and($content)->not->toContain('Hall Guy');
});

// ── 13. Reports filter by booking_type works ──────────────────────────────────

test('reports can filter by booking_type', function () {
    $user = makeUser('admin');
    $hall = makeHall();

    HallBooking::create(baseBookingData(['booking_type' => 'hall_food', 'hall_id' => $hall->id, 'customer_name' => 'Hall Food Customer']));
    HallBooking::create(baseBookingData(['booking_type' => 'food_only', 'hall_id' => null, 'service_location' => 'Loc', 'customer_name' => 'Food Only Customer', 'hall_cost' => 0]));

    $response = $this->actingAs($user)
        ->get(route('hall.reports.index', ['booking_type' => 'food_only']))
        ->assertOk();

    $content = $response->getContent();
    expect($content)->toContain('Food Only Customer')
        ->and($content)->not->toContain('Hall Food Customer');
});

// ── 14. Employee cannot see financial data on calendar events ─────────────────

test('employee calendar events endpoint strips financial fields', function () {
    $employee = makeUser('employee');
    $hall = makeHall();

    HallBooking::create(baseBookingData([
        'booking_type' => 'hall_food',
        'hall_id'      => $hall->id,
        'booking_date' => now()->format('Y-m-d'),
        'total_amount' => 15000,
    ]));

    $start = now()->startOfMonth()->format('Y-m-d');
    $end   = now()->endOfMonth()->format('Y-m-d');

    $response = $this->actingAs($employee)
        ->getJson(route('employee.hall.bookings.calendar-events') . "?start={$start}&end={$end}")
        ->assertOk();

    $data = $response->json();
    foreach ($data as $event) {
        $props = $event['extendedProps'] ?? [];
        expect($props)->not->toHaveKey('amount')
            ->and($props)->not->toHaveKey('balance')
            ->and($props)->not->toHaveKey('payment_url');
    }
});
