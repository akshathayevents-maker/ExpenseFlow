<?php

use App\Models\MaterialRequest;
use App\Models\Recipe;
use App\Models\User;
use App\Services\Inventory\MaterialRequestService;
use App\Services\Inventory\RecipeStandardService;

function recordUpma(User $emp, User $admin, User $store, $rava, int $guests, float $issued, float $used): MaterialRequest
{
    $svc = app(MaterialRequestService::class);
    $r = $svc->save(['source' => 'other', 'department' => 'kitchen', 'purpose' => 'Breakfast', 'preparation_name' => 'Upma',
        'required_date' => now()->toDateString(), 'guest_count' => $guests,
        'lines' => [['inventory_item_id' => $rava->id, 'quantity' => $issued, 'unit' => 'kg']]], $emp, null, true);
    $svc->approve($r, $admin);
    $svc->handover($r, $store, $emp->id, null);
    $svc->reconcile($r, $emp, [$r->items->first()->id => ['consumed' => $used, 'wasted' => $issued - $used, 'wastage_reason' => 'leftover']]);

    return $r->fresh();
}

it('builds per-person history from recorded usage and saves versioned standards', function () {
    $emp = icUser();
    $admin = icUser('admin');
    $store = icUser('employee', true);
    $this->actingAs($admin);
    $rava = icItem('Rava', 'kg', 1000);
    recordUpma($emp, $admin, $store, $rava, 100, 20, 19);
    recordUpma($emp, $admin, $store, $rava, 250, 50, 48);
    recordUpma($emp, $admin, $store, $rava, 500, 100, 99);

    $svc = app(RecipeStandardService::class);
    $h = $svc->history('upma');
    expect($h['requests'])->toBe(3)->and($h['rows'][$rava->id]['median'])->toBe(0.2);

    $this->actingAs($emp)->get(route('inventory.standards.index'))->assertForbidden();
    $this->actingAs($admin)->get(route('inventory.standards.index'))->assertOk()->assertSee('Upma');
    $this->actingAs($admin)->get(route('inventory.standards.show', ['name' => 'Upma']))->assertOk()->assertSee('Rava');

    $this->actingAs($admin)->post(route('inventory.standards.store'), ['name' => 'Upma', 'lines' => [$rava->id => 0.2]])->assertSessionHasNoErrors();
    $v1 = Recipe::where('name', 'Upma')->first();
    expect($v1->version)->toBe(1);

    // suggestion for 250 people is 50 kg, and a request remembers it
    expect($svc->suggest($v1->id, 250)['lines'][0]['quantity'])->toBe(50.0);
    $this->actingAs($emp)->get(route('inventory.material-requests.create', ['recipe_id' => $v1->id, 'guest_count' => 250]))
        ->assertOk()->assertSee('suggested from');
    $this->actingAs($emp)->post(route('inventory.material-requests.store'), [
        'source' => 'other', 'department' => 'kitchen', 'purpose' => 'x', 'required_date' => now()->toDateString(),
        'preparation_name' => 'Upma', 'guest_count' => 250, 'recipe_id' => $v1->id,
        'lines' => [['inventory_item_id' => $rava->id, 'quantity' => 55, 'unit' => 'kg']],   // staff override
    ])->assertSessionHasNoErrors();
    $req = MaterialRequest::latest('id')->first();
    expect($req->recipe_version)->toBe(1)->and($req->items->first()->suggested_base_quantity)->toBe(50.0)
        ->and($req->items->first()->requested_base_quantity)->toBe(55.0);

    // new version never rewrites old requests
    $this->actingAs($admin)->post(route('inventory.standards.store'), ['name' => 'Upma', 'lines' => [$rava->id => 0.21]]);
    $v2 = Recipe::where('name', 'Upma')->orderByDesc('version')->first();
    expect($v2->version)->toBe(2)->and($v2->parent_recipe_id)->toBe($v1->id)
        ->and($v1->fresh()->superseded_at)->not->toBeNull()
        ->and($v1->fresh()->ingredients->first()->quantity_per_guest)->toEqual(0.2)
        ->and($req->fresh()->recipe_version)->toBe(1)
        ->and($req->fresh()->items->first()->suggested_base_quantity)->toBe(50.0);
});
