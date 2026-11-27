<?php

use App\Models\Hall;
use App\Models\HallBooking;
use App\Models\MaterialRequest;
use App\Models\MealPlan;
use App\Services\Inventory\HallMenuService;
use App\Services\Inventory\HallOrderPrefillService;
use App\Services\InvoiceCalculationService;

function bmcPlan(): MealPlan
{
    return MealPlan::create(['name' => 'Veg 360', 'category' => 'standard', 'price_per_person' => 360, 'is_active' => true,
        'items' => ['Upma', 'Idly', 'Banana', 'Payasam']]);
}

function bmcPost(MealPlan $plan, array $menu = [], array $o = []): array
{
    return array_merge([
        'booking_type' => 'food_only', 'service_location' => 'Anna Nagar, Block C', 'meal_plan_id' => $plan->id,
        'customer_name' => 'Ravi', 'customer_mobile' => '9876543210', 'event_type' => 'wedding',
        'booking_date' => '2026-12-20', 'start_time' => '08:00', 'end_time' => '11:00', 'number_of_people' => 200,
        'total_amount' => 72000, 'advance_amount' => 0, 'payment_status' => 'pending', 'status' => 'confirmed',
        'menu_present' => 1, 'menu' => $menu,
    ], $o);
}

function bmcMenu(): array
{
    return [
        ['name' => 'Semiya', 'change_type' => 'replaced', 'replaces' => 'Upma', 'charge_type' => 'included'],
        ['name' => 'Idly', 'change_type' => 'plan', 'notes' => 'soft'],
        ['name' => 'Banana', 'change_type' => 'removed'],
        ['name' => 'Payasam', 'change_type' => 'plan'],
        ['name' => 'Ice Cream', 'change_type' => 'added', 'charge_type' => 'per_person', 'charge_amount' => 40],
        ['name' => 'Fruit Salad', 'change_type' => 'added', 'charge_type' => 'included'],
        ['name' => 'Gulab Jamun', 'change_type' => 'added', 'charge_type' => 'fixed', 'charge_amount' => 1500, 'servings' => 50],
    ];
}

it('creates a food-only booking with a customised menu and never touches the package', function () {
    $plan = bmcPlan();
    $hall = Hall::create(['name' => 'Main Hall', 'capacity' => 300, 'is_active' => true]);

    // hall id / hall cost sent by a stale form are dropped server-side for Food Only
    $this->actingAs(icUser('manager'))->post(route('hall.bookings.store'), bmcPost($plan, bmcMenu(), ['hall_id' => $hall->id, 'hall_cost' => 9000]))
        ->assertRedirect()->assertSessionHasNoErrors();

    $b = HallBooking::firstOrFail();
    expect($b->hall_id)->toBeNull()->and((float) $b->hall_cost)->toBe(0.0)
        ->and($b->menuItems)->toHaveCount(7)
        ->and($plan->fresh()->items)->toBe(['Upma', 'Idly', 'Banana', 'Payasam']);

    $rep = $b->menuItems->firstWhere('name', 'Semiya');
    expect($rep->change_type)->toBe('replaced')->and($rep->replaces)->toBe('Upma')->and((float) $rep->charge_amount)->toBe(0.0);
    expect($b->menuItems->firstWhere('name', 'Banana')->is_removed)->toBeTrue();
});

it('does not require a hall or hall cost for food only, but still validates the essentials', function () {
    $plan = bmcPlan();
    $u = icUser('manager');
    $this->actingAs($u)->post(route('hall.bookings.store'), bmcPost($plan, [], ['service_location' => '']))
        ->assertSessionHasErrors('service_location');
    $this->actingAs($u)->post(route('hall.bookings.store'), bmcPost($plan, [['name' => 'Semiya', 'change_type' => 'replaced']]))
        ->assertSessionHasErrors('menu.0.replaces');
    $this->actingAs($u)->post(route('hall.bookings.store'), bmcPost($plan, [['name' => 'X', 'change_type' => 'added', 'charge_type' => 'bogus']]))
        ->assertSessionHasErrors('menu.0.charge_type');
    $this->actingAs($u)->post(route('hall.bookings.store'), bmcPost($plan, [['name' => 'X', 'change_type' => 'added', 'servings' => 0]]))
        ->assertSessionHasErrors('menu.0.servings');
    expect(HallBooking::count())->toBe(0);
});

