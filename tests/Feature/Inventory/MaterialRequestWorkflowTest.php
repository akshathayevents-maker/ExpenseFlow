<?php

use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Models\InventoryTransaction;
use App\Models\MaterialRequest;
use App\Models\User;
use App\Services\Inventory\MaterialRequestService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

function mrPayload(array $lines, array $extra = []): array
{
    return array_merge([
        'source' => 'other', 'department' => 'kitchen', 'purpose' => 'Staff breakfast',
        'required_date' => now('Asia/Kolkata')->addDay()->toDateString(),
        'preparation_name' => 'Upma', 'guest_count' => 250, 'lines' => $lines,
    ], $extra);
}

function mrSubmitted(User $emp, array $lines): MaterialRequest
{
    test()->actingAs($emp)->post(route('inventory.material-requests.store'), mrPayload($lines, ['action' => 'submit']))->assertSessionHasNoErrors();

    return MaterialRequest::latest('id')->first();
}

it('lets a kitchen employee request 20+ materials in one go', function () {
    $emp = icUser();
    $lines = [];
    foreach (range(1, 22) as $n) {
        $lines[] = ['inventory_item_id' => icItem("Item {$n}", 'kg', 100)->id, 'quantity' => 1.5, 'unit' => 'kg'];
    }
    $mr = mrSubmitted($emp, $lines);
    expect($mr->status)->toBe('submitted')->and($mr->items)->toHaveCount(22)
        ->and(AppNotification::where('type', 'material_submitted')->exists())->toBeFalse(); // no admins yet
    expect(InventoryTransaction::count())->toBe(0);
});

it('rejects the same material twice in one request', function () {
    $rava = icItem('Rava', 'kg', 100);
    $this->actingAs(icUser())->post(route('inventory.material-requests.store'), mrPayload([
        ['inventory_item_id' => $rava->id, 'quantity' => 5, 'unit' => 'kg'],
        ['inventory_item_id' => $rava->id, 'quantity' => 2, 'unit' => 'kg'],
    ]))->assertSessionHasErrors('lines.1.inventory_item_id');
});

it('deducts stock on approval, supports partial approval and line rejection', function () {
    $emp = icUser();
    $admin = icUser('admin');
    $rava = icItem('Rava', 'kg', 100);
    $oil = icItem('Cooking Oil', 'litre', 10);
    $mustard = icItem('Mustard Seeds', 'kg', 2);
    $mr = mrSubmitted($emp, [
        ['inventory_item_id' => $rava->id, 'quantity' => 50, 'unit' => 'kg'],
        ['inventory_item_id' => $oil->id, 'quantity' => 1, 'unit' => 'litre'],
        ['inventory_item_id' => $mustard->id, 'quantity' => 250, 'unit' => 'gram'],
    ]);
    [$lRava, $lOil, $lMustard] = $mr->items->all();

    $this->actingAs($emp)->post(route('inventory.material-requests.approve', $mr))->assertForbidden();
    $this->actingAs($admin)->post(route('inventory.material-requests.approve', $mr), ['lines' => [
        $lRava->id => ['quantity' => 45, 'remark' => 'Use 45'],
        $lOil->id => ['quantity' => 0, 'remark' => 'Oil from yesterday'],
    ]])->assertSessionHasNoErrors();

    $mr->refresh();
    expect($mr->status)->toBe('approved')->and($mr->approved_by)->toBe($admin->id)
        ->and($rava->fresh()->current_stock)->toBe(55.0)
        ->and($oil->fresh()->current_stock)->toBe(10.0)
        ->and($mustard->fresh()->current_stock)->toBe(1.75);
    expect($lRava->fresh()->line_status)->toBe('reduced')->and($lOil->fresh()->line_status)->toBe('rejected')
        ->and($lMustard->fresh()->approved_base_quantity)->toBe(0.25);
    expect(InventoryTransaction::where('type', 'material_issue')->where('reference_id', $mr->id)->count())->toBe(2);
});

