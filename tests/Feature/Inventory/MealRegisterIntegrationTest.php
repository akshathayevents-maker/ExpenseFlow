<?php

use App\Models\MaterialRequest;
use App\Models\MealClient;
use App\Models\MealEntry;
use App\Services\Inventory\ConsumptionService;

function mealEntry(int $planned = 250): MealEntry
{
    $by = icUser('manager')->id;
    $client = MealClient::create(['name' => 'Hostel', 'active' => true, 'created_by' => $by]);
    $entry = MealEntry::create(['meal_client_id' => $client->id, 'entry_date' => now('Asia/Kolkata')->addDay()->toDateString(), 'created_by' => $by]);
    $entry->items()->create(['meal_type' => 'breakfast', 'planned_count' => $planned, 'sort_order' => 1]);

    return $entry;
}

it('starts a material request from a meal entry carrying meal reference, date, type and guests', function () {
    $emp = icUser();
    $entry = mealEntry(250);

    $this->actingAs($emp)->get(route('meal-register.entries.show', $entry))->assertOk()->assertSee('Request materials');
    $this->actingAs($emp)->get(route('inventory.material-requests.create', ['meal_entry_id' => $entry->id, 'meal_type' => 'breakfast']))
        ->assertOk()->assertSee('value="250"', false);

    $rava = icItem('Rava', 'kg', 100);
    $this->actingAs($emp)->post(route('inventory.material-requests.store'), [
        'source' => 'meal', 'meal_entry_id' => $entry->id, 'meal_type' => 'breakfast', 'department' => 'kitchen',
        'required_date' => $entry->entry_date->toDateString(), 'preparation_name' => 'Upma', 'guest_count' => 250,
        'action' => 'submit', 'lines' => [['inventory_item_id' => $rava->id, 'quantity' => 50, 'unit' => 'kg']],
    ])->assertSessionHasNoErrors();

    $mr = MaterialRequest::first();
    expect($mr->meal_entry_id)->toBe($entry->id)->and($mr->meal_type)->toBe('breakfast')->and($mr->source)->toBe('meal');
    expect($entry->materialRequests()->count())->toBe(1);
});

it('requires a meal when the request is for a meal', function () {
    $rava = icItem('Rava', 'kg', 100);
    $this->actingAs(icUser())->post(route('inventory.material-requests.store'), [
        'source' => 'meal', 'department' => 'kitchen', 'required_date' => now()->toDateString(),
        'lines' => [['inventory_item_id' => $rava->id, 'quantity' => 5, 'unit' => 'kg']],
    ])->assertSessionHasErrors(['meal_entry_id', 'meal_type']);
});

it('shows approved, issued, returned, used, wasted and unexplained on the meal entry', function () {
    $emp = icUser();
    $admin = icUser('admin');
    $store = icUser('employee', true);
    $entry = mealEntry(250);
    $rava = icItem('Rava', 'kg', 100);
    $rava->update(['average_cost' => 40]);
    $this->actingAs($emp)->post(route('inventory.material-requests.store'), [
        'source' => 'meal', 'meal_entry_id' => $entry->id, 'meal_type' => 'breakfast', 'department' => 'kitchen',
        'required_date' => $entry->entry_date->toDateString(), 'preparation_name' => 'Upma', 'guest_count' => 250,
        'action' => 'submit', 'lines' => [['inventory_item_id' => $rava->id, 'quantity' => 55, 'unit' => 'kg']],
    ]);
    $mr = MaterialRequest::first();
    $line = $mr->items->first();
    $this->actingAs($admin)->post(route('inventory.material-requests.approve', $mr));
    $this->actingAs($store)->post(route('inventory.material-requests.handover', $mr), ['collected_by' => $emp->id]);
    $this->actingAs($store)->post(route('inventory.material-requests.returns', $mr), ['returns' => [$line->id => 2]]);
    $this->actingAs($emp)->post(route('inventory.material-requests.reconcile', $mr), ['lines' => [$line->id => [
        'consumed' => 49, 'wasted' => 3, 'wastage_reason' => 'Spilled', 'variance_reason' => 'Unknown 1 kg',
    ]]])->assertSessionHasNoErrors();

    $summary = app(ConsumptionService::class)->summarize($entry->fresh()->materialRequests);
    $row = $summary['rows'][$rava->id];
    expect($row)->toMatchArray(['approved' => 55.0, 'issued' => 55.0, 'returned' => 2.0, 'consumed' => 49.0, 'wasted' => 3.0, 'unexplained' => 1.0])
        ->and($row['issued'])->toBe($row['returned'] + $row['consumed'] + $row['wasted'] + $row['unexplained'])
        ->and($summary['cost'])->toBe(2120.0);

    $this->actingAs($admin)->get(route('meal-register.entries.show', $entry))
        ->assertOk()->assertSee($mr->reference())->assertSee('Material cost')->assertSee('per person');
});
