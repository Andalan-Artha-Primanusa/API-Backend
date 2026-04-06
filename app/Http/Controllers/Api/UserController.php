<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function assignRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        $user = User::findOrFail($id);

        // 🔥 replace role lama
        $user->syncRoles([$request->role]);

        return response()->json([
            'message' => 'Role berhasil diupdate',
            'user' => $user,
            'roles' => $user->getRoleNames()
        ]);
    }
}
