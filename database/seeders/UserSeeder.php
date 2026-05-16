<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'      => 'Admin User',
                'email'     => 'admin@expenseflow.com',
                'phone'     => '9999999991',
                'role'      => 'admin',
                'password'  => 'password',
                'is_active' => true,
            ],
            [
                'name'      => 'Manager User',
                'email'     => 'manager@expenseflow.com',
                'phone'     => '9999999992',
                'role'      => 'manager',
                'password'  => 'password',
                'is_active' => true,
            ],
            [
                'name'      => 'Employee User',
                'email'     => 'employee@expenseflow.com',
                'phone'     => '9999999993',
                'role'      => 'employee',
                'password'  => 'password',
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name'       => $user['name'],
                    'phone'      => $user['phone'],
                    'role'       => $user['role'],
                    'password'   => Hash::make($user['password']),
                    'is_active'  => $user['is_active'],
                    'updated_at' => now(),
                ]
            );
        }
    }
}
