<?php

use App\Models\InventoryCategory;
use App\Models\InventoryItem;

function qaCat(): InventoryCategory
{
    return InventoryCategory::first() ?? InventoryCategory::create(['name' => 'Beverages', 'is_active' => true]);
}

it('lets staff add a missing item from a form; it is usable at once and flagged for admin review', function () {
    $emp = icUser();
    $res = $this->actingAs($emp)->postJson(route('inventory.items.quick'), [
        'name' => '  Boost ', 'name_ta' => 'பூஸ்ட்', 'inventory_category_id' => qaCat()->id, 'unit' => 'packet',
    ])->assertCreated()->assertJsonPath('status', 'created')->assertJsonPath('item.name', 'Boost')->assertJsonPath('item.unit', 'packet');

    $item = InventoryItem::find($res->json('item.id'));
    expect($item->status)->toBe('active')->and($item->needs_review)->toBeTrue()->and($item->created_by)->toBe($emp->id);

    // shows up in search straight away
    $this->getJson(route('inventory.items.search', ['q' => 'boost']))->assertOk()->assertJsonPath('results.0.id', $item->id);

    // admin sees it to review, then confirms it
    $admin = icUser('admin');
    $this->actingAs($admin)->get(route('admin.inventory.items.index'))->assertOk()->assertSee('added by staff from forms');
    $this->get(route('admin.inventory.items.show', $item))->assertOk()->assertSee('Looks good');
    $this->patch(route('admin.inventory.items.reviewed', $item))->assertRedirect();
    expect($item->fresh()->needs_review)->toBeFalse();
});

it('returns the existing item instead of creating a duplicate, and warns about similar names', function () {
    $rice = icItem('Tea Powder', 'kg');
    $this->actingAs(icUser());

    $this->postJson(route('inventory.items.quick'), ['name' => 'tea powder', 'inventory_category_id' => qaCat()->id, 'unit' => 'kg'])
        ->assertOk()->assertJsonPath('status', 'exists')->assertJsonPath('item.id', $rice->id);

    $this->postJson(route('inventory.items.quick'), ['name' => 'Tea Powdr', 'inventory_category_id' => qaCat()->id, 'unit' => 'kg'])
        ->assertStatus(409)->assertJsonPath('similar.0.id', $rice->id);
    $this->postJson(route('inventory.items.quick'), ['name' => 'Tea Powdr', 'inventory_category_id' => qaCat()->id, 'unit' => 'kg', 'confirm' => true])
        ->assertCreated();
    expect(InventoryItem::count())->toBe(2);
});

it('admin-created items need no review; input is validated', function () {
    $this->actingAs(icUser('admin'))->postJson(route('inventory.items.quick'), ['name' => 'Horlicks', 'inventory_category_id' => qaCat()->id, 'unit' => 'packet'])
        ->assertCreated()->assertJsonPath('item.needs_review', false);

    $this->postJson(route('inventory.items.quick'), ['name' => 'X', 'inventory_category_id' => 999, 'unit' => 'sack'])
        ->assertUnprocessable()->assertJsonValidationErrors(['name', 'inventory_category_id', 'unit']);
    $this->post(route('inventory.items.quick'))->assertRedirect();   // non-JSON falls back to normal validation redirect
});

it('forms offer adding a new item from the picker', function () {
    $this->actingAs(icUser())->get(route('inventory.material-requests.create'))->assertOk()
        ->assertSee('allowCreate: true', false)->assertSee('inventory\/items\/quick', false);
});
