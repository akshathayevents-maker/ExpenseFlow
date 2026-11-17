<?php

use App\Models\InventoryItemAlias;
use App\Models\Vendor;
use App\Services\Inventory\ItemMatchingService;

it('matches exact, alias, supplier alias and fuzzy names with confidence', function () {
    $rice = icItem('Rice');
    $onion = icItem('Onion');
    $oil = icItem('Cooking Oil', 'litre');
    icItem('Onion Powder');
    $vendor = Vendor::create(['name' => 'Ravi', 'is_active' => true]);
    $m = app(ItemMatchingService::class);

    expect($m->best('rice'))->toMatchArray(['item_id' => $rice->id, 'confidence' => 100, 'method' => 'exact']);

    InventoryItemAlias::create(['inventory_item_id' => $onion->id, 'alias' => 'Vengayam', 'normalized_alias' => 'vengayam', 'source' => 'confirmed']);
    expect(app(ItemMatchingService::class)->best('VENGAYAM'))->toMatchArray(['item_id' => $onion->id, 'method' => 'alias']);

    InventoryItemAlias::create(['inventory_item_id' => $oil->id, 'alias' => 'Sunflower Refined', 'normalized_alias' => 'sunflower refined', 'vendor_id' => $vendor->id, 'source' => 'confirmed']);
    $hit = app(ItemMatchingService::class)->best('Sunflower Refined 1L', $vendor->id);
    expect($hit['item_id'])->toBe($oil->id)->and($hit['method'])->toBe('supplier_alias')->and($hit['confidence'])->toBe(98);

    $fuzzy = $m->best('Big Onion');
    expect($fuzzy['item_id'])->toBe($onion->id)->and($fuzzy['confidence'])->toBeLessThan(95);

    // low confidence suggestions are never auto-accepted
    $weak = $m->best('Sona Masoori');
    expect($m->isAutoAcceptable($weak))->toBeFalse();
});

it('saves a corrected mapping as a reusable alias and reuses it next time', function () {
    $admin = icUser('admin');
    $rice = icItem('Rice');
    $m = app(ItemMatchingService::class);
    expect($m->isAutoAcceptable($m->best('Sona Masuri')))->toBeFalse();

    $this->actingAs($admin)->post(route('inventory.purchases.store'), [
        'purchase_date' => now()->toDateString(), 'supplier_name' => 'Ravi',
        'lines' => [['inventory_item_id' => $rice->id, 'description' => 'Sona Masuri', 'quantity' => 1, 'rate' => 60, 'remember_alias' => 1]],
    ])->assertSessionHasNoErrors();

    $alias = InventoryItemAlias::first();
    expect($alias->normalized_alias)->toBe('sona masuri')->and($alias->source)->toBe('confirmed');
    $again = app(ItemMatchingService::class)->best('Sona Masuri');
    expect($again['item_id'])->toBe($rice->id)->and(app(ItemMatchingService::class)->isAutoAcceptable($again))->toBeTrue();
});

it('serves pasted-row matching as JSON to signed-in users only', function () {
    icItem('Tomato');
    $this->postJson(route('inventory.items.match'), ['texts' => ['tomato']])->assertUnauthorized();
    $this->actingAs(icUser())->postJson(route('inventory.items.match'), ['texts' => ['Tomato', 'zzz']])
        ->assertOk()->assertJsonPath('results.0.suggestions.0.name', 'Tomato')->assertJsonPath('results.1.auto', null);
});
