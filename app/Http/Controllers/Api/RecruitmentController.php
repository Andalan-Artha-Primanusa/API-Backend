<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\JobOpening;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RecruitmentController extends Controller
{
    public function openingsIndex(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|string|in:draft,open,on_hold,closed,cancelled',
            'department' => 'sometimes|string|max:255',
            'search' => 'sometimes|string|max:255',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $query = JobOpening::with(['location', 'creator:id,name,email'])
            ->withCount('candidates')
            ->latest();

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['department'])) {
            $query->where('department', $validated['department']);
        }

        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($builder) use ($search) {
                $builder->where('code', 'like', '%' . $search . '%')
                    ->orWhere('title', 'like', '%' . $search . '%')
                    ->orWhere('department', 'like', '%' . $search . '%')
                    ->orWhere('position_level', 'like', '%' . $search . '%');
            });
        }

        return ApiResponse::success('Job openings retrieved successfully', $query->paginate($validated['per_page'] ?? 15));
    }

    public function openingsStore(Request $request): JsonResponse
    {
        $this->authorizeManage($request);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'position_level' => 'nullable|string|max:255',
            'employment_type' => 'nullable|string|max:255',
            'headcount' => 'sometimes|integer|min:1',
            'description' => 'nullable|string|max:10000',
            'requirements' => 'nullable|string|max:10000',
            'location_id' => 'nullable|integer|exists:locations,id',
            'status' => 'sometimes|string|in:draft,open,on_hold,closed,cancelled',
            'opened_at' => 'nullable|date',
            'closed_at' => 'nullable|date|after_or_equal:opened_at',
        ]);

        $opening = JobOpening::create([
            ...$validated,
            'code' => $this->generateOpeningCode(),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return ApiResponse::success('Job opening created successfully', $opening->load(['location', 'creator:id,name,email']), 201);
    }

    public function openingsShow(Request $request, int $id): JsonResponse
    {
        $opening = JobOpening::with(['location', 'creator:id,name,email', 'candidates.assignee:id,name,email'])
            ->withCount('candidates')
            ->find($id);

        if (!$opening) {
            return ApiResponse::error('Job opening not found', null, 404);
        }

        return ApiResponse::success('Job opening detail', $opening);
    }

    public function openingsUpdate(Request $request, int $id): JsonResponse
    {
        $this->authorizeManage($request);

        $opening = JobOpening::find($id);

        if (!$opening) {
            return ApiResponse::error('Job opening not found', null, 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'department' => 'sometimes|nullable|string|max:255',
            'position_level' => 'sometimes|nullable|string|max:255',
            'employment_type' => 'sometimes|nullable|string|max:255',
            'headcount' => 'sometimes|integer|min:1',
            'description' => 'sometimes|nullable|string|max:10000',
            'requirements' => 'sometimes|nullable|string|max:10000',
            'location_id' => 'sometimes|nullable|integer|exists:locations,id',
            'status' => 'sometimes|string|in:draft,open,on_hold,closed,cancelled',
            'opened_at' => 'sometimes|nullable|date',
            'closed_at' => 'sometimes|nullable|date',
        ]);

        $opening->update([
            ...$validated,
            'updated_by' => $request->user()->id,
        ]);

        return ApiResponse::success('Job opening updated successfully', $opening->fresh(['location', 'creator:id,name,email']));
    }

    public function openingsDestroy(Request $request, int $id): JsonResponse
    {
        $this->authorizeManage($request);

        $opening = JobOpening::withCount('candidates')->find($id);

        if (!$opening) {
            return ApiResponse::error('Job opening not found', null, 404);
        }

        $deleted = $opening->toArray();
        $opening->delete();

        return ApiResponse::success('Job opening deleted successfully', $deleted);
    }

    public function candidatesIndex(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_opening_id' => 'sometimes|integer|exists:job_openings,id',
            'current_stage' => 'sometimes|string|in:applied,screening,interview,offer,hired,rejected,withdrawn',
            'status' => 'sometimes|string|in:active,archived',
            'search' => 'sometimes|string|max:255',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $query = Candidate::with(['opening:id,code,title,status', 'assignee:id,name,email'])
            ->latest();

        if (!empty($validated['job_opening_id'])) {
            $query->where('job_opening_id', $validated['job_opening_id']);
        }

        if (!empty($validated['current_stage'])) {
            $query->where('current_stage', $validated['current_stage']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($builder) use ($search) {
                $builder->where('full_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('source', 'like', '%' . $search . '%');
            });
        }

        return ApiResponse::success('Candidates retrieved successfully', $query->paginate($validated['per_page'] ?? 15));
    }

    public function candidatesStore(Request $request): JsonResponse
    {
        $this->authorizeManage($request);

        $validated = $request->validate([
            'job_opening_id' => 'required|integer|exists:job_openings,id',
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'source' => 'nullable|string|max:100',
            'current_stage' => 'sometimes|string|in:applied,screening,interview,offer,hired,rejected,withdrawn',
            'status' => 'sometimes|string|in:active,archived',
            'score' => 'nullable|numeric|min:0|max:100',
            'expected_salary' => 'nullable|numeric|min:0',
            'applied_at' => 'nullable|date',
            'last_activity_at' => 'nullable|date',
            'notes' => 'nullable|string|max:10000',
            'assigned_to' => 'nullable|integer|exists:users,id',
        ]);

        $candidate = Candidate::create([
            ...$validated,
            'current_stage' => $validated['current_stage'] ?? Candidate::STAGE_APPLIED,
            'status' => $validated['status'] ?? Candidate::STATUS_ACTIVE,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
            'applied_at' => $validated['applied_at'] ?? now()->toDateString(),
            'last_activity_at' => $validated['last_activity_at'] ?? now(),
        ]);

        return ApiResponse::success('Candidate created successfully', $candidate->load(['opening:id,code,title,status', 'assignee:id,name,email']), 201);
    }

    public function candidatesShow(Request $request, int $id): JsonResponse
    {
        $candidate = Candidate::with(['opening.location', 'assignee:id,name,email', 'creator:id,name,email'])
            ->find($id);

        if (!$candidate) {
            return ApiResponse::error('Candidate not found', null, 404);
        }

        return ApiResponse::success('Candidate detail', $candidate);
    }

    public function candidatesUpdate(Request $request, int $id): JsonResponse
    {
        $this->authorizeManage($request);

        $candidate = Candidate::find($id);

        if (!$candidate) {
            return ApiResponse::error('Candidate not found', null, 404);
        }

        $validated = $request->validate([
            'job_opening_id' => 'sometimes|integer|exists:job_openings,id',
            'full_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|nullable|email|max:255',
            'phone' => 'sometimes|nullable|string|max:50',
            'source' => 'sometimes|nullable|string|max:100',
            'current_stage' => 'sometimes|string|in:applied,screening,interview,offer,hired,rejected,withdrawn',
            'status' => 'sometimes|string|in:active,archived',
            'score' => 'sometimes|nullable|numeric|min:0|max:100',
            'expected_salary' => 'sometimes|nullable|numeric|min:0',
            'applied_at' => 'sometimes|nullable|date',
            'last_activity_at' => 'sometimes|nullable|date',
            'notes' => 'sometimes|nullable|string|max:10000',
            'assigned_to' => 'sometimes|nullable|integer|exists:users,id',
        ]);

        $candidate->update([
            ...$validated,
            'updated_by' => $request->user()->id,
            'last_activity_at' => $validated['last_activity_at'] ?? now(),
        ]);

        return ApiResponse::success('Candidate updated successfully', $candidate->fresh(['opening:id,code,title,status', 'assignee:id,name,email']));
    }

    public function candidatesMoveStage(Request $request, int $id): JsonResponse
    {
        $this->authorizeManage($request);

        $candidate = Candidate::find($id);

        if (!$candidate) {
            return ApiResponse::error('Candidate not found', null, 404);
        }

        $validated = $request->validate([
            'current_stage' => 'required|string|in:applied,screening,interview,offer,hired,rejected,withdrawn',
            'notes' => 'nullable|string|max:10000',
        ]);

        $candidate->update([
            'current_stage' => $validated['current_stage'],
            'notes' => $validated['notes'] ?? $candidate->notes,
            'updated_by' => $request->user()->id,
            'last_activity_at' => now(),
            'status' => in_array($validated['current_stage'], [Candidate::STAGE_HIRED, Candidate::STAGE_REJECTED, Candidate::STAGE_WITHDRAWN], true)
                ? Candidate::STATUS_ARCHIVED
                : Candidate::STATUS_ACTIVE,
        ]);

        return ApiResponse::success('Candidate stage updated successfully', $candidate->fresh(['opening:id,code,title,status', 'assignee:id,name,email']));
    }

    public function candidatesDestroy(Request $request, int $id): JsonResponse
    {
        $this->authorizeManage($request);

        $candidate = Candidate::with(['opening:id,code,title,status'])->find($id);

        if (!$candidate) {
            return ApiResponse::error('Candidate not found', null, 404);
        }

        $deleted = $candidate->toArray();
        $candidate->delete();

        return ApiResponse::success('Candidate deleted successfully', $deleted);
    }

    public function summary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_opening_id' => 'sometimes|integer|exists:job_openings,id',
        ]);

        $openingQuery = JobOpening::query();
        $candidateQuery = Candidate::query();

        if (!empty($validated['job_opening_id'])) {
            $candidateQuery->where('job_opening_id', $validated['job_opening_id']);
        }

        $byStage = (clone $candidateQuery)
            ->selectRaw('current_stage, COUNT(*) as total')
            ->groupBy('current_stage')
            ->orderByDesc('total')
            ->get();

        $byStatus = (clone $candidateQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        return ApiResponse::success('Recruitment summary retrieved successfully', [
            'openings' => [
                'total' => (clone $openingQuery)->count(),
                'open' => (clone $openingQuery)->where('status', JobOpening::STATUS_OPEN)->count(),
                'draft' => (clone $openingQuery)->where('status', JobOpening::STATUS_DRAFT)->count(),
                'closed' => (clone $openingQuery)->where('status', JobOpening::STATUS_CLOSED)->count(),
            ],
            'candidates' => [
                'total' => (clone $candidateQuery)->count(),
                'active' => (clone $candidateQuery)->where('status', Candidate::STATUS_ACTIVE)->count(),
                'archived' => (clone $candidateQuery)->where('status', Candidate::STATUS_ARCHIVED)->count(),
                'by_stage' => $byStage,
                'by_status' => $byStatus,
            ],
        ]);
    }

    private function authorizeManage(Request $request): void
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR())) {
            abort(403, 'No permission');
        }
    }

    private function generateOpeningCode(): string
    {
        do {
            $code = 'JOB-' . now()->format('Ymd') . '-' . Str::upper(Str::random(4));
        } while (JobOpening::where('code', $code)->exists());

        return $code;
    }
}
