<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Http\Requests\CheckInRequest;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    /**
     * Define permission requirements per action
     */
    private const PERMISSION_MAP = [
        'checkIn'  => 'attendance.check_in',
        'checkOut' => 'attendance.check_out',
        'history'  => 'attendance.view_own',
        'today'    => 'attendance.view_own',
        'all'      => 'attendance.view_all',
        'show'     => 'attendance.view_all',
        'destroy'  => 'attendance.delete',
    ];

    public function __construct(
        protected AttendanceService $attendanceService
    ) {
        // Apply permission middleware to all methods
        $this->middleware(function ($request, $next) {
            $action = $request->route()->getActionMethod();
            $permission = self::PERMISSION_MAP[$action] ?? null;

            if ($permission && !$request->user()->hasPermission($permission)) {
                return ApiResponse::error('Forbidden', 'Insufficient permissions', 403);
            }

            return $next($request);
        });
    }

    /**
     * Check-in with geofencing validation.
     * 
     * @param CheckInRequest $request Validated: latitude, longitude
     * @return JsonResponse
     */
    public function checkIn(CheckInRequest $request): JsonResponse
    {
        try {
            $result = $this->attendanceService->checkIn(
                $request->user(),
                $request->validated()['latitude'],
                $request->validated()['longitude']
            );

            return ApiResponse::success('Check-in successful', [
                'location' => $result['location'],
                'distance' => $result['distance'] . ' meter',
                'data'     => $result['attendance'],
                'status'   => $result['attendance']->status,
            ]);
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        } catch (\Exception $e) {
            \Log::error('CheckIn Error', ['user_id' => $request->user()->id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Check-in failed', null, 500);
        }
    }

    /**
     * Check-out for today's attendance.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function checkOut(Request $request): JsonResponse
    {
        try {
            $attendance = $this->attendanceService->checkOut($request->user());

            return ApiResponse::success('Check-out successful', $attendance);
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        } catch (\Exception $e) {
            \Log::error('CheckOut Error', ['user_id' => $request->user()->id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Check-out failed', null, 500);
        }
    }

    /**
     * Get attendance history for the current user.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $data = $this->attendanceService->getHistory($request->user());

            return ApiResponse::success('Attendance history', $data);
        } catch (\Exception $e) {
            \Log::error('History Error', ['user_id' => $request->user()->id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to fetch history', null, 500);
        }
    }

    /**
     * Get today's attendance for the current user.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function today(Request $request): JsonResponse
    {
        try {
            $attendance = $this->attendanceService->getToday($request->user());

            return ApiResponse::success('Today attendance', $attendance);
        } catch (\Exception $e) {
            \Log::error('Today Error', ['user_id' => $request->user()->id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to fetch today attendance', null, 500);
        }
    }

    /**
     * Get all attendance records with pagination and filtering (admin only).
     * 
     * Query params:
     * - per_page: int (default: 15, max: 100)
     * - sort_by: string (default: 'date', options: 'date', 'user_id', 'status')
     * - sort_order: string (default: 'desc', options: 'asc', 'desc')
     * - date_from: string (Y-m-d)
     * - date_to: string (Y-m-d)
     * - status: string (present, late, absent)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function all(Request $request): JsonResponse
    {
        try {
            // Validate query parameters
            $validated = $request->validate([
                'per_page'  => 'sometimes|integer|min:1|max:100',
                'sort_by'   => 'sometimes|in:date,user_id,status',
                'sort_order'=> 'sometimes|in:asc,desc',
                'date_from' => 'sometimes|date_format:Y-m-d',
                'date_to'   => 'sometimes|date_format:Y-m-d|after_or_equal:date_from',
                'status'    => 'sometimes|in:present,late,absent',
            ]);

            $perPage = $validated['per_page'] ?? 15;
            $sortBy = $validated['sort_by'] ?? 'date';
            $sortOrder = $validated['sort_order'] ?? 'desc';

            // Optimized query with eager loading
            $query = Attendance::with(['user:id,name,email', 'user.profile:user_id,phone,address'])
                ->select(['id', 'user_id', 'date', 'check_in_time', 'check_out_time', 'status', 'created_at']);

            // Apply filters
            if (!empty($validated['date_from'])) {
                $query->whereDate('date', '>=', $validated['date_from']);
            }
            if (!empty($validated['date_to'])) {
                $query->whereDate('date', '<=', $validated['date_to']);
            }
            if (!empty($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            // Apply sorting with protection against injection
            $query->orderBy($sortBy, $sortOrder);

            $data = $query->paginate($perPage);

            return ApiResponse::success('All attendance records', $data);
        } catch (ValidationException $e) {
            return ApiResponse::error('Invalid query parameters', $e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('All Attendance Error', ['user_id' => $request->user()->id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to fetch records', null, 500);
        }
    }

    /**
     * Show a specific attendance record detail.
     * 
     * Security: Non-owners can only view if they have attendance.view_all permission
     * 
     * @param Request $request
     * @param int $id Attendance record ID
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            // Validate ID to prevent invalid casting
            if ($id <= 0) {
                throw ValidationException::withMessages(['id' => 'Invalid attendance ID']);
            }

            // Optimized query with eager loading
            $attendance = Attendance::with([
                'user:id,name,email',
                'user.profile:user_id,phone,address,department'
            ])
            ->select(['id', 'user_id', 'date', 'check_in_time', 'check_out_time', 'status', 'notes', 'created_at'])
            ->findOrFail($id);

            $user = $request->user();

            // Authorization: Owner can view own record, others need permission
            if ($attendance->user_id !== $user->id && !$user->hasPermission('attendance.view_all')) {
                return ApiResponse::error('Forbidden', 'You cannot access this record', 403);
            }

            return ApiResponse::success('Attendance detail', $attendance);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', 'Attendance record not found', 404);
        } catch (ValidationException $e) {
            return ApiResponse::error('Invalid request', $e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('Show Attendance Error', ['id' => $id, 'user_id' => $request->user()->id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to fetch record', null, 500);
        }
    }

    /**
     * Delete an attendance record (admin only).
     * 
     * @param Request $request
     * @param int $id Attendance record ID
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            // Validate ID
            if ($id <= 0) {
                throw ValidationException::withMessages(['id' => 'Invalid attendance ID']);
            }

            // Find and soft-delete record (if using soft deletes) or hard delete
            $attendance = Attendance::select(['id', 'user_id', 'date', 'check_in_time', 'check_out_time', 'status'])
                ->findOrFail($id);

            // Convert to array BEFORE deleting to ensure we have the data
            $deleted = $attendance->toArray();

            $attendance->delete();

            \Log::info('Attendance Deleted', [
                'deleted_id' => $id,
                'deleted_by' => $request->user()->id,
                'data' => $deleted
            ]);

            return ApiResponse::success('Attendance record deleted', $deleted);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', 'Attendance record not found', 404);
        } catch (ValidationException $e) {
            return ApiResponse::error('Invalid request', $e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('Delete Attendance Error', ['id' => $id, 'user_id' => $request->user()->id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to delete record', null, 500);
        }
    }
}
