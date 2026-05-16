<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Fuel',       'description' => 'Fuel and vehicle running costs'],
            ['name' => 'Vegetables', 'description' => 'Fresh vegetables and produce'],
            ['name' => 'Grocery',    'description' => 'General grocery items'],
            ['name' => 'Cleaning',   'description' => 'Cleaning supplies and housekeeping'],
            ['name' => 'Kitchen',    'description' => 'Kitchen equipment and consumables'],
            ['name' => 'Electrical', 'description' => 'Electrical repairs and supplies'],
            ['name' => 'Plumbing',   'description' => 'Plumbing repairs and materials'],
            ['name' => 'Vehicle',    'description' => 'Vehicle maintenance and repairs'],
            ['name' => 'Emergency',  'description' => 'Emergency and unplanned expenses'],
            ['name' => 'Others',     'description' => 'Miscellaneous expenses'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
