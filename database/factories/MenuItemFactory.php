<?php

namespace Database\Factories;

use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MenuItem> */
class MenuItemFactory extends Factory
{
    public function definition(): array
    {
        $categories = config('menu_categories.items', [
            'sweet' => ['en' => 'Sweet', 'ta' => 'ஸ்வீட்'],
        ]);
        $key = $this->faker->randomElement(array_keys($categories));
        $cat = $categories[$key];

        return [
            'category_key' => $key,
            'category_en'  => $cat['en'],
            'category_ta'  => $cat['ta'],
            'item_en'      => $this->faker->words(2, true),
            'item_ta'      => 'டெஸ்ட்',
            'sort_order'   => 0,
            'is_active'    => true,
        ];
    }
}
