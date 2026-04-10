<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reimbursement;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreReimbursementRequest;
use App\Traits\HasEmployee;
use Illuminate\Http\JsonResponse;

class ReimbursementController extends Controller
{
    use HasEmployee;

    /*
    |--------------------------------------------------------------------------
    | 🔥 HR / MANAGER / FINANCE (Admin Level)
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Reimbursement::with(['employee.user.profile', 'employee.manager.profile', 'approver.profile']);

        // Scope by manager subordinates if not admin/hr
        if ($user->isManager() && !$user->isAdmin() && !$user->isHR()) {
            $subordinateIds = $user->teamMembers()->pluck('id');
            $query->whereIn('employee_id', $subordinateIds);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        if ($request->has('employee_id') && $request->employee_id) {
            // Further verify they can see this employee if they are a manager
            if ($user->isManager() && !$user->isAdmin() && !$user->isHR()) {
                if (!in_array($request->employee_id, $subordinateIds->toArray())) {
                    return ApiResponse::error('Forbidden', 'Cannot access this employee data', 403);
                }
            }
            $query->where('employee_id', $request->employee_id);
        }

        $reimbursements = $query->latest()->get();

        return ApiResponse::success('Reimbursements retrieved successfully', $reimbursements);
    }

    public function store(StoreReimbursementRequest $request): JsonResponse
    {
        $reimbursement = Reimbursement::create(array_merge(
            $request->validated(),
            ['status' => 'draft']
        ));

        return ApiResponse::success('Reimbursement created successfully', $reimbursement->load(['employee.user.profile', 'employee.manager.profile', 'approver.profile']));
    }

    public function show($id): JsonResponse
    {
        $reimbursement = Reimbursement::with(['employee.user.profile', 'employee.manager.profile', 'approver.profile'])->findOrFail($id);

        return ApiResponse::success('Reimbursement details', $reimbursement);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $reimbursement = Reimbursement::findOrFail($id);

        if (!$reimbursement->isDraft()) {
            return ApiResponse::error('Reimbursement already submitted cannot be updated', null, 400);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'sometimes|numeric|min:0',
            'category' => 'sometimes|string|in:travel,medical,office_supplies,training,meal,accommodation,transportation,other',
            'expense_date' => 'sometimes|date|before_or_equal:today',
            'receipt_path' => 'nullable|string'
        ]);

        $reimbursement->update($request->only([
            'title', 'description', 'amount', 'category', 'expense_date', 'receipt_path'
        ]));

        return ApiResponse::success('Reimbursement updated successfully', $reimbursement->fresh(['employee.user.profile', 'employee.manager.profile', 'approver.profile']));
    }

    public function destroy($id): JsonResponse
    {
        $reimbursement = Reimbursement::with(['employee.user.profile', 'employee.manager.profile', 'approver.profile'])->findOrFail($id);

        if (!$reimbursement->isDraft()) {
            return ApiResponse::error('Submitted reimbursement cannot be deleted', null, 400);
        }

        $deleted = $reimbursement->toArray();
        $reimbursement->delete();

        return ApiResponse::success('Reimbursement deleted successfully', $deleted);
    }

    public function approve(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isManager() && !$user->isHR()) {
            return ApiResponse::error('Forbidden', 'You are not authorized', 403);
        }
        $reimbursement = Reimbursement::findOrFail($id);

        if (!$reimbursement->isSubmitted()) {
            return ApiResponse::error('Only submitted reimbursements can be approved', null, 400);
        }

        $reimbursement->approve($user->id, $request->note);

        return ApiResponse::success('Reimbursement approved', $reimbursement->fresh(['employee.user.profile', 'employee.manager.profile', 'approver.profile']));
    }

    public function reject(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isManager() && !$user->isHR()) {
            return ApiResponse::error('Forbidden', 'You are not authorized', 403);
        }
        $reimbursement = Reimbursement::findOrFail($id);

        if (!$reimbursement->isSubmitted()) {
            return ApiResponse::error('Only submitted reimbursements can be rejected', null, 400);
        }

        $request->validate([
            'note' => 'required|string|max:500'
        ]);

        $reimbursement->reject($user->id, $request->note);

        return ApiResponse::success('Reimbursement rejected', $reimbursement->fresh(['employee.user.profile', 'employee.manager.profile', 'approver.profile']));
    }

    public function markAsPaid(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR()))  {
            return ApiResponse::error('Forbidden', 'You are not authorized', 403);
        }
        $reimbursement = Reimbursement::findOrFail($id);

        if (!$reimbursement->isApproved()) {
            return ApiResponse::error('Only approved reimbursements can be marked as paid', null, 400);
        }

        $reimbursement->markAsPaid();

        return ApiResponse::success('Reimbursement marked as paid', $reimbursement->fresh(['employee.user.profile', 'employee.manager.profile', 'approver.profile']));
    }

    public function pending(): JsonResponse
    {
        $reimbursements = Reimbursement::with(['employee.user.profile', 'employee.manager.profile', 'approver.profile'])
            ->where('status', 'submitted')
            ->latest()
            ->get();

        return ApiResponse::success('Pending reimbursements', $reimbursements);
    }

    public function byEmployee($employee_id): JsonResponse
    {
        $reimbursements = Reimbursement::where('employee_id', $employee_id)
            ->with(['employee.user.profile', 'employee.manager.profile', 'approver.profile'])
            ->latest()
            ->get();

        return ApiResponse::success('Employee reimbursements', $reimbursements);
    }

    public function statistics(Request $request): JsonResponse
    {
        $employeeId = $request->employee_id;

        $stats = [
            'total_count' => Reimbursement::getCountByStatus($employeeId),
            'total_amount' => Reimbursement::getTotalByStatus($employeeId),
            'draft_count' => Reimbursement::getCountByStatus($employeeId, 'draft'),
            'draft_amount' => Reimbursement::getTotalByStatus($employeeId, 'draft'),
            'submitted_count' => Reimbursement::getCountByStatus($employeeId, 'submitted'),
            'submitted_amount' => Reimbursement::getTotalByStatus($employeeId, 'submitted'),
            'approved_count' => Reimbursement::getCountByStatus($employeeId, 'approved'),
            'approved_amount' => Reimbursement::getTotalByStatus($employeeId, 'approved'),
            'paid_count' => Reimbursement::getCountByStatus($employeeId, 'paid'),
            'paid_amount' => Reimbursement::getTotalByStatus($employeeId, 'paid'),
        ];

        return ApiResponse::success('Reimbursement statistics', $stats);
    }

    /*
    |--------------------------------------------------------------------------
    | 👤 EMPLOYEE SELF-SERVICE (ESS)
    |--------------------------------------------------------------------------
    */