it('shows the customised menu to the kitchen: replacement in, original and removed out, extras with servings', function () {
    $plan = bmcPlan();
    $this->actingAs(icUser('manager'))->post(route('hall.bookings.store'), bmcPost($plan, bmcMenu()));

    $html = $this->actingAs(icUser('manager'))->get(route('hall.bookings.kitchen', ['date' => '2026-12-20']))->assertOk()
        ->assertSee('Customized menu')->assertSee('CUSTOMIZATION')->assertSee('Base menu: Veg 360')
        ->assertSee('Upma → Semiya')->assertSee('Extra: Ice Cream – 200 servings')->assertSee('Removed: Banana')
        ->assertSee('Extra: Gulab Jamun – 50 servings')->assertSee('instead of Upma')
        ->getContent();
    // the dish list itself: Semiya in, Upma/Banana not prepared
    preg_match_all('/<span class="ef-kit-dish-name">\s*([^<\n]+)/', $html, $m);
    $dishes = array_map('trim', $m[1]);
    expect($dishes)->toContain('Semiya')->toContain('Ice Cream')->not->toContain('Upma')->not->toContain('Banana');
});

it('feeds the final menu into materials prefill and the material request snapshot', function () {
    $plan = bmcPlan();
    $this->actingAs(icUser('manager'))->post(route('hall.bookings.store'), bmcPost($plan, bmcMenu()));
    $b = HallBooking::firstOrFail();

    $prefill = app(HallOrderPrefillService::class)->forBooking($b);
    $preps = collect($prefill['preparations'])->keyBy('name');
    expect($preps->keys()->all())->toContain('Semiya', 'Ice Cream')->not->toContain('Upma', 'Banana')
        ->and($preps['Gulab Jamun']['people'])->toBe(50)
        ->and($preps['Ice Cream']['people'])->toBe(200);

    $snap = app(HallMenuService::class)->snapshot($b);
    expect($snap['source'])->toBe('booking_menu')
        ->and(collect($snap['items'])->firstWhere('name', 'Semiya')['replaces'])->toBe('Upma')
        ->and($snap['customizations'])->toContain('Upma → Semiya', 'Removed: Banana');
});

it('itemises only charged extras on the invoice and leaves the package price alone', function () {
    $plan = bmcPlan();
    $this->actingAs(icUser('manager'))->post(route('hall.bookings.store'), bmcPost($plan, bmcMenu(), ['total_amount' => 72000 + 8000 + 1500]));
    $b = HallBooking::with('mealPlan', 'additionalServices', 'foodSplits', 'menuItems')->firstOrFail();

    $lines = collect(app(InvoiceCalculationService::class)->lineItems($b));
    expect($lines->firstWhere('label', 'Food / Catering — Veg 360')['amount'])->toBe(72000.0)
        ->and($lines->firstWhere('label', 'Menu extra — Ice Cream')['amount'])->toBe(8000.0)
        ->and($lines->firstWhere('label', 'Menu extra — Gulab Jamun')['amount'])->toBe(1500.0)
        ->and($lines->pluck('label'))->not->toContain('Menu extra — Fruit Salad')->not->toContain('Menu extra — Semiya');
});

