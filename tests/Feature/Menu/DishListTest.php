<?php

use App\Models\MenuItem;

function dlDish(string $en, array $a = []): MenuItem
{
    return MenuItem::create(array_merge(['category_key' => 'sweet', 'category_en' => 'Sweet', 'category_ta' => 'ஸ்வீட்',
        'item_en' => $en, 'item_ta' => $en.' த', 'sort_order' => 0, 'is_active' => true], $a));
}

it('filters dishes by category, hidden state and search, with counts', function () {
    dlDish('Rava Kesari');
    dlDish('Tomato Soup', ['category_key' => 'soup', 'category_en' => 'Soup']);
    dlDish('Old Halwa', ['is_active' => false]);
    $u = icUser('admin');

    $this->actingAs($u)->get(route('menu.items.index'))->assertOk()
        ->assertSee('Rava Kesari')->assertSee('Tomato Soup')->assertSee('Old Halwa')
        ->assertSeeInOrder(['data-k="all"', '3', 'data-k="recipe_none"', '3'], false);

    $html = $this->actingAs($u)->get(route('menu.items.index', ['category' => 'soup']))->getContent();
    expect($html)->toContain('Tomato Soup')->not->toContain('Rava Kesari');

    $html = $this->actingAs($u)->get(route('menu.items.index', ['state' => 'inactive']))->getContent();
    expect(substr_count($html, 'data-dish='))->toBe(1)->and($html)->toContain('Old Halwa');

    $html = $this->actingAs($u)->get(route('menu.items.index', ['search' => 'kesari']))->getContent();
    expect(substr_count($html, 'data-dish='))->toBe(1);

    $this->actingAs($u)->get(route('menu.items.index', ['category' => 'nope', 'recipe' => 'bogus']))
        ->assertOk()->assertSee('Old Halwa');
    $this->actingAs($u)->get(route('menu.items.index', ['search' => 'zzz']))->assertSee('No dishes match');
});

it('paginates 50 dishes per page', function () {
    foreach (range(1, 55) as $n) {
        dlDish(sprintf('Dish %02d', $n));
    }
    $html = $this->actingAs(icUser('admin'))->get(route('menu.items.index'))->getContent();
    expect(substr_count($html, 'data-dish='))->toBe(50);
    $html = $this->actingAs(icUser('admin'))->get(route('menu.items.index', ['page' => 2]))->getContent();
    expect(substr_count($html, 'data-dish='))->toBe(5);
});
