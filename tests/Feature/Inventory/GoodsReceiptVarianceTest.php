<?php

use App\Models\GoodsReceipt;
use App\Models\InventoryTransaction;
use App\Models\Purchase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function approvedPurchase(float $qty = 10, float $rate = 50): array
{
    $admin = icUser('admin');
    $item = icItem('Rice', 'kg', 0);
    $p = Purchase::create([
        'purchase_date' => now()->toDateString(), 'supplier_name' => 'Local shop', 'status' => 'approved',
        'created_by' => $admin->id, 'approved_by' => $admin->id, 'approved_at' => now(),
        'subtotal' => $qty * $rate, 'tax_amount' => 0, 'discount_amount' => 0, 'grand_total' => $qty * $rate,
        'payment_mode' => 'cash', 'payment_status' => 'unpaid',
    ]);
    $line = $p->items()->create([
        'inventory_item_id' => $item->id, 'quantity' => $qty, 'unit' => 'kg', 'base_quantity' => $qty,
        'rate' => $rate, 'tax_percent' => 0, 'amount' => $qty * $rate, 'received_base_quantity' => 0,
    ]);

    return [$admin, $p, $line, $item];
}

it('receives less than ordered, closes the rest, keeps the bill and the billed rate', function () {
    Storage::fake('private');
    [, $p, $line, $item] = approvedPurchase();
    $store = icUser('employee', true);

    $this->actingAs($store)->post(route('inventory.purchases.receive', $p), [
        'lines' => [$line->id => ['received' => 8, 'rate' => 55, 'close' => '1', 'remarks' => 'Supplier ran out']],
        'bill_number' => 'INV-77', 'bill_amount' => 440,
        'bill_file' => UploadedFile::fake()->create('bill.pdf', 120, 'application/pdf'),
    ])->assertRedirect(route('inventory.purchases.show', $p))->assertSessionHasNoErrors();

    $r = GoodsReceipt::first();
    expect($p->fresh()->status)->toBe('received')                    // 2 kg closed => nothing due
        ->and($p->fresh()->grand_total)->toEqual(500)                    // approved figures untouched
        ->and($line->fresh()->closed_base_quantity)->toBe(2.0)
        ->and($item->fresh()->current_stock)->toBe(8.0)
        ->and((float) InventoryTransaction::latest('id')->first()->unit_cost)->toBe(55.0)
        ->and($r->bill_number)->toBe('INV-77')->and($r->bill_amount)->toBe(440.0)
        ->and($r->items->first()->actual_rate)->toBe(55.0);
    Storage::disk('private')->assertExists($r->bill_path);

    $this->get(route('inventory.purchases.show', $p))->assertOk()
        ->assertSee('View bill')->assertSee('2 kg not coming')->assertSee('approved ₹50.00');
    $this->get(route('inventory.purchases.receipt-bill', [$p, $r]))->assertOk()->assertHeader('Content-Type', 'application/pdf');
    $this->actingAs(icUser())->get(route('inventory.purchases.receipt-bill', [$p, $r]))->assertForbidden();
});

it('can close an item that never arrived; failed receipts drop the upload', function () {
    Storage::fake('private');
    [, $p, $line, $item] = approvedPurchase();
    $store = icUser('employee', true);

    $this->actingAs($store)->post(route('inventory.purchases.receive', $p), [
        'lines' => [$line->id => ['received' => '', 'rejected' => 1, 'remarks' => '']],     // rejection still needs a reason
        'bill_file' => UploadedFile::fake()->create('bill.jpg', 50, 'image/jpeg'),
    ])->assertSessionHasErrors("lines.{$line->id}");
    expect(GoodsReceipt::count())->toBe(0)->and(Storage::disk('private')->allFiles())->toBe([]);   // upload cleaned up

    $this->actingAs($store)->post(route('inventory.purchases.receive', $p), [
        'lines' => [$line->id => ['received' => '', 'close' => '1']],
    ])->assertSessionHasNoErrors();
    expect($p->fresh()->status)->toBe('received')->and($item->fresh()->current_stock)->toBe(0.0);
});

