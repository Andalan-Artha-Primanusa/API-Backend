<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;

class UserController extends Controller
{
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

        return ApiResponse::success(
            'Roles assigned successfully',
            $targetUser->load([
                'roles:id,name',
                'roles.permissions:id,name',
                'profile:id,user_id,phone,address,gender,profile_photo_path',
                'employee:id,user_id,employee_code,department_id,position_id,location_id,work_schedule_id',
                'employee.department:id,name',
                'employee.position:id,name',
                'employee.location:id,name',
                'employee.workSchedule:id,name,check_in_time,check_out_time',
                'employee.manager:id,name',
                'employee.manager.profile:id,user_id,profile_photo_path',
            ])
        );
    }

    /**
     * List all users with their roles and profiles (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isHR() && !$user->hasPermission('user.view')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $query = User::with([
            'roles:id,name',
            'roles.permissions:id,name',
            'profile:id,user_id,phone,address,gender,profile_photo_path',
            'employee:id,user_id,employee_code,department_id,position_id,location_id,work_schedule_id',
            'employee.department:id,name',
            'employee.position:id,name',
            'employee.location:id,name',
            'employee.workSchedule:id,name,check_in_time,check_out_time',
            'employee.manager:id,name',
            'employee.manager.profile:id,user_id,profile_photo_path',
        ]);

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

        return ApiResponse::success(
            'Role removed successfully',
            $targetUser->load([
                'roles:id,name',
                'roles.permissions:id,name',
                'profile:id,user_id,phone,address,gender,profile_photo_path',
                'employee:id,user_id,employee_code,department_id,position_id,location_id,work_schedule_id',
                'employee.department:id,name',
                'employee.position:id,name',
                'employee.location:id,name',
                'employee.workSchedule:id,name,check_in_time,check_out_time',
                'employee.manager:id,name',
                'employee.manager.profile:id,user_id,profile_photo_path',
            ])
        );
    }
}
