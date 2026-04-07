<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Leave;
use App\Models\User;

class LeaveSeeder extends Seeder
{
    public function run(): void
    {
        $employees = User::where('role', User::ROLE_EMPLOYEE)->get();

        foreach ($employees as $user) {
            // tiap employee punya 1-3 leave
            for ($i = 0; $i < rand(1, 3); $i++) {

                $start = now()->subDays(rand(1, 30));
                $end = (clone $start)->addDays(rand(1, 5));

                Leave::create([
                    'user_id' => $user->id,
                    'start_date' => $start,
                    'end_date' => $end,
                    'reason' => fake()->sentence(),
                    'status' => fake()->randomElement([
                        Leave::STATUS_PENDING,
                        Leave::STATUS_APPROVED,
                        Leave::STATUS_REJECTED,
                    ]),
                ]);
            }
        }
    }
}
