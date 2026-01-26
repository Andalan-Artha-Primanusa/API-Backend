<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function index()
    {
        return response()->json(UserProfile::all(), 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required',
            'email' => 'required|email|unique:user_profiles',
            'phone' => 'nullable'
        ]);

        $user = UserProfile::create($data);

        return response()->json([
            'message' => 'User berhasil dibuat',
            'data' => $user
        ], 201);
    }

    public function show($id)
    {
        return response()->json(
            UserProfile::findOrFail($id),
            200
        );
    }

    public function update(Request $request, $id)
    {
        $user = UserProfile::findOrFail($id);
        $user->update($request->all());

        return response()->json([
            'message' => 'User berhasil diupdate',
            'data' => $user
        ], 200);
    }

    public function destroy($id)
    {
        UserProfile::findOrFail($id)->delete();

        return response()->json([
            'message' => 'User berhasil dihapus'
        ], 200);
    }
}