it('refuses an approval that would make stock negative and deducts nothing', function () {
    $emp = icUser();
    $admin = icUser('admin');
    $rava = icItem('Rava', 'kg', 100);
    $onion = icItem('Onion', 'kg', 3);
    $mr = mrSubmitted($emp, [
        ['inventory_item_id' => $rava->id, 'quantity' => 50, 'unit' => 'kg'],
        ['inventory_item_id' => $onion->id, 'quantity' => 5, 'unit' => 'kg'],
    ]);
    $this->actingAs($admin)->post(route('inventory.material-requests.approve', $mr))->assertSessionHasErrors('approval');
    expect($mr->fresh()->status)->toBe('submitted')->and($rava->fresh()->current_stock)->toBe(100.0)
        ->and(InventoryTransaction::count())->toBe(0);
});

it('prevents two approvals from consuming the same stock', function () {
    $emp = icUser();
    $admin = icUser('admin');
    $paneer = icItem('Paneer', 'kg', 10);
    $a = mrSubmitted($emp, [['inventory_item_id' => $paneer->id, 'quantity' => 7, 'unit' => 'kg']]);
    $b = mrSubmitted($emp, [['inventory_item_id' => $paneer->id, 'quantity' => 7, 'unit' => 'kg']]);
    $svc = app(MaterialRequestService::class);
    $svc->approve($a, $admin);
    expect(fn () => $svc->approve($b, $admin))->toThrow(ValidationException::class);
    expect($paneer->fresh()->current_stock)->toBe(3.0)->and($b->fresh()->status)->toBe('submitted');
    // the same request cannot be approved twice either
    expect(fn () => $svc->approve($a->fresh(), $admin))->toThrow(RuntimeException::class);
    expect($paneer->fresh()->current_stock)->toBe(3.0);
});

it('records handover separately without a second deduction; short handover restores the gap', function () {
    $emp = icUser();
    $admin = icUser('admin');
    $store = icUser('employee', true);
    $rava = icItem('Rava', 'kg', 100);
    $chilli = icItem('Green Chilli', 'kg', 5);
    $mr = mrSubmitted($emp, [
        ['inventory_item_id' => $rava->id, 'quantity' => 50, 'unit' => 'kg'],
        ['inventory_item_id' => $chilli->id, 'quantity' => 1, 'unit' => 'kg'],
    ]);
    $this->actingAs($admin)->post(route('inventory.material-requests.approve', $mr));
    [$lRava, $lChilli] = $mr->items->all();

    $this->actingAs($emp)->post(route('inventory.material-requests.handover', $mr), ['collected_by' => $emp->id])->assertForbidden();
    $this->actingAs($store)->post(route('inventory.material-requests.handover', $mr), ['issued' => [$lRava->id => 50]])
        ->assertSessionHasErrors('collected_by');
    $this->actingAs($store)->post(route('inventory.material-requests.handover', $mr), [
        'collected_by' => $emp->id, 'issued' => [$lRava->id => 48],
    ])->assertSessionHasNoErrors();

    $mr->refresh();
    expect($mr->status)->toBe('issued')->and($mr->handed_over_by)->toBe($store->id)->and($mr->collected_by)->toBe($emp->id)
        ->and($mr->handed_over_at)->not->toBeNull()
        ->and($rava->fresh()->current_stock)->toBe(52.0)   // 100 − 50 + 2 reversed
        ->and($chilli->fresh()->current_stock)->toBe(4.0)
        ->and(InventoryTransaction::where('type', 'material_issue')->count())->toBe(2)
        ->and(InventoryTransaction::where('type', 'reversal')->first()->quantity)->toBe(2.0);
    // handing over again is impossible
    $this->actingAs($store)->post(route('inventory.material-requests.handover', $mr), ['collected_by' => $emp->id])->assertForbidden();
});

