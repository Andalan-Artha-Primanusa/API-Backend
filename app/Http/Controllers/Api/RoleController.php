<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Role;
use App\Models\User;
use App\Models\Permission;

class RoleController extends Controller
{
    public function assignPermission(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->isSuperAdmin() && !$user->hasPermission('role.assign_permission')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $role = Role::findOrFail($id);

        // Prevent modification of super_admin role entirely
        if ($role->name === User::ROLE_SUPER_ADMIN && !$user->isSuperAdmin()) {
            return ApiResponse::error('Forbidden', 'Cannot modify super_admin role permissions', 403);
        }

        $data = $request->validate([
            'permission_ids' => ['required', 'array'],
            'permission_ids.*' => ['exists:permissions,id']
        ]);

        // Prevent non-super-admins from granting highly sensitive permissions
        if (!$user->isSuperAdmin()) {
            $sensitivePerms = Permission::whereIn('name', [
                'role.assign_permission',
                'user.assign_role'
            ])->pluck('id')->toArray();
            
            $data['permission_ids'] = array_values(array_diff($data['permission_ids'], $sensitivePerms));
        }

        $role->permissions()->sync($data['permission_ids']);

        return ApiResponse::success(
            'Permissions assigned successfully',
            $role->load('permissions')
        );
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isSuperAdmin() && !$user->hasPermission('role.view')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $roles = Role::with('permissions')->get();

        return ApiResponse::success('Role list', $roles);
    }
}
