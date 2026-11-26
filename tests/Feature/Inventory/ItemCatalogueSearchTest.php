<?php

use App\Models\InventoryBillItem;
use App\Models\InventoryBillUpload;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryItemAlias;
use App\Models\InventoryTransaction;
use App\Models\MaterialRequestItem;
use App\Models\Vendor;
use App\Services\Inventory\ItemCatalogueService;
use App\Services\Inventory\ItemMatchingService;
use App\Services\Inventory\ItemSearchService;
use App\Services\Inventory\UnitConversionService;
use App\Services\InventoryService;
use App\Support\ItemText;
use Database\Seeders\InventoryCatalogueSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function seedCatalogue(): InventoryCatalogueSeeder
{
    $s = app(InventoryCatalogueSeeder::class);
    $s->run(app(ItemCatalogueService::class));

    return $s;
}

function searchNames(string $q, int $limit = 10): array
{
    return array_column(app(ItemSearchService::class)->search($q, $limit)['results'], 'name');
}

function topOf(string $q): ?string
{
    return searchNames($q, 1)[0] ?? null;
}

beforeEach(fn () => ItemSearchService::flush());

it('finds items by exact English name, case-insensitively, ranked first', function () {
    seedCatalogue();
    expect(topOf('rice'))->toBe('Rice')
        ->and(topOf('RICE'))->toBe('Rice')
        ->and(topOf('toor dal'))->toBe('Toor Dal')
        ->and(app(ItemSearchService::class)->search('Sugar')['exact_id'])->toBe(InventoryItem::where('name', 'Sugar')->value('id'));
});

it('finds items by exact Tamil name', function () {
    seedCatalogue();
    expect(topOf('துவரம் பருப்பு'))->toBe('Toor Dal')
        ->and(topOf('சர்க்கரை'))->toBe('Sugar')
        ->and(topOf('அரிசி'))->toBe('Rice')
        ->and(topOf('பாசிப்பருப்பு'))->toBe('Moong Dal')
        ->and(topOf('பாசி பருப்பு'))->toBe('Moong Dal');   // spacing variant
});

it('finds items by Tanglish aliases and common spelling variations', function (string $q, string $expected) {
    seedCatalogue();
    expect(topOf($q))->toBe($expected);
})->with([
    ['parupu', 'Toor Dal'], ['parpu', 'Toor Dal'], ['paruppu', 'Toor Dal'], ['Paruppu ', 'Toor Dal'],
    ['sakkarai', 'Sugar'], ['sarkarai', 'Sugar'], ['ennai', 'Cooking Oil'], ['vengayam', 'Onion'],
    ['thakali', 'Tomato'], ['manjal podi', 'Turmeric Powder'], ['nei', 'Ghee'], ['ulundu', 'Urad Dal'],
    ['toor dhal', 'Toor Dal'], ['tur dal', 'Toor Dal'], ['chiken', 'Chicken'], ['tomatto', 'Tomato'],
]);

it('lists every relevant item for an ambiguous word instead of choosing one', function () {
    seedCatalogue();
    foreach (['dal', 'dhal', 'daal'] as $q) {
        $r = app(ItemSearchService::class)->search($q, 20);
        $names = array_column($r['results'], 'name');
        expect($r['exact_id'])->toBeNull()
            ->and($r['ambiguous'])->toBeTrue()
            ->and($names)->toContain('Toor Dal', 'Urad Dal', 'Chana Dal', 'Moong Dal', 'Masoor Dal');
    }
    $oils = searchNames('ennai', 20);
    expect($oils)->toContain('Cooking Oil', 'Groundnut Oil', 'Gingelly Oil', 'Coconut Oil');
});

