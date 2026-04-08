<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Leave;
use App\Models\Employee;
use Carbon\Carbon;
use App\Models\User;
use App\Enums\LeaveStatus;

class LeaveSeeder extends Seeder
{
    public function run(): void
    {
<<<<<<< HEAD
        $employees = Employee::all();

=======
>>>>>>> 00291747f9f9ea27e31930f137014d77d4b4870f
        $employees = User::whereHas('roles', function ($q) {
            $q->where('name', User::ROLE_EMPLOYEE);

        foreach ($employees as $user) {
            // Each employee gets 1-3 leave requests
            for ($i = 0; $i < rand(1, 3); $i++) {

                $end = (clone $start)->addDays(rand(1, 5));

                $flow = \App\Models\ApprovalFlow::where('module', 'leave')->first();

                Leave::create([
                    'employee_id' => $employee->id,
                    'start_date' => $start,
                    'end_date' => $end,
                    'total_days' => $start->diffInDays($end) + 1,
                    'type' => 'annual',
                    'reason' => fake()->sentence(),
                    'status' => fake()->randomElement([
<<<<<<< HEAD
                        'pending',
                        'approved',
                        'rejected',
=======
>>>>>>> 00291747f9f9ea27e31930f137014d77d4b4870f
                        LeaveStatus::Pending,
                        LeaveStatus::Approved,
                        LeaveStatus::Rejected,
                    ]),
                    'approval_flow_id' => $flow?->id,
                    'current_step' => 1,
                ]);
            }
        }
    }
}
