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
        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', 'superadmin123')),
            ]
        );

        $superAdminRole = Role::where('name', User::ROLE_SUPER_ADMIN)->first();
        if ($superAdminRole) {
            $superAdmin->roles()->syncWithoutDetaching([$superAdminRole->id]);
        }

        Employee::firstOrCreate(
            ['user_id' => $superAdmin->id],
            [
                'employee_code' => 'ADM-001',
                'position'      => 'Super Administrator',
                'department'    => 'Management',
                'hire_date'     => now(),
                'salary'        => 20000000,
            ]
        );

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'     => 'Admin',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'admin123')),
            ]
        );

        $adminRole = Role::where('name', User::ROLE_ADMIN)->first();
        if ($adminRole) {
            $admin->roles()->syncWithoutDetaching([$adminRole->id]);
        }

        Employee::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'employee_code' => 'ADM-002',
                'position'      => 'Administrator',
                'department'    => 'Management',
                'hire_date'     => now(),
                'salary'        => 15000000,
            ]
        );

        if (app()->isLocal()) {
            $this->command?->info('Admin accounts seeded. Set SUPER_ADMIN_PASSWORD and ADMIN_PASSWORD in .env for custom passwords.');
        }
    }
}
