<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', Str::random(32))),
            ]
        );

        $superAdminRole = Role::where('name', User::ROLE_SUPER_ADMIN)->first();
        if ($superAdminRole) {
            $superAdmin->roles()->syncWithoutDetaching([$superAdminRole->id]);
        }

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make(env('ADMIN_PASSWORD', Str::random(32))),
            ]
        );

        $adminRole = Role::where('name', User::ROLE_ADMIN)->first();
        if ($adminRole) {
            $admin->roles()->syncWithoutDetaching([$adminRole->id]);
        }

        // Log generated passwords in local environment
        if (app()->isLocal()) {
            $this->command?->info('Admin accounts seeded. Set SUPER_ADMIN_PASSWORD and ADMIN_PASSWORD in .env for custom passwords.');
        }
    }
}