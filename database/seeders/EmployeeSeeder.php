<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $index => $user) {
            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'manager_id' => null,
                    'employee_code' => 'EMP-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'position' => fake()->jobTitle(),
                    'department' => fake()->randomElement(['IT', 'HR', 'Finance']),
                    'hire_date' => now()->subYears(rand(1, 5)),
                    'salary' => rand(4000000, 15000000),
                ]
            );
        $managers = User::whereHas('roles', function ($q) {
            $q->where('name', User::ROLE_MANAGER);
        })->get();

        $employees = User::whereHas('roles', function ($q) {
            $q->where('name', User::ROLE_EMPLOYEE);
        })->get();

        foreach ($employees as $index => $user) {
            // Skip if employee record already exists (idempotent)
            if (Employee::where('user_id', $user->id)->exists()) {
                continue;
            }

            Employee::create([
                'user_id' => $user->id,
                'manager_id' => $managers->random()->id,
                'employee_code' => 'EMP-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'position' => fake()->jobTitle(),
                'department' => fake()->randomElement(['IT', 'HR', 'Finance', 'Marketing']),
                'hire_date' => now()->subYears(rand(1, 5)),
                'salary' => rand(4000000, 15000000),
            ]);
        }
    }
}