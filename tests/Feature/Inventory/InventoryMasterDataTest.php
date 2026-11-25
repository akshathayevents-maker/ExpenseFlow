<?php

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryItemAlias;
use App\Models\InventoryTransaction;
use App\Models\Purchase;
use App\Services\Inventory\ItemCatalogueService;
use App\Services\Inventory\ItemSearchService;
use App\Services\InventoryService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\InventoryCatalogueSeeder;
use Illuminate\Support\Str;

function masterSeed(): InventoryCatalogueSeeder
{
    $s = app(InventoryCatalogueSeeder::class);
    $s->run(app(ItemCatalogueService::class));
    ItemSearchService::flush();

    return $s;
}

function masterPayload(array $lines, array $extra = []): array
{
    return array_merge(['purchase_date' => now()->toDateString(), 'supplier_name' => 'Local Supplier', 'lines' => $lines], $extra);
}

function masterSearch(string $q): array
{
    return app(ItemSearchService::class)->search($q, 15);
}

function masterExact(string $q): ?string
{
    $r = masterSearch($q);

    return $r['exact_id'] ? InventoryItem::find($r['exact_id'])->name : null;
}

beforeEach(fn () => ItemSearchService::flush());

it('seeds the full bilingual master: every item has a Tamil name, a category and at least one alias', function () {
    $s = masterSeed();
    $data = require database_path('seeders/data/kitchen_catalogue.php');
    $expected = array_sum(array_map('count', $data));

    expect(InventoryItem::count())->toBe($expected)->and($expected)->toBeGreaterThan(270)
        ->and(InventoryCategory::count())->toBe(count($data))
        ->and(InventoryItem::whereNull('name_ta')->count())->toBe(0)
        ->and(InventoryItem::where('status', '!=', 'active')->count())->toBe(0)
        ->and(InventoryItem::doesntHave('aliases')->pluck('name')->all())->toBe([])
        ->and($s->stats['conflicts'])->toBe([]);
    // no duplicate items by name
    expect(InventoryItem::selectRaw('LOWER(name) n')->groupBy('n')->havingRaw('COUNT(*) > 1')->count())->toBe(0);
});

it('maps categories and default units correctly', function (string $name, string $category, string $unit) {
    masterSeed();
    $item = InventoryItem::with('category')->where('name', $name)->firstOrFail();
    expect($item->category->name)->toBe($category)->and($item->unit)->toBe($unit);
})->with([
    ['Raw Rice', 'Rice & Grains', 'kg'],
    ['Toor Dal', 'Pulses & Dals', 'kg'],
    ['Onion', 'Vegetables', 'kg'],
    ['Turmeric Powder', 'Spice Powders & Masalas', 'kg'],
    ['Sunflower Oil', 'Oils & Ghee', 'litre'],
    ['Milk', 'Dairy', 'litre'],
    ['Eggs', 'Meat & Poultry', 'piece'],
    ['Bread', 'Other Kitchen Supplies', 'packet'],
    ['Salt', 'Sugar, Salt & Sweeteners', 'kg'],
    ['Coconut', 'Vegetables', 'piece'],
]);

it('is idempotent and adds nothing on a second run', function () {
    masterSeed();
    $items = InventoryItem::count();
    $aliases = InventoryItemAlias::count();
    $s = masterSeed();
    expect(InventoryItem::count())->toBe($items)->and(InventoryItemAlias::count())->toBe($aliases)
        ->and($s->stats['items_created'])->toBe(0)->and($s->stats['aliases_created'])->toBe(0);
});

it('is registered in DatabaseSeeder', function () {
    expect(file_get_contents(database_path('seeders/DatabaseSeeder.php')))->toContain('InventoryCatalogueSeeder::class');
    expect(class_exists(DatabaseSeeder::class))->toBeTrue();
});

