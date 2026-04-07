<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use App\Models\User;

class UserProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isEmployee()) {
            return ApiResponse::success(
                'Data profile sendiri',
                UserProfile::where('user_id', $user->id)->with('user')->get()
            );
        }

        if ($user->isHR() || $user->isAdmin()) {
            return ApiResponse::success(
                'Semua data profile',
                UserProfile::with('user')->get()
            );
        }

        return ApiResponse::error('Forbidden', 'Unauthorized', 403);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        // 🔥 CEK SUDAH ADA PROFILE ATAU BELUM
        if ($user->profile) {
            return ApiResponse::error(
                'Profile sudah ada',
                'User sudah memiliki profile',
                400
            );
        }

        $data = $request->validate([
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'birth_date' => 'nullable|date',
        ]);

        $data['user_id'] = $user->id;

        $profile = UserProfile::create($data);

        return ApiResponse::success(
            'User profile berhasil dibuat',
            $profile,
            201
        );
    }

    public function show($id, Request $request)
    {
        $profile = UserProfile::findOrFail($id);
        $user = $request->user();

        if ($user->isEmployee() && $profile->user_id !== $user->id) {
            return ApiResponse::error('Forbidden', 'Unauthorized', 403);
        }

        return ApiResponse::success(
            'Detail user profile',
            $profile
        );
    }

    public function update(Request $request, $id)
    {
        $profile = UserProfile::findOrFail($id);
        $user = $request->user();

        if ($user->isEmployee() && $profile->user_id !== $user->id) {
            return ApiResponse::error('Forbidden', 'Unauthorized', 403);
        }

        $profile->update($request->only([
            'phone',
            'address',
            'birth_date'
        ]));

        return ApiResponse::success(
            'User profile berhasil diupdate',
            $profile
        );
    }

    public function destroy($id, Request $request)
    {
        $profile = UserProfile::findOrFail($id);
        $user = $request->user();

        // employee hanya bisa delete sendiri
        if ($user->isEmployee() && $profile->user_id !== $user->id) {
            return ApiResponse::error('Forbidden', 'Unauthorized', 403);
        }

        //  hanya admin/hr boleh delete semua
        if (!($user->isHR() || $user->isAdmin()) && $profile->user_id !== $user->id) {
            return ApiResponse::error('Forbidden', 'Unauthorized', 403);
        }

        $profile->delete();

        return ApiResponse::success('User profile berhasil dihapus');
    }
}
