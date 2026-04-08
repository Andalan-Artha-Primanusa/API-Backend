<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@gmail.com',
                'password' => Hash::make('superadmin123'),
                'role' => User::ROLE_SUPER_ADMIN
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin123'),
                'role' => User::ROLE_ADMIN
            ]
        ];

        foreach ($users as $index => $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            // 🔥 WAJIB: kasih employee
            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_code' => 'ADM-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'position' => 'Administrator',
                    'department' => 'Management',
                    'hire_date' => now(),
                    'salary' => 15000000
                ]
            );
        }
    }
}