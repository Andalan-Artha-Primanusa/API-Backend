<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\UserProfile;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function index()
    {
        return ApiResponse::success(
            'Data user profile berhasil diambil',
            UserProfile::all()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:user_profiles',
            'phone' => 'nullable|string',
        ]);

        $user = UserProfile::create($data);

        return ApiResponse::success(
            'User profile berhasil dibuat',
            $user,
            201
        );
    }

    public function show($id)
    {
        return ApiResponse::success(
            'Detail user profile',
            UserProfile::findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $user = UserProfile::findOrFail($id);
        $user->update($request->only(['name', 'phone']));

        return ApiResponse::success(
            'User profile berhasil diupdate',
            $user
        );
    }

    public function destroy($id)
    {
        UserProfile::findOrFail($id)->delete();

        return ApiResponse::success('User profile berhasil dihapus');
    }
}