it('restores stock through a reversal when an approved request is cancelled before handover', function () {
    $emp = icUser();
    $admin = icUser('admin');
    $onion = icItem('Onion', 'kg', 20);
    $mr = mrSubmitted($emp, [['inventory_item_id' => $onion->id, 'quantity' => 8, 'unit' => 'kg']]);
    $this->actingAs($admin)->post(route('inventory.material-requests.approve', $mr));
    expect($onion->fresh()->current_stock)->toBe(12.0);

    $this->actingAs($emp)->post(route('inventory.material-requests.cancel', $mr), ['reason' => 'Event moved'])->assertForbidden();
    $this->actingAs($admin)->post(route('inventory.material-requests.cancel', $mr), ['reason' => 'Event moved'])->assertSessionHasNoErrors();

    $issue = InventoryTransaction::where('type', 'material_issue')->first();
    $rev = InventoryTransaction::where('type', 'reversal')->first();
    expect($mr->fresh()->status)->toBe('cancelled')->and($onion->fresh()->current_stock)->toBe(20.0)
        ->and($rev->reversal_of_id)->toBe($issue->id)->and($issue->fresh()->quantity)->toBe(8.0);
});

it('records returns after handover and reconciles issued = returned + used + wasted + unexplained', function () {
    $emp = icUser();
    $admin = icUser('admin');
    $store = icUser('employee', true);
    $rava = icItem('Rava', 'kg', 100);
    $mr = mrSubmitted($emp, [['inventory_item_id' => $rava->id, 'quantity' => 55, 'unit' => 'kg']]);
    $this->actingAs($admin)->post(route('inventory.material-requests.approve', $mr));
    $line = $mr->items->first();
    $this->actingAs($store)->post(route('inventory.material-requests.handover', $mr), ['collected_by' => $emp->id]);

    $this->actingAs($store)->post(route('inventory.material-requests.returns', $mr), ['returns' => [$line->id => 60]])->assertSessionHasErrors();
    $this->actingAs($store)->post(route('inventory.material-requests.returns', $mr), ['returns' => [$line->id => 2]])->assertSessionHasNoErrors();
    expect($rava->fresh()->current_stock)->toBe(47.0)->and(InventoryTransaction::where('type', 'material_return')->count())->toBe(1);

    // wastage without reason is refused; over-accounting refused
    $this->actingAs($emp)->post(route('inventory.material-requests.reconcile', $mr), ['lines' => [$line->id => ['consumed' => 50, 'wasted' => 3]]])
        ->assertSessionHasErrors();
    $this->actingAs($emp)->post(route('inventory.material-requests.reconcile', $mr), ['lines' => [$line->id => ['consumed' => 52, 'wasted' => 3, 'wastage_reason' => 'x']]])
        ->assertSessionHasErrors();

    $this->actingAs($emp)->post(route('inventory.material-requests.reconcile', $mr), ['lines' => [$line->id => [
        'consumed' => 50, 'wasted' => 3, 'wastage_reason' => 'Burnt bottom of vessel',
    ]]])->assertSessionHasNoErrors();
    $line->refresh();
    expect($line->consumed_base_quantity)->toBe(50.0)->and($line->wasted_base_quantity)->toBe(3.0)
        ->and($line->unexplained_base_quantity)->toBe(0.0)
        ->and($line->issued_base_quantity)->toBe($line->returned_base_quantity + 50.0 + 3.0 + 0.0)
        ->and($mr->fresh()->status)->toBe('reconciled')
        ->and($mr->fresh()->reconciliation_status)->toBe('accepted'); // 3/55 = 5.5% < 10%
});

