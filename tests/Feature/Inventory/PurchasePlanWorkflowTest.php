<?php

use App\Models\Purchase;
use App\Models\PurchasePlan;

/** Employee prepares → admin approves (changing a qty) → employee buys → storekeeper receives → plan closed. */
function pwSetup(): array
{
    $emp = icUser();
    $admin = icUser('admin');
    $store = icUser('employee', true);
    $onion = icItem('Onion', 'kg', 0);
    $tomato = icItem('Tomato', 'kg', 0);
    $oil = icItem('Oil', 'litre', 0);

    return [$emp, $admin, $store, $onion, $tomato, $oil];
}

function pwLines($onion, $tomato, $oil): array
{
    return [
        ['selected' => 1, 'inventory_item_id' => $onion->id, 'quantity' => 30, 'unit_cost' => 40, 'priority' => 'high'],
        ['selected' => 1, 'inventory_item_id' => $tomato->id, 'quantity' => 20, 'unit_cost' => 30],
        ['selected' => 1, 'inventory_item_id' => $oil->id, 'quantity' => 5, 'unit_cost' => 150],
        ['inventory_item_id' => icItem('Sugar', 'kg', 0)->id, 'quantity' => 9],       // not ticked → ignored
    ];
}

it('runs the full plan → approve → buy → receive → close flow with differences and a shortfall plan', function () {
    [$emp, $admin, $store, $onion, $tomato, $oil] = pwSetup();

    // 1. employee prepares and sends for approval
    $onion->update(['minimum_stock' => 10]);   // low stock → shows as a ticked suggestion on the original plan page
    $this->actingAs($emp)->get(route('inventory.plans.create'))->assertOk()->assertSee('Send for approval')
        ->assertSee('suggested — low stock')->assertSee('Onion')->assertSee('Add another item');
    $this->post(route('inventory.plans.store'), ['title' => 'Weekend veg', 'planned_date' => now()->addDay()->toDateString(),
        'items' => pwLines($onion, $tomato, $oil), 'submit' => 1])->assertRedirect();
    $plan = PurchasePlan::firstOrFail();
    expect($plan->status)->toBe('submitted')->and($plan->items)->toHaveCount(3)->and($plan->created_by)->toBe($emp->id);
    $this->post(route('inventory.plans.approve', $plan))->assertForbidden();        // employees can't approve
    $this->get(route('inventory.plans.edit', $plan))->assertForbidden();             // locked while waiting

    // 2. admin approves, cutting onions to 25 and dropping oil
    $ids = $plan->items->keyBy('inventory_item_id');
    $this->actingAs($admin)->get(route('inventory.plans.show', $plan))->assertOk()->assertSee('Approve plan')->assertSee('Send back');
    $this->post(route('inventory.plans.approve', $plan), ['approved' => [
        $ids[$onion->id]->id => 25, $ids[$tomato->id]->id => 20, $ids[$oil->id]->id => 0,
    ], 'approval_note' => 'Oil in stock'])->assertSessionHasNoErrors();
    expect($plan->fresh()->status)->toBe('approved');

    // 3. employee buys: cannot exceed what is approved; purchase is approved straight away, no stock yet
    $this->actingAs($emp)->post(route('inventory.plans.buy', $plan), ['purchase_date' => now()->toDateString(), 'supplier_name' => 'Market',
        'lines' => [$ids[$onion->id]->id => ['quantity' => 30, 'rate' => 40]]])->assertSessionHasErrors();
    $this->post(route('inventory.plans.buy', $plan), ['purchase_date' => now()->toDateString(), 'supplier_name' => 'Market',
        'lines' => [$ids[$onion->id]->id => ['quantity' => 25, 'rate' => 44], $ids[$tomato->id]->id => ['quantity' => 20, 'rate' => 30]]])
        ->assertSessionHasNoErrors();
    $purchase = Purchase::firstOrFail();
    expect($purchase->purchase_plan_id)->toBe($plan->id)->and($purchase->status)->toBe('approved')
        ->and($plan->fresh()->status)->toBe('ordered')->and($onion->fresh()->current_stock)->toBe(0.0);

    // cannot close while goods are awaited
    $this->post(route('inventory.plans.close', $plan), ['closing_note' => 'x'])->assertSessionHas('error');

    // 4. storekeeper receives: 20 kg onions (rest not coming), tomatoes 20 of which 4 rejected, plus 2 kg extra garlic
    $garlic = icItem('Garlic', 'kg', 0);
    $lines = $purchase->items->keyBy('inventory_item_id');
    $this->actingAs($store)->post(route('inventory.purchases.receive', $purchase), [
        'lines' => [
            $lines[$onion->id]->id => ['received' => 20, 'close' => '1', 'remarks' => 'Only 20 kg at the shop'],
            $lines[$tomato->id]->id => ['received' => 16, 'rejected' => 4, 'close' => '1', 'remarks' => 'Spoiled'],
        ],
        'extra' => [['inventory_item_id' => $garlic->id, 'quantity' => 2, 'rate' => 120]],
    ])->assertSessionHasNoErrors();
    expect($onion->fresh()->current_stock)->toBe(20.0);

    // 5. plan page shows the differences; closing needs a note; shortfall goes to a new draft plan
    $this->actingAs($emp)->get(route('inventory.plans.show', $plan))->assertOk()
        ->assertSee('Short')->assertSee('4 rejected')->assertSee('Garlic')->assertSee('not on the plan')
        ->assertSee('+10% vs est.')->assertSee('Close plan with differences');
    $this->post(route('inventory.plans.close', $plan), [])->assertSessionHasErrors('closing_note');
    $this->post(route('inventory.plans.close', $plan), ['closing_note' => 'Shop short on onions; tomatoes spoiled', 'replan' => 1])->assertRedirect();

    $plan->refresh();
    $short = PurchasePlan::findOrFail($plan->shortfall_plan_id);
    expect($plan->status)->toBe('completed')->and($plan->closing_note)->toContain('onions')
        ->and($short->status)->toBe('draft')->and($short->created_by)->toBe($emp->id)
        ->and($short->items->pluck('suggested_quantity', 'inventory_item_id')->all())->toEqual([$onion->id => 5.0, $tomato->id => 4.0]);
});

