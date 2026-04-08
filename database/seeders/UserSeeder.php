<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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

        // HR
        $hrs = User::factory()->count(2)->create();
        foreach ($hrs as $user) {
            $user->roles()->syncWithoutDetaching([$hrRole->id]);
        }

        // MANAGER
        $managers = User::factory()->count(3)->create();
        foreach ($managers as $user) {
            $user->roles()->syncWithoutDetaching([$managerRole->id]);
        }

        // EMPLOYEE
        $employees = User::factory()->count(10)->create();
        foreach ($employees as $user) {
            $user->roles()->syncWithoutDetaching([$employeeRole->id]);
        }
    }
}
