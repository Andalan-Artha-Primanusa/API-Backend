<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Models\User;
use App\Modules\Administration\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Helpers\ApiResponse;

class UserController extends Controller
{
    private function userLoadRelations(): array
    {
        return [
            'roles:id,name',
            'roles.permissions:id,name',
            'profile:id,user_id,phone,address,gender',
            'employee:id,user_id,employee_code,department_id,position_id,location_id,work_schedule_id',
            'employee.department:id,name',
            'employee.position:id,name',
            'employee.location:id,name',
            'employee.workSchedule:id,name,check_in_time,check_out_time',
            'employee.manager:id,name',
            'employee.manager.profile:id,user_id',
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $authUser = $request->user();

        if (!$authUser->isSuperAdmin() && !$authUser->hasPermission('user.create') && !$authUser->hasPermission('admin.user.create')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8'],
            'generate_password' => ['sometimes', 'boolean'],
            'must_change_password' => ['sometimes', 'boolean'],
            'role_ids' => ['sometimes', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        $plainPassword = !empty($data['generate_password'])
            ? Str::password(12, true, true, false, false)
            : ($data['password'] ?? null);

        if (!$plainPassword) {
            return ApiResponse::error('Password is required when generate_password is false', null, 422);
        }

        $roleIds = collect($data['role_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if (!$authUser->isSuperAdmin() && $roleIds->isNotEmpty()) {
            $superAdminRoleId = Role::where('name', User::ROLE_SUPER_ADMIN)->value('id');
            if ($superAdminRoleId) {
                $roleIds = $roleIds->reject(fn ($roleId) => $roleId === (int) $superAdminRoleId)->values();
            }
        }

        $createdUser = DB::transaction(function () use ($data, $plainPassword, $roleIds) {
            $createdUser = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($plainPassword),
                'must_change_password' => $data['must_change_password'] ?? true,
                'password_changed_at' => null,
            ]);

            if ($roleIds->isNotEmpty()) {
                $createdUser->roles()->sync($roleIds->all());
            }

            return $createdUser;
        });

        return ApiResponse::success('User account created successfully', [
            'user' => $createdUser->load($this->userLoadRelations()),
            'temporary_password' => $plainPassword,
            'must_change_password' => (bool) $createdUser->must_change_password,
        ], 201);
    }

    /**
     * Assign roles to a user.
     *
     * Security: prevents non-super-admins from assigning the super_admin role,
     * and prevents any modification of a super_admin user's roles.
     */
    public function assignRole(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->isSuperAdmin() && !$user->hasPermission('user.assign_role')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $targetUser = User::findOrFail($id);

        // Protect super admin from role changes
        if ($targetUser->isSuperAdmin()) {
            return ApiResponse::error('Cannot modify Super Admin roles', null, 403);
        }

        $data = $request->validate([
            'role_ids' => ['required', 'array'],
            'role_ids.*' => ['exists:roles,id']
        ]);

        // Prevent privilege escalation: non-super-admins cannot assign super_admin role
        if (!$user->isSuperAdmin()) {
            $superAdminRole = Role::where('name', User::ROLE_SUPER_ADMIN)->first();
            if ($superAdminRole) {
                $data['role_ids'] = array_values(
                    array_diff($data['role_ids'], [$superAdminRole->id])
                );
            }
        }

        if (empty($data['role_ids'])) {
            return ApiResponse::error('No valid roles to assign', null, 422);
        }

        $targetUser->roles()->sync($data['role_ids']);

        return ApiResponse::success('Roles assigned successfully', $targetUser->load($this->userLoadRelations()));
    }

    /**
     * List all users with their roles and profiles (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasPermission('user.view')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $query = User::with($this->userLoadRelations());

        if ($request->has('role')) {
            $roleParam = $request->role;
            $query->whereHas('roles', function ($q) use ($roleParam) {
                if (is_numeric($roleParam)) {
                    $q->where('roles.id', $roleParam);
                } else {
                    $q->where('roles.name', $roleParam);
                }
            });
        }

        $perPage = $request->get('per_page', 10);
        $users = $query->paginate($perPage)->withQueryString();

        return ApiResponse::success('User list', $users);
    }

    public function removeRole(Request $request, $id, $roleId): JsonResponse
    {
        $user = $request->user();

        if (!$user->isSuperAdmin() && !$user->hasPermission('user.assign_role')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $targetUser = User::findOrFail($id);

        if ($targetUser->isSuperAdmin()) {
            return ApiResponse::error('Cannot modify Super Admin roles', null, 403);
        }

        $role = Role::findOrFail($roleId);

        // Prevent removing super_admin role if it were somehow assigned
        if ($role->name === User::ROLE_SUPER_ADMIN && !$user->isSuperAdmin()) {
            return ApiResponse::error('Cannot remove super_admin role', null, 403);
        }

        $targetUser->roles()->detach($role->id);

        return ApiResponse::success('Role removed successfully', $targetUser->load($this->userLoadRelations()));
    }
}