it('closes a plan without a note when everything arrived as planned', function () {
    [$emp, $admin, $store, $onion] = pwSetup();
    $this->actingAs($emp)->post(route('inventory.plans.store'), ['title' => 'Onions', 'planned_date' => now()->toDateString(),
        'items' => [['selected' => 1, 'inventory_item_id' => $onion->id, 'quantity' => 10, 'unit_cost' => 40]], 'submit' => 1]);
    $plan = PurchasePlan::firstOrFail();
    $this->actingAs($admin)->post(route('inventory.plans.approve', $plan))->assertSessionHasNoErrors();
    $this->actingAs($store)->post(route('inventory.plans.buy', $plan), ['purchase_date' => now()->toDateString(), 'supplier_name' => 'Shop',
        'lines' => [$plan->items->first()->id => ['quantity' => 10, 'rate' => 40]]])->assertSessionHasNoErrors();
    $p = Purchase::firstOrFail();
    $this->post(route('inventory.purchases.receive', $p), ['lines' => [$p->items->first()->id => ['received' => 10]]])->assertSessionHasNoErrors();

    $this->actingAs($emp)->get(route('inventory.plans.show', $plan))->assertSee('Everything was received as planned');
    $this->post(route('inventory.plans.close', $plan))->assertSessionHasNoErrors();
    expect($plan->fresh()->status)->toBe('completed')->and($plan->fresh()->closing_note)->toBe('Received as planned.');
});