it('restores the menu on edit and keeps it when unrelated fields change', function () {
    $plan = bmcPlan();
    $u = icUser('manager');
    $this->actingAs($u)->post(route('hall.bookings.store'), bmcPost($plan, bmcMenu()));
    $b = HallBooking::firstOrFail();
    $ids = $b->menuItems->pluck('id')->all();

    $this->actingAs($u)->get(route('hall.bookings.edit', $b))->assertOk()
        ->assertSee('data-menu-builder', false)->assertSee('"replaces":"Upma"', false)->assertSee('"charge_type":"per_person"', false);

    // form posted back without the builder (e.g. script blocked): saved menu untouched
    $this->actingAs($u)->put(route('hall.bookings.update', $b), bmcPost($plan, [], ['menu_present' => 0, 'customer_name' => 'Ravi K', 'number_of_people' => 250]))
        ->assertRedirect()->assertSessionHasNoErrors();
    expect($b->fresh()->menuItems->pluck('id')->all())->toBe($ids);

    // same menu posted back: no rewrite (ids unchanged), explicit servings kept while guests changed
    $this->actingAs($u)->put(route('hall.bookings.update', $b), bmcPost($plan, bmcMenu(), ['number_of_people' => 250]));
    $fresh = $b->fresh();
    expect($fresh->menuItems->pluck('id')->all())->toBe($ids)
        ->and($fresh->menuItems->firstWhere('name', 'Gulab Jamun')->servings)->toBe(50)
        ->and($fresh->customer_name)->toBe('Ravi');
});

it('warns material requests made for the old menu and never edits them', function () {
    $plan = bmcPlan();
    $u = icUser('manager');
    $this->actingAs($u)->post(route('hall.bookings.store'), bmcPost($plan, bmcMenu()));
    $b = HallBooking::firstOrFail();
    $mr = MaterialRequest::create(['requested_by' => $u->id, 'hall_booking_id' => $b->id, 'status' => 'approved',
        'required_date' => '2026-12-20', 'menu_snapshot' => app(HallMenuService::class)->snapshot($b)]);
    $before = $mr->fresh()->menu_snapshot;

    $menu = bmcMenu();
    $menu[] = ['name' => 'Vada', 'change_type' => 'added', 'charge_type' => 'included'];
    $this->actingAs($u)->put(route('hall.bookings.update', $b), bmcPost($plan, $menu))
        ->assertSessionHas('error', fn ($m) => str_contains($m, $mr->reference()));
    expect($mr->fresh()->menu_snapshot)->toBe($before)->and($b->fresh()->menuItems)->toHaveCount(8);
});

it('keeps an old booking menu when the package is edited later', function () {
    $plan = bmcPlan();
    $this->actingAs(icUser('manager'))->post(route('hall.bookings.store'), bmcPost($plan, bmcMenu()));
    $plan->update(['items' => ['Pongal', 'Vada']]);

    $lines = collect(app(HallMenuService::class)->draftLines(HallBooking::firstOrFail()))->pluck('name');
    expect($lines)->toContain('Semiya', 'Idly')->not->toContain('Pongal');
});

it('does not save a menu for hall-only bookings', function () {
    $plan = bmcPlan();
    $hall = Hall::create(['name' => 'Main Hall', 'capacity' => 300, 'is_active' => true]);
    $this->actingAs(icUser('manager'))->post(route('hall.bookings.store'), bmcPost($plan, bmcMenu(), [
        'booking_type' => 'hall_only', 'hall_id' => $hall->id, 'service_location' => null, 'hall_cost' => 5000,
    ]))->assertSessionHasNoErrors();
    expect(HallBooking::firstOrFail()->menuItems)->toHaveCount(0);
});

it('renders the inline menu builder and food-only switches on the create page', function () {
    bmcPlan();
    $this->actingAs(icUser('manager'))->get(route('hall.bookings.create'))->assertOk()
        ->assertSee('data-menu-builder', false)->assertSee('+ Add extra item')
        ->assertSee('"dishes":["Upma","Idly","Banana","Payasam"]', false)
        ->assertSee('id="field-hall-cost"', false);
});

it('puts the meal ticks in the event section and sends package meal tags to the filter', function () {
    MealPlan::create(['name' => 'Tiffin', 'category' => 'standard', 'price_per_person' => 150, 'is_active' => true, 'meal_times' => ['breakfast'], 'items' => ['Idly']]);
    $html = $this->actingAs(icUser('manager'))->get(route('hall.bookings.create'))->assertOk()
        ->assertSee('"meal_times":["breakfast"]', false)->assertSee('id="planFilterNote"', false)->getContent();
    // meals sit before the start time (event section), not in the catering section
    expect(strpos($html, 'id="has_breakfast"'))->toBeLessThan(strpos($html, 'id="start_time"'));
});