it('sends unexplained differences and high wastage to admin review with mandatory remarks', function () {
    $emp = icUser();
    $admin = icUser('admin');
    $store = icUser('employee', true);
    $onion = icItem('Onion', 'kg', 50);
    $mr = mrSubmitted($emp, [['inventory_item_id' => $onion->id, 'quantity' => 8, 'unit' => 'kg']]);
    $this->actingAs($admin)->post(route('inventory.material-requests.approve', $mr));
    $this->actingAs($store)->post(route('inventory.material-requests.handover', $mr), ['collected_by_name' => 'Helper Ravi']);
    $line = $mr->items->first();

    $this->actingAs($emp)->post(route('inventory.material-requests.reconcile', $mr), ['lines' => [$line->id => ['consumed' => 5]]])
        ->assertSessionHasErrors();
    $this->actingAs($emp)->post(route('inventory.material-requests.reconcile', $mr), ['lines' => [$line->id => ['consumed' => 5, 'variance_reason' => 'Not sure']]])
        ->assertSessionHasNoErrors();
    $mr->refresh();
    expect($mr->reconciliation_status)->toBe('pending_review')->and($mr->items->first()->unexplained_base_quantity)->toBe(3.0)
        ->and($mr->collected_by_name)->toBe('Helper Ravi');
    expect(AppNotification::where('user_id', $admin->id)->where('type', 'material_review')->exists())->toBeTrue();

    $this->actingAs($emp)->post(route('inventory.material-requests.review', $mr), ['remarks' => 'ok'])->assertForbidden();
    $this->actingAs($admin)->post(route('inventory.material-requests.review', $mr), ['remarks' => 'Checked with cook'])->assertSessionHasNoErrors();
    expect($mr->fresh()->reconciliation_status)->toBe('accepted');
});

it('keeps an audit trail and enforces who sees what', function () {
    $emp = icUser();
    $other = icUser();
    $admin = icUser('admin');
    $rava = icItem('Rava', 'kg', 100);
    $mr = mrSubmitted($emp, [['inventory_item_id' => $rava->id, 'quantity' => 5, 'unit' => 'kg']]);
    $this->actingAs($other)->get(route('inventory.material-requests.show', $mr))->assertForbidden();
    $this->actingAs($emp)->get(route('inventory.material-requests.show', $mr))->assertOk();
    $this->actingAs($admin)->get(route('inventory.material-requests.show', $mr))->assertOk()->assertSee('Approve materials');
    $this->actingAs($admin)->post(route('inventory.material-requests.approve', $mr));
    expect(AuditLog::where('module', 'material_request')->pluck('action')->all())->toContain('created', 'submitted', 'approved');
});

it('renders the store pages for each role', function () {
    $emp = icUser();
    $admin = icUser('admin');
    $store = icUser('employee', true);
    icItem('Rava', 'kg', 10);
    $this->actingAs($emp)->get(route('inventory.home'))->assertOk()->assertSee('Request materials')->assertDontSee('Store tasks');
    $this->actingAs($store)->get(route('inventory.home'))->assertOk()->assertSee('Ready for handover');
    $this->actingAs($admin)->get(route('inventory.home'))->assertOk()->assertSee('Material approvals');
    $this->actingAs($emp)->get(route('inventory.material-requests.create'))->assertOk();
    $this->actingAs($emp)->get(route('inventory.material-requests.index'))->assertOk();
    $this->actingAs($emp)->get(route('inventory.stock'))->assertForbidden();
    $this->actingAs($store)->get(route('inventory.stock'))->assertOk()->assertSee('Rava');
});

it('ignores a double-posted request form', function () {
    $emp = icUser();
    $rava = icItem('Rava', 'kg', 10);
    $p = mrPayload([['inventory_item_id' => $rava->id, 'quantity' => 1, 'unit' => 'kg']], ['client_token' => (string) Str::uuid()]);
    $this->actingAs($emp)->post(route('inventory.material-requests.store'), $p);
    $this->actingAs($emp)->post(route('inventory.material-requests.store'), $p);
    expect(MaterialRequest::count())->toBe(1);
});

it('lets an admin mark an employee as storekeeper (audited)', function () {
    $admin = icUser('admin');
    $emp = icUser();
    $this->actingAs($admin)->put(route('admin.employees.update', $emp), [
        'name' => $emp->name, 'email' => $emp->email, 'role' => 'employee', 'is_active' => 1, 'is_storekeeper' => 1,
    ])->assertSessionHasNoErrors();
    expect($emp->fresh()->is_storekeeper)->toBeTrue()->and($emp->fresh()->isStorekeeper())->toBeTrue()
        ->and(\App\Models\AuditLog::where('action', 'storekeeper_changed')->exists())->toBeTrue();
});