it('lets the admin send a plan back, and the employee fix and resend it', function () {
    [$emp, $admin, , $onion] = pwSetup();
    $this->actingAs($emp)->post(route('inventory.plans.store'), ['title' => 'Onions', 'planned_date' => now()->toDateString(),
        'items' => [['selected' => 1, 'inventory_item_id' => $onion->id, 'quantity' => 100]], 'submit' => 1]);
    $plan = PurchasePlan::firstOrFail();
    $this->actingAs($admin)->post(route('inventory.plans.reject', $plan), ['reason' => 'Too much — 40 kg is enough'])->assertSessionHasNoErrors();
    expect($plan->fresh()->status)->toBe('rejected');

    $this->actingAs($emp)->get(route('inventory.plans.show', $plan))->assertSee('Too much');
    $this->put(route('inventory.plans.update', $plan), ['title' => 'Onions', 'planned_date' => now()->toDateString(),
        'items' => [['selected' => 1, 'inventory_item_id' => $onion->id, 'quantity' => 40]], 'submit' => 1])->assertSessionHasNoErrors();
    expect($plan->fresh()->status)->toBe('submitted')->and($plan->fresh()->items->first()->suggested_quantity)->toBe(40.0);
});

it('keeps plans private to their creator and the store side', function () {
    [$emp, $admin, $store, $onion] = pwSetup();
    $this->actingAs($emp)->post(route('inventory.plans.store'), ['title' => 'Mine', 'planned_date' => now()->toDateString(),
        'items' => [['selected' => 1, 'inventory_item_id' => $onion->id, 'quantity' => 1]]]);
    $plan = PurchasePlan::firstOrFail();
    expect($plan->status)->toBe('draft');

    $this->actingAs(icUser())->get(route('inventory.plans.show', $plan))->assertForbidden();
    $this->actingAs(icUser())->get(route('inventory.plans.index'))->assertOk()->assertDontSee('Mine');
    $this->actingAs($store)->get(route('inventory.plans.show', $plan))->assertOk();
    $this->actingAs($emp)->post(route('inventory.plans.store'), ['title' => 'Bad', 'planned_date' => now()->toDateString(),
        'items' => [['selected' => 1, 'inventory_item_id' => $onion->id, 'quantity' => 0]]])->assertSessionHasErrors('lines.0.quantity');
    $this->actingAs($emp)->post(route('inventory.plans.store'), ['title' => 'None', 'planned_date' => now()->toDateString(),
        'items' => [['inventory_item_id' => $onion->id, 'quantity' => 4]]])->assertSessionHasErrors('items');
    $this->actingAs($emp)->get(route('inventory.plans.index'))->assertOk()->assertSee('Mine')
        ->assertSee(route('inventory.plans.index'), false);
});

it('surfaces submitted plans to admins on the dashboard, the store inbox and both plan lists', function () {
    [$emp, $admin, , $onion] = pwSetup();
    $this->actingAs($emp)->post(route('inventory.plans.store'), ['title' => 'Need onions', 'planned_date' => now()->toDateString(),
        'items' => [['selected' => 1, 'inventory_item_id' => $onion->id, 'quantity' => 5]], 'submit' => 1]);
    $plan = PurchasePlan::firstOrFail();

    $this->actingAs($admin);
    $this->get(route('admin.dashboard'))->assertSee('data-attn="plans"', false)->assertSee('Review Plans');
    $this->get(route('inventory.home'))->assertSee('Purchase plans to approve');
    $this->get(route('inventory.plans.index'))->assertSee('Need onions')->assertSee('Review')->assertSee('data-status="submitted"', false);
    $this->get(route('admin.purchase-plans.index'))->assertSee('Waiting approval')->assertSee('data-k="submitted"', false);
    $this->get(route('admin.purchase-plans.index', ['status' => 'submitted']))->assertSee('Need onions');
    expect(\App\Models\AppNotification::where('type', 'purchase_plan_submitted')->where('user_id', $admin->id)->exists())->toBeTrue();
});

