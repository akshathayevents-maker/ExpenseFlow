<?php

use App\Models\MenuItem;

it('adds tiffin and cauliflower dishes once, hidden at ₹0 on the customer menu', function () {
    MenuItem::create(['category_key' => 'starter', 'category_en' => 'Starter', 'category_ta' => 'ஸ்டார்டர்',
        'item_en' => 'gobi 65', 'item_ta' => 'கோபி', 'sort_order' => 0, 'is_active' => true]);

    $this->artisan('menu:add-tiffin-dishes')->assertSuccessful();
    $total = MenuItem::count();
    $this->artisan('menu:add-tiffin-dishes')->assertSuccessful();   // re-run adds nothing

    $dosa = MenuItem::with('eventRequestItem')->where('item_en', 'Masala Dosa')->first();
    expect(MenuItem::count())->toBe($total)
        ->and(MenuItem::whereRaw('LOWER(item_en) = ?', ['gobi 65'])->count())->toBe(1)   // existing dish untouched
        ->and($dosa->category_key)->toBe('tiffin')->and($dosa->item_ta)->toBe('மசாலா தோசை')
        ->and($dosa->eventRequestItem->is_active)->toBeFalse()
        ->and((float) $dosa->eventRequestItem->price_per_person)->toBe(0.0);
    foreach (['Onion Dosa', 'Podi Dosa', 'Idly', 'Ven Pongal', 'Cauliflower Poriyal'] as $n) {
        expect(MenuItem::where('item_en', $n)->exists())->toBeTrue();
    }

    $this->actingAs(icUser('admin'))->get(route('menu.items.index', ['category' => 'tiffin']))->assertOk()->assertSee('Masala Dosa');
});
