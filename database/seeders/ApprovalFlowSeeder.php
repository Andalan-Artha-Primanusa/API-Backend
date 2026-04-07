<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStep;
use App\Models\Role;

class ApprovalFlowSeeder extends Seeder
{
    public function run(): void
    {
        //  ambil role
        $manager = Role::where('name', 'manager')->first();
        $hr = Role::where('name', 'hr')->first();

        if (!$manager || !$hr) {
            $this->command->error('Role manager / hr belum ada!');
            return;
        }

        //  buat flow
        $flow = ApprovalFlow::create([
            'name' => 'Leave Approval Flow',
            'module' => 'leave',
        ]);

        //  step 1 → manager
        ApprovalStep::create([
            'approval_flow_id' => $flow->id,
            'step_order' => 1,
            'role_id' => $manager->id,
        ]);

        //  step 2 → hr
        ApprovalStep::create([
            'approval_flow_id' => $flow->id,
            'step_order' => 2,
            'role_id' => $hr->id,
        ]);

        $this->command->info('Approval Flow Leave berhasil dibuat!');
    }
}
