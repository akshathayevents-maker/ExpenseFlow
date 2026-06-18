<?php

namespace Database\Factories;

use App\Models\MenuTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MenuTemplate> */
class MenuTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'        => $this->faker->words(3, true) . ' Template',
            'description' => $this->faker->optional()->sentence(),
            'content'     => [
                ['key' => 'lunch', 'label_en' => 'Lunch', 'label_ta' => 'மதிய உணவு', 'items' => [
                    ['id' => 1, 'item_en' => 'Kesari', 'item_ta' => 'கேசரி', 'category_key' => 'sweet', 'category_en' => 'Sweet', 'category_ta' => 'ஸ்வீட்'],
                ]],
            ],
            'created_by'  => User::factory(),
        ];
    }
}
