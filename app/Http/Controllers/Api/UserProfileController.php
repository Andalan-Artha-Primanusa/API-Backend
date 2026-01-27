<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA; // ✅ WAJIB

/**
 * @OA\Tag(
 *     name="User Profile",
 *     description="User Profile Management"
 * )
 */
class UserProfileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get all users",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of users"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function index()
    {
        return response()->json(UserProfile::all(), 200);
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Create new user",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email"},
     *             @OA\Property(property="name", type="string", example="Budi"),
     *             @OA\Property(property="email", type="string", format="email", example="budi@mail.com"),
     *             @OA\Property(property="phone", type="string", example="08123456789")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get user by ID",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User detail"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function show($id)
    {
        return response()->json(
            UserProfile::findOrFail($id),
            200
        );
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Update user",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Budi Update"),
     *             @OA\Property(property="phone", type="string", example="0899999999")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $user = UserProfile::findOrFail($id);
        $user->update($request->all());

        return response()->json([
            'message' => 'User berhasil diupdate',
            'data' => $user
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Delete user",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted"
     *     )
     * )
     */
    public function destroy($id)
    {
        UserProfile::findOrFail($id)->delete();

        return response()->json([
            'message' => 'User berhasil dihapus'
        ], 200);
    }
}
