<?php

use App\Models\HallBooking;
use App\Models\MealPlan;
use App\Models\Recipe;

function kitchenBooking(): HallBooking
{
    $plan = MealPlan::create(['name' => 'Tiffin', 'category' => 'standard', 'price_per_person' => 200, 'is_active' => true, 'description' => "Idly\nRava Upma"]);

    return HallBooking::create([
        'booking_type' => 'food_only', 'created_by' => icUser('manager')->id, 'meal_plan_id' => $plan->id,
        'customer_name' => 'Kumar Family', 'customer_mobile' => '9876543210', 'event_type' => 'wedding',
        'booking_date' => '2026-12-05', 'start_time' => '18:30', 'end_time' => '22:00',
        'number_of_people' => 150, 'has_dinner' => true, 'hall_cost' => 0, 'total_amount' => 30000,
        'payment_status' => 'pending', 'status' => 'confirmed',
    ]);
}

it('shows expandable booking cards with the menu items and recipe links', function () {
    $b = kitchenBooking();
    $r = Recipe::create(['name' => 'Idly', 'yield_per_batch' => 10, 'yield_unit' => 'kg', 'is_active' => true, 'version' => 1, 'created_by' => icUser('admin')->id]);

    $this->actingAs(icUser('manager'))
        ->get(route('hall.bookings.kitchen', ['date' => '2026-12-05']))
        ->assertOk()
        ->assertSee('data-kit-card', false)
        ->assertSee('Kumar Family')
        ->assertSee('Idly')->assertSee('Rava Upma')->assertSee('2 items')
        ->assertSee(route('kitchen.recipes.show', $r), false)
        ->assertSee(route('hall.bookings.menu.edit', $b), false)
        ->assertSee('hall_booking_id='.$b->id, false);
});

it('prefers the final menu and hides removed dishes', function () {
    $b = kitchenBooking();
    $b->menuItems()->create(['name' => 'Paneer Butter Masala', 'meal_type' => 'dinner', 'change_type' => 'added', 'is_removed' => false]);
    $b->menuItems()->create(['name' => 'Rava Upma', 'meal_type' => 'dinner', 'change_type' => 'removed', 'is_removed' => true]);

    $this->actingAs(icUser('admin'))
        ->get(route('hall.bookings.kitchen', ['date' => '2026-12-05']))
        ->assertOk()->assertSee('Paneer Butter Masala')->assertDontSee('Rava Upma')->assertSee('Dinner');
});

it('falls back to today for an invalid date and blocks employees', function () {
    $this->actingAs(icUser('admin'))->get(route('hall.bookings.kitchen', ['date' => 'bogus']))->assertOk();
    $this->actingAs(icUser('employee'))->get(route('hall.bookings.kitchen'))->assertForbidden();
});
