<?php

use App\Models\AppNotification;
use App\Models\MaterialRequest;
use App\Services\Inventory\RecipeStandardService;

function wcRequest(array $lines, string $prep = 'Upma', int $people = 100): MaterialRequest
{
    $emp = icUser();
    test()->actingAs($emp)->post(route('inventory.material-requests.store'), [
        'source' => 'other', 'department' => 'kitchen', 'required_date' => now('Asia/Kolkata')->addDay()->toDateString(), 'action' => 'submit',
        'preparations' => [['name' => $prep, 'people' => $people]], 'lines' => $lines,
    ])->assertSessionHasNoErrors();

    return MaterialRequest::latest('id')->first();
}

it('shows the recipe standard at approval and flags quantities far above it', function () {
    $admin = icUser('admin');
    $rava = icItem('Rava', 'kg', 100);
    $this->actingAs($admin);
    app(RecipeStandardService::class)->saveStandard('Upma', [$rava->id => 0.05], $admin);   // 50 g per person
    $mr = wcRequest([['inventory_item_id' => $rava->id, 'quantity' => 8, 'unit' => 'kg']]);   // standard for 100 = 5 kg

    expect(app(RecipeStandardService::class)->expectedFor($mr)[$rava->id]['qty'])->toEqual(5.0);
    $this->actingAs($admin)->get(route('inventory.material-requests.show', $mr))->assertOk()
        ->assertSee('Standard: 5 kg')->assertSee('+60% over')->assertSee('Approve at standard')->assertSee('data-std="5"', false);
});

it('sends usage far above the standard to admin review even without wastage', function () {
    $admin = icUser('admin');
    $rava = icItem('Rava', 'kg', 100);
    $this->actingAs($admin);
    app(RecipeStandardService::class)->saveStandard('Upma', [$rava->id => 0.05], $admin);
    $mr = wcRequest([['inventory_item_id' => $rava->id, 'quantity' => 8, 'unit' => 'kg']]);
    $this->actingAs($admin)->post(route('inventory.material-requests.approve', $mr))->assertSessionHasNoErrors();
    $line = $mr->items->first();

    $this->actingAs($mr->requester)->get(route('inventory.material-requests.show', $mr))->assertOk()
        ->assertSee('Why wasted?')->assertSee('Left over')->assertSee('data-std="5"', false);
    $this->post(route('inventory.material-requests.reconcile', $mr), ['lines' => [$line->id => ['consumed' => 8, 'wasted' => 0]]])
        ->assertSessionHasNoErrors();

    expect($mr->fresh()->reconciliation_status)->toBe('pending_review');
    expect(AppNotification::where('user_id', $admin->id)->where('type', 'material_review')->latest('id')->first()->body)
        ->toContain('60% more than standard');
});

it('accepts usage within the standard and still refuses used + wasted above what was had', function () {
    $admin = icUser('admin');
    $rava = icItem('Rava', 'kg', 100);
    $this->actingAs($admin);
    app(RecipeStandardService::class)->saveStandard('Upma', [$rava->id => 0.05], $admin);
    $mr = wcRequest([['inventory_item_id' => $rava->id, 'quantity' => 5, 'unit' => 'kg']]);
    $this->actingAs($admin)->post(route('inventory.material-requests.approve', $mr));
    $line = $mr->items->first();

    $this->actingAs($mr->requester)->post(route('inventory.material-requests.reconcile', $mr), ['lines' => [$line->id => ['consumed' => 5, 'wasted' => 0.5, 'wastage_reason' => 'Burnt']]])
        ->assertSessionHasErrors("lines.{$line->id}");
    $this->post(route('inventory.material-requests.reconcile', $mr), ['lines' => [$line->id => ['consumed' => 4.8, 'wasted' => 0.2, 'wastage_reason' => 'Burnt']]])
        ->assertSessionHasNoErrors();
    expect($mr->fresh()->reconciliation_status)->toBe('accepted');   // 4% waste, at standard
});
