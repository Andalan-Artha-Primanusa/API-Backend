<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function index()
    {
        return UserProfile::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:user_profiles',
            'phone' => 'nullable',
        ]);

        return UserProfile::create($data);
    }

    public function show($id)
    {
        return UserProfile::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $user = UserProfile::findOrFail($id);
        $user->update($request->all());
        return $user;
    }

    public function destroy($id)
    {
        UserProfile::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
