<?php

use App\Models\InventoryBillItem;
use App\Models\InventoryBillUpload;
use Illuminate\Support\Facades\Schema;

function reviewBill(array $extra = []): InventoryBillUpload
{
    $bill = InventoryBillUpload::create(array_merge([
        'original_filename' => 'bill.jpg', 'stored_path' => 'x', 'file_type' => 'image', 'file_hash' => 'h'.uniqid(),
        'status' => 'review_pending', 'uploaded_by' => auth()->id() ?? icUser('admin')->id,
        'vendor_name' => 'Sri Lakshmi', 'invoice_number' => 'SM-1', 'total_amount' => 500,
    ], $extra));
    InventoryBillItem::create(['bill_upload_id' => $bill->id, 'item_name' => 'Toor Dal', 'original_item_name' => 'Toor Dal', 'quantity' => 2, 'unit' => 'kg', 'unit_price' => 140, 'total' => 280]);

    return $bill;
}

function reviewPayload(InventoryBillUpload $bill, array $items, array $extra = []): array
{
    return array_merge([
        'vendor_name' => $bill->vendor_name, 'invoice_number' => $bill->invoice_number,
        'total_amount' => $bill->total_amount, 'items' => $items,
    ], $extra);
}

it('renders mobile cards with the searchable matcher, totals and a discrepancy warning', function () {
    $this->actingAs(icUser('admin'));
    $bill = reviewBill();

    $html = $this->get(route('admin.inventory.bills.show', $bill))->assertOk()->getContent();

    expect($html)
        ->toContain('data-line')
        ->toContain('Search item (English / தமிழ்)')
        ->toContain('window.IcItemPicker')
        ->toContain('Save &amp; create purchase')
        ->toContain('Not matched')
        ->toContain('difference ₹220.00')          // lines ₹280 vs bill ₹500
        ->not->toContain('item-match-select')       // no giant native item dropdown
        ->not->toContain('#importModal');           // dead legacy button is gone
});

it('keeps the original bill text when the review is saved', function () {
    $this->actingAs(icUser('admin'));
    $bill = reviewBill();

    $this->put(route('admin.inventory.bills.update', $bill), reviewPayload($bill, [
        ['item_name' => 'Toor Dal (corrected)', 'original_item_name' => 'Toor Dal', 'quantity' => 2, 'unit_price' => 140, 'total' => 280],
        ['item_name' => 'Hand added', 'quantity' => 1, 'unit_price' => 10, 'total' => 10],
    ]))->assertRedirect();

    $items = $bill->items()->orderBy('id')->get();
    expect($items)->toHaveCount(2)
        ->and($items[0]->original_item_name)->toBe('Toor Dal')
        ->and($items[0]->item_name)->toBe('Toor Dal (corrected)')
        ->and($items[1]->original_item_name)->toBe('Hand added');
});

it('saves first and then opens the purchase form for "Save & create purchase" without moving stock', function () {
    $this->actingAs(icUser('admin'));
    $bill = reviewBill();
    $item = icItem('Toor Dal', 'kg', 0);

    $this->put(route('admin.inventory.bills.update', $bill), reviewPayload($bill, [
        ['item_name' => 'Toor Dal', 'original_item_name' => 'Toor Dal', 'inventory_item_id' => $item->id, 'quantity' => 3, 'unit_price' => 140, 'total' => 420],
    ], ['next' => 'purchase']))->assertRedirect(route('inventory.purchases.create', ['bill' => $bill->id]));

    expect($bill->items()->first()->quantity)->toEqual(3)
        ->and($bill->items()->first()->inventory_item_id)->toBe($item->id)
        ->and($item->fresh()->current_stock)->toBe(0.0)
        ->and($bill->fresh()->purchase_id)->toBeNull();
});

it('rejects an unknown next step and shows line errors next to the field with the edits kept', function () {
    $this->actingAs(icUser('admin'));
    $bill = reviewBill();

    $this->from(route('admin.inventory.bills.show', $bill))
        ->put(route('admin.inventory.bills.update', $bill), reviewPayload($bill, [
            ['item_name' => 'Toor Dal typed', 'original_item_name' => 'Toor Dal', 'quantity' => 0, 'unit_price' => 140, 'total' => 280],
        ], ['next' => 'stock']))
        ->assertSessionHasErrors(['items.0.quantity', 'next']);

    $html = $this->followingRedirects()->from(route('admin.inventory.bills.show', $bill))
        ->put(route('admin.inventory.bills.update', $bill), reviewPayload($bill, [
            ['item_name' => 'Toor Dal typed', 'original_item_name' => 'Toor Dal', 'quantity' => 0, 'unit_price' => 140, 'total' => 280],
        ]))->getContent();
    expect($html)->toContain('value="Toor Dal typed"')->toContain('<span class="ib-ferr">')
        ->and($bill->items()->first()->item_name)->toBe('Toor Dal');   // nothing saved
});

it('shows a linked bill read-only with only the purchase action', function () {
    $this->actingAs(icUser('admin'));
    $bill = reviewBill(['status' => 'converted']);
    $bill->forceFill(['purchase_id' => 999999])->saveQuietly();

    $html = $this->get(route('admin.inventory.bills.show', $bill))->assertOk()->getContent();
    expect($html)->toContain('Open purchase')
        ->not->toContain('Save &amp; create purchase')
        ->not->toContain('id="btnAddRow"')
        ->not->toContain('id="rerunOcrForm"');
})->skip(fn () => Schema::hasColumn('inventory_bill_uploads', 'purchase_id') === false, 'no purchase_id column');
