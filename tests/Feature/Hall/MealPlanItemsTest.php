<?php

use App\Models\HallBooking;
use App\Models\MealPlan;
use App\Services\Inventory\HallMenuService;

function planPayload(array $extra = []): array
{
    return array_merge(['name' => 'Veg-360', 'category' => 'standard', 'price_per_person' => 360, 'is_active' => 1], $extra);
}

it('creates a plan with an ordered dish list (blank and duplicate rows dropped)', function () {
    $this->actingAs(icUser('admin'))->get(route('hall.meal-plans.create'))->assertOk()->assertSee('Dishes in this plan')->assertSee('Add dish');

    $this->post(route('hall.meal-plans.store'), planPayload(['items' => [' Idly ', '', 'Sambar', 'idly', 'Kesari']]))
        ->assertRedirect(route('hall.meal-plans.index'));

    expect(MealPlan::first()->items)->toBe(['Idly', 'Sambar', 'Kesari']);
});

it('edits dishes; old plans show their description lines as dishes until re-saved', function () {
    $old = MealPlan::create(planPayload(['description' => "1. Idly\n- Vada\nCoffee"]));
    expect($old->dishList())->toBe(['Idly', 'Vada', 'Coffee']);

    $this->actingAs(icUser('admin'))->get(route('hall.meal-plans.edit', $old))->assertOk()
        ->assertSee('value="Vada"', false)->assertSee('value="Coffee"', false);

    $this->put(route('hall.meal-plans.update', $old), planPayload(['description' => 'Buffet', 'items' => ['Idly', 'Pongal', 'Coffee']]))
        ->assertRedirect();
    expect($old->fresh()->dishList())->toBe(['Idly', 'Pongal', 'Coffee']);
});

it('feeds the hall order final menu from the plan dishes', function () {
    $plan = MealPlan::create(planPayload(['items' => ['Idly', 'Kesari']]));
    $b = HallBooking::create([
        'booking_type' => 'food_only', 'created_by' => icUser('manager')->id, 'meal_plan_id' => $plan->id,
        'customer_name' => 'Vijay', 'customer_mobile' => '9876543210', 'event_type' => 'wedding',
        'booking_date' => now()->addDays(3)->toDateString(), 'start_time' => '18:30', 'end_time' => '22:00',
        'number_of_people' => 100, 'has_dinner' => true, 'hall_cost' => 0, 'total_amount' => 36000,
        'payment_status' => 'pending', 'status' => 'confirmed',
    ]);
    expect(collect(app(HallMenuService::class)->draftLines($b))->pluck('name')->all())->toBe(['Idly', 'Kesari']);
});

it('validates dish names', function () {
    $this->actingAs(icUser('admin'))->post(route('hall.meal-plans.store'), planPayload(['items' => [str_repeat('x', 151)]]))
        ->assertSessionHasErrors('items.0');
    expect(MealPlan::count())->toBe(0);
});
