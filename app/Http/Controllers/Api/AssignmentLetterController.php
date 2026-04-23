<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Models\AssignmentLetter;
use App\Models\ApprovalFlow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;


class AssignmentLetterController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = AssignmentLetter::with(['user.profile', 'approvalFlow.steps.role', 'approver.profile']);
        if (!$user->isAdmin() && !$user->isHR()) {
            $query->where('user_id', $user->id);
        }
        $letters = $query->latest()->paginate(15);
        return ApiResponse::success('Assignment letters', $letters);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
        ]);
        $flow = ApprovalFlow::where('module', 'assignment_letter')->first();
        if (!$flow) {
            return ApiResponse::error('Approval flow for assignment letter not configured', null, 500);
        }
        $letter = AssignmentLetter::create([
            ...$validated,
            'user_id' => $user->id,
            'approval_flow_id' => $flow->id,
            'current_step' => 1,
            'status' => 'pending',
        ]);
        return ApiResponse::success('Assignment letter submitted', $letter, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $letter = AssignmentLetter::with(['user.profile', 'approvalFlow.steps.role', 'approver.profile'])->findOrFail($id);
        $user = $request->user();
        if ($letter->user_id !== $user->id && !$user->isAdmin() && !$user->isHR()) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }
        return ApiResponse::success('Assignment letter detail', $letter);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $letter = AssignmentLetter::with('approvalFlow.steps.role')->findOrFail($id);
        if ($letter->status !== 'pending') {
            return ApiResponse::error('Assignment letter already processed', null, 400);
        }
        $step = $letter->approvalFlow->steps->where('step_order', $letter->current_step)->first();
        if (!$step) {
            return ApiResponse::error('Approval step not found', null, 500);
        }
        if (!$user->hasRole($step->role->name)) {
            return ApiResponse::error('It is not your turn to approve', null, 403);
        }
        $nextStep = $letter->approvalFlow->steps->where('step_order', $letter->current_step + 1)->first();
        if ($nextStep) {
            $letter->update(['current_step' => $letter->current_step + 1]);
            return ApiResponse::success('Assignment letter advanced to next approval step', $letter->fresh(['approvalFlow.steps.role', 'approver.profile']));
        }
        $letter->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
        return ApiResponse::success('Assignment letter approved', $letter->fresh(['approvalFlow.steps.role', 'approver.profile']));
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $letter = AssignmentLetter::with('approvalFlow.steps.role')->findOrFail($id);
        if ($letter->status !== 'pending') {
            return ApiResponse::error('Assignment letter already processed', null, 400);
        }
        $step = $letter->approvalFlow->steps->where('step_order', $letter->current_step)->first();
        if (!$step) {
            return ApiResponse::error('Approval step not found', null, 500);
        }
        if (!$user->hasRole($step->role->name)) {
            return ApiResponse::error('It is not your turn to approve', null, 403);
        }
        $letter->update(['status' => 'rejected']);
        return ApiResponse::success('Assignment letter rejected', $letter->fresh(['approvalFlow.steps.role', 'approver.profile']));
    }

    public function generatePdf(Request $request, int $id): JsonResponse
    {
        $letter = AssignmentLetter::with(['user.profile', 'user.employee'])->findOrFail($id);
        
        if ($letter->status !== 'approved') {
            return ApiResponse::error('Only approved assignment letters can generate PDF', null, 400);
        }

        $user = $request->user();
        if ($letter->user_id !== $user->id && !$user->isAdmin() && !$user->isHR()) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $now = now();
        $filename = 'assignment-letter-' . $letter->id . '-' . $now->format('YmdHis') . '.pdf';

        $pdf = Pdf::loadView('pdf.assignment-letter', [
            'letter' => $letter,
            'date' => $now->toDateString(),
        ]);

        $pdfContent = $pdf->output();
        $storedPath = 'employee-documents/' . $letter->user->employee->id . '/' . $filename;
        Storage::disk('public')->put($storedPath, $pdfContent);

        return ApiResponse::success('Surat tugas berhasil dibuat', [
            'file_url' => asset('storage/' . $storedPath),
            'filename' => $filename
        ]);
    }
}