it('preserves existing items, stock and history', function () {
    $rice = icItem('Rice', 'kg', 0);
    app(InventoryService::class)->addStock($rice, 40, 'Opening', icUser('admin'), 55, 'purchase');
    $rice->refresh();
    $before = [(float) $rice->current_stock, (float) $rice->average_cost, $rice->inventory_category_id, InventoryTransaction::count()];

    masterSeed();
    $rice->refresh();
    expect(InventoryItem::where('name', 'Rice')->count())->toBe(1)
        ->and([(float) $rice->current_stock, (float) $rice->average_cost, $rice->inventory_category_id, InventoryTransaction::count()])->toBe($before)
        ->and($rice->name_ta)->toBe('அரிசி');
});

it('resolves English, Tamil and Tanglish to one item', function (string $q, string $expected) {
    masterSeed();
    expect(masterExact($q))->toBe($expected);
})->with([
    ['Toor Dal', 'Toor Dal'], ['tur dal', 'Toor Dal'], ['arhar dal', 'Toor Dal'],
    ['துவரம் பருப்பு', 'Toor Dal'], ['துவரம்பருப்பு', 'Toor Dal'],
    ['thuvaram paruppu', 'Toor Dal'], ['parupu', 'Toor Dal'], ['parpu', 'Toor Dal'],
    ['arisi', 'Rice'], ['அரிசி', 'Rice'],
    ['uppu', 'Salt'], ['upp', 'Salt'], ['உப்பு', 'Salt'], ['கல்லுப்பு', 'Crystal Salt'],
    ['sambar podi', 'Sambar Powder'], ['sambhar powder', 'Sambar Powder'], ['சாம்பார் பொடி', 'Sambar Powder'],
    ['rasam podi', 'Rasam Powder'], ['ரசம் பொடி', 'Rasam Powder'],
    ['kulambu podi', 'Kulambu Powder'], ['sukku podi', 'Dry Ginger Powder'],
    ['semolina', 'Rava'], ['poha', 'Rice Flakes'], ['split moong dal', 'Moong Dal'],
    ['பச்சைப்பயறு', 'Green Gram Whole'], ['peanuts', 'Groundnut'], ['நிலக்கடலை', 'Groundnut'],
    ['rose water', 'Rose Water'], ['paneer', 'Paneer'],
    ['meen', 'Fish'], ['dosai arisi', 'Dosa Rice'], ['thengai paal', 'Coconut Milk'],
]);

it('lists all dals for a generic dal search in any language without auto-picking', function (string $q) {
    masterSeed();
    $r = masterSearch($q);
    $names = array_column($r['results'], 'name');
    expect($names)->toContain('Toor Dal')->toContain('Moong Dal')->toContain('Chana Dal');
})->with(['dal', 'dhal', 'daal']);

it('returns bilingual rows with category and unit for the picker', function () {
    masterSeed();
    $this->actingAs(icUser('employee'));
    $row = $this->getJson(route('inventory.items.search', ['q' => 'parupu']))->assertOk()->json('results.0');
    expect($row)->toMatchArray(['name' => 'Toor Dal', 'name_ta' => 'துவரம் பருப்பு', 'category' => 'Pulses & Dals', 'unit' => 'kg']);
});

it('still creates a purchase with seeded items and shows the bilingual picker', function () {
    masterSeed();
    $emp = icUser();
    $this->actingAs($emp)->get(route('inventory.purchases.create'))->assertOk()
        ->assertSee('IcItemPicker', false)->assertSee('ic-pick-clear', false);
    $toor = InventoryItem::where('name', 'Toor Dal')->first();
    $this->post(route('inventory.purchases.store'), masterPayload([
        ['inventory_item_id' => $toor->id, 'quantity' => 25, 'unit' => 'kg', 'rate' => 140],
    ], ['action' => 'submit', 'client_token' => (string) Str::uuid()]))->assertSessionHasNoErrors();
    expect(Purchase::count())->toBe(1)->and(Purchase::first()->items->first()->inventory_item_id)->toBe($toor->id);
});
