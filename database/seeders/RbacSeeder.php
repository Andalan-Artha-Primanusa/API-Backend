<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RbacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // roles
        $roles = ['super_admin', 'admin', 'hr', 'manager', 'employee'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // permissions
        $permissions = [
            'employee.view',
            'employee.create',
            'employee.update',
            'employee.delete',

            'leave.view',
            'leave.create',
            'leave.approve',

            'user.assign_role',
            'role.assign_permission',
            'role.view',
            'permission.view',
            'user.view'
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }
    }
}
