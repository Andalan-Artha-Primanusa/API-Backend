<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $hrRole = Role::where('name', User::ROLE_HR)->first();
        $managerRole = Role::where('name', User::ROLE_MANAGER)->first();
        $employeeRole = Role::where('name', User::ROLE_EMPLOYEE)->first();

        if (!$hrRole || !$managerRole || !$employeeRole) {
            $this->command?->error('Roles not found! Run RbacSeeder first.');
            return;
        }

        // HR users
        $hrs = User::factory()->count(2)->create();
        foreach ($hrs as $user) {
            $user->roles()->syncWithoutDetaching([$hrRole->id]);
        }

        // Manager users
        $managers = User::factory()->count(3)->create();
        foreach ($managers as $user) {
            $user->roles()->syncWithoutDetaching([$managerRole->id]);
        }

        // Employee users
        $employees = User::factory()->count(10)->create();
        foreach ($employees as $user) {
            $user->roles()->syncWithoutDetaching([$employeeRole->id]);
        }

        $this->command?->info('Users seeded with pivot-based roles.');
    }
}
