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
            UserProfile::with('user')->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'birth_date' => 'nullable|date',
        ]);

        $data['user_id'] = auth()->id();

        $profile = UserProfile::create($data);

        return ApiResponse::success(
            'User profile berhasil dibuat',
            $profile,
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
        $user->update($request->only([
            'phone',
            'address',
            'birth_date'
        ]));

        return ApiResponse::success(
            'User profile berhasil diupdate',
            $user
        );
    }

    public function destroy($id)
    {
        UserProfile::with('user')->findOrFail($id)->delete();

        return ApiResponse::success('User profile berhasil dihapus');
    }
}
