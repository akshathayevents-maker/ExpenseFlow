<?php

namespace Database\Factories;

use App\Models\MenuDraft;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MenuDraft> */
class MenuDraftFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'        => $this->faker->words(3, true) . ' Menu',
            'venue'        => $this->faker->optional()->company(),
            'event_date'   => $this->faker->optional()->dateTimeBetween('now', '+6 months')?->format('Y-m-d'),
            'people_count' => $this->faker->optional()->numberBetween(50, 500),
            'content'      => [],   // new format: empty array of sections
            'created_by'   => User::factory(),
        ];
    }

    public function withLunch(): static
    {
        return $this->state(['content' => [
            ['key' => 'lunch', 'label_en' => 'Lunch', 'label_ta' => 'மதிய உணவு', 'items' => [
                ['id' => 1, 'item_en' => 'Kesari', 'item_ta' => 'கேசரி', 'category_key' => 'sweet', 'category_en' => 'Sweet', 'category_ta' => 'ஸ்வீட்'],
            ]],
        ]]);
    }
}