    public function myReimbursements(Request $request): JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();

        $query = Reimbursement::where('employee_id', $employee->id)
            ->with(['employee.user.profile', 'employee.manager.profile', 'approver.profile']);

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $reimbursements = $query->latest()->get();

        return ApiResponse::success('My Reimbursements', $reimbursements);
    }

    public function submit(Request $request, $id): JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        $reimbursement = Reimbursement::findOrFail($id);

        if ($reimbursement->employee_id !== $employee->id) {
            return ApiResponse::error('Access denied to this reimbursement', null, 403);
        }

        if (!$reimbursement->isDraft()) {
            return ApiResponse::error('Reimbursement is already submitted or processed', null, 400);
        }

        $reimbursement->submit();

        return ApiResponse::success('Reimbursement submitted', $reimbursement->fresh(['employee.user.profile', 'employee.manager.profile', 'approver.profile']));
    }

    public function createMyReimbursement(StoreReimbursementRequest $request): JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();

        $reimbursement = Reimbursement::create(array_merge(
            $request->validated(),
            [
                'employee_id' => $employee->id,
                'status' => 'draft'
            ]
        ));

        return ApiResponse::success('My Reimbursement created successfully', $reimbursement->load(['employee.user.profile', 'employee.manager.profile', 'approver.profile']));
    }
}