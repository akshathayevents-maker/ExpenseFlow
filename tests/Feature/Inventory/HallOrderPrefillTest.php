<?php

use App\Models\HallBooking;
use App\Models\MealPlan;
use App\Models\Recipe;

function prefillBooking(string $menu = "Idly\nRava Upma\nPayasam"): HallBooking
{
    $plan = MealPlan::create(['name' => 'Tiffin', 'category' => 'standard', 'price_per_person' => 200, 'is_active' => true, 'description' => $menu]);

    return HallBooking::create([
        'booking_type' => 'food_only', 'created_by' => icUser('manager')->id, 'meal_plan_id' => $plan->id,
        'customer_name' => 'Vijay', 'customer_mobile' => '9876543210', 'event_type' => 'wedding',
        'booking_date' => now()->addDays(3)->toDateString(), 'start_time' => '18:30', 'end_time' => '22:00',
        'number_of_people' => 200, 'has_dinner' => true, 'hall_cost' => 0, 'total_amount' => 40000,
        'payment_status' => 'pending', 'status' => 'confirmed',
    ]);
}

function prefillRecipe(string $name, float $yield, string $yieldUnit, array $ings): Recipe
{
    $r = Recipe::create(['name' => $name, 'yield_per_batch' => $yield, 'yield_unit' => $yieldUnit, 'is_active' => true, 'version' => 1, 'created_by' => icUser('admin')->id]);
    foreach ($ings as $i => $ing) {
        $r->ingredients()->create($ing + ['sort_order' => $i]);
    }

    return $r;
}

it('pre-fills date, time, preparations from the menu and materials from matching recipes', function () {
    $rice = icItem('Rice', 'kg');
    $salt = icItem('Salt', 'kg');
    $rava = icItem('Rava', 'kg');
    // batch recipe not linked to stock items: 10 kg rice + 100 grams salt per 100 servings
    prefillRecipe('Idly 1', 100, 'unit', [
        ['ingredient_name' => 'rice', 'quantity_per_batch' => 10, 'unit' => 'kg'],
        ['ingredient_name' => 'salt', 'quantity_per_batch' => 100, 'unit' => 'grams'],
        ['ingredient_name' => 'Unknown spice', 'quantity_per_batch' => 1, 'unit' => 'kg'],
    ]);
    // standard recipe: per guest in stock unit
    prefillRecipe('Upma', 1, 'people', [['inventory_item_id' => $rava->id, 'ingredient_name' => 'Rava', 'quantity_per_batch' => 0.05, 'unit' => 'kg', 'quantity_per_guest' => 0.05]]);
    $b = prefillBooking();

    $d = $this->actingAs(icUser())->getJson(route('inventory.material-requests.hall-prefill', $b))->assertOk()->json();

    expect($d['required_date'])->toBe($b->booking_date->toDateString())
        ->and($d['required_time'])->toBe('18:30')
        ->and(collect($d['preparations'])->pluck('name')->all())->toBe(['Idly', 'Rava Upma', 'Payasam'])
        ->and($d['preparations'][0]['people'])->toBe(200)
        ->and(collect($d['lines'])->keyBy('inventory_item_id')->map(fn ($l) => [$l['quantity'], $l['unit']])->all())->toEqual([
            $rice->id => [20.0, 'kg'],      // 10 kg per 100 → 200 people
            $salt->id => [0.2, 'kg'],       // 100 g per 100 → 200 g, in stock unit
            $rava->id => [10.0, 'kg'],      // 0.05 kg per guest
        ])
        ->and(collect($d['recipes'])->pluck('recipe', 'menu')->all())->toBe(['Idly' => 'Idly 1', 'Rava Upma' => 'Upma'])
        ->and($d['no_recipe'])->toBe(['Payasam'])
        ->and($d['unlinked'])->toBe(['Unknown spice — Idly 1']);

    // opened from a hall booking link: the form is pre-filled server-side, all fields editable
    $this->get(route('inventory.material-requests.create', ['hall_booking_id' => $b->id]))->assertOk()
        ->assertSee('value="Rava Upma"', false)->assertSee('value="18:30"', false)->assertSee('From recipe: Idly 1');
});

it('needs request rights and ignores cancelled orders', function () {
    $b = prefillBooking();
    $b->update(['status' => 'cancelled']);
    $this->actingAs(icUser())->getJson(route('inventory.material-requests.hall-prefill', $b))->assertNotFound();
    $this->getJson(route('inventory.material-requests.hall-prefill', prefillBooking('')))->assertOk()
        ->assertJsonPath('preparations', [])->assertJsonPath('lines', [])->assertJsonPath('has_menu', false);
});
