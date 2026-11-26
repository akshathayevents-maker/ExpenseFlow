<?php

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function plPurchase(User $by, string $status, array $extra = [], array $lines = [[10, 0]]): Purchase
{
    $p = Purchase::create($extra + ['purchase_date' => '2026-11-20', 'supplier_name' => 'DP Store', 'grand_total' => 1000,
        'status' => $status, 'created_by' => $by->id]);
    foreach ($lines as $i => [$qty, $got]) {
        $p->items()->create(['inventory_item_id' => icItem("Item {$p->id}-$i")->id, 'quantity' => $qty, 'unit' => 'kg',
            'base_quantity' => $qty, 'rate' => 10, 'amount' => $qty * 10, 'received_base_quantity' => $got, 'sort_order' => $i]);
    }

    return $p;
}

it('groups purchases into lifecycle stages with counts', function () {
    $admin = icUser('admin');
    plPurchase($admin, 'submitted');
    plPurchase($admin, 'approved');
    plPurchase($admin, 'partially_received');
    plPurchase($admin, 'draft');

    $html = $this->actingAs($admin)->get(route('inventory.purchases.index', ['stage' => 'awaiting']))->assertOk()->getContent();
    expect(substr_count($html, 'data-purchase'))->toBe(2)
        ->and($html)->toContain('<b>2</b> purchases');
    $this->actingAs($admin)->get(route('inventory.purchases.index', ['stage' => 'bogus']))->assertSessionHasErrors('stage');
    // legacy ?status= links (dashboard) still work
    $html = $this->actingAs($admin)->get(route('inventory.purchases.index', ['status' => 'submitted']))->getContent();
    expect(substr_count($html, 'data-purchase'))->toBe(1);
});

it('searches by supplier, bill number and PO reference', function () {
    $admin = icUser('admin');
    $a = plPurchase($admin, 'approved', ['supplier_name' => 'Murugan Traders', 'invoice_number' => 'INV-77']);
    plPurchase($admin, 'approved', ['supplier_name' => 'DP Store']);

    foreach (['murugan', 'inv-77', $a->reference(), (string) $a->id] as $q) {
        $html = $this->actingAs($admin)->get(route('inventory.purchases.index', ['q' => $q]))->getContent();
        expect(substr_count($html, 'data-purchase'))->toBe(1, "query $q")->and($html)->toContain('Murugan Traders');
    }
});

it('filters by date range and bill attachment', function () {
    $admin = icUser('admin');
    plPurchase($admin, 'received', ['purchase_date' => '2026-10-01']);
    plPurchase($admin, 'received', ['purchase_date' => '2026-11-25', 'invoice_number' => 'B1']);

    $html = $this->actingAs($admin)->get(route('inventory.purchases.index', ['from' => '2026-11-01']))->getContent();
    expect(substr_count($html, 'data-purchase'))->toBe(1);
    $html = $this->actingAs($admin)->get(route('inventory.purchases.index', ['bill' => 'without']))->getContent();
    expect(substr_count($html, 'data-purchase'))->toBe(2);
});

it('shows receipt progress and the right next action per role', function () {
    $admin = icUser('admin');
    $store = icUser('employee', true);
    $p = plPurchase($admin, 'partially_received', [], [[10, 10], [5, 2]]);
    plPurchase($admin, 'submitted');

    $this->actingAs($store)->get(route('inventory.purchases.index'))->assertOk()
        ->assertSee('1/2 recv.')->assertSee(route('inventory.purchases.receive-form', $p), false);
    // admins are storekeepers too (User::isStorekeeper) and also approve; a manager gets neither action
    $this->actingAs($admin)->get(route('inventory.purchases.index'))->assertOk()
        ->assertSee('Review')->assertSee(route('inventory.purchases.receive-form', $p), false);
    $this->actingAs($store)->get(route('inventory.purchases.index'))->assertDontSee('bi-check2-circle', false);
});

it('only shows staff their own purchases and keeps query count flat', function () {
    $admin = icUser('admin');
    $emp = icUser();
    plPurchase($admin, 'approved', ['supplier_name' => 'Hidden Supplier']);
    plPurchase($emp, 'draft', ['supplier_name' => 'My Shop']);
    foreach (range(1, 10) as $i) {
        plPurchase($admin, 'approved');
    }

    $this->actingAs($emp)->get(route('inventory.purchases.index'))->assertOk()->assertSee('My Shop')->assertDontSee('Hidden Supplier');

    DB::enableQueryLog();
    $this->actingAs($admin)->get(route('inventory.purchases.index'))->assertOk();
    expect(count(DB::getQueryLog()))->toBeLessThanOrEqual(15);
});

it('never changes stock when the list is viewed', function () {
    $admin = icUser('admin');
    $p = plPurchase($admin, 'approved');
    $before = InventoryItem::sum('current_stock');
    $this->actingAs($admin)->get(route('inventory.purchases.index'))->assertOk();
    expect(InventoryItem::sum('current_stock'))->toEqual($before)
        ->and(InventoryTransaction::count())->toBe(0);
});
