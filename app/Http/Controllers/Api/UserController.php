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
            $targetUser->load('roles')
        );
    }

    /**
     * List all users with their roles and profiles (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isSuperAdmin() && !$user->hasPermission('user.view')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $users = User::with([
            'roles.permissions',
            'profile',
            'employee.manager.profile',
        ])->paginate(15);

        return ApiResponse::success('User list', $users);
    }
}
