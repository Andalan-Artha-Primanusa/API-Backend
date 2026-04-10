<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        // ======================
        // ROLES
        // ======================
        $roles = ['super_admin', 'admin', 'hr', 'manager', 'employee'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // ======================
        // PERMISSIONS
        // ======================
        $permissions = [
            // Employee
            'employee.view',
            'employee.create',
            'employee.update',
            'employee.delete',

            // Leave
            'leave.view',
            'leave.create',
            'leave.approve',

            // Attendance
            'attendance.view_all',
            'attendance.delete',
            'attendance.check_in',
            'attendance.check_out',
            'attendance.view_own',

            // Location
            'location.view',
            'location.create',
            'location.update',
            'location.delete',

            // Profile
            'profile.view_all',
            'profile.update',
            'profile.delete',

            // RBAC
            'user.view',
            'user.assign_role',
            'role.view',
            'role.assign_permission',
            'permission.view',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $allPermissions = Permission::pluck('id');

        // ======================
        // ROLE PERMISSION MAP
        // ======================
        $map = [

            // 🔥 FULL CONTROL (LOCKED)
            'super_admin' => $allPermissions,

            // 🔥 SYSTEM OPERATOR (CUSTOMIZABLE)
            'admin' => Permission::whereIn('name', [
                'employee.view',
                'employee.create',
                'employee.update',
                'employee.delete',

                'leave.view',
                'leave.approve',

                'attendance.view_all',
                'attendance.delete',

                'location.view',
                'location.create',
                'location.update',
                'location.delete',

                'profile.view_all',
                'profile.update',
                'profile.delete',

                // 🔥 RBAC CONTROL (PENTING)
                'user.view',
                'user.assign_role',
                'role.view',
                'role.assign_permission',
                'permission.view',
            ])->pluck('id'),

            // 👨‍💼 HR
            'hr' => Permission::whereIn('name', [
                'employee.view',
                'employee.create',
                'employee.update',

                'leave.view',
                'leave.approve',

                'attendance.view_all',
                'attendance.delete',

                'profile.view_all',
                'profile.update',
            ])->pluck('id'),

            // 👨‍💼 MANAGER
            'manager' => Permission::whereIn('name', [
                'employee.view',
                'profile.view_all',

                'leave.view',
                'leave.approve',
            ])->pluck('id'),

            // 👨‍💻 EMPLOYEE
            'employee' => Permission::whereIn('name', [
                'leave.view',
                'leave.create',

                'attendance.check_in',
                'attendance.check_out',
                'attendance.view_own',

                'profile.update',
            ])->pluck('id'),
        ];

        foreach ($map as $roleName => $permissionIds) {
            $role = Role::where('name', $roleName)->first();
            $role?->permissions()->sync($permissionIds);
        }
    }
}
