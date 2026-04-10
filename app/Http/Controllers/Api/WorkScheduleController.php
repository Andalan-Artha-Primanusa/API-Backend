<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WorkScheduleController extends Controller
{
    /**
     * GET /work-schedules
     */
    public function index(Request $request): JsonResponse
    {
        $schedules = WorkSchedule::latest()->get();

        return ApiResponse::success('Work schedules list', $schedules);
    }

    /**
     * POST /work-schedules
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'check_in_time' => 'required|date_format:H:i',
            'grace_period' => 'required|integer|min:0',
            'check_out_time' => 'required|date_format:H:i',
        ]);

        $schedule = WorkSchedule::create($validated);

        return ApiResponse::success('Work schedule created', $schedule, 201);
    }

    /**
     * GET /work-schedules/{id}
     */
    public function show($id): JsonResponse
    {
        $schedule = WorkSchedule::findOrFail($id);

        return ApiResponse::success('Work schedule detail', $schedule);
    }

    /**
     * PUT /work-schedules/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $schedule = WorkSchedule::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'check_in_time' => 'sometimes|date_format:H:i',
            'grace_period' => 'sometimes|integer|min:0',
            'check_out_time' => 'sometimes|date_format:H:i',
        ]);

        $schedule->update($validated);

        return ApiResponse::success('Work schedule updated', $schedule->fresh());
    }

    /**
     * DELETE /work-schedules/{id}
     */
    public function destroy($id): JsonResponse
    {
        $schedule = WorkSchedule::findOrFail($id);
        $deleted = $schedule->toArray();

        $schedule->delete();

        return ApiResponse::success('Work schedule deleted', $deleted);
    }
}