it('fills a purchase bought without prices from the rates entered at receipt', function () {
    [$emp, $admin, $store, $onion, $tomato] = pwSetup();
    $this->actingAs($emp)->post(route('inventory.plans.store'), ['title' => 'No prices', 'planned_date' => now()->toDateString(), 'submit' => 1,
        'items' => [['selected' => 1, 'inventory_item_id' => $onion->id, 'quantity' => 2], ['selected' => 1, 'inventory_item_id' => $tomato->id, 'quantity' => 3]]]);
    $plan = PurchasePlan::firstOrFail();
    $this->actingAs($admin)->post(route('inventory.plans.approve', $plan));
    $ids = $plan->items->pluck('id', 'inventory_item_id');
    $this->actingAs($emp)->post(route('inventory.plans.buy', $plan), ['purchase_date' => now()->toDateString(), 'supplier_name' => 'DP Store',
        'lines' => [$ids[$onion->id] => ['quantity' => 2, 'rate' => ''], $ids[$tomato->id] => ['quantity' => 3, 'rate' => '']]])->assertSessionHasNoErrors();
    $p = Purchase::firstOrFail();
    expect((float) $p->grand_total)->toBe(0.0);
    $this->get(route('inventory.purchases.show', $p))->assertSee('Prices not entered yet')->assertSee('Waiting for the storekeeper');

    $l = $p->items->keyBy('inventory_item_id');
    $this->actingAs($store)->post(route('inventory.purchases.receive', $p), ['lines' => [
        $l[$onion->id]->id => ['received' => 2, 'rate' => 45], $l[$tomato->id]->id => ['received' => 3, 'rate' => 30],
    ]])->assertSessionHasNoErrors();
    $p->refresh();
    expect((float) $p->grand_total)->toBe(180.0)->and((float) $p->subtotal)->toBe(180.0)
        ->and((float) $p->items->firstWhere('inventory_item_id', $onion->id)->rate)->toBe(45.0);
    $this->get(route('inventory.purchases.show', $p))->assertDontSee('Prices not entered yet');
});

it('requires the billed rate for unpriced lines at receipt, and lets admins price lines already received at ₹0', function () {
    [$emp, $admin, $store, $onion] = pwSetup();
    $onion->update(['current_stock' => 8, 'average_cost' => 40]);
    $p = Purchase::create(['purchase_date' => now()->toDateString(), 'supplier_name' => 'DP Store', 'status' => 'approved',
        'created_by' => $emp->id, 'approved_by' => $admin->id, 'approved_at' => now(), 'subtotal' => 0, 'tax_amount' => 0,
        'discount_amount' => 0, 'grand_total' => 0, 'payment_status' => 'unpaid']);
    $line = $p->items()->create(['inventory_item_id' => $onion->id, 'quantity' => 2, 'unit' => 'kg', 'base_quantity' => 2,
        'rate' => 0, 'tax_percent' => 0, 'amount' => 0, 'received_base_quantity' => 0]);

    // one-tap "Received as ordered" without a rate is refused; the panel asks for it
    \Illuminate\Support\Facades\Storage::fake('private');
    $bill = fn () => \Illuminate\Http\UploadedFile::fake()->create('bill.pdf', 50, 'application/pdf');
    $this->actingAs($store)->get(route('inventory.purchases.show', $p))->assertSee('No price was entered when ordering');
    $this->post(route('inventory.purchases.receive', $p), ['mode' => 'all', 'bill_file' => $bill()])->assertSessionHasErrors("lines.{$line->id}");
    expect($onion->fresh()->current_stock)->toBe(8.0);
    $this->post(route('inventory.purchases.receive', $p), ['mode' => 'all', 'bill_file' => $bill(), 'lines' => [$line->id => ['rate' => 50]]])->assertSessionHasNoErrors();
    expect((float) $p->fresh()->grand_total)->toBe(100.0)->and($onion->fresh()->current_stock)->toBe(10.0);

    // an older purchase received at ₹0 (before this rule): admin enters the price afterwards
    $old = Purchase::create(['purchase_date' => now()->toDateString(), 'supplier_name' => 'DP Store', 'status' => 'received',
        'created_by' => $emp->id, 'subtotal' => 0, 'tax_amount' => 0, 'discount_amount' => 0, 'grand_total' => 0, 'payment_status' => 'unpaid']);
    $ol = $old->items()->create(['inventory_item_id' => $onion->id, 'quantity' => 2, 'unit' => 'kg', 'base_quantity' => 2,
        'rate' => 0, 'tax_percent' => 0, 'amount' => 0, 'received_base_quantity' => 2]);
    $this->actingAs($emp)->get(route('inventory.purchases.show', $old))->assertDontSee('Enter missing prices');
    $this->actingAs($emp)->post(route('inventory.purchases.prices', $old), ['rates' => [$ol->id => 45]])->assertForbidden();
    $this->actingAs($admin)->get(route('inventory.purchases.show', $old))->assertSee('Enter missing prices');
    $avg = (float) $onion->fresh()->average_cost;
    $this->post(route('inventory.purchases.prices', $old), ['rates' => [$ol->id => 45]])->assertSessionHasNoErrors();
    expect((float) $old->fresh()->grand_total)->toBe(90.0)->and((float) $ol->fresh()->rate)->toBe(45.0)
        ->and((float) $onion->fresh()->average_cost)->toBeGreaterThan($avg);
    $this->get(route('inventory.purchases.show', $old))->assertDontSee('Enter missing prices');
});

