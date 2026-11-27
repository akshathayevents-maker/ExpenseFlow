<?php

use App\Models\PurchasePlan;
use Illuminate\Support\Facades\DB;

function pplPlan(string $title, string $status = 'draft', array $items = [], string $date = '2026-12-10'): PurchasePlan
{
    $p = PurchasePlan::create(['title' => $title, 'planned_date' => $date, 'status' => $status, 'created_by' => icUser('admin')->id]);
    foreach ($items as [$qty, $cost, $prio]) {
        $p->items()->create(['inventory_item_id' => icItem($title.' item '.$qty.$prio)->id, 'suggested_quantity' => $qty, 'estimated_unit_cost' => $cost, 'priority' => $prio]);
    }

    return $p;
}

it('lists plans with SQL-computed estimate, urgent count and status strip', function () {
    pplPlan('Weekly groceries', 'draft', [[10, 50, 'urgent'], [2, 100, 'normal']]);
    pplPlan('Diwali stock', 'approved');

    $html = $this->actingAs(icUser('admin'))->get(route('admin.purchase-plans.index'))->assertOk()->getContent();
    expect(substr_count($html, 'data-plan'))->toBe(2)
        ->and($html)->toContain('₹700')->and($html)->toContain('1 urgent')->and($html)->toContain('No estimate');

    $html = $this->actingAs(icUser('admin'))->get(route('admin.purchase-plans.index', ['status' => 'approved']))->getContent();
    expect(substr_count($html, 'data-plan'))->toBe(1)->and($html)->toContain('Diwali stock');
});

it('searches by title, filters by planned date and validates input', function () {
    pplPlan('Weekly groceries', 'draft', [], '2026-12-01');
    pplPlan('Diwali stock', 'draft', [], '2026-12-20');
    $admin = icUser('admin');
    expect(substr_count($this->actingAs($admin)->get(route('admin.purchase-plans.index', ['q' => 'diwali']))->getContent(), 'data-plan'))->toBe(1)
        ->and(substr_count($this->actingAs($admin)->get(route('admin.purchase-plans.index', ['from' => '2026-12-10']))->getContent(), 'data-plan'))->toBe(1);
    $this->actingAs($admin)->get(route('admin.purchase-plans.index', ['from' => 'not-a-date']))->assertSessionHasErrors('from');
});

it('shows what needs buying, blocks non-admins and keeps queries flat', function () {
    icItem('Toor Dal', 'kg', 1, 10);
    foreach (range(1, 8) as $i) {
        pplPlan("Plan $i", 'draft', [[1, 10, 'normal']]);
    }
    $this->actingAs(icUser('manager'))->get(route('admin.purchase-plans.index'))->assertForbidden();
    DB::enableQueryLog();
    $this->actingAs(icUser('admin'))->get(route('admin.purchase-plans.index'))->assertOk()->assertSee('Needs buying')->assertSee('Toor Dal');
    expect(count(DB::getQueryLog()))->toBeLessThanOrEqual(16);
});
