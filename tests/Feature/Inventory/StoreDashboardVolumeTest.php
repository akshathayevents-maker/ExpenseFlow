<?php

use App\Models\InventoryCategory;
use Illuminate\Support\Facades\DB;

/**
 * Store dashboard and the Stock page at volume: thousands of items, requests, purchases and
 * stock movements must not add queries (no N+1) and must stay fast.
 */
function volumeSeed(int $items = 3000, int $requests = 2000, int $purchases = 800, int $moves = 5000): array
{
    $admin = icUser('admin');
    $cat = InventoryCategory::create(['name' => 'Groceries', 'is_active' => true]);
    $now = now()->toDateTimeString();
    foreach (array_chunk(range(1, $items), 500) as $chunk) {
        DB::table('inventory_items')->insert(array_map(fn ($i) => [
            'name' => "Item $i", 'inventory_category_id' => $cat->id, 'unit' => 'kg', 'status' => 'active',
            'current_stock' => $i % 7 === 0 ? 0 : $i % 50, 'minimum_stock' => $i % 3 === 0 ? 10 : 0,
            'average_cost' => 40, 'created_at' => $now, 'updated_at' => $now,
        ], $chunk));
    }
    $ids = DB::table('inventory_items')->pluck('id')->all();
    $statuses = ['submitted', 'approved', 'issued', 'reconciled', 'cancelled'];
    foreach (array_chunk(range(1, $requests), 400) as $chunk) {
        DB::table('material_requests')->insert(array_map(fn ($i) => [
            'requested_by' => $admin->id, 'status' => $statuses[$i % 5], 'source' => 'other', 'department' => 'kitchen',
            'purpose' => 'Volume', 'preparation_name' => 'Dish '.($i % 40), 'guest_count' => 100,
            'required_date' => now()->addDays($i % 30 - 10)->toDateString(), 'created_at' => $now, 'updated_at' => $now,
        ], $chunk));
    }
    $mr = DB::table('material_requests')->pluck('id')->all();
    foreach (array_chunk($mr, 300) as $chunk) {
        $rows = [];
        foreach ($chunk as $m) {
            foreach ([0, 1, 2] as $k) {
                $rows[] = ['material_request_id' => $m, 'inventory_item_id' => $ids[($m * 3 + $k) % count($ids)], 'line_status' => 'pending',
                    'requested_quantity' => 2, 'requested_unit' => 'kg', 'requested_base_quantity' => 2, 'created_at' => $now, 'updated_at' => $now];
            }
        }
        DB::table('material_request_items')->insert($rows);
    }
    foreach (array_chunk(range(1, $purchases), 400) as $chunk) {
        DB::table('purchases')->insert(array_map(fn ($i) => [
            'supplier_name' => 'Supplier '.($i % 20), 'purchase_date' => now()->subDays($i % 60)->toDateString(),
            'status' => ['draft', 'approved', 'partially_received', 'received'][$i % 4], 'created_by' => $admin->id,
            'grand_total' => 1000, 'created_at' => $now, 'updated_at' => $now,
        ], $chunk));
    }
    foreach (array_chunk(range(1, $moves), 1000) as $chunk) {
        DB::table('inventory_transactions')->insert(array_map(fn ($i) => [
            'inventory_item_id' => $ids[$i % count($ids)], 'type' => $i % 2 ? 'stock_in' : 'stock_out', 'quantity' => 1,
            'balance_before' => 5, 'balance_after' => $i % 2 ? 6 : 4, 'created_by' => $admin->id, 'created_at' => $now, 'updated_at' => $now,
        ], $chunk));
    }

    return [$admin];
}

it('keeps the store dashboard flat and fast with thousands of rows', function () {
    [$admin] = volumeSeed();
    DB::enableQueryLog();
    $t = microtime(true);
    $this->actingAs($admin)->get(route('inventory.home'))->assertOk()->assertSee('data-kpi', false);
    $ms = (microtime(true) - $t) * 1000;
    $queries = count(DB::getQueryLog());

    expect($queries)->toBeLessThanOrEqual(26)   // same budget as with an empty store (no per-row queries)
        ->and($ms)->toBeLessThan(2500);
});

it('keeps the stock page usable with thousands of items', function () {
    [$admin] = volumeSeed(3000, 300, 100, 100);
    DB::enableQueryLog();
    $t = microtime(true);
    $html = $this->actingAs($admin)->get(route('inventory.stock', ['show' => 'low']))->assertOk()->getContent();
    $ms = (microtime(true) - $t) * 1000;

    expect(count(DB::getQueryLog()))->toBeLessThanOrEqual(20)->and($ms)->toBeLessThan(4000)
        ->and($html)->toContain('data-mode="server"')
        ->and(substr_count($html, 'data-item '))->toBe(60)          // one page, not 3000 rows
        ->and(strlen($html))->toBeLessThan(400 * 1024);
    $this->get(route('inventory.stock', ['show' => 'low', 'page' => 2]))->assertOk();
});
