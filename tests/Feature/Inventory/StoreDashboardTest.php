<?php

use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;

it('lists critical items out-of-stock first and only items with a reorder level', function () {
    icItem('Toor Dal', 'kg', 2, 10);      // low (20%)
    icItem('Rice', 'kg', 0, 25);          // out
    icItem('Saffron', 'g', 0, 0);         // never stocked, no reorder level → not critical
    icItem('Salt', 'kg', 50, 5);          // healthy

    $html = $this->actingAs(icUser('admin'))->get(route('inventory.home'))->assertOk()->getContent();

    expect(substr_count($html, 'data-critical'))->toBe(2)
        ->and(strpos($html, '>Rice<'))->toBeLessThan(strpos($html, '>Toor Dal<'))
        ->and($html)->toContain('Items at or below minimum')
        ->and($html)->toContain(route('admin.purchase-plans.create'));
});

it('counts stock health from one consistent definition', function () {
    icItem('A', 'kg', 5, 1);   // in
    icItem('B', 'kg', 1, 3);   // in + low
    icItem('C', 'kg', 0, 2);   // out
    $html = $this->actingAs(icUser('admin'))->get(route('inventory.home'))->assertOk()->getContent();
    $kpi = fn ($k) => preg_match('/data-kpi="'.$k.'".*?<strong>([^<]+)<\/strong>/s', $html, $m) ? $m[1] : null;
    expect($html)->toContain('of 3 active items')
        ->and($kpi('in'))->toBe('2')->and($kpi('low'))->toBe('1')->and($kpi('out'))->toBe('1');
});

it('hides store panels, costs and admin actions from kitchen staff', function () {
    icItem('Rice', 'kg', 0, 25);
    $this->actingAs(icUser())->get(route('inventory.home'))->assertOk()
        ->assertDontSee('data-critical', false)->assertDontSee('Stock health')->assertDontSee('Recent stock movement')
        ->assertDontSee('data-kpi="value"', false)->assertDontSee('Scan bill')->assertSee('Request materials');
});

it('shows storekeepers the handover queue but no stock value', function () {
    icItem('Rice', 'kg', 100, 10)->update(['average_cost' => 50]);
    $this->actingAs(icUser('employee', true))->get(route('inventory.home'))->assertOk()
        ->assertSee('To hand over')->assertSee('Stock health')->assertDontSee('data-kpi="value"', false)->assertDontSee('To approve');
    $this->actingAs(icUser('admin'))->get(route('inventory.home'))->assertOk()->assertSee('₹5,000');
});

it('keeps the dashboard query count small and independent of data size', function () {
    foreach (range(1, 15) as $i) {
        icItem("Item $i", 'kg', $i % 3 ? 0 : 5, 4);
    }
    $admin = icUser('admin', true);
    DB::enableQueryLog();
    $this->actingAs($admin)->get(route('inventory.home'))->assertOk();
    $n = count(DB::getQueryLog());
    expect($n)->toBeLessThanOrEqual(22);
});

it('shows stock movement with the real direction of the balance change', function () {
    $rice = icItem('Rice', 'kg', 7);
    InventoryTransaction::create(['inventory_item_id' => $rice->id, 'type' => 'material_issue', 'quantity' => 3,
        'balance_before' => 10, 'balance_after' => 7, 'created_by' => icUser('admin')->id]);
    $this->actingAs(icUser('admin'))->get(route('inventory.home'))->assertOk()->assertSee('−3 kg')->assertDontSee('+3 kg');
});
