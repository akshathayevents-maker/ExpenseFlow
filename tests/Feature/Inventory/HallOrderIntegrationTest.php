<?php

use App\Models\AppNotification;
use App\Models\HallBooking;
use App\Models\MaterialRequest;
use App\Models\MealPlan;

function hallOrder(): HallBooking
{
    $plan = MealPlan::create(['name' => 'Premium Veg', 'category' => 'premium', 'price_per_person' => 400, 'is_active' => true,
        'description' => "Paneer Butter Masala\nVeg Biryani\nDal Fry\nChapati\nGulab Jamun"]);

    return HallBooking::create([
        'booking_type' => 'food_only', 'created_by' => icUser('manager')->id, 'meal_plan_id' => $plan->id,
        'customer_name' => 'Wedding Reception', 'customer_mobile' => '9876543210', 'event_type' => 'wedding',
        'booking_date' => now()->addDays(5)->toDateString(), 'start_time' => '19:00', 'end_time' => '22:00',
        'number_of_people' => 500, 'has_dinner' => true, 'hall_cost' => 0, 'total_amount' => 200000,
        'payment_status' => 'pending', 'status' => 'confirmed',
    ]);
}

it('builds the final menu from the plan and keeps customisations', function () {
    $mgr = icUser('manager');
    $b = hallOrder();
    $this->actingAs($mgr)->get(route('hall.bookings.menu.edit', $b))->assertOk()->assertSee('Dal Fry')->assertSee('Paneer Butter Masala');
    $this->actingAs(icUser())->get(route('hall.bookings.menu.edit', $b))->assertForbidden(); // employees blocked by role.hall

    $this->actingAs($mgr)->put(route('hall.bookings.menu.update', $b), ['menu' => [
        ['name' => 'Paneer Butter Masala', 'change_type' => 'modified', 'notes' => 'Increase paneer'],
        ['name' => 'Veg Biryani', 'change_type' => 'plan'],
        ['name' => 'Dal Fry', 'change_type' => 'removed'],
        ['name' => 'Chapati', 'change_type' => 'plan'],
        ['name' => 'Gulab Jamun', 'change_type' => 'plan'],
        ['name' => 'Paneer Tikka', 'change_type' => 'added'],
        ['name' => 'Rasmalai', 'change_type' => 'added', 'notes' => 'Extra dessert'],
    ]])->assertRedirect(route('hall.bookings.show', $b));

    $menu = $b->menuItems()->get();
    expect($menu)->toHaveCount(7)->and($menu->firstWhere('name', 'Dal Fry')->is_removed)->toBeTrue()
        ->and($b->fresh()->meal_plan_id)->not->toBeNull(); // original plan reference kept
});

it('links a material request to the hall order with a snapshot of the FINAL menu', function () {
    $mgr = icUser('manager');
    $emp = icUser();
    $b = hallOrder();
    $this->actingAs($mgr)->put(route('hall.bookings.menu.update', $b), ['menu' => [
        ['name' => 'Paneer Tikka', 'change_type' => 'added'],
        ['name' => 'Dal Fry', 'change_type' => 'removed'],
    ]]);
    $paneer = icItem('Paneer', 'kg', 100);

    $this->actingAs($emp)->get(route('inventory.material-requests.create', ['hall_booking_id' => $b->id]))->assertOk()->assertSee('value="500"', false);
    $this->actingAs($emp)->post(route('inventory.material-requests.store'), [
        'source' => 'hall', 'hall_booking_id' => $b->id, 'department' => 'hall', 'required_date' => $b->booking_date->toDateString(),
        'guest_count' => 500, 'preparation_name' => 'Reception dinner', 'action' => 'submit',
        'lines' => [['inventory_item_id' => $paneer->id, 'quantity' => 40, 'unit' => 'kg']],
    ])->assertSessionHasNoErrors();

    $mr = MaterialRequest::first();
    expect($mr->hall_booking_id)->toBe($b->id)->and($mr->menu_snapshot['source'])->toBe('booking_menu')
        ->and(collect($mr->menu_snapshot['items'])->pluck('name')->all())->toBe(['Paneer Tikka', 'Dal Fry'])
        ->and(collect($mr->menu_snapshot['items'])->firstWhere('name', 'Dal Fry')['removed'])->toBeTrue()
        ->and($mr->menu_snapshot['plans'][0]['plan'])->toBe('Premium Veg');
});

