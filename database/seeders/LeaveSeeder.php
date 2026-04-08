<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Leave;
use App\Models\User;
use App\Enums\LeaveStatus;

class LeaveSeeder extends Seeder
{
    public function run(): void
    {
        $employees = User::whereHas('roles', function ($q) {
            $q->where('name', User::ROLE_EMPLOYEE);
        })->get();

        foreach ($employees as $user) {
            // Each employee gets 1-3 leave requests
            for ($i = 0; $i < rand(1, 3); $i++) {

                $start = now()->subDays(rand(1, 30));
                $end = (clone $start)->addDays(rand(1, 5));

                $flow = \App\Models\ApprovalFlow::where('module', 'leave')->first();

                Leave::create([
                    'user_id' => $user->id,
                    'start_date' => $start,
                    'end_date' => $end,
                    'reason' => fake()->sentence(),
                    'status' => fake()->randomElement([
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
