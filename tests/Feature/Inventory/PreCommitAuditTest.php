<?php

use App\Models\InventoryBillItem;
use App\Models\InventoryBillUpload;
use App\Models\InventoryItemAlias;
use App\Models\InventoryTransaction;
use App\Models\MaterialRequest;
use App\Models\Purchase;
use App\Models\Vendor;
use App\Services\Inventory\ItemMatchingService;
use App\Services\Inventory\MaterialRequestService;
use App\Services\InventoryService;

it('matches spelling variations without auto-accepting weak or wrong suggestions', function () {
    $onion = icItem('Onion'); icItem('Onion Powder'); $oil = icItem('Cooking Oil', 'litre');
    $m = app(ItemMatchingService::class);

    $typo = $m->best('Onions');                      // spelling variation
    expect($typo['item_id'])->toBe($onion->id)->and($m->isAutoAcceptable($typo))->toBeFalse();
    $o = $m->best('Onoin');                          // typo: suggested, never auto-selected
    expect($o['item_id'])->toBe($onion->id)->and($m->isAutoAcceptable($o))->toBeFalse();

    $wrong = $m->best('Oil Cake');                   // plausible but wrong suggestion
    expect($wrong === null || ! $m->isAutoAcceptable($wrong))->toBeTrue();

    expect($m->best('xyz unknown thing'))->toBeNull();
});

it('treats another supplier\'s alias with less confidence than the same supplier\'s', function () {
    $oil = icItem('Cooking Oil', 'litre');
    $v1 = Vendor::create(['name' => 'A', 'is_active' => true]);
    $v2 = Vendor::create(['name' => 'B', 'is_active' => true]);
    InventoryItemAlias::create(['inventory_item_id' => $oil->id, 'alias' => 'Gold Drop', 'normalized_alias' => 'gold drop', 'vendor_id' => $v1->id, 'source' => 'confirmed']);
    expect(app(ItemMatchingService::class)->best('Gold Drop', $v1->id)['confidence'])->toBe(98)
        ->and(app(ItemMatchingService::class)->best('Gold Drop', $v2->id)['confidence'])->toBe(88);
});

it('refuses an invoice line whose unit cannot convert to the stock unit', function () {
    $admin = icUser('admin');
    $egg = icItem('Egg', 'piece');
    $bill = InventoryBillUpload::create(['original_filename' => 'b.jpg', 'stored_path' => 'x', 'file_type' => 'image', 'file_hash' => 'h2', 'status' => 'review_pending', 'uploaded_by' => $admin->id]);
    InventoryBillItem::create(['bill_upload_id' => $bill->id, 'item_name' => 'Egg', 'quantity' => 3, 'unit' => 'kg', 'unit_price' => 100, 'total' => 300]);
    $this->actingAs($admin)->post(route('inventory.purchases.store'), [
        'purchase_date' => now()->toDateString(), 'supplier_name' => 'S', 'bill_id' => $bill->id,
        'lines' => [['inventory_item_id' => $egg->id, 'description' => 'Egg', 'quantity' => 3, 'unit' => 'kg', 'rate' => 100]],
    ])->assertSessionHasErrors('lines.0.unit');
    expect(Purchase::count())->toBe(0)->and($bill->fresh()->purchase_id)->toBeNull()->and($egg->fresh()->current_stock)->toBe(0.0);
});

it('stops non-admins attaching someone\'s uploaded bill to their purchase', function () {
    $admin = icUser('admin');
    $rice = icItem('Rice');
    $bill = InventoryBillUpload::create(['original_filename' => 'b.jpg', 'stored_path' => 'x', 'file_type' => 'image', 'file_hash' => 'h3', 'status' => 'review_pending', 'uploaded_by' => $admin->id]);
    $this->actingAs(icUser())->post(route('inventory.purchases.store'), [
        'purchase_date' => now()->toDateString(), 'supplier_name' => 'S', 'bill_id' => $bill->id,
        'lines' => [['inventory_item_id' => $rice->id, 'quantity' => 1, 'rate' => 1]],
    ])->assertSessionHasErrors('bill_id');
    expect($bill->fresh()->purchase_id)->toBeNull();
    $this->actingAs(icUser())->get(route('admin.inventory.bills.index'))->assertForbidden();
});

it('never lets reversals exceed the original movement', function () {
    $admin = icUser('admin');
    $item = icItem('Rava', 'kg', 10);
    $svc = app(InventoryService::class);
    $issue = $svc->post($item, -6, 'material_issue', $admin);
    $svc->reverse($issue, $admin, 4);
    expect(fn () => $svc->reverse($issue, $admin, 3))->toThrow(RuntimeException::class, 'already reversed');
    expect($item->fresh()->current_stock)->toBe(8.0);
});

