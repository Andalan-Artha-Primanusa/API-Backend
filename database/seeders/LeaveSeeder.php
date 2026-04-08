<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Leave;
use App\Models\Employee;
use Carbon\Carbon;

class LeaveSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();

        foreach ($employees as $employee) {
            for ($i = 0; $i < rand(1, 3); $i++) {

                $start = Carbon::now()->subDays(rand(1, 30));
                $end = (clone $start)->addDays(rand(1, 5));

                Leave::create([
                    'employee_id' => $employee->id,
                    'start_date' => $start,
                    'end_date' => $end,
                    'total_days' => $start->diffInDays($end) + 1,
                    'type' => 'annual',
                    'reason' => fake()->sentence(),
                    'status' => fake()->randomElement([
                        'pending',
                        'approved',
                        'rejected',
                    ]),
                ]);
            }
        }
    }
}