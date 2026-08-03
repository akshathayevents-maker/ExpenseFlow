<?php

namespace Database\Seeders;

use App\Models\EventRequestMenuCategory;
use Illuminate\Database\Seeder;

class EventRequestMenuCategorySeeder extends Seeder
{
    /**
     * Category order matches how a catering menu is actually served —
     * welcome drinks first, dessert/beverage last. MenuItemSeeder relies
     * on these exact names to attach items to the right category.
     */
    public static function categories(): array
    {
        return [
            ['name' => 'Welcome Drinks', 'description' => 'Served as guests arrive'],
            ['name' => 'Soup', 'description' => 'To begin the meal'],
            ['name' => 'Starter', 'description' => 'Appetizers before the main course'],
            ['name' => 'Main Course', 'description' => 'The heart of the meal'],
            ['name' => 'Gravy', 'description' => 'Additional curries to pair with rice and bread'],
            ['name' => 'Rice', 'description' => 'Biryanis, pulaos, and rice specialties'],
            ['name' => 'Indian Bread', 'description' => 'Fresh from the tandoor'],
            ['name' => 'Dessert', 'description' => 'A sweet finish'],
            ['name' => 'Ice Cream', 'description' => 'Chilled and creamy'],
            ['name' => 'Beverage', 'description' => 'Hot and cold drinks'],
        ];
    }

    public function run(): void
    {
        foreach (self::categories() as $index => $category) {
            EventRequestMenuCategory::updateOrCreate(
                ['name' => $category['name']],
                [
                    'description'   => $category['description'],
                    'display_order' => $index + 1,
                    'is_active'     => true,
                ]
            );
        }
    }
}