it('matches partial words and ranks exact > Tamil > alias > prefix > partial > fuzzy', function () {
    $cat = InventoryCategory::create(['name' => 'Test', 'is_active' => true]);
    $mk = fn ($n, $ta = null) => InventoryItem::create(['name' => $n, 'name_ta' => $ta, 'unit' => 'kg', 'inventory_category_id' => $cat->id, 'status' => 'active']);
    $exact = $mk('Masala');
    $prefix = $mk('Masala Powder');
    $partial = $mk('Garam Masalas');
    $tamil = $mk('Spice Mix', 'மசாலா');
    $alias = $mk('Curry Mix');
    app(ItemCatalogueService::class)->addAlias($alias, 'Masaala Mix');

    $s = app(ItemSearchService::class);
    $r = collect($s->search('masala', 10)['results'])->keyBy('id');
    expect($r[$exact->id]['score'])->toBe(100)
        ->and($r[$prefix->id]['score'])->toBe(80)        // whole word inside a longer name
        ->and($r[$partial->id]['score'])->toBe(75);       // prefix of a word
    expect($s->search('மசாலா')['results'][0]['id'])->toBe($tamil->id)
        ->and($s->search('மசாலா')['results'][0]['score'])->toBe(96);
    expect($s->search('masaala mix')['results'][0]['id'])->toBe($alias->id)
        ->and($s->search('masaala mix')['results'][0]['score'])->toBe(92);
    expect(collect($s->search('alas')['results'])->pluck('score')->max())->toBe(60);  // contains only
    $fz = $s->search('masalla powdr');                                                // typo -> fuzzy only
    expect($fz['results'][0]['id'])->toBe($prefix->id)
        ->and($fz['results'][0]['score'])->toBeLessThan(60)
        ->and($fz['exact_id'])->toBeNull();
});

it('never invents matches for unrelated text', function () {
    seedCatalogue();
    expect(searchNames('xylophone'))->toBe([])
        ->and(app(ItemSearchService::class)->search('zzzz qqq')['exact_id'])->toBeNull();
});

it('prevents one generic alias from pointing to two items', function () {
    $a = icItem('Toor Dal');
    $b = icItem('Urad Dal');
    $svc = app(ItemCatalogueService::class);
    expect($svc->addAlias($a, 'Paruppu')['status'])->toBe('created')
        ->and($svc->addAlias($a, 'paruppu')['status'])->toBe('exists')
        ->and($svc->addAlias($b, 'Paruppu')['status'])->toBe('conflict')
        ->and($svc->addAlias($b, 'Paruppu')['other']->id)->toBe($a->id)
        ->and($svc->addAlias($b, 'Parupu')['status'])->toBe('conflict')       // same spelling key
        ->and($svc->addAlias($b, 'toor dhal')['status'])->toBe('conflict')    // another item's name
        ->and($svc->addAlias($a, 'Toor Dhal')['status'])->toBe('redundant');  // own name already matches

    // database guard too (partial unique index on generic aliases)
    // (savepoint: PostgreSQL aborts the surrounding transaction on a constraint error)
    expect(fn () => DB::transaction(fn () => InventoryItemAlias::create(['inventory_item_id' => $b->id, 'alias' => 'Paruppu'])))
        ->toThrow(UniqueConstraintViolationException::class);
    // supplier wording may repeat across items
    $v = Vendor::create(['name' => 'V', 'is_active' => true]);
    InventoryItemAlias::create(['inventory_item_id' => $b->id, 'alias' => 'Paruppu', 'vendor_id' => $v->id, 'source' => 'confirmed']);
    expect(InventoryItemAlias::where('alias', 'Paruppu')->count())->toBe(2);
});

it('keeps Tamil vowel signs when normalising', function () {
    expect(ItemText::normalize('துவரம் பருப்பு 25kg'))->toBe('துவரம் பருப்பு')
        ->and(ItemText::phonetic('Toor Dhal'))->toBe(ItemText::phonetic('tur dal'));
});

