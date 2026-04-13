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
    
        $data = Payroll::with(['employee.user.profile', 'employee.manager.profile', 'details'])->latest()->get();

        return ApiResponse::success(
            $data->isEmpty() ? 'No payroll data available' : 'Payroll data retrieved successfully',
            $data
        );
    }

    // 📌 MY PAYROLL
    public function myPayroll(Request $request): JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();

        $data = Payroll::with(['employee.user.profile', 'employee.manager.profile', 'details'])
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

                return ApiResponse::success('Payroll created successfully', $payroll->load(['employee.user.profile', 'employee.manager.profile', 'details']), 201);
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
    public function show(Request $request, $id): JsonResponse
    {
        $data = Payroll::with(['employee.user.profile', 'employee.manager.profile', 'details'])->find($id);

        if (!$data) {
            return ApiResponse::error('Payroll not found', null, 404);
        }

        $user = $request->user();

        if ($data->employee?->user_id !== $user->id && !($user->isAdmin() || $user->isHR())) {
            return ApiResponse::error('Forbidden', 'You cannot access this payroll', 403);
        }

        return ApiResponse::success('Payroll details retrieved successfully', $data);
    }

    // 📌 SLIP GAJI SELF-SERVICE
    public function myPayrollSlip(Request $request, int $id): JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();

        $payroll = Payroll::with(['employee.user.profile', 'employee.manager.profile', 'details'])
            ->where('id', $id)
            ->where('employee_id', $employee->id)
            ->first();

        if (!$payroll) {
            return ApiResponse::error('Payroll not found', null, 404);
        }

        return ApiResponse::success('Payroll slip retrieved successfully', $this->buildSlipPayload($payroll));
    }

    // 📌 SLIP GAJI ADMIN/HR
    public function slip(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR())) {
            return ApiResponse::error('Forbidden', 'You are not authorized', 403);
        }

        $payroll = Payroll::with(['employee.user.profile', 'employee.manager.profile', 'details'])->find($id);

        if (!$payroll) {
            return ApiResponse::error('Payroll not found', null, 404);
        }

        return ApiResponse::success('Payroll slip retrieved successfully', $this->buildSlipPayload($payroll));
    }

    // 📌 CSV EXPORT
    public function exportSlipCsv(Request $request, int $id)
    {
        $user = $request->user();
        $payroll = Payroll::with(['employee.user.profile', 'employee.manager.profile', 'details'])->find($id);

        if (!$payroll) {
            return ApiResponse::error('Payroll not found', null, 404);
        }

        if ($payroll->employee?->user_id !== $user->id && !($user->isAdmin() || $user->isHR())) {
            return ApiResponse::error('Forbidden', 'You cannot access this payroll', 403);
        }

        $payload = $this->buildSlipPayload($payroll);
        $filename = 'payroll-slip-' . $payroll->period . '-employee-' . $payroll->employee_id . '.csv';

        $rows = [
            ['Item', 'Amount'],
            ['Employee', $payload['employee']['name']],
            ['Employee Code', $payload['employee']['employee_code']],
            ['Period', $payload['period']],
            ['Status', $payload['status']],
            ['Basic Salary', (string) $payload['summary']['basic_salary']],
            ['Allowance', (string) $payload['summary']['allowance']],
            ['Bonus', (string) $payload['summary']['bonus']],
            ['Gross Pay', (string) $payload['summary']['gross_pay']],
            ['Total Deduction', (string) $payload['summary']['total_deduction']],
            ['Take Home Pay', (string) $payload['summary']['take_home_pay']],
        ];

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    // 📌 PDF EXPORT
    public function exportSlipPdf(Request $request, int $id)
    {
        $user = $request->user();
        $payroll = Payroll::with(['employee.user.profile', 'employee.manager.profile', 'details'])->find($id);

        if (!$payroll) {
            return ApiResponse::error('Payroll not found', null, 404);
        }

        if ($payroll->employee?->user_id !== $user->id && !($user->isAdmin() || $user->isHR())) {
            return ApiResponse::error('Forbidden', 'You cannot access this payroll', 403);
        }

        $payload = $this->buildSlipPayload($payroll);
        $filename = 'payroll-slip-' . str_replace(['/', '\\', ' '], '-', (string) $payroll->period) . '-employee-' . $payroll->employee_id . '.pdf';

        if (class_exists('Barryvdh\\DomPDF\\Facade\\Pdf')) {
            $pdfFacade = 'Barryvdh\\DomPDF\\Facade\\Pdf';
            $pdf = $pdfFacade::loadView('pdf.payroll-slip', [
                'payload' => $payload,
            ]);

            return $pdf->download($filename);
        }

        if (app()->bound('dompdf.wrapper')) {
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.payroll-slip', [
                'payload' => $payload,
            ]);

            return $pdf->download($filename);
        }

        return ApiResponse::error('PDF engine not available. Please install barryvdh/laravel-dompdf.', null, 500);
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

        return ApiResponse::success('Payroll updated successfully', $payroll->fresh(['employee.user.profile', 'employee.manager.profile', 'details']));
    }

    // 📌 DELETE
    public function destroy($id): JsonResponse
    {
        $payroll = Payroll::with(['employee.user.profile', 'employee.manager.profile', 'details'])->find($id);

        if (!$payroll) {
            return ApiResponse::error('Payroll not found', null, 404);
        }

        $deleted = $payroll->toArray();
        $payroll->delete();

        return ApiResponse::success('Payroll deleted successfully', $deleted);
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

        return ApiResponse::success('Payroll approved', $payroll->fresh(['employee.user.profile', 'employee.manager.profile', 'details']));
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

        return ApiResponse::success('Payroll paid', $payroll->fresh(['employee.user.profile', 'employee.manager.profile', 'details']));
    }

    private function buildSlipPayload(Payroll $payroll): array
    {
        $details = $payroll->details->map(function ($detail) {
            return [
                'id' => $detail->id,
                'type' => $detail->type,
                'name' => $detail->name,
                'amount' => (float) $detail->amount,
            ];
        })->values();

        $earnings = $details->where('type', 'allowance')->values();
        $deductions = $details->where('type', 'deduction')->values();

        $grossPay = (float) $payroll->basic_salary + (float) $payroll->allowance + (float) $payroll->bonus;
        $netEarnings = $grossPay + $earnings->sum('amount');
        $netDeductions = (float) $payroll->total_deduction + $deductions->sum('amount');

        return [
            'id' => $payroll->id,
            'period' => $payroll->period,
            'status' => $payroll->status,
            'employee' => [
                'id' => $payroll->employee?->id,
                'employee_code' => $payroll->employee?->employee_code,
                'name' => $payroll->employee?->user?->name,
                'email' => $payroll->employee?->user?->email,
                'department' => $payroll->employee?->department,
                'position' => $payroll->employee?->position,
            ],
            'summary' => [
                'basic_salary' => (float) $payroll->basic_salary,
                'allowance' => (float) $payroll->allowance,
                'bonus' => (float) $payroll->bonus,
                'gross_pay' => $grossPay,
                'additional_allowances' => $earnings->sum('amount'),
                'additional_deductions' => $deductions->sum('amount'),
                'bpjs_kesehatan' => (float) $payroll->bpjs_kesehatan,
                'bpjs_ketenagakerjaan' => (float) $payroll->bpjs_ketenagakerjaan,
                'pph21' => (float) $payroll->pph21,
                'total_deduction' => $netDeductions,
                'take_home_pay' => (float) $payroll->take_home_pay,
            ],
            'earnings' => $earnings,
            'deductions' => $deductions,
            'details' => $details,
        ];
    }


}