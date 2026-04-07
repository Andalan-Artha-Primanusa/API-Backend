<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Role;

class RoleController extends Controller
{
    public function assignPermission(Request $request, $id)
    {
        $user = $request->user();

        //  Permission / Super Admin bypass
        if (!$user->isSuperAdmin() && !$user->hasPermission('role.assign_permission')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $role = Role::findOrFail($id);

        $data = $request->validate([
            'permission_ids' => ['required', 'array'],
            'permission_ids.*' => ['exists:permissions,id']
        ]);

        $role->permissions()->sync($data['permission_ids']);

        return ApiResponse::success(
            'Permission berhasil diassign',
            $role->permissions()->get()
        );
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->isSuperAdmin() && !$user->hasPermission('role.view')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $roles = Role::with('permissions')->get();

        return ApiResponse::success('List roles', $roles);
    }
}
