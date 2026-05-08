<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\OvertimeRequest;
use App\Models\OvertimeEvidence;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    /**
     * GET /my/overtime - Employee's own overtime requests
     */
    public function myOvertimeRequests(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            return ApiResponse::error('Employee not found', null, 404);
        }

        $requests = OvertimeRequest::where('employee_id', $employee->id)
            ->with(['attendance', 'approver:id,name', 'evidences'])
            ->latest('date')
            ->get();

        return ApiResponse::success('My overtime requests', $requests);
    }

    /**
     * PUT /my/overtime/{id}/reason - Employee adds reason to overtime request
     */
    public function addReason(Request $request, int $id): JsonResponse
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            return ApiResponse::error('Employee not found', null, 404);
        }

        $overtimeRequest = OvertimeRequest::where('employee_id', $employee->id)
            ->where('id', $id)
            ->first();

        if (!$overtimeRequest) {
            return ApiResponse::error('Overtime request not found', null, 404);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $overtimeRequest->update(['reason' => $validated['reason']]);

        return ApiResponse::success('Reason updated', $overtimeRequest->fresh());
    }

    /**
     * GET /overtime/requests - Admin/Manager view all overtime requests
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR() || $user->isManager())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $query = OvertimeRequest::with(['employee.user:id,name,email', 'attendance', 'approver:id,name', 'evidences'])
            ->latest('date');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return ApiResponse::success('Overtime requests', $query->get());
    }

    /**
     * GET /overtime/requests/pending - Admin/Manager view pending requests
     */
    public function pending(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR() || $user->isManager())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $requests = OvertimeRequest::where('status', 'pending')
            ->with(['employee.user:id,name,email', 'attendance', 'approver:id,name', 'evidences'])
            ->latest('date')
            ->get();

        return ApiResponse::success('Pending overtime requests', $requests);
    }

    /**
     * PUT /overtime/requests/{id}/approve - Approve overtime request
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR() || $user->isManager())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $overtimeRequest = OvertimeRequest::with('employee.user', 'evidences')->findOrFail($id);

        if ($overtimeRequest->status !== 'pending') {
            return ApiResponse::error('Request already processed', null, 422);
        }

        $overtimeRequest->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        // Send notification to employee
        if ($overtimeRequest->employee && $overtimeRequest->employee->user) {
            UserNotification::create([
                'user_id' => $overtimeRequest->employee->user->id,
                'title' => 'Lembur Disetujui',
                'message' => 'Pengajuan lembur Anda tanggal ' . $overtimeRequest->date->format('d M Y') . ' (' . floor($overtimeRequest->overtime_minutes / 60) . 'j ' . ($overtimeRequest->overtime_minutes % 60) . 'm) telah disetujui.',
                'type' => 'overtime',
                'is_read' => false,
            ]);
        }

        return ApiResponse::success('Overtime request approved', $overtimeRequest->fresh(['employee.user', 'approver', 'evidences']));
    }

    /**
     * PUT /overtime/requests/{id}/reject - Reject overtime request
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR() || $user->isManager())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'reject_reason' => 'sometimes|string|max:500',
        ]);

        $overtimeRequest = OvertimeRequest::with('employee.user')->findOrFail($id);

        if ($overtimeRequest->status !== 'pending') {
            return ApiResponse::error('Request already processed', null, 422);
        }

        $overtimeRequest->update([
            'status' => 'rejected',
            'reject_reason' => $validated['reject_reason'] ?? null,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        // Send notification to employee
        if ($overtimeRequest->employee && $overtimeRequest->employee->user) {
            UserNotification::create([
                'user_id' => $overtimeRequest->employee->user->id,
                'title' => 'Lembur Ditolak',
                'message' => 'Pengajuan lembur Anda tanggal ' . $overtimeRequest->date->format('d M Y') . ' telah ditolak.' . ($validated['reject_reason'] ?? ''),
                'type' => 'overtime',
                'is_read' => false,
            ]);
        }

        return ApiResponse::success('Overtime request rejected', $overtimeRequest->fresh(['employee.user', 'approver', 'evidences']));
    }

    /**
     * POST /my/overtime/{id}/evidence - Employee upload evidence for overtime
     */
    public function uploadEvidence(Request $request, int $id): JsonResponse
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            return ApiResponse::error('Employee not found', null, 404);
        }

        $overtimeRequest = OvertimeRequest::where('employee_id', $employee->id)
            ->where('id', $id)
            ->where('status', 'pending')
            ->first();

        if (!$overtimeRequest) {
            return ApiResponse::error('Overtime request not found or already processed', null, 404);
        }

        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $path = $file->store("overtime/{$id}", 'public');

        $evidence = OvertimeEvidence::create([
            'overtime_request_id' => $overtimeRequest->id,
            'uploaded_by' => $request->user()->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_mime' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'pending',
        ]);

        return ApiResponse::success('Evidence uploaded successfully', $evidence->fresh(['uploader']));
    }

    /**
     * GET /my/overtime/{id}/evidences - Employee view their overtime evidences
     */
    public function myOvertimeEvidences(Request $request, int $id): JsonResponse
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            return ApiResponse::error('Employee not found', null, 404);
        }

        $overtimeRequest = OvertimeRequest::where('employee_id', $employee->id)
            ->where('id', $id)
            ->first();

        if (!$overtimeRequest) {
            return ApiResponse::error('Overtime request not found', null, 404);
        }

        $evidences = OvertimeEvidence::where('overtime_request_id', $overtimeRequest->id)
            ->with(['uploader:id,name', 'reviewer:id,name'])
            ->latest()
            ->get();

        return ApiResponse::success('My overtime evidences', $evidences);
    }

    /**
     * GET /overtime/{id}/evidences - Manager/HR/Admin view all evidences for overtime
     */
    public function overtimeEvidences(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR() || $user->isManager())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $overtimeRequest = OvertimeRequest::findOrFail($id);

        $evidences = OvertimeEvidence::where('overtime_request_id', $overtimeRequest->id)
            ->with(['uploader:id,name', 'reviewer:id,name'])
            ->latest()
            ->get();

        return ApiResponse::success('Overtime evidences', $evidences);
    }

    /**
     * PUT /overtime/evidences/{id}/approve - Manager/HR/Admin approve evidence
     */
    public function approveEvidence(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR() || $user->isManager())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $evidence = OvertimeEvidence::with('overtimeRequest.employee.user')->findOrFail($id);

        if ($evidence->status !== 'pending') {
            return ApiResponse::error('Evidence already reviewed', null, 422);
        }

        $evidence->update([
            'status' => 'approved',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);

        // Send notification to employee
        if ($evidence->overtimeRequest && $evidence->overtimeRequest->employee && $evidence->overtimeRequest->employee->user) {
            UserNotification::create([
                'user_id' => $evidence->overtimeRequest->employee->user->id,
                'title' => 'Bukti Lembur Disetujui',
                'message' => 'Bukti lembur Anda untuk tanggal ' . $evidence->overtimeRequest->date->format('d M Y') . ' telah disetujui.',
                'type' => 'overtime',
                'is_read' => false,
            ]);
        }

        return ApiResponse::success('Evidence approved', $evidence->fresh(['uploader', 'reviewer']));
    }

    /**
     * PUT /overtime/evidences/{id}/reject - Manager/HR/Admin reject evidence
     */
    public function rejectEvidence(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR() || $user->isManager())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'review_notes' => 'sometimes|string|max:500',
        ]);

        $evidence = OvertimeEvidence::with('overtimeRequest.employee.user')->findOrFail($id);

        if ($evidence->status !== 'pending') {
            return ApiResponse::error('Evidence already reviewed', null, 422);
        }

        $evidence->update([
            'status' => 'rejected',
            'review_notes' => $validated['review_notes'] ?? null,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);

        // Send notification to employee
        if ($evidence->overtimeRequest && $evidence->overtimeRequest->employee && $evidence->overtimeRequest->employee->user) {
            UserNotification::create([
                'user_id' => $evidence->overtimeRequest->employee->user->id,
                'title' => 'Bukti Lembur Ditolak',
                'message' => 'Bukti lembur Anda untuk tanggal ' . $evidence->overtimeRequest->date->format('d M Y') . ' ditolak. Silakan upload bukti lagi.' . ($validated['review_notes'] ? ' Catatan: ' . $validated['review_notes'] : ''),
                'type' => 'overtime',
                'is_read' => false,
            ]);
        }

        return ApiResponse::success('Evidence rejected', $evidence->fresh(['uploader', 'reviewer']));
    }

    /**
     * POST /overtime/approval - Backward-compatible endpoint accepting JSON payload { id }
     * This helper extracts `id` from the request body and delegates to `approve()`.
     */
    public function approveByBody(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'sometimes|integer',
        ]);

        $id = $validated['id'] ?? $request->query('id');

        if (!$id) {
            return ApiResponse::error('id is required', ['id' => ['The id field is required.']], 422);
        }

        return $this->approve($request, (int) $id);
    }
}
