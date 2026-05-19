<?php

namespace Database\Seeders;

use App\Models\Hall;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class HallSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('halls')) {
            $this->command->warn('HallSeeder: halls table does not exist, skipping.');
            return;
        }

        Hall::firstOrCreate(
            ['name' => 'RR Kalliru'],
            ['capacity' => 100, 'is_active' => true]
        );
    }
}
