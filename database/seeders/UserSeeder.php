<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->count(2)->create([
            'role' => User::ROLE_HR
        ]);

        User::factory()->count(3)->create([
            'role' => User::ROLE_MANAGER
        ]);

        User::factory()->count(10)->create([
            'role' => User::ROLE_EMPLOYEE
        ]);
    }
}