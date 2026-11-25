<?php

use App\Models\MaterialRequest;

function prepPayload(array $preps, array $extra = []): array
{
    return array_merge([
        'source' => 'other', 'department' => 'kitchen', 'required_date' => now('Asia/Kolkata')->addDay()->toDateString(),
        'preparations' => $preps,
        'lines' => [['inventory_item_id' => icItem('Rava', 'kg', 50)->id, 'quantity' => 2, 'unit' => 'kg']],
    ], $extra);
}

it('stores several preparations, each with its own number of people', function () {
    $this->actingAs(icUser());

    $this->post(route('inventory.material-requests.store'), prepPayload([
        ['name' => 'Upma', 'people' => 80],
        ['name' => ' Coffee ', 'people' => 120],
        ['name' => '', 'people' => ''],                 // blank row is ignored
        ['name' => 'Kesari', 'people' => 40],
    ]))->assertSessionHasNoErrors();

    $mr = MaterialRequest::first();
    expect($mr->preparations)->toBe([
        ['name' => 'Upma', 'people' => 80], ['name' => 'Coffee', 'people' => 120], ['name' => 'Kesari', 'people' => 40],
    ])
        ->and($mr->preparation_name)->toBe('Upma, Coffee, Kesari')   // summary for labels / lists
        ->and($mr->guest_count)->toBe(120);                          // largest head count

    $this->get(route('inventory.material-requests.show', $mr))->assertOk()
        ->assertSee('Coffee')->assertSee('120')->assertSee('Kesari');
    $this->get(route('inventory.material-requests.edit', $mr))->assertOk()
        ->assertSee('name="preparations[2][name]"', false)->assertSee('value="Kesari"', false);
});

it('rejects a preparation with an invalid number of people', function () {
    $this->actingAs(icUser());

    $this->post(route('inventory.material-requests.store'), prepPayload([['name' => 'Upma', 'people' => 0]]))
        ->assertSessionHasErrors('preparations.0.people');
    expect(MaterialRequest::count())->toBe(0);
});

it('keeps old single preparation requests working', function () {
    $this->actingAs(icUser());
    $payload = prepPayload([]);
    unset($payload['preparations']);
    $payload += ['preparation_name' => 'Pongal', 'guest_count' => 60];

    $this->post(route('inventory.material-requests.store'), $payload)->assertSessionHasNoErrors();

    $mr = MaterialRequest::first();
    expect($mr->preparations)->toBeNull()
        ->and($mr->preparationList())->toBe([['name' => 'Pongal', 'people' => 60]]);
    $this->get(route('inventory.material-requests.create'))->assertOk()->assertSee('Add preparation');
});