it('does not overwrite an approved request when the menu changes; a revision is created instead', function () {
    $mgr = icUser('manager');
    $emp = icUser();
    $admin = icUser('admin');
    $b = hallOrder();
    $paneer = icItem('Paneer', 'kg', 100);
    $this->actingAs($emp)->post(route('inventory.material-requests.store'), [
        'source' => 'hall', 'hall_booking_id' => $b->id, 'department' => 'hall', 'required_date' => $b->booking_date->toDateString(),
        'guest_count' => 500, 'action' => 'submit', 'lines' => [['inventory_item_id' => $paneer->id, 'quantity' => 40, 'unit' => 'kg']],
    ]);
    $mr = MaterialRequest::first();
    $this->actingAs($admin)->post(route('inventory.material-requests.approve', $mr));
    $snapshot = $mr->fresh()->menu_snapshot;

    $this->actingAs($mgr)->put(route('hall.bookings.menu.update', $b), ['menu' => [['name' => 'Paneer Tikka', 'change_type' => 'added']]])
        ->assertSessionHas('error');
    expect($mr->fresh()->menu_snapshot)->toBe($snapshot)->and($mr->fresh()->status)->toBe('approved')
        ->and($mr->fresh()->items->first()->approved_base_quantity)->toBe(40.0);
    expect(AppNotification::where('user_id', $emp->id)->where('type', 'hall_menu_changed')->exists())->toBeTrue();

    $this->actingAs($emp)->post(route('inventory.material-requests.revise', $mr))->assertRedirect();
    $rev = MaterialRequest::where('revision_of_id', $mr->id)->first();
    expect($rev->status)->toBe('draft')->and($rev->revision_no)->toBe(2)->and($rev->reference())->toEndWith('-R2')
        ->and(collect($rev->menu_snapshot['items'])->pluck('name')->all())->toBe(['Paneer Tikka'])
        ->and($mr->fresh()->status)->toBe('approved');
});

it('shows material cost per event and per guest on the booking', function () {
    $mgr = icUser('manager');
    $emp = icUser();
    $admin = icUser('admin');
    $store = icUser('employee', true);
    $b = hallOrder();
    $paneer = icItem('Paneer', 'kg', 100);
    $paneer->update(['average_cost' => 300]);
    $this->actingAs($emp)->post(route('inventory.material-requests.store'), [
        'source' => 'hall', 'hall_booking_id' => $b->id, 'department' => 'hall', 'required_date' => $b->booking_date->toDateString(),
        'guest_count' => 500, 'action' => 'submit', 'lines' => [['inventory_item_id' => $paneer->id, 'quantity' => 40, 'unit' => 'kg']],
    ]);
    $mr = MaterialRequest::first();
    $this->actingAs($admin)->post(route('inventory.material-requests.approve', $mr));
    $this->actingAs($store)->post(route('inventory.material-requests.handover', $mr), ['collected_by' => $emp->id]);
    $this->actingAs($store)->post(route('inventory.material-requests.returns', $mr), ['returns' => [$mr->items->first()->id => 5]]);

    $this->actingAs($mgr)->get(route('hall.bookings.show', $b))->assertOk()
        ->assertSee('Menu &amp; materials', false)->assertSee('10,500.00')->assertSee('21.00')->assertSee($mr->reference());
});

it('refuses material requests for cancelled hall orders', function () {
    $b = hallOrder();
    $b->update(['status' => 'cancelled']);
    $paneer = icItem('Paneer', 'kg', 100);
    $this->actingAs(icUser())->post(route('inventory.material-requests.store'), [
        'source' => 'hall', 'hall_booking_id' => $b->id, 'department' => 'hall', 'required_date' => now()->toDateString(),
        'lines' => [['inventory_item_id' => $paneer->id, 'quantity' => 1, 'unit' => 'kg']],
    ])->assertSessionHasErrors('hall_booking_id');
});
