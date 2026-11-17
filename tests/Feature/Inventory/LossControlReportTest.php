<?php

use App\Services\Inventory\GoodsReceiptService;
use App\Services\Inventory\LossControlReportService;
use App\Services\Inventory\MaterialRequestService;
use App\Services\Inventory\PurchaseService;
use App\Services\InventoryService;

function lcScenario(): array
{
    $admin = icUser('admin');
    $store = icUser('employee', true);
    $a = icUser();
    $b = icUser();
    test()->actingAs($admin); // AuditLogService records the signed-in user
    $rice = icItem('Rice', 'kg', 0);
    $svc = app(PurchaseService::class);
    $gr = app(GoodsReceiptService::class);
    foreach ([60, 61, 59, 90] as $rate) {   // 90 is an outlier
        $p = $svc->save(['purchase_date' => now()->toDateString(), 'supplier_name' => 'Shop '.$rate,
            'lines' => [['inventory_item_id' => $rice->id, 'quantity' => 50, 'unit' => 'kg', 'rate' => $rate]]], $a, null, true);
        $svc->approve($p, $admin);
        $gr->receive($p, [$p->items->first()->id => ['received' => 50]], $store);
    }
    $mr = app(MaterialRequestService::class);
    $mk = function ($user, $qty, $guests, $source = 'other') use ($mr, $rice, $admin, $store) {
        $r = $mr->save(['source' => $source, 'department' => 'kitchen', 'purpose' => 'x', 'required_date' => now()->subDay()->toDateString(),
            'guest_count' => $guests, 'lines' => [['inventory_item_id' => $rice->id, 'quantity' => $qty, 'unit' => 'kg']]], $user, null, true);
        $mr->approve($r, $admin);
        $mr->handover($r, $store, $user->id, null);

        return $r;
    };
    $r1 = $mk($a, 10, 100);
    $r2 = $mk($a, 10, 100);
    $r3 = $mk($b, 30, 100);
    $mr->reconcile($r1, $a, [$r1->items->first()->id => ['consumed' => 9, 'wasted' => 1, 'wastage_reason' => 'spill']]);
    $inv = app(InventoryService::class);
    foreach ([1, 2, 3] as $i) {
        $inv->post($rice, -0.5, 'correction', $admin, ['notes' => "recount {$i}"]);
    }

    return compact('admin', 'rice', 'a', 'b', 'r1', 'r3');
}

it('renders every report and the dashboard for management only', function () {
    $s = lcScenario();
    $this->actingAs($s['a'])->get(route('inventory.reports.index'))->assertForbidden();
    $this->actingAs($s['admin'])->get(route('inventory.reports.index'))->assertOk()->assertSee('Worth checking');
    foreach (array_keys(LossControlReportService::REPORTS) as $key) {
        $this->actingAs($s['admin'])->get(route('inventory.reports.show', $key))->assertOk();
    }
    $this->actingAs(icUser('manager'))->get(route('inventory.reports.show', ['report' => 'wastage', 'format' => 'csv']))
        ->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $this->actingAs($s['admin'])->get(route('inventory.reports.show', 'no-such-report'))->assertNotFound();
});

it('highlights unusual prices, heavy requesters, unlinked issues, frequent adjustments and missing usage', function () {
    $s = lcScenario();
    $svc = app(LossControlReportService::class);
    $f = ['from' => now()->subDays(5)->toDateString(), 'to' => now()->addDay()->toDateString()];

    $price = collect($svc->run('price-history', $f)['rows']);
    expect($price->where('_flag', true)->pluck('supplier')->all())->toBe(['Shop 90']);

    $emp = collect($svc->run('employee-requests', $f)['rows']);
    expect($emp->firstWhere('employee', $s['b']->name)['_flag'])->toBeTrue()
        ->and($emp->firstWhere('employee', $s['a']->name)['_flag'])->toBeFalse();

    $signals = collect($svc->signals($f))->keyBy('report');
    expect($signals['material-issues']['count'])->toBe(3)   // all three were "other"
        ->and($signals['adjustments']['count'])->toBe(1)
        ->and($signals['reconciliation']['count'])->toBe(2); // r2, r3 handed over yesterday, usage not recorded

    $waste = collect($svc->run('wastage', $f)['rows']);
    expect($waste)->toHaveCount(1)->and($waste->first()['reason'])->toBe('spill');

    $item = collect($svc->run('movements', $f + ['item_id' => $s['rice']->id])['rows']);
    expect($item->count())->toBe(4 + 3 + 3);   // receipts + issues + corrections
});
