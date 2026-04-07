<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // 🔥 ambil semua manager
        $managers = User::where('role', User::ROLE_MANAGER)->get();

        // 🔥 ambil semua employee
        $employees = User::where('role', User::ROLE_EMPLOYEE)->get();

        foreach ($employees as $index => $user) {
            Employee::create([
                'user_id' => $user->id,
                'manager_id' => $managers->random()->id, // random manager
                'employee_code' => 'EMP-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'position' => fake()->jobTitle(),
                'department' => fake()->randomElement(['IT', 'HR', 'Finance', 'Marketing']),
                'hire_date' => now()->subYears(rand(1, 5)),
                'salary' => rand(4000000, 15000000),
            ]);
        }
    }
}