it('reuses existing items when seeding and never touches their stock or history', function () {
    $admin = icUser('admin');
    $this->actingAs($admin);
    $cat = InventoryCategory::create(['name' => 'Kitchen', 'is_active' => true]);
    $toor = InventoryItem::create(['name' => 'Toor Dhal', 'unit' => 'kg', 'inventory_category_id' => $cat->id, 'minimum_stock' => 5, 'status' => 'active', 'average_cost' => 120]);
    app(InventoryService::class)->post($toor, 40, 'correction', $admin, ['notes' => 'opening']);
    $sugar = InventoryItem::create(['name' => 'Sugar', 'name_ta' => 'சீனி பழையது', 'unit' => 'kg', 'inventory_category_id' => $cat->id, 'status' => 'active']);
    $txBefore = InventoryTransaction::count();

    $s = seedCatalogue();

    $toor->refresh();
    expect(InventoryItem::where('search_key', 'tur dal')->count())->toBe(1)       // no second Toor Dal
        ->and($toor->name)->toBe('Toor Dhal')                                      // name untouched
        ->and($toor->name_ta)->toBe('துவரம் பருப்பு')                               // only the missing Tamil name filled
        ->and((float) $toor->current_stock)->toBe(40.0)
        ->and((float) $toor->minimum_stock)->toBe(5.0)
        ->and((float) $toor->average_cost)->toBe(120.0)
        ->and($toor->inventory_category_id)->toBe($cat->id)
        ->and($sugar->fresh()->name_ta)->toBe('சீனி பழையது')                       // existing Tamil name kept
        ->and(InventoryTransaction::count())->toBe($txBefore)
        ->and($s->stats['items_reused'])->toBeGreaterThanOrEqual(2)
        ->and(topOf('parupu'))->toBe('Toor Dhal');
});

it('is idempotent: a second run creates nothing new', function () {
    seedCatalogue();
    $counts = [InventoryItem::count(), InventoryItemAlias::count(), InventoryCategory::count()];
    $second = seedCatalogue();
    expect([InventoryItem::count(), InventoryItemAlias::count(), InventoryCategory::count()])->toBe($counts)
        ->and($second->stats['items_created'])->toBe(0)
        ->and($second->stats['aliases_created'])->toBe(0)
        ->and($second->stats['items_reused'])->toBe($counts[0])
        ->and($counts[0])->toBeGreaterThan(250)
        ->and(InventoryItem::where('current_stock', '!=', 0)->count())->toBe(0);
});

it('restricts catalogue management to admins', function () {
    $item = icItem('Rice');
    $alias = InventoryItemAlias::create(['inventory_item_id' => $item->id, 'alias' => 'Arisi']);
    foreach ([icUser('employee'), icUser('manager'), icUser('employee', true)] as $u) {
        $this->actingAs($u);
        $this->get(route('admin.inventory.items.index'))->assertForbidden();
        $this->get(route('admin.inventory.items.duplicates'))->assertForbidden();
        $this->post(route('admin.inventory.items.aliases.store', $item), ['alias' => 'Chawal', 'language' => 'en'])->assertForbidden();
        $this->delete(route('admin.inventory.items.aliases.destroy', [$item, $alias]))->assertForbidden();
        $this->post(route('admin.inventory.items.units.store', $item), ['unit' => 'bag', 'factor' => 25])->assertForbidden();
    }
    expect(InventoryItemAlias::count())->toBe(1);
    auth()->logout();
    $this->get(route('inventory.items.search', ['q' => 'rice']))->assertRedirect(route('login'));

    // the search itself is open to signed-in staff (they request materials) and exposes no costs
    $this->actingAs(icUser('employee'));
    $json = $this->getJson(route('inventory.items.search', ['q' => 'arisi']))->assertOk()->json();
    expect($json['results'][0]['name'])->toBe('Rice')
        ->and($json['results'][0])->not->toHaveKeys(['average_cost', 'current_stock']);
});

