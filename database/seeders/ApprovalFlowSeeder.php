<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStep;
use App\Models\Role;

class ApprovalFlowSeeder extends Seeder
{
    /**
     * Fully idempotent: safe to run multiple times without creating duplicates.
     */
    public function run(): void
    {
        $manager = Role::where('name', 'manager')->first();
        $hr = Role::where('name', 'hr')->first();

        if (!$manager || !$hr) {
            $this->command?->error('Role manager / hr not found! Run RbacSeeder first.');
            return;
        }

        // Create or find the leave approval flow
        $flow = ApprovalFlow::firstOrCreate(
            ['module' => 'leave'],
            ['name' => 'Leave Approval Flow']
        );

        // Step 1 → Manager (idempotent)
        ApprovalStep::firstOrCreate(
            [
                'approval_flow_id' => $flow->id,
                'step_order' => 1,
            ],
            [
                'role_id' => $manager->id,
            ]
        );

        // Step 2 → HR (idempotent)
        ApprovalStep::firstOrCreate(
            [
                'approval_flow_id' => $flow->id,
                'step_order' => 2,
            ],
            [
                'role_id' => $hr->id,
            ]
        );

        $this->command?->info('Approval Flow for Leave seeded successfully.');

        // ==============================
        // Assignment Letter Approval Flow
        // ==============================
        $letterFlow = ApprovalFlow::firstOrCreate(
            ['module' => 'assignment_letter'],
            ['name' => 'Assignment Letter Approval Flow']
        );

        ApprovalStep::firstOrCreate(
            [
                'approval_flow_id' => $letterFlow->id,
                'step_order' => 1,
            ],
            [
                'role_id' => $manager->id,
            ]
        );

        ApprovalStep::firstOrCreate(
            [
                'approval_flow_id' => $letterFlow->id,
                'step_order' => 2,
            ],
            [
                'role_id' => $hr->id,
            ]
        );

        $this->command?->info('Approval Flow for Assignment Letter seeded successfully.');

        // ==============================
        // Overtime Approval Flow
        // ==============================
        $overtimeFlow = ApprovalFlow::firstOrCreate(
            ['module' => 'overtime'],
            ['name' => 'Overtime Approval Flow']
        );

        ApprovalStep::firstOrCreate(
            [
                'approval_flow_id' => $overtimeFlow->id,
                'step_order' => 1,
            ],
            [
                'role_id' => $manager->id,
            ]
        );

        ApprovalStep::firstOrCreate(
            [
                'approval_flow_id' => $overtimeFlow->id,
                'step_order' => 2,
            ],
            [
                'role_id' => $hr->id,
            ]
        );

        $this->command?->info('Approval Flow for Overtime seeded successfully.');
    }
}
