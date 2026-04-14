<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\PerformanceReview;
use App\Models\ReviewCycle;
use App\Traits\HasEmployee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformanceReviewController extends Controller
{
    use HasEmployee;

    public function cyclesIndex(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|string|in:draft,open,closed',
            'year' => 'sometimes|integer|min:2000|max:2100',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $query = ReviewCycle::withCount('reviews')->latest();

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['year'])) {
            $query->where('year', $validated['year']);
        }

        return ApiResponse::success('Review cycles retrieved successfully', $query->paginate($validated['per_page'] ?? 15));
    }

    public function cyclesStore(Request $request): JsonResponse
    {
        $this->authorizeManage($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'period_type' => 'sometimes|string|in:monthly,quarterly,semiannual,annual,custom',
            'year' => 'required|integer|min:2000|max:2100',
            'quarter' => 'nullable|integer|min:1|max:4',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'sometimes|string|in:draft,open,closed',
            'description' => 'nullable|string|max:5000',
        ]);

        $cycle = ReviewCycle::create([
            ...$validated,
            'period_type' => $validated['period_type'] ?? 'quarterly',
            'status' => $validated['status'] ?? ReviewCycle::STATUS_DRAFT,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return ApiResponse::success('Review cycle created successfully', $cycle, 201);
    }

    public function cyclesShow(Request $request, int $id): JsonResponse
    {
        $cycle = ReviewCycle::with(['reviews.employee.user.profile', 'reviews.reviewer:id,name,email'])
            ->withCount('reviews')
            ->find($id);

        if (!$cycle) {
            return ApiResponse::error('Review cycle not found', null, 404);
        }

        return ApiResponse::success('Review cycle detail', $cycle);
    }

    public function cyclesUpdate(Request $request, int $id): JsonResponse
    {
        $this->authorizeManage($request);

        $cycle = ReviewCycle::find($id);

        if (!$cycle) {
            return ApiResponse::error('Review cycle not found', null, 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'period_type' => 'sometimes|string|in:monthly,quarterly,semiannual,annual,custom',
            'year' => 'sometimes|integer|min:2000|max:2100',
            'quarter' => 'sometimes|nullable|integer|min:1|max:4',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'status' => 'sometimes|string|in:draft,open,closed',
            'description' => 'sometimes|nullable|string|max:5000',
        ]);

        $cycle->update([
            ...$validated,
            'updated_by' => $request->user()->id,
        ]);

        return ApiResponse::success('Review cycle updated successfully', $cycle->fresh());
    }

    public function cyclesClose(Request $request, int $id): JsonResponse
    {
        $this->authorizeManage($request);

        $cycle = ReviewCycle::find($id);

        if (!$cycle) {
            return ApiResponse::error('Review cycle not found', null, 404);
        }

        $cycle->update([
            'status' => ReviewCycle::STATUS_CLOSED,
            'updated_by' => $request->user()->id,
        ]);

        return ApiResponse::success('Review cycle closed successfully', $cycle->fresh());
    }

    public function reviewsIndex(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'review_cycle_id' => 'sometimes|integer|exists:review_cycles,id',
            'employee_id' => 'sometimes|integer|exists:employees,id',
            'reviewer_user_id' => 'sometimes|integer|exists:users,id',
            'status' => 'sometimes|string|in:draft,submitted,reviewed,approved,rejected',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $query = PerformanceReview::with(['cycle', 'employee.user.profile', 'reviewer:id,name,email', 'kpi'])
            ->latest();

        if (!empty($validated['review_cycle_id'])) {
            $query->where('review_cycle_id', $validated['review_cycle_id']);
        }

        if (!empty($validated['employee_id'])) {
            $query->where('employee_id', $validated['employee_id']);
        }

        if (!empty($validated['reviewer_user_id'])) {
            $query->where('reviewer_user_id', $validated['reviewer_user_id']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        return ApiResponse::success('Performance reviews retrieved successfully', $query->paginate($validated['per_page'] ?? 15));
    }

    public function reviewsStore(Request $request): JsonResponse
    {
        $this->authorizeManage($request);

        $validated = $request->validate([
            'review_cycle_id' => 'required|integer|exists:review_cycles,id',
            'employee_id' => 'required|integer|exists:employees,id',
            'reviewer_user_id' => 'required|integer|exists:users,id',
            'kpi_id' => 'nullable|integer|exists:kpis,id',
            'score' => 'nullable|numeric|min:0|max:100',
            'status' => 'sometimes|string|in:draft,submitted,reviewed,approved,rejected',
            'strengths' => 'nullable|string|max:5000',
            'improvements' => 'nullable|string|max:5000',
            'feedback' => 'nullable|string|max:5000',
            'reviewer_comment' => 'nullable|string|max:5000',
        ]);

        $review = PerformanceReview::create([
            ...$validated,
            'status' => $validated['status'] ?? PerformanceReview::STATUS_DRAFT,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return ApiResponse::success('Performance review created successfully', $review->load(['cycle', 'employee.user.profile', 'reviewer:id,name,email', 'kpi']), 201);
    }

    public function reviewsShow(Request $request, int $id): JsonResponse
    {
        $review = PerformanceReview::with(['cycle', 'employee.user.profile', 'reviewer:id,name,email', 'kpi'])
            ->find($id);

        if (!$review) {
            return ApiResponse::error('Performance review not found', null, 404);
        }

        return ApiResponse::success('Performance review detail', $review);
    }

    public function reviewsUpdate(Request $request, int $id): JsonResponse
    {
        $review = PerformanceReview::find($id);

        if (!$review) {
            return ApiResponse::error('Performance review not found', null, 404);
        }

        $user = $request->user();
        $employee = $this->getAuthenticatedEmployee();
        $isOwner = $employee->id === $review->employee_id;

        if (!($user->isAdmin() || $user->isHR() || $user->isManager() || $isOwner)) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'review_cycle_id' => 'sometimes|integer|exists:review_cycles,id',
            'reviewer_user_id' => 'sometimes|integer|exists:users,id',
            'kpi_id' => 'sometimes|nullable|integer|exists:kpis,id',
            'score' => 'sometimes|nullable|numeric|min:0|max:100',
            'status' => 'sometimes|string|in:draft,submitted,reviewed,approved,rejected',
            'strengths' => 'sometimes|nullable|string|max:5000',
            'improvements' => 'sometimes|nullable|string|max:5000',
            'feedback' => 'sometimes|nullable|string|max:5000',
            'reviewer_comment' => 'sometimes|nullable|string|max:5000',
        ]);

        $review->update([
            ...$validated,
            'updated_by' => $user->id,
        ]);

        return ApiResponse::success('Performance review updated successfully', $review->fresh(['cycle', 'employee.user.profile', 'reviewer:id,name,email', 'kpi']));
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        $review = PerformanceReview::find($id);

        if (!$review) {
            return ApiResponse::error('Performance review not found', null, 404);
        }

        $employee = $this->getAuthenticatedEmployee();

        if ($employee->id !== $review->employee_id) {
            return ApiResponse::error('Forbidden', 'You cannot submit this review', 403);
        }

        if ($review->status !== PerformanceReview::STATUS_DRAFT) {
            return ApiResponse::error('Invalid status', 'Only draft review can be submitted', 400);
        }

        $review->update([
            'status' => PerformanceReview::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'updated_by' => $request->user()->id,
        ]);

        return ApiResponse::success('Performance review submitted successfully', $review->fresh(['cycle', 'employee.user.profile', 'reviewer:id,name,email', 'kpi']));
    }

    public function review(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR() || $user->isManager())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $record = PerformanceReview::find($id);

        if (!$record) {
            return ApiResponse::error('Performance review not found', null, 404);
        }

        $validated = $request->validate([
            'score' => 'nullable|numeric|min:0|max:100',
            'reviewer_comment' => 'nullable|string|max:5000',
            'feedback' => 'nullable|string|max:5000',
        ]);

        $record->update([
            'score' => $validated['score'] ?? $record->score,
            'reviewer_comment' => $validated['reviewer_comment'] ?? $record->reviewer_comment,
            'feedback' => $validated['feedback'] ?? $record->feedback,
            'status' => PerformanceReview::STATUS_REVIEWED,
            'reviewed_at' => now(),
            'updated_by' => $user->id,
        ]);

        return ApiResponse::success('Performance review marked as reviewed', $record->fresh(['cycle', 'employee.user.profile', 'reviewer:id,name,email', 'kpi']));
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $record = PerformanceReview::find($id);

        if (!$record) {
            return ApiResponse::error('Performance review not found', null, 404);
        }

        if (!in_array($record->status, [PerformanceReview::STATUS_SUBMITTED, PerformanceReview::STATUS_REVIEWED], true)) {
            return ApiResponse::error('Invalid status', 'Review must be submitted or reviewed first', 400);
        }

        $record->update([
            'status' => PerformanceReview::STATUS_APPROVED,
            'approved_at' => now(),
            'updated_by' => $user->id,
        ]);

        return ApiResponse::success('Performance review approved successfully', $record->fresh(['cycle', 'employee.user.profile', 'reviewer:id,name,email', 'kpi']));
    }

    public function myReviews(Request $request): JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();

        $reviews = PerformanceReview::with(['cycle', 'reviewer:id,name,email', 'kpi'])
            ->where('employee_id', $employee->id)
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return ApiResponse::success('My performance reviews retrieved successfully', $reviews);
    }

    public function summary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'review_cycle_id' => 'sometimes|integer|exists:review_cycles,id',
        ]);

        $query = PerformanceReview::query();

        if (!empty($validated['review_cycle_id'])) {
            $query->where('review_cycle_id', $validated['review_cycle_id']);
        }

        $byStatus = (clone $query)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        return ApiResponse::success('Performance review summary retrieved successfully', [
            'total_reviews' => (clone $query)->count(),
            'average_score' => round((float) ((clone $query)->whereNotNull('score')->avg('score') ?? 0), 2),
            'approved_reviews' => (clone $query)->where('status', PerformanceReview::STATUS_APPROVED)->count(),
            'pending_reviews' => (clone $query)->whereIn('status', [PerformanceReview::STATUS_DRAFT, PerformanceReview::STATUS_SUBMITTED, PerformanceReview::STATUS_REVIEWED])->count(),
            'by_status' => $byStatus,
        ]);
    }

    private function authorizeManage(Request $request): void
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR() || $user->isManager())) {
            abort(403, 'No permission');
        }
    }
}
