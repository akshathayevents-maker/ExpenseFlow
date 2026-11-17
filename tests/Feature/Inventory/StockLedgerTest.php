<?php

use App\Models\InventoryTransaction;
use App\Models\InventoryUnitConversion;
use App\Services\Inventory\UnitConversionService;
use App\Services\InventoryService;

it('posts signed movements with balances and refuses negative stock', function () {
    $user = icUser('admin');
    $rice = icItem('Rice', 'kg', 10);
    $svc = app(InventoryService::class);

    $in = $svc->post($rice, 5, 'purchase_receipt', $user, ['unit_cost' => 60]);
    expect($in->balance_before)->toBe(10.0)->and($in->balance_after)->toBe(15.0)->and($in->isAddition())->toBeTrue();

    $out = $svc->post($rice, -4.5, 'material_issue', $user);
    expect($out->signedQuantity())->toBe(-4.5)->and($rice->fresh()->current_stock)->toBe(10.5);

    expect(fn () => $svc->post($rice, -11, 'material_issue', $user))->toThrow(RuntimeException::class, 'Insufficient stock');
    expect($rice->fresh()->current_stock)->toBe(10.5);
});

it('reverses a movement through a linked, opposite transaction without editing the original', function () {
    $user = icUser('admin');
    $oil = icItem('Oil', 'litre', 8);
    $svc = app(InventoryService::class);
    $issue = $svc->post($oil, -3, 'material_issue', $user);

    $rev = $svc->reverse($issue, $user, 2);

    expect($rev->type)->toBe('reversal')->and($rev->reversal_of_id)->toBe($issue->id)
        ->and($rev->signedQuantity())->toBe(2.0)
        ->and($oil->fresh()->current_stock)->toBe(7.0)
        ->and($issue->fresh()->quantity)->toBe(3.0);
    expect(fn () => $svc->reverse($issue, $user, 5))->toThrow(RuntimeException::class);
});

it('records a physical count as a signed adjustment (downward counts are deductions)', function () {
    $user = icUser('admin');
    $item = icItem('Onion', 'kg', 20);
    $txn = app(InventoryService::class)->adjustStock($item, 17, 'Weekly count', $user);

    expect($txn->type)->toBe('adjustment')->and($txn->isDeduction())->toBeTrue()->and($item->fresh()->current_stock)->toBe(17.0);
});

it('converts units within a dimension and refuses pieces to kilograms without a rule', function () {
    $conv = app(UnitConversionService::class);
    $rice = icItem('Rice', 'kg');
    $eggs = icItem('Egg', 'piece');

    expect($conv->toBase($rice, 500, 'gm'))->toBe(0.5)
        ->and($conv->toBase(icItem('Milk', 'litre'), 250, 'ml'))->toBe(0.25)
        ->and($conv->toBase($eggs, 2, 'dozen'))->toBe(24.0);
    expect(fn () => $conv->toBase($eggs, 1, 'kg'))->toThrow(InvalidArgumentException::class);
    expect(fn () => $conv->toBase($rice, 1, 'bag'))->toThrow(InvalidArgumentException::class);

    InventoryUnitConversion::create(['inventory_item_id' => $rice->id, 'unit' => 'bag', 'factor' => 25]);
    expect($conv->toBase($rice, 2, 'bag'))->toBe(50.0);
});

it('only allows count, correction and store wastage from the manual stock form', function () {
    $admin = icUser('admin');
    $item = icItem('Rava', 'kg', 10);

    $this->actingAs($admin)->post(route('admin.inventory.items.transact', $item), ['type' => 'purchase', 'quantity' => 5, 'notes' => 'x'])
        ->assertSessionHasErrors('type');
    $this->actingAs($admin)->post(route('admin.inventory.items.transact', $item), ['type' => 'wastage', 'quantity' => 2, 'notes' => 'Rats in sack'])
        ->assertSessionHasNoErrors();
    $this->actingAs($admin)->post(route('admin.inventory.items.transact', $item), ['type' => 'correction', 'direction' => 'in', 'quantity' => 1, 'notes' => 'Miscounted'])
        ->assertSessionHasNoErrors();

    expect($item->fresh()->current_stock)->toBe(9.0)
        ->and(InventoryTransaction::where('inventory_item_id', $item->id)->pluck('type')->all())->toBe(['wastage', 'correction']);
});
