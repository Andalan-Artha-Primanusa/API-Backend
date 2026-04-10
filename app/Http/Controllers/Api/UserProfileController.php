<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserProfileController extends Controller
{
    /**
     * Optimized relation graph for profile queries
     */
    private const PROFILE_RELATIONS = [
        'user:id,name,email,created_at',
        'user.roles:id,name',
        'user.roles.permissions:id,name',
        'employee:id,user_id,position,department,manager_id',
        'employee.manager:id,user_id,position',
    ];

    /**
     * List user profiles with access control.
     * Users with profile.view_all see all profiles; others see only their own.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasPermission('profile.view_all')) {
                $profiles = UserProfile::with(self::PROFILE_RELATIONS)
                    ->select([
                        'id', 'user_id', 'phone', 'address', 'gender',
                        'birth_date', 'nationality', 'id_number', 'created_at'
                    ])
                    ->latest()
                    ->paginate(15);

                return ApiResponse::success('All user profiles', $profiles);
            }

            // Default: own profile only
            $profile = UserProfile::with(self::PROFILE_RELATIONS)
                ->where('user_id', $user->id)
                ->select([
                    'id', 'user_id', 'phone', 'address', 'gender',
                    'birth_date', 'nationality', 'id_number', 'created_at'
                ])
                ->first();

            return ApiResponse::success('Own profile', $profile);

        } catch (\Exception $e) {
            return ApiResponse::error('Failed to fetch profiles', null, 500);
        }
    }

    /**
     * Create a new profile for the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->profile) {
                return ApiResponse::error(
                    'Conflict',
                    'Profile already exists for this user',
                    409
                );
            }

            $validated = $request->validate([
                'phone'                      => 'nullable|string|max:20',
                'address'                    => 'nullable|string|max:500',
                'birth_date'                 => 'nullable|date|before:today',
                'gender'                     => 'nullable|string|in:male,female,other',
                'marital_status'             => 'nullable|string|in:single,married,divorced,widowed',
                'religion'                   => 'nullable|string|max:50',
                'nationality'                => 'nullable|string|max:100',
                'id_number'                  => 'nullable|string|max:100|unique:user_profiles,id_number',
                'emergency_contact_name'     => 'nullable|string|max:255',
                'emergency_contact_phone'    => 'nullable|string|max:20',
                'emergency_contact_relation' => 'nullable|string|max:100',
                'current_address'            => 'nullable|string|max:1000',
                'permanent_address'          => 'nullable|string|max:1000',
                'bank_name'                  => 'nullable|string|max:100',
                'bank_account_number'        => 'nullable|string|max:100',
                'bank_account_name'          => 'nullable|string|max:255',
                'tax_number'                 => 'nullable|string|max:100',
                'last_education'             => 'nullable|string|max:100',
                'institution_name'           => 'nullable|string|max:255',
                'graduation_year'            => 'nullable|integer|digits:4|min:1950|max:' . date('Y'),
                'profile_photo_path'         => 'nullable|string|max:255',
            ]);

            $validated['user_id'] = $user->id;
            $profile = UserProfile::create($validated);

            return ApiResponse::success(
                'Profile created successfully',
                $profile->load(self::PROFILE_RELATIONS),
                201
            );

        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to create profile', null, 500);
        }
    }

    /**
     * Show a specific user profile with access control.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            if ($id <= 0) {
                throw ValidationException::withMessages(['id' => 'Invalid profile ID']);
            }

            $profile = UserProfile::with(self::PROFILE_RELATIONS)
                ->select([
                    'id', 'user_id', 'phone', 'address', 'gender', 'birth_date',
                    'marital_status', 'religion', 'nationality', 'id_number',
                    'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation',
                    'current_address', 'permanent_address', 'bank_name', 'bank_account_number',
                    'bank_account_name', 'tax_number', 'last_education', 'institution_name',
                    'graduation_year', 'profile_photo_path'
                ])
                ->findOrFail($id);

            $user = $request->user();

            // Authorization: Non-owner must have profile.view_all permission
            if ($profile->user_id !== $user->id && !$user->hasPermission('profile.view_all')) {
                return ApiResponse::error('Forbidden', 'You cannot view this profile', 403);
            }

            return ApiResponse::success('Profile detail', $profile);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', 'Profile not found', 404);
        } catch (ValidationException $e) {
            return ApiResponse::error('Invalid request', $e->errors(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to fetch profile', null, 500);
        }
    }

    /**
     * Update a user profile with access control.
     */
    public function update(UpdateProfileRequest $request, int $id): JsonResponse
    {
        try {
            if ($id <= 0) {
                throw ValidationException::withMessages(['id' => 'Invalid profile ID']);
            }

            $profile = UserProfile::findOrFail($id);
            $user = $request->user();

            // Authorization: Non-owner must have profile.update permission
            if ($profile->user_id !== $user->id && !$user->hasPermission('profile.update')) {
                return ApiResponse::error('Forbidden', 'You cannot update this profile', 403);
            }

            $profile->update($request->validated());

            return ApiResponse::success(
                'Profile updated successfully',
                $profile->fresh()->load(self::PROFILE_RELATIONS)
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', 'Profile not found', 404);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update profile', null, 500);
        }
    }

    /**
     * Delete a user profile with access control.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            if ($id <= 0) {
                throw ValidationException::withMessages(['id' => 'Invalid profile ID']);
            }

            $profile = UserProfile::select([
                'id', 'user_id', 'phone', 'address', 'gender', 'birth_date'
            ])->findOrFail($id);

            $user = $request->user();
            $isOwner = $profile->user_id === $user->id;

            // Authorization: Non-owner must have profile.delete permission
            if (!$isOwner && !$user->hasPermission('profile.delete')) {
                return ApiResponse::error('Forbidden', 'You cannot delete this profile', 403);
            }

            $deleted = $profile->toArray();
            $profile->delete();

            return ApiResponse::success('Profile deleted successfully', $deleted);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', 'Profile not found', 404);
        } catch (ValidationException $e) {
            return ApiResponse::error('Invalid request', $e->errors(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to delete profile', null, 500);
        }
    }
}
