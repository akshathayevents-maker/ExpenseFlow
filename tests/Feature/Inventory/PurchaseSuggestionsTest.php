<?php

use App\Models\InventoryTransaction;
use App\Models\Purchase;
use App\Services\PurchasePlanningService;

it('suggests low items and recently used empty items, minus what is already coming', function () {
    $admin = icUser('admin');
    $rice = icItem('Rice', 'kg', 2, 10);               // below minimum → top up to 2 × min = 20 → 18
    $rice->update(['average_cost' => 50]);
    $dal = icItem('Toor Dal', 'kg', 0, 0);             // no minimum, used recently → one month's usage
    $salt = icItem('Rock Salt', 'kg', 0, 0);           // no minimum, never used → listed last, quantity left to the user
    $oil = icItem('Oil', 'litre', 1, 5);               // below min, but fully on order → covered
    InventoryTransaction::create(['inventory_item_id' => $dal->id, 'type' => 'material_issue', 'quantity' => 12, 'balance_before' => 12, 'balance_after' => 0, 'created_by' => $admin->id]);
    $p = Purchase::create(['purchase_date' => now()->toDateString(), 'supplier_name' => 'X', 'status' => 'approved', 'created_by' => $admin->id,
        'subtotal' => 0, 'tax_amount' => 0, 'discount_amount' => 0, 'grand_total' => 0, 'payment_status' => 'unpaid']);
    $p->items()->create(['inventory_item_id' => $oil->id, 'quantity' => 9, 'unit' => 'litre', 'base_quantity' => 9, 'rate' => 0, 'tax_percent' => 0, 'amount' => 0, 'received_base_quantity' => 0]);

    $s = app(PurchasePlanningService::class)->getSuggestions()->keyBy('name');
    expect($s->keys()->all())->toContain('Rice', 'Toor Dal', 'Oil', 'Rock Salt')
        ->and($s['Rock Salt']->suggested_quantity)->toBeNull()->and($s['Rock Salt']->priority)->toBe('empty')
        ->and($s->keys()->last())->toBe('Oil')                // covered (on order) goes last
        ->and($s->keys()->search('Rock Salt'))->toBeGreaterThan($s->keys()->search('Rice'))
        ->and($s['Rice']->suggested_quantity)->toBe(18.0)
        ->and($s['Toor Dal']->suggested_quantity)->toBe(12.0)->and($s['Toor Dal']->priority)->toBe('urgent')
        ->and($s['Oil']->covered)->toBeTrue()->and($s['Oil']->on_order)->toBe(9.0);
    expect(app(PurchasePlanningService::class)->unconfiguredEmptyCount())->toBe(1);

    $this->actingAs($admin)->get(route('admin.purchase-plans.suggestions'))->assertOk()
        ->assertSee('Purchase suggestions')->assertSee('Toor Dal')->assertSee('used 12 kg in 30 days')
        ->assertSee('Already on order')->assertSee('Create plan with selected')->assertSee('₹900')
        ->assertSee('1</b> empty item', false)->assertSee('Rock Salt')->assertSee('you decide')->assertSee('Out · no minimum');

    // picked items arrive ticked on the plan form
    $html = $this->get(route('inventory.plans.create', ['pick' => [$dal->id]]))->assertOk()->getContent();
    expect($html)->toMatch('/value="'.$dal->id.'">\s*<input type="checkbox" name="items\[\d+\]\[selected\]" value="1" class="item-checkbox" checked/');
});
