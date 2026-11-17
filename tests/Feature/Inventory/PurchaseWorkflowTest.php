<?php

use App\Models\AuditLog;
use App\Models\InventoryBillItem;
use App\Models\InventoryBillUpload;
use App\Models\InventoryItemAlias;
use App\Models\InventoryTransaction;
use App\Models\InventoryUnitConversion;
use App\Models\Purchase;
use App\Models\Vendor;
use Illuminate\Support\Str;

function purchasePayload(array $lines, array $extra = []): array
{
    return array_merge([
        'purchase_date' => now()->toDateString(),
        'supplier_name' => 'Local Vegetable Supplier',
        'lines' => $lines,
    ], $extra);
}

it('lets an employee enter many items in one bulk purchase and submit it', function () {
    $emp = icUser();
    $rice = icItem('Rice');
    $onion = icItem('Onion');
    $tomato = icItem('Tomato');
    $oil = icItem('Cooking Oil', 'litre');
    $rava = icItem('Rava');

    $res = $this->actingAs($emp)->post(route('inventory.purchases.store'), purchasePayload([
        ['inventory_item_id' => $rice->id, 'quantity' => 25, 'unit' => 'kg', 'rate' => 60],
        ['inventory_item_id' => $onion->id, 'quantity' => 10, 'unit' => 'kg', 'rate' => 40],
        ['inventory_item_id' => $tomato->id, 'quantity' => 5, 'unit' => 'kg', 'rate' => 35],
        ['inventory_item_id' => $oil->id, 'quantity' => 2, 'unit' => 'litre', 'rate' => 150],
        ['inventory_item_id' => $rava->id, 'quantity' => 10, 'unit' => 'kg', 'rate' => 45],
        ['inventory_item_id' => '', 'quantity' => ''],   // blank grid row ignored
    ], ['action' => 'submit', 'client_token' => (string) Str::uuid()]));

    $p = Purchase::first();
    $res->assertRedirect(route('inventory.purchases.show', $p));
    expect($p->status)->toBe('submitted')->and($p->items)->toHaveCount(5)
        ->and((float) $p->grand_total)->toBe(2825.0);
    // no stock moves before receipt
    expect(InventoryTransaction::count())->toBe(0);
});

it('rejects exact duplicate rows and ignores a re-posted form', function () {
    $emp = icUser();
    $rice = icItem('Rice');
    $row = ['inventory_item_id' => $rice->id, 'quantity' => 25, 'unit' => 'kg', 'rate' => 60];

    $this->actingAs($emp)->post(route('inventory.purchases.store'), purchasePayload([$row, $row]))
        ->assertSessionHasErrors('lines.1.inventory_item_id');

    $token = (string) Str::uuid();
    $this->actingAs($emp)->post(route('inventory.purchases.store'), purchasePayload([$row], ['client_token' => $token]));
    $this->actingAs($emp)->post(route('inventory.purchases.store'), purchasePayload([$row], ['client_token' => $token]));
    expect(Purchase::count())->toBe(1);
});

it('blocks the same supplier invoice being entered twice', function () {
    $emp = icUser();
    $rice = icItem('Rice');
    $vendor = Vendor::create(['name' => 'Ravi Stores', 'is_active' => true]);
    $p = purchasePayload([['inventory_item_id' => $rice->id, 'quantity' => 1, 'rate' => 60]], ['vendor_id' => $vendor->id, 'invoice_number' => 'INV-9']);

    $this->actingAs($emp)->post(route('inventory.purchases.store'), $p)->assertSessionHasNoErrors();
    $this->actingAs($emp)->post(route('inventory.purchases.store'), $p)->assertSessionHasErrors('invoice_number');
});

it('converts purchase units to the stock unit and refuses impossible conversions', function () {
    $emp = icUser();
    $mustard = icItem('Mustard Seeds');
    $egg = icItem('Egg', 'piece');

    $this->actingAs($emp)->post(route('inventory.purchases.store'), purchasePayload([
        ['inventory_item_id' => $egg->id, 'quantity' => 2, 'unit' => 'kg', 'rate' => 10],
    ]))->assertSessionHasErrors('lines.0.unit');

    $this->actingAs($emp)->post(route('inventory.purchases.store'), purchasePayload([
        ['inventory_item_id' => $mustard->id, 'quantity' => 500, 'unit' => 'gram', 'rate' => 0.2],
    ]))->assertSessionHasNoErrors();
    expect(Purchase::first()->items->first()->base_quantity)->toBe(0.5);
});

