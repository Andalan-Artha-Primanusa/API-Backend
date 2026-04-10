<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    /**
     * Relation graph to return a complete profile context.
     */
    private function profileRelations(): array
    {
        return [
            'user.roles.permissions',
            'employee.manager',
            'roles.permissions',
            'attendances',
            'leaves.approver',
            'kpis.employee',
            'reimbursements.approver',
            'payrolls.details',
        ];
    }

    /**
     * List profiles.
     * Users with profile.view_all see all profiles; others see only their own.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasPermission('profile.view_all')) {
            return ApiResponse::success(
                'All user profiles',
                UserProfile::with($this->profileRelations())->paginate(15)
            );
        }

        // Default: own profile only
        $profile = UserProfile::where('user_id', $user->id)
            ->with($this->profileRelations())
            ->first();

        return ApiResponse::success('Own profile', $profile);
    }

    /**
     * Create a new profile for the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->profile) {
            return ApiResponse::error(
                'Profile already exists',
                'User already has a profile',
                400
            );
        }

        $data = $request->validate([
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|string|in:male,female,other',
            'marital_status' => 'nullable|string|in:single,married,divorced,widowed',
            'religion' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
            'id_number' => 'nullable|string|max:100|unique:user_profiles,id_number',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relation' => 'nullable|string|max:100',
            'current_address' => 'nullable|string|max:1000',
            'permanent_address' => 'nullable|string|max:1000',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:100',
            'last_education' => 'nullable|string|max:100',
            'institution_name' => 'nullable|string|max:255',
            'graduation_year' => 'nullable|integer|digits:4|min:1950|max:' . date('Y'),
            'profile_photo_path' => 'nullable|string|max:255',
        ]);

        $data['user_id'] = $user->id;

        $profile = UserProfile::create($data);

        return ApiResponse::success('Profile created successfully', $profile->load($this->profileRelations()), 201);
    }

    /**
     * Show a specific profile.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $profile = UserProfile::with($this->profileRelations())->findOrFail($id);
        $user = $request->user();

        // Non-owner must have profile.view_all permission
        if ($profile->user_id !== $user->id && !$user->hasPermission('profile.view_all')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        return ApiResponse::success('Profile detail', $profile);
    }

    /**
     * Update a profile.
     * Authorization handled by UpdateProfileRequest.
     */
    public function update(UpdateProfileRequest $request, $id): JsonResponse
    {
        $profile = UserProfile::findOrFail($id);
        $user = $request->user();

        // Non-owner must have profile.update permission
        if ($profile->user_id !== $user->id && !$user->hasPermission('profile.update')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $profile->update($request->validated());

        return ApiResponse::success('Profile updated successfully', $profile->fresh()->load($this->profileRelations()));
    }

    /**
     * Delete a profile.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $profile = UserProfile::with($this->profileRelations())->findOrFail($id);
        $user = $request->user();

        $isOwner = $profile->user_id === $user->id;

        if (!$isOwner && !$user->hasPermission('profile.delete')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $deleted = $profile->toArray();
        $profile->delete();

        return ApiResponse::success('Profile deleted successfully', $deleted);
    }
}