it('lets an admin manage names, aliases and purchase units', function () {
    $admin = icUser('admin');
    $this->actingAs($admin);
    $item = icItem('Toor Dal');
    $other = icItem('Urad Dal');

    $this->post(route('admin.inventory.items.aliases.store', $item), ['alias' => 'Parupu', 'language' => 'tanglish'])->assertSessionHas('success');
    $this->post(route('admin.inventory.items.aliases.store', $other), ['alias' => 'parupu', 'language' => 'tanglish'])->assertSessionHasErrors('alias');
    $alias = InventoryItemAlias::firstWhere('alias', 'Parupu');
    expect($alias->language)->toBe('tanglish')->and($alias->alias_type)->toBe('tanglish');

    $this->post(route('admin.inventory.items.units.store', $item), ['unit' => 'bag', 'factor' => 25])->assertSessionHas('success');
    $this->post(route('admin.inventory.items.units.store', $item), ['unit' => 'gram', 'factor' => 1])->assertSessionHasErrors('unit');
    expect(app(UnitConversionService::class)->unitsFor($item->fresh()->load('unitConversions')))->toContain('bag');

    $this->get(route('admin.inventory.items.show', $item))->assertOk()->assertSee('Parupu')->assertSee('1 <strong>bag</strong>', false);
    $this->delete(route('admin.inventory.items.aliases.destroy', [$other, $alias]))->assertNotFound();
    $this->delete(route('admin.inventory.items.aliases.destroy', [$item, $alias]))->assertSessionHas('success');
    expect(InventoryItemAlias::count())->toBe(0);
});

it('warns before creating a duplicate item and only saves after confirmation', function () {
    $this->actingAs(icUser('admin'));
    $toor = icItem('Toor Dal');
    $toor->update(['name_ta' => 'துவரம் பருப்பு']);
    app(ItemCatalogueService::class)->addAlias($toor, 'Parupu');
    $base = ['inventory_category_id' => $toor->inventory_category_id, 'unit' => 'kg', 'minimum_stock' => 0, 'status' => 'active'];

    foreach (['Toor Dhal', 'Parupu', 'Tur Dal'] as $name) {
        $this->post(route('admin.inventory.items.store'), $base + ['name' => $name])->assertSessionHas('duplicates');
    }
    $this->post(route('admin.inventory.items.store'), $base + ['name' => 'Arhar', 'name_ta' => 'துவரம் பருப்பு'])->assertSessionHasErrors('name_ta');
    $this->post(route('admin.inventory.items.store'), $base + ['name' => 'toor dal'])->assertSessionHasErrors('name');
    expect(InventoryItem::count())->toBe(1);

    $this->post(route('admin.inventory.items.store'), $base + ['name' => 'Toor Dhal', 'confirm_duplicate' => 1])->assertRedirect();
    expect(InventoryItem::count())->toBe(2);
    $this->get(route('admin.inventory.items.duplicates'))->assertOk()->assertSee('Toor Dhal')->assertSee('Toor Dal');
    $this->post(route('admin.inventory.items.store'), $base + ['name' => 'Sunflower Oil'])->assertSessionMissing('duplicates');
});

it('uses the shared search in the purchase and material request selectors', function () {
    seedCatalogue();
    $this->actingAs($u = icUser('employee'));
    foreach ([route('inventory.purchases.create'), route('inventory.material-requests.create')] as $url) {
        $html = $this->get($url)->assertOk()->getContent();
        expect($html)->toContain('window.IcItemPicker')
            // the picker must be defined before the grid script uses it
            ->and(strpos($html, 'window.IcItemPicker = '))->toBeLessThan(strpos($html, 'window.IcItemPicker.attach(input'))
            ->and($html)->toContain(trim(json_encode(route('inventory.items.search')), '"'))  // as emitted by @json
            ->and($html)->not->toContain('<datalist')
            ->and($html)->toContain(trim(json_encode('துவரம் பருப்பு'), '"'));      // Tamil names reach the grid catalogue
    }
    // what the grids call while typing
    $r = $this->getJson(route('inventory.items.search', ['q' => 'parpu']))->json();
    expect($r['exact_id'])->toBe(InventoryItem::where('name', 'Toor Dal')->value('id'))
        ->and($r['results'][0]['matched'])->toBe('Parpu')
        ->and($r['results'][0]['category'])->toBe('Pulses & Dals')
        ->and($r['results'][0]['unit'])->toBe('kg');
    $amb = $this->getJson(route('inventory.items.search', ['q' => 'dal']))->json();
    expect($amb['exact_id'])->toBeNull()->and(count($amb['results']))->toBeGreaterThan(3);

    // a request saved with an item picked through Tamil search goes through as normal
    $toor = InventoryItem::where('name', 'Toor Dal')->first();
    $toor->update(['current_stock' => 10]);
    $this->post(route('inventory.material-requests.store'), [
        'client_token' => (string) Str::uuid(), 'source' => 'other', 'purpose' => 'Staff lunch',
        'required_date' => now()->toDateString(), 'department' => 'kitchen', 'action' => 'draft',
        'lines' => [['inventory_item_id' => $toor->id, 'quantity' => 2, 'unit' => 'kg', 'description' => 'துவரம் பருப்பு']],
    ])->assertRedirect();
    expect(MaterialRequestItem::where('inventory_item_id', $toor->id)->exists())->toBeTrue();
});

