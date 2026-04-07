<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;

class UserController extends Controller
{
    public function assignRole(Request $request, $id)
    {
        $user = $request->user();

        //  Permission / Super Admin bypass
        if (!$user->isSuperAdmin() && !$user->hasPermission('user.assign_role')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $targetUser = User::findOrFail($id);

        //  Protect super admin
        if ($targetUser->isSuperAdmin()) {
            return ApiResponse::error('Tidak bisa mengubah Super Admin', null, 403);
        }

        $data = $request->validate([
            'role_ids' => ['required', 'array'],
            'role_ids.*' => ['exists:roles,id']
        ]);

        $targetUser->roles()->sync($data['role_ids']);

        return ApiResponse::success(
            'Role berhasil diassign',
            $targetUser->roles()->get()
        );
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->isSuperAdmin() && !$user->hasPermission('user.view')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $users = User::with('roles')->get();

        return ApiResponse::success('List users', $users);
    }
}