it('only admins approve, only storekeepers receive, and stock rises only on receipt (partial receipts)', function () {
    $emp = icUser();
    $admin = icUser('admin');
    $store = icUser('employee', true);
    $rice = icItem('Rice', 'kg', 2);

    $this->actingAs($emp)->post(route('inventory.purchases.store'), purchasePayload([
        ['inventory_item_id' => $rice->id, 'quantity' => 2, 'unit' => 'bag', 'rate' => 1500],
    ]))->assertSessionHasErrors('lines.0.unit'); // no bag rule yet

    InventoryUnitConversion::create(['inventory_item_id' => $rice->id, 'unit' => 'bag', 'factor' => 25]);
    $this->actingAs($emp)->post(route('inventory.purchases.store'), purchasePayload([
        ['inventory_item_id' => $rice->id, 'quantity' => 2, 'unit' => 'bag', 'rate' => 1500],
    ], ['action' => 'submit']));
    $p = Purchase::first();
    $line = $p->items->first();
    expect($line->base_quantity)->toBe(50.0);

    $this->actingAs($emp)->post(route('inventory.purchases.approve', $p))->assertForbidden();
    $this->actingAs($store)->get(route('inventory.purchases.receive-form', $p))->assertForbidden(); // not approved yet
    $this->actingAs($admin)->post(route('inventory.purchases.approve', $p))->assertRedirect();
    expect($p->fresh()->status)->toBe('approved')->and($rice->fresh()->current_stock)->toBe(2.0);

    $this->actingAs($emp)->post(route('inventory.purchases.receive', $p), ['lines' => [$line->id => ['received' => 1]]])->assertForbidden();

    $this->actingAs($store)->post(route('inventory.purchases.receive', $p), ['lines' => [$line->id => ['received' => 1]]])->assertRedirect();
    expect($p->fresh()->status)->toBe('partially_received')->and($rice->fresh()->current_stock)->toBe(27.0);

    // cannot receive more than still due
    $this->actingAs($store)->post(route('inventory.purchases.receive', $p), ['lines' => [$line->id => ['received' => 2]]])
        ->assertSessionHasErrors();
    $this->actingAs($store)->post(route('inventory.purchases.receive', $p), ['lines' => [$line->id => ['received' => 1]]]);
    expect($p->fresh()->status)->toBe('received')->and($rice->fresh()->current_stock)->toBe(52.0)
        ->and($p->receipts()->count())->toBe(2);

    $txn = InventoryTransaction::where('type', 'purchase_receipt')->first();
    expect($txn->reference_type)->toBe('purchase')->and($txn->reference_id)->toBe($p->id)
        ->and($txn->entered_unit)->toBe('bag')->and($txn->unit_cost)->toBe(60.0);

    $this->actingAs($admin)->post(route('inventory.purchases.complete', $p));
    expect($p->fresh()->status)->toBe('completed');
    expect(AuditLog::where('module', 'purchase')->count())->toBeGreaterThanOrEqual(4);
});

it('keeps employees out of other people\'s purchases', function () {
    $a = icUser();
    $b = icUser();
    $rice = icItem('Rice');
    $this->actingAs($a)->post(route('inventory.purchases.store'), purchasePayload([['inventory_item_id' => $rice->id, 'quantity' => 1, 'rate' => 1]]));
    $this->actingAs($b)->get(route('inventory.purchases.show', Purchase::first()))->assertForbidden();
});

it('turns an uploaded bill into a reviewable purchase and never adds stock directly', function () {
    $admin = icUser('admin');
    $rice = icItem('Rice', 'kg', 0);
    InventoryItemAlias::create(['inventory_item_id' => $rice->id, 'alias' => 'Sona Masoori Rice', 'normalized_alias' => 'sona masoori rice', 'source' => 'confirmed']);
    $bill = InventoryBillUpload::create([
        'original_filename' => 'b.jpg', 'stored_path' => 'x', 'file_type' => 'image', 'file_hash' => 'h',
        'status' => 'review_pending', 'uploaded_by' => $admin->id, 'vendor_name' => 'Ravi', 'invoice_number' => 'A1', 'total_amount' => 1500,
    ]);
    InventoryBillItem::create(['bill_upload_id' => $bill->id, 'item_name' => 'Sona Masoori Rice 25kg', 'quantity' => 25, 'unit' => 'kgs', 'unit_price' => 60, 'total' => 1500]);

    // legacy import button now leads to the purchase grid
    $this->actingAs($admin)->post(route('admin.inventory.bills.import', $bill))
        ->assertRedirect(route('inventory.purchases.create', ['bill' => $bill->id]));
    expect($rice->fresh()->current_stock)->toBe(0.0);

    $this->actingAs($admin)->get(route('inventory.purchases.create', ['bill' => $bill->id]))
        ->assertOk()->assertSee('Sona Masoori Rice 25kg');

    $this->actingAs($admin)->post(route('inventory.purchases.store'), purchasePayload([
        ['inventory_item_id' => $rice->id, 'description' => 'Sona Masoori Rice 25kg', 'quantity' => 25, 'unit' => 'kg', 'rate' => 60],
    ], ['bill_id' => $bill->id, 'supplier_name' => 'Ravi', 'invoice_number' => 'A1']));

    $p = Purchase::first();
    expect($bill->fresh()->status)->toBe('converted')->and($bill->fresh()->purchase_id)->toBe($p->id)
        ->and($p->inventory_bill_upload_id)->toBe($bill->id)
        ->and($rice->fresh()->current_stock)->toBe(0.0);
    // a second purchase cannot reuse the bill
    $this->actingAs($admin)->get(route('inventory.purchases.create', ['bill' => $bill->id]))->assertRedirect();
});

it('renders the purchase pages', function () {
    $admin = icUser('admin');
    $rice = icItem('Rice');
    $this->actingAs($admin)->get(route('inventory.purchases.create'))->assertOk()->assertSee('Bulk purchase entry');
    $this->actingAs($admin)->post(route('inventory.purchases.store'), purchasePayload([['inventory_item_id' => $rice->id, 'quantity' => 1, 'rate' => 5]], ['action' => 'submit']));
    $p = Purchase::first();
    $this->actingAs($admin)->get(route('inventory.purchases.index'))->assertOk()->assertSee($p->reference());
    $this->actingAs($admin)->get(route('inventory.purchases.show', $p))->assertOk()->assertSee('Approve');
    $this->actingAs($admin)->get(route('inventory.purchases.edit', $p))->assertOk();
    $this->actingAs($admin)->post(route('inventory.purchases.approve', $p));
    $this->actingAs($admin)->get(route('inventory.purchases.receive-form', $p))->assertOk()->assertSee('Confirm receipt');
    $this->actingAs($admin)->get(route('admin.inventory.items.show', $rice))->assertOk();
});
