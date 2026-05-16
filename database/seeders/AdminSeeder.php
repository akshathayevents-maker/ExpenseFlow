<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Super Admin',
                'email'    => 'admin@expenseflow.com',
                'phone'    => '9000000001',
                'role'     => 'admin',
                'is_active' => true,
                'password' => Hash::make('password'),
            ],
            [
                'name'     => 'Demo Manager',
                'email'    => 'manager@expenseflow.com',
                'phone'    => '9000000002',
                'role'     => 'manager',
                'is_active' => true,
                'password' => Hash::make('password'),
            ],
            [
                'name'     => 'Demo Employee',
                'email'    => 'employee@expenseflow.com',
                'phone'    => '9000000003',
                'role'     => 'employee',
                'is_active' => true,
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(['email' => $data['email']], $data);
        }
    }
}
