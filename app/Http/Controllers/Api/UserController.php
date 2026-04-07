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
            'role' => 'required|in:super_admin,admin,hr,manager,employee'
        ]);

        $user = User::findOrFail($id);

        $user->role = $request->role;
        $user->save();

        return response()->json([
            'message' => 'Role berhasil diupdate',
            'user' => $user,
        ]);
    }
}
