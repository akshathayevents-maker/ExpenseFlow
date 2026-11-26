<?php

use App\Models\EventRequestMenuCategory;
use App\Models\EventRequestMenuItem;
use App\Models\MealPlan;
use App\Models\MenuItem;
use App\Models\Recipe;
use App\Services\MenuItemSync;

beforeEach(function () {
    $this->admin = icUser('admin');
    $this->drinks = EventRequestMenuCategory::create(['name' => 'Welcome Drinks', 'display_order' => 1, 'is_active' => true]);
    $this->dessert = EventRequestMenuCategory::create(['name' => 'Dessert', 'display_order' => 2, 'is_active' => true]);
});

it('mirrors every event-request dish into the menu item master and vice versa', function () {
    $er = EventRequestMenuItem::create(['category_id' => $this->drinks->id, 'name' => 'Rose Milk', 'price_per_person' => 22, 'is_active' => true]);
    $m = MenuItem::where('event_request_menu_item_id', $er->id)->first();
    expect($m)->not->toBeNull()->and($m->item_en)->toBe('Rose Milk')->and($m->category_key)->toBe('welcome_drink')->and($m->is_active)->toBeTrue();

    // menu-only dish → event request dish, inactive at ₹0 (customers never see an unpriced dish)
    $menu = MenuItem::create(['category_key' => 'sweet', 'category_en' => 'Sweet', 'category_ta' => 'ஸ்வீட்', 'item_en' => 'Laddu', 'item_ta' => 'லட்டு', 'is_active' => true]);
    $erLaddu = EventRequestMenuItem::find($menu->fresh()->event_request_menu_item_id);
    expect($erLaddu->name)->toBe('Laddu')->and($erLaddu->category_id)->toBe($this->dessert->id)
        ->and($erLaddu->is_active)->toBeFalse()->and((float) $erLaddu->price_per_person)->toBe(0.0);

    expect(MenuItem::count())->toBe(EventRequestMenuItem::count());
});

it('keeps names and categories in step both ways, but not price, Tamil or active flags', function () {
    $er = EventRequestMenuItem::create(['category_id' => $this->drinks->id, 'name' => 'Rose Milk', 'price_per_person' => 22, 'is_active' => true]);
    $m = $er->menuItemMirror;
    $m->update(['item_ta' => 'ரோஸ் மில்க்', 'is_active' => false]);

    $er->update(['name' => 'Rose Milk Shake', 'category_id' => $this->dessert->id, 'price_per_person' => 30]);
    expect($m->fresh()->item_en)->toBe('Rose Milk Shake')->and($m->fresh()->category_key)->toBe('dessert')
        ->and($m->fresh()->item_ta)->toBe('ரோஸ் மில்க்')->and($m->fresh()->is_active)->toBeFalse();

    $m->fresh()->update(['item_en' => 'Rose Shake']);
    expect($er->fresh()->name)->toBe('Rose Shake')->and((float) $er->fresh()->price_per_person)->toBe(30.0)->and($er->fresh()->is_active)->toBeTrue();
});

it('syncs existing lists once and is safe to re-run; deleting on one side only switches the other off', function () {
    MenuItemSync::quietly(function () {
        EventRequestMenuItem::create(['category_id' => $this->drinks->id, 'name' => 'Virgin Mojito', 'price_per_person' => 30, 'is_active' => true]);
        MenuItem::create(['category_key' => 'starter', 'category_en' => 'Starter', 'category_ta' => 'ஸ்டார்டர்', 'item_en' => 'Mushroom fry', 'item_ta' => 'காளான் வறுவல்', 'is_active' => true]);
    });
    expect(MenuItem::count())->toBe(1)->and(EventRequestMenuItem::count())->toBe(1);

    $this->artisan('menu:sync-items')->expectsOutputToContain('Menu items added: 1; event-request dishes added (inactive, ₹0): 1')->assertSuccessful();
    $this->artisan('menu:sync-items')->expectsOutputToContain('Menu items added: 0; event-request dishes added (inactive, ₹0): 0');
    expect(MenuItem::count())->toBe(2)->and(EventRequestMenuItem::count())->toBe(2)
        ->and(EventRequestMenuItem::where('name', 'Mushroom fry')->first()->category->name)->toBe('Starter');   // category created when missing

    $mojito = EventRequestMenuItem::where('name', 'Virgin Mojito')->first();
    $mojito->delete();
    $mirror = MenuItem::where('item_en', 'Virgin Mojito')->first();
    expect($mirror)->not->toBeNull()->and($mirror->is_active)->toBeFalse()->and($mirror->event_request_menu_item_id)->toBeNull();

    $fry = MenuItem::where('item_en', 'Mushroom fry')->first();
    $erId = $fry->event_request_menu_item_id;
    $this->actingAs($this->admin)->delete(route('menu.items.destroy', $fry));
    expect(EventRequestMenuItem::find($erId)->is_active)->toBeFalse();     // kept for past customer requests
});

it('shows the same dishes on both admin pages with price, status and Tamil-needed flags', function () {
    EventRequestMenuItem::create(['category_id' => $this->drinks->id, 'name' => 'Rose Milk', 'price_per_person' => 22, 'is_active' => true]);
    $this->actingAs($this->admin)->get(route('menu.items.index'))->assertOk()
        ->assertSee('Rose Milk')->assertSee('₹22')->assertSee('offered')->assertSee('Event Request Menu');
    $this->get(route('admin.event-request-menu.items.index'))->assertOk()->assertSee('Rose Milk');

    MenuItemSync::quietly(fn () => MenuItem::first()->update(['item_ta' => 'Rose Milk']));
    $this->get(route('menu.items.index', ['tamil' => 'missing']))->assertOk()->assertSee('Tamil needed');
});

it('shows recipe status and usage per dish and filters dishes without a recipe', function () {
    EventRequestMenuItem::create(['category_id' => $this->drinks->id, 'name' => 'Rose Milk', 'price_per_person' => 22, 'is_active' => true]);
    EventRequestMenuItem::create(['category_id' => $this->dessert->id, 'name' => 'Rava Kesari', 'price_per_person' => 20, 'is_active' => true]);
    Recipe::create(['name' => 'Rava Kesari', 'category' => 'Dessert', 'yield_per_batch' => 100, 'yield_unit' => 'people', 'created_by' => $this->admin->id]);
    MealPlan::create(['name' => 'Veg', 'category' => 'standard', 'price_per_person' => 300, 'is_active' => true, 'items' => ['Rava Kesari']]);

    $this->actingAs($this->admin)->get(route('menu.items.index'))->assertOk()
        ->assertSee('Draft')->assertSee('1 plan')->assertSee('without recipe')
        ->assertSee(route('kitchen.recipes.create', ['name' => 'Rose Milk']), false);
    $html = $this->get(route('menu.items.index', ['recipe' => 'none']))->getContent();
    expect($html)->toContain('Rose Milk')->not->toContain('>Rava Kesari<');
    $this->get(route('kitchen.recipes.create', ['name' => 'Rose Milk']))->assertSee('value="Rose Milk"', false);
});

it('groups every dish list under one Menu & Kitchen section for admins', function () {
    $html = $this->actingAs($this->admin)->get(route('menu.items.index'))->getContent();
    expect(substr_count($html, 'sb-grp-label">Menu &amp; Kitchen</span>'))->toBe(1)
        ->and($html)->toContain('Customer Menu &amp; Prices')->toContain(route('hall.meal-plans.index'))
        ->and($html)->toContain('Dish Categories');     // now under Settings
});
