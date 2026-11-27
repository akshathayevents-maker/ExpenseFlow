<?php

use App\Models\HallBooking;

it('shows attention strip, today events and operations snapshot', function () {
    HallBooking::create([
        'booking_type' => 'food_only', 'created_by' => icUser('manager')->id, 'service_location' => 'Anna Nagar',
        'customer_name' => 'Kumar Family', 'customer_mobile' => '9876543210', 'event_type' => 'wedding',
        'booking_date' => today()->toDateString(), 'start_time' => '08:00', 'end_time' => '11:00',
        'number_of_people' => 150, 'hall_cost' => 0, 'total_amount' => 30000, 'payment_status' => 'pending', 'status' => 'confirmed',
    ]);

    $html = $this->actingAs(icUser('admin'))->get(route('admin.dashboard'))->assertOk()
        ->assertSee('Kumar Family')->assertSee('Anna Nagar')->assertSee('150 guests')
        ->assertSee('Attendance Today')->assertSeeText("Today's Leave")->assertSee('Store &amp; kitchen', false)->assertSee('Material requests to approve')
        ->getContent();

    foreach (['expenses', 'overtime', 'advances', 'leave', 'regularizations', 'wallets'] as $k) {
        expect($html)->toContain('data-attn="'.$k.'"');
    }
    expect(substr_count($html, 'data-booking'))->toBe(1);
});

it('is admin only', function () {
    $this->actingAs(icUser('employee'))->get(route('admin.dashboard'))->assertForbidden();
});