it('blocks deleting meal entries and hall bookings that carry material history', function () {
    $mgr = icUser('manager'); $emp = icUser();
    $this->actingAs($mgr);
    $client = \App\Models\MealClient::create(['name' => 'C', 'active' => true, 'created_by' => $mgr->id]);
    $entry = \App\Models\MealEntry::create(['meal_client_id' => $client->id, 'entry_date' => now()->toDateString(), 'created_by' => $mgr->id]);
    $rava = icItem('Rava', 'kg', 10);
    app(MaterialRequestService::class)->save(['source' => 'meal', 'meal_entry_id' => $entry->id, 'meal_type' => 'lunch', 'department' => 'kitchen',
        'required_date' => now()->toDateString(), 'lines' => [['inventory_item_id' => $rava->id, 'quantity' => 1, 'unit' => 'kg']]], $emp);
    $this->actingAs($mgr)->delete(route('meal-register.entries.destroy', $entry))->assertSessionHas('error');
    expect(\App\Models\MealEntry::find($entry->id))->not->toBeNull();
});

it('keeps employees away from other people\'s requests and admin-only actions', function () {
    $a = icUser(); $b = icUser(); $store = icUser('employee', true); $admin = icUser('admin');
    $this->actingAs($a);
    $rava = icItem('Rava', 'kg', 100);
    $svc = app(MaterialRequestService::class);
    $draft = $svc->save(['source' => 'other', 'department' => 'kitchen', 'purpose' => 'x', 'required_date' => now()->toDateString(),
        'lines' => [['inventory_item_id' => $rava->id, 'quantity' => 1, 'unit' => 'kg']]], $a);

    $this->actingAs($b)->put(route('inventory.material-requests.update', $draft), ['source' => 'other', 'department' => 'kitchen', 'required_date' => now()->toDateString(),
        'lines' => [['inventory_item_id' => $rava->id, 'quantity' => 99, 'unit' => 'kg']]])->assertForbidden();
    $this->actingAs($b)->post(route('inventory.material-requests.cancel', $draft), ['reason' => 'nope'])->assertForbidden();
    $this->actingAs($a)->post(route('inventory.material-requests.submit', $draft));
    // requester and storekeeper cannot approve; storekeeper cannot approve purchases
    $this->actingAs($a)->post(route('inventory.material-requests.approve', $draft))->assertForbidden();
    $this->actingAs($store)->post(route('inventory.material-requests.approve', $draft))->assertForbidden();
    $p = app(\App\Services\Inventory\PurchaseService::class)->save(['purchase_date' => now()->toDateString(), 'supplier_name' => 'S',
        'lines' => [['inventory_item_id' => $rava->id, 'quantity' => 1, 'rate' => 1]]], $store, null, true);
    $this->actingAs($store)->post(route('inventory.purchases.approve', $p))->assertForbidden();
    // reports, standards, stock: management only
    foreach (['inventory.reports.index', 'inventory.standards.index', 'inventory.stock'] as $r) {
        $this->actingAs($a)->get(route($r))->assertForbidden();
    }
    // guests see nothing
    auth()->logout();
    $this->get(route('inventory.home'))->assertRedirect(route('login'));
    $this->get(route('inventory.material-requests.show', $draft))->assertRedirect(route('login'));
    expect(MaterialRequest::find($draft->id)->status)->toBe('submitted')->and(InventoryTransaction::count())->toBe(0);
});

it('puts a CSRF token in every POST form of the new screens (Laravel disables the check in tests)', function () {
    $files = array_merge(glob(resource_path('views/inventory/**/*.blade.php')), glob(resource_path('views/inventory/*.blade.php')),
        [resource_path('views/hall/bookings/menu.blade.php')]);
    foreach ($files as $f) {
        $src = file_get_contents($f);
        $posts = preg_match_all('/<form[^>]*method="POST"/i', $src);
        expect(substr_count($src, '@csrf'))->toBeGreaterThanOrEqual($posts, basename($f));
    }
    $web = app(\Illuminate\Contracts\Http\Kernel::class)->getMiddlewareGroups()['web'];
    expect(collect($web)->contains(fn ($m) => str_contains($m, 'PreventRequestForgery') || str_contains($m, 'VerifyCsrfToken')))->toBeTrue();
});

it('neutralises spreadsheet formulas in CSV exports', function () {
    $admin = icUser('admin');
    $this->actingAs($admin);
    $item = icItem('Rava', 'kg', 10);
    app(InventoryService::class)->post($item, -1, 'wastage', $admin, ['notes' => '=HYPERLINK("http://evil")']);
    $csv = $this->get(route('inventory.reports.show', ['report' => 'adjustments', 'format' => 'csv']))->streamedContent();
    expect($csv)->toContain("'=HYPERLINK")->not->toContain(',=HYPERLINK');
});
