<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use App\Helpers\ApiResponse;
use App\Traits\HasEmployee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\PayrollService;

class PayrollController extends Controller
{
    use HasEmployee;

    public function __construct(
        protected PayrollService $payrollService
    ) {}

    // 📌 GET ALL
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR()))  {
            return ApiResponse::error('Forbidden', 'You are not authorized', 403);
        }
    
        $data = Payroll::with(['employee', 'details'])->latest()->get();

        return ApiResponse::success(
            $data->isEmpty() ? 'No payroll data available' : 'Payroll data retrieved successfully',
            $data
        );
    }

    // 📌 MY PAYROLL
    public function myPayroll(Request $request): JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();

        $data = Payroll::with('details')
            ->where('employee_id', $employee->id)
            ->latest()
            ->get();

        return ApiResponse::success(
            $data->isEmpty() ? 'No payroll data found' : 'Payroll retrieved successfully',
            $data
        );
    }

    // 📌 STORE
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR()))  {
            return ApiResponse::error('Forbidden', 'You are not authorized', 403);
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period' => 'required'
        ]);

        return DB::transaction(function () use ($request) {
            $employee = Employee::findOrFail($request->employee_id);

            $exists = Payroll::where('employee_id', $employee->id)
                ->where('period', $request->period)
                ->exists();

            if ($exists) {
                return ApiResponse::error('Payroll for this period already exists', null, 400);
            }

            try {
                $payroll = $this->payrollService->calculateAndCreate(
                    $employee,
                    $request->period,
                    $request->allowance ?? 0,
                    $request->bonus ?? 0
                );

                return ApiResponse::success('Payroll created successfully', $payroll, 201);
            } catch (\DomainException $e) {
                return ApiResponse::error($e->getMessage(), null, 400);
            }
        });
    }

    // 🔥 GENERATE BULK
    public function generateMonthly(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR()))  {
            return ApiResponse::error('Forbidden', 'You are not authorized', 403);
        }

        $request->validate([
            'period' => 'required'
        ]);

        try {
            $result = $this->payrollService->generateMonthlyBulk($request->period);
            
            return ApiResponse::success('Payroll generation completed successfully', [
                'total' => count($result),
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return ApiResponse::error('Error generating payroll', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 📌 DETAIL
    public function show($id): JsonResponse
    {
        $data = Payroll::with(['employee', 'details'])->find($id);

        if (!$data) {
            return ApiResponse::error('Payroll not found', null, 404);
        }

        return ApiResponse::success('Payroll details retrieved successfully', $data);
    }

    // 📌 UPDATE
    public function update(Request $request, int $id): JsonResponse
    {
        $payroll = Payroll::find($id);

        if (!$payroll) {
            return ApiResponse::error('Payroll not found', null, 404);
        }

        if ($payroll->status !== 'draft') {
            return ApiResponse::error('Cannot edit payroll that has been processed', null, 400);
        }

        $validated = $request->validate([
            'allowance' => 'sometimes|numeric|min:0',
            'bonus'     => 'sometimes|numeric|min:0',
        ]);

        $payroll->update($validated);

        return ApiResponse::success('Payroll updated successfully', $payroll->fresh());
    }

    // 📌 DELETE
    public function destroy($id): JsonResponse
    {
        $payroll = Payroll::find($id);

        if (!$payroll) {
            return ApiResponse::error('Payroll not found', null, 404);
        }

        $payroll->delete();

        return ApiResponse::success('Payroll deleted successfully');
    }

    // 🔥 APPROVE
    public function approve(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR()))  {
            return ApiResponse::error('Forbidden', 'You are not authorized', 403);
        }

        $payroll = Payroll::find($id);

        if (!$payroll) {
            return ApiResponse::error('Payroll not found', null, 404);
        }

        if ($payroll->status !== 'draft') {
            return ApiResponse::error('Payroll has already been processed', null, 400);
        }

        $payroll->update(['status' => 'approved']);

        return ApiResponse::success('Payroll approved', $payroll);
    }

    // 💸 PAY
    public function pay(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR()))  {
            return ApiResponse::error('Forbidden', 'You are not authorized', 403);
        }

        $payroll = Payroll::find($id);

        if (!$payroll) {
            return ApiResponse::error('Payroll not found', null, 404);
        }

        if ($payroll->status !== 'approved') {
            return ApiResponse::error('Payroll must be approved first', null, 400);
        }

        $payroll->update(['status' => 'paid']);

        return ApiResponse::success('Payroll paid', $payroll);
    }


}