it('lets an admin undo a goods receipt: stock reversed, purchase reopened, record kept; refused once stock was used', function () {
    [$emp, $admin, $store, $onion] = pwSetup();
    $p = Purchase::create(['purchase_date' => now()->toDateString(), 'supplier_name' => 'DP Store', 'status' => 'approved',
        'created_by' => $emp->id, 'approved_by' => $admin->id, 'approved_at' => now(), 'subtotal' => 100, 'tax_amount' => 0,
        'discount_amount' => 0, 'grand_total' => 100, 'payment_status' => 'unpaid']);
    $line = $p->items()->create(['inventory_item_id' => $onion->id, 'quantity' => 2, 'unit' => 'kg', 'base_quantity' => 2,
        'rate' => 50, 'tax_percent' => 0, 'amount' => 100, 'received_base_quantity' => 0]);
    $this->actingAs($store)->post(route('inventory.purchases.receive', $p), ['lines' => [$line->id => ['received' => 2]]])->assertSessionHasNoErrors();
    $r = \App\Models\GoodsReceipt::firstOrFail();
    expect($onion->fresh()->current_stock)->toBe(2.0)->and($p->fresh()->status)->toBe('received');

    $this->actingAs($store)->post(route('inventory.purchases.receipts.undo', [$p, $r]), ['reason' => 'wrong'])->assertForbidden();
    $this->actingAs($admin)->get(route('inventory.purchases.show', $p))->assertSee('Undo this receipt');
    $this->post(route('inventory.purchases.receipts.undo', [$p, $r]), ['reason' => 'Received without checking prices'])->assertSessionHasNoErrors();

    expect($onion->fresh()->current_stock)->toBe(0.0)->and($p->fresh()->status)->toBe('approved')
        ->and($line->fresh()->received_base_quantity)->toBe(0.0)->and($r->fresh()->reversed_at)->not->toBeNull()
        ->and(\App\Models\InventoryTransaction::where('type', 'reversal')->count())->toBe(1);
    $this->get(route('inventory.purchases.show', $p))->assertSee('Undone')->assertSee('Received without checking prices');
    $this->post(route('inventory.purchases.receipts.undo', [$p, $r]), ['reason' => 'again'])->assertSessionHas('error');

    // receive again, use some stock, then undo is refused
    $this->actingAs($store)->post(route('inventory.purchases.receive', $p), ['lines' => [$line->id => ['received' => 2]]])->assertSessionHasNoErrors();
    $onion->update(['current_stock' => 1]);
    $r2 = \App\Models\GoodsReceipt::latest('id')->first();
    $this->actingAs($admin)->post(route('inventory.purchases.receipts.undo', [$p, $r2]), ['reason' => 'x again'])->assertSessionHas('error');
    expect($r2->fresh()->reversed_at)->toBeNull();
});
