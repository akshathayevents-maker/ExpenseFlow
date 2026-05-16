<?php

namespace Database\Seeders;

use App\Models\Hall;
use Illuminate\Database\Seeder;

class HallSeeder extends Seeder
{
    public function run(): void
    {
        Hall::firstOrCreate(
            ['name' => 'RR Kalliru'],
            ['capacity' => 100, 'is_active' => true]
        );
    }
}
