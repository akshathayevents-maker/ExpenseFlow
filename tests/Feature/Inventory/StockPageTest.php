<?php

it('lists stocked items first and filters by status, search and category without losing items', function () {
    $store = icUser('employee', true);
    icItem('Almonds', 'kg', 0);
    icItem('Rice', 'kg', 25, 5);
    icItem('Rava', 'kg', 2, 5);

    $html = $this->actingAs($store)->get(route('inventory.stock'))->assertOk()
        ->assertSee('In stock')->assertSee('Low / out')->assertSee('<span data-count>3</span> of 3', false)->getContent();
    expect(strpos($html, 'Rice'))->toBeLessThan(strpos($html, 'Almonds'));   // stocked before never-stocked

    // server-side filters still work (links / no JavaScript): non-matching rows are rendered hidden
    $this->get(route('inventory.stock', ['show' => 'low']))->assertOk()->assertSee('<span data-count>2</span>', false);
    $this->get(route('inventory.stock', ['show' => 'in']))->assertOk()->assertSee('<span data-count>2</span>', false);
    $this->get(route('inventory.stock', ['q' => 'ric']))->assertOk()->assertSee('<span data-count>1</span>', false);
    $this->get(route('inventory.stock', ['show' => 'bogus']))->assertOk()->assertSee('<span data-count>3</span>', false);

    $this->actingAs(icUser())->get(route('inventory.stock'))->assertForbidden();
});