it('matches invoice lines written in Tamil or Tanglish through the same search', function () {
    seedCatalogue();
    $m = app(ItemMatchingService::class);
    $sugar = InventoryItem::where('name', 'Sugar')->value('id');
    $toor = InventoryItem::where('name', 'Toor Dal')->value('id');

    $b = $m->best('சர்க்கரை 50kg');
    expect($b['item_id'])->toBe($sugar)->and($m->isAutoAcceptable($b))->toBeTrue();
    $b = $m->best('Thuvaram Paruppu');
    expect($b['item_id'])->toBe($toor)->and($m->isAutoAcceptable($b))->toBeTrue();
    expect($m->isAutoAcceptable($m->best('Dal')))->toBeFalse();       // ambiguous: never auto

    // review screen offers the shared search box above each line's item dropdown
    $this->actingAs(icUser('admin'));
    $bill = InventoryBillUpload::create(['original_filename' => 'b.jpg', 'stored_path' => 'x', 'file_type' => 'image', 'file_hash' => 'hc1', 'status' => 'review_pending', 'uploaded_by' => auth()->id()]);
    InventoryBillItem::create(['bill_upload_id' => $bill->id, 'item_name' => 'Sakkarai', 'quantity' => 5, 'unit_price' => 40, 'total' => 200]);
    $html = $this->get(route('admin.inventory.bills.show', $bill))->assertOk()->getContent();
    expect($html)->toContain('window.IcItemPicker')->and($html)->toContain('Search item (English / தமிழ்)');
});

it('consolidates admin navigation into one Store & Inventory entry and keeps old routes working', function () {
    $this->actingAs(icUser('admin'));
    $html = $this->get(route('inventory.home'))->assertOk()->getContent();
    expect(substr_count($html, 'Store &amp; Inventory</span>'))->toBe(1)
        ->and($html)->not->toContain('Central Store')
        ->and($html)->toContain(route('admin.inventory.items.index'))
        ->and($html)->toContain('>Daily</div>')->and($html)->toContain('>Buying</div>')->and($html)->toContain('Scan Supplier Bill');
    // alerts and movements are reached from the Stock page instead of the sidebar
    expect($this->get(route('inventory.stock'))->getContent())
        ->toContain(route('admin.inventory.alerts.index'))->toContain(route('inventory.reports.show', 'movements'));
    foreach (['admin.inventory.items.index', 'admin.inventory.bills.index', 'admin.inventory.alerts.index', 'admin.inventory.categories.index',
        'admin.purchase-plans.index', 'inventory.stock', 'inventory.purchases.index', 'inventory.material-requests.index', 'inventory.reports.index'] as $r) {
        $this->get(route($r))->assertOk();
    }

    $this->actingAs(icUser('employee', true));      // storekeeper
    $html = $this->get(route('inventory.home'))->assertOk()->getContent();
    expect(substr_count($html, 'Store &amp; Inventory</span>'))->toBe(1)
        ->and($html)->toContain(route('inventory.stock'))
        ->and($html)->not->toContain(route('admin.inventory.items.index'))
        ->and($html)->not->toContain(route('inventory.reports.index'));

    $this->actingAs(icUser('employee'));
    $html = $this->get(route('inventory.home'))->assertOk()->getContent();
    expect($html)->toContain(route('inventory.material-requests.create'))
        ->and($html)->not->toContain('href="'.route('inventory.stock').'"');
});
