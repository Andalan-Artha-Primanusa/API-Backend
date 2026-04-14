<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkforcePolicyController extends Controller
{
    public function holidayCalendarIndex(Request $request): JsonResponse
    {
        return ApiResponse::success('Holiday calendars retrieved successfully', DB::table('holiday_calendars')->orderByDesc('id')->paginate($request->integer('per_page', 15)));
    }

    public function holidayCalendarStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|integer|min:2000|max:2100',
            'active' => 'sometimes|boolean',
            'dates' => 'sometimes|array',
            'dates.*.holiday_date' => 'required_with:dates|date',
            'dates.*.name' => 'required_with:dates|string|max:255',
            'dates.*.is_national' => 'sometimes|boolean',
        ]);

        $calendarId = DB::table('holiday_calendars')->insertGetId([
            'name' => $validated['name'],
            'year' => $validated['year'],
            'active' => $validated['active'] ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (($validated['dates'] ?? []) as $date) {
            DB::table('holiday_dates')->insert([
                'holiday_calendar_id' => $calendarId,
                'holiday_date' => $date['holiday_date'],
                'name' => $date['name'],
                'is_national' => $date['is_national'] ?? true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return ApiResponse::success('Holiday calendar created successfully', DB::table('holiday_calendars')->where('id', $calendarId)->first(), 201);
    }

    public function advancedLeavePolicyUpdate(Request $request, int $policyId): JsonResponse
    {
        $validated = $request->validate([
            'carry_over_enabled' => 'sometimes|boolean',
            'encashment_enabled' => 'sometimes|boolean',
            'blackout_ranges' => 'sometimes|array',
            'holiday_calendar_id' => 'sometimes|nullable|integer|exists:holiday_calendars,id',
        ]);

        if (!DB::table('leave_policies')->where('id', $policyId)->exists()) {
            return ApiResponse::error('Leave policy not found', null, 404);
        }

        $updateData = ['updated_at' => now()];
        if (array_key_exists('carry_over_enabled', $validated)) {
            $updateData['carry_over_enabled'] = $validated['carry_over_enabled'];
        }
        if (array_key_exists('encashment_enabled', $validated)) {
            $updateData['encashment_enabled'] = $validated['encashment_enabled'];
        }
        if (array_key_exists('blackout_ranges', $validated)) {
            $updateData['blackout_ranges'] = json_encode($validated['blackout_ranges']);
        }
        if (array_key_exists('holiday_calendar_id', $validated)) {
            $updateData['holiday_calendar_id'] = $validated['holiday_calendar_id'];
        }

        DB::table('leave_policies')->where('id', $policyId)->update($updateData);

        return ApiResponse::success('Advanced leave policy updated successfully', DB::table('leave_policies')->where('id', $policyId)->first());
    }

    public function shiftSwapIndex(Request $request): JsonResponse
    {
        $data = DB::table('shift_swap_requests')->orderByDesc('id')->paginate($request->integer('per_page', 15));
        return ApiResponse::success('Shift swap requests retrieved successfully', $data);
    }

    public function shiftSwapStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'requester_employee_id' => 'required|integer|exists:employees,id',
            'target_employee_id' => 'required|integer|exists:employees,id|different:requester_employee_id',
            'swap_date' => 'required|date',
            'reason' => 'nullable|string|max:5000',
        ]);

        $id = DB::table('shift_swap_requests')->insertGetId([
            ...$validated,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ApiResponse::success('Shift swap request created successfully', DB::table('shift_swap_requests')->where('id', $id)->first(), 201);
    }

    public function shiftSwapApprove(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:approved,rejected,cancelled',
        ]);

        if (!DB::table('shift_swap_requests')->where('id', $id)->exists()) {
            return ApiResponse::error('Shift swap request not found', null, 404);
        }

        DB::table('shift_swap_requests')->where('id', $id)->update([
            'status' => $validated['status'],
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'updated_at' => now(),
        ]);

        return ApiResponse::success('Shift swap request updated successfully', DB::table('shift_swap_requests')->where('id', $id)->first());
    }

    public function overtimeRuleIndex(Request $request): JsonResponse
    {
        return ApiResponse::success('Overtime rules retrieved successfully', DB::table('overtime_rules')->orderByDesc('id')->paginate($request->integer('per_page', 15)));
    }

    public function overtimeRuleStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'location_id' => 'nullable|integer|exists:locations,id',
            'min_minutes' => 'sometimes|integer|min:0|max:1000',
            'multiplier' => 'sometimes|numeric|min:0',
            'requires_approval' => 'sometimes|boolean',
            'active' => 'sometimes|boolean',
        ]);

        $id = DB::table('overtime_rules')->insertGetId([
            ...$validated,
            'min_minutes' => $validated['min_minutes'] ?? 0,
            'multiplier' => $validated['multiplier'] ?? 1,
            'requires_approval' => $validated['requires_approval'] ?? true,
            'active' => $validated['active'] ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ApiResponse::success('Overtime rule created successfully', DB::table('overtime_rules')->where('id', $id)->first(), 201);
    }
}
