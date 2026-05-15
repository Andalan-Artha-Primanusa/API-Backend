<?php

namespace Tests\Feature\Rbac;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class UserPermissionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTables();
        $this->seedRolesAndPermissions();
    }

    private function setupTables(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }

    private function seedRolesAndPermissions(): void
    {
        foreach (config('rbac.roles') as $roleName => $perms) {
            Role::create(['name' => $roleName]);
        }

        $allPerms = [];
        foreach (config('rbac.roles') as $roleName => $perms) {
            foreach ((array)$perms as $p) {
                $allPerms[$p] = $p;
            }
        }

        foreach ($allPerms as $permName) {
            Permission::create(['name' => $permName]);
        }

        foreach (config('rbac.roles') as $roleName => $perms) {
            $role = Role::where('name', $roleName)->first();
            if ($role && is_array($perms)) {
                $permIds = Permission::whereIn('name', $perms)->pluck('id')->toArray();
                $role->permissions()->sync($permIds);
            }
        }
    }

    public function test_super_admin_has_all_permissions(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'super_admin')->first()->id);
        $user->unsetRelation('roles');
        $user->load('roles.permissions');

        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue($user->hasPermission('employee.view'));
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->isHR());
        $this->assertTrue($user->isManager());
        $this->assertTrue($user->isEmployee());
    }

    public function test_admin_role_has_admin_level(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'admin')->first()->id);
        $user->unsetRelation('roles');
        $user->load('roles.permissions');

        $this->assertFalse($user->isSuperAdmin());
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isHR());
        $this->assertFalse($user->isManager());
    }

    public function test_hr_role_has_hr_level(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'hr')->first()->id);
        $user->unsetRelation('roles');
        $user->load('roles.permissions');

        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isHR());
        $this->assertFalse($user->isManager());
    }

    public function test_manager_role_has_manager_level(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'manager')->first()->id);
        $user->unsetRelation('roles');
        $user->load('roles.permissions');

        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isHR());
        $this->assertTrue($user->isManager());
    }

    public function test_employee_role_has_employee_level(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'employee')->first()->id);
        $user->unsetRelation('roles');
        $user->load('roles.permissions');

        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isManager());
        $this->assertTrue($user->isEmployee());
    }

    public function test_dynamic_role_via_permissions(): void
    {
        $customRole = Role::create(['name' => 'custom_supervisor']);
        $customRole->permissions()->sync(
            Permission::whereIn('name', ['leave.approve', 'employee.view'])->pluck('id')
        );

        $user = User::factory()->create();
        $user->roles()->attach($customRole->id);
        $user->unsetRelation('roles');
        $user->load('roles.permissions');

        $this->assertTrue($user->hasPermission('leave.approve'));
        $this->assertTrue($user->hasPermission('employee.view'));
        $this->assertFalse($user->hasPermission('payroll.view'));
    }

    public function test_user_with_no_role_has_no_permissions(): void
    {
        $user = User::factory()->create();
        $this->assertFalse($user->hasPermission('employee.view'));
        $this->assertFalse($user->isSuperAdmin());
    }
}
