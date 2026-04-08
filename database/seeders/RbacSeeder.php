<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RbacSeeder extends Seeder
{
    /**
     * Seed roles and permissions. Fully idempotent (safe to run multiple times).
     */
    public function run(): void
    {
        // Roles
        $roles = ['super_admin', 'admin', 'hr', 'manager', 'employee'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Permissions
        $permissions = [
            // Employee module
            'employee.view',
            'employee.create',
            'employee.update',
            'employee.delete',

            // Leave module
            'leave.view',
            'leave.create',
            'leave.approve',

            // Attendance module
            'attendance.view_all',
            'attendance.delete',

            // Location module
            'location.view',
            'location.create',
            'location.update',
            'location.delete',

            // Profile module
            'profile.view_all',
            'profile.update',
            'profile.delete',

            // RBAC admin
            'user.assign_role',
            'role.assign_permission',
            'role.view',
            'permission.view',
            'user.view',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ASSIGN PERMISSIONS TO ROLES
        $allPermissions = Permission::all();

        $map = [
            'super_admin' => $allPermissions->pluck('id'),

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
                'user.view',
            ])->pluck('id'),

            'hr' => Permission::whereIn('name', [
                'employee.view',
                'employee.create',
                'employee.update',
                'leave.view',
                'leave.approve',
                'profile.view_all',
                'profile.update',
            ])->pluck('id'),

            'manager' => Permission::whereIn('name', [
                'leave.view',
                'leave.approve',
            ])->pluck('id'),

            'employee' => Permission::whereIn('name', [
                'leave.view',
                'leave.create',
            ])->pluck('id'),
        ];

        foreach ($map as $roleName => $permissionIds) {
            $role = Role::where('name', $roleName)->first();
            $role?->permissions()->sync($permissionIds);
        }
    }
}
