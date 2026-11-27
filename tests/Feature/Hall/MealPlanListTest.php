<?php

use App\Models\MealPlan;

function mplPlan(array $a = []): MealPlan
{
    return MealPlan::create(array_merge(['name' => 'Plan '.uniqid(), 'category' => 'standard', 'price_per_person' => 300, 'is_active' => true, 'items' => ['Idli', 'Vada']], $a));
}

it('lists plans with status and category counts', function () {
    mplPlan(['name' => 'South Breakfast']);
    mplPlan(['name' => 'Royal Feast', 'category' => 'premium', 'price_per_person' => 900]);
    mplPlan(['name' => 'Old Lunch', 'is_active' => false]);

    $this->actingAs(icUser('admin'))->get(route('hall.meal-plans.index'))->assertOk()
        ->assertSee('South Breakfast')->assertSee('Royal Feast')->assertSee('Old Lunch')
        ->assertSee('Idli, Vada')->assertSee('₹900')->assertSee('How plans work')
        ->assertSeeInOrder(['data-k="all"', '3', 'data-k="active"', '2', 'data-k="inactive"', '1'], false);
});

it('filters by status, category and search; ignores unknown status', function () {
    mplPlan(['name' => 'South Breakfast']);
    mplPlan(['name' => 'Royal Feast', 'category' => 'premium']);
    mplPlan(['name' => 'Old Lunch', 'is_active' => false]);
    $u = icUser('admin');

    $this->actingAs($u)->get(route('hall.meal-plans.index', ['status' => 'inactive']))
        ->assertSee('Old Lunch')->assertDontSee('Royal Feast');
    $this->actingAs($u)->get(route('hall.meal-plans.index', ['category' => 'premium']))
        ->assertSee('Royal Feast')->assertDontSee('South Breakfast');
    $this->actingAs($u)->get(route('hall.meal-plans.index', ['q' => 'south']))
        ->assertSee('South Breakfast')->assertDontSee('Old Lunch');
    $this->actingAs($u)->get(route('hall.meal-plans.index', ['status' => 'bogus']))
        ->assertOk()->assertSee('Old Lunch')->assertSee('Royal Feast');
    $this->actingAs($u)->get(route('hall.meal-plans.index', ['q' => 'zzz']))
        ->assertSee('No meal plans match');
});

it('shows empty state', function () {
    $this->actingAs(icUser('admin'))->get(route('hall.meal-plans.index'))->assertOk()->assertSee('No meal plans yet');
});
