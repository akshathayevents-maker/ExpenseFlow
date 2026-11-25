<?php

use App\Models\PurchasePlan;
use App\Models\PurchasePlanItem;

function ppPlan(string $status, string $title = 'Weekly plan'): PurchasePlan
{
    $admin = icUser('admin');
    $plan = PurchasePlan::create(['title' => $title, 'planned_date' => now()->toDateString(), 'status' => $status, 'created_by' => $admin->id]);
    PurchasePlanItem::create(['purchase_plan_id' => $plan->id, 'inventory_item_id' => icItem('Rice '.$title)->id,
        'suggested_quantity' => 5, 'estimated_unit_cost' => 40, 'priority' => 'high']);

    return $plan;
}

it('renders every purchase plan page in the standard store design', function () {
    $plan = ppPlan('draft');
    $this->actingAs(icUser('admin'));
    foreach ([route('admin.purchase-plans.index'), route('admin.purchase-plans.suggestions'),
        route('admin.purchase-plans.create'), route('admin.purchase-plans.show', $plan)] as $url) {
        $this->get($url)->assertOk()->assertSee('ic-wrap', false)->assertDontSee('ef-pp-hero', false)->assertDontSee('confirm(', false);
    }
    $this->get(route('admin.purchase-plans.show', $plan))->assertSee('₹200.00')->assertSee('Approve plan');
});

it('filters plans by status', function () {
    ppPlan('draft', 'Draft one');
    ppPlan('ordered', 'Ordered one');
    $this->actingAs(icUser('admin'));
    $this->get(route('admin.purchase-plans.index', ['status' => 'ordered']))->assertOk()
        ->assertSee('Ordered one')->assertDontSee('Draft one');
    $this->get(route('admin.purchase-plans.index', ['status' => 'bogus']))->assertOk()
        ->assertSee('Ordered one')->assertSee('Draft one');
});

it('only allows valid plan status changes', function () {
    $done = ppPlan('completed', 'Done');
    $draft = ppPlan('draft', 'Fresh');
    $this->actingAs(icUser('admin'));
    $this->patch(route('admin.purchase-plans.status', $done), ['status' => 'cancelled'])->assertSessionHas('error');
    $this->patch(route('admin.purchase-plans.status', $draft), ['status' => 'completed'])->assertSessionHas('error');
    expect($done->fresh()->status)->toBe('completed')->and($draft->fresh()->status)->toBe('draft');
    $this->get(route('admin.purchase-plans.show', $done))->assertDontSee('Cancel plan');

    $this->patch(route('admin.purchase-plans.status', $draft), ['status' => 'cancelled'])->assertSessionHas('success');
    expect($draft->fresh()->status)->toBe('cancelled');
});