it('keeps partial receipts open when the rest is still coming', function () {
    [, $p, $line] = approvedPurchase();
    $this->actingAs(icUser('employee', true))->post(route('inventory.purchases.receive', $p), [
        'lines' => [$line->id => ['received' => 6, 'close' => '0']],
    ])->assertSessionHasNoErrors();

    expect($p->fresh()->status)->toBe('partially_received')->and($line->fresh()->pendingBaseQuantity())->toBe(4.0);
    $this->get(route('inventory.purchases.receive-form', $p))->assertOk()
        ->assertSee('Due 4 kg')->assertSee('Rest not coming')->assertSee('name="bill_file"', false);
});

it('receives everything as ordered in one tap from the purchase page, bill required', function () {
    Storage::fake('private');
    [, $p, $line, $item] = approvedPurchase();
    $store = icUser('employee', true);

    $this->actingAs($store)->get(route('inventory.purchases.show', $p))->assertOk()
        ->assertSee('Received as ordered')->assertSee('Something different?');

    $this->post(route('inventory.purchases.receive', $p), ['mode' => 'all'])->assertSessionHasErrors('bill_file');
    expect(GoodsReceipt::count())->toBe(0);

    $this->post(route('inventory.purchases.receive', $p), [
        'mode' => 'all', 'bill_file' => UploadedFile::fake()->create('bill.jpg', 50, 'image/jpeg'),
    ])->assertSessionHasNoErrors();
    expect($p->fresh()->status)->toBe('received')->and($item->fresh()->current_stock)->toBe(10.0);
});

it('records only what differs: short qty closes the rest, new rate, added item', function () {
    Storage::fake('private');
    [, $p, $line, $item] = approvedPurchase();
    $dal = icItem('Toor Dal', 'kg', 0);

    $this->actingAs(icUser('employee', true))->post(route('inventory.purchases.receive', $p), [
        'mode' => 'changes',
        'lines' => [$line->id => ['received' => 7, 'rate' => 52]],
        'extra' => [['inventory_item_id' => $dal->id, 'quantity' => 3, 'rate' => 120], ['inventory_item_id' => '', 'quantity' => '']],
        'bill_file' => UploadedFile::fake()->create('bill.pdf', 50, 'application/pdf'),
    ])->assertSessionHasNoErrors();

    $p->refresh();
    expect($p->status)->toBe('received')                                 // short 3 kg closed automatically
        ->and($line->fresh()->closed_base_quantity)->toBe(3.0)
        ->and($item->fresh()->current_stock)->toBe(7.0)
        ->and($dal->fresh()->current_stock)->toBe(3.0)
        ->and($p->items()->count())->toBe(2)
        ->and(GoodsReceipt::first()->items->firstWhere('purchase_item_id', $line->id)->actual_rate)->toBe(52.0);
    $this->get(route('inventory.purchases.show', $p))->assertSee('Added on receipt');
});

it('keeps short items open when more is coming', function () {
    Storage::fake('private');
    [, $p, $line] = approvedPurchase();
    $this->actingAs(icUser('employee', true))->post(route('inventory.purchases.receive', $p), [
        'mode' => 'changes', 'more_coming' => '1', 'lines' => [$line->id => ['received' => 4]],
        'bill_file' => UploadedFile::fake()->create('bill.pdf', 50, 'application/pdf'),
    ])->assertSessionHasNoErrors();
    expect($p->fresh()->status)->toBe('partially_received')->and($line->fresh()->pendingBaseQuantity())->toBe(6.0);

    // second delivery: bill already on file, so it is optional now
    $this->post(route('inventory.purchases.receive', $p), ['mode' => 'all'])->assertSessionHasNoErrors();
    expect($p->fresh()->status)->toBe('received');
});
