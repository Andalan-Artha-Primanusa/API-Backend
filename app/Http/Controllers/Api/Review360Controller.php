<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Review360;
use App\Models\Review360Feeder;
use App\Models\CalibrationSession;
use App\Models\CalibrationEmployeeReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Review360Controller extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Review360::query();

        if ($request->has('cycle_id')) {
            $query->where('cycle_id', $request->integer('cycle_id'));
        }

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->integer('employee_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->string('status'));
        }

        $reviews = $query->with(['cycle', 'employee', 'manager', 'feeders'])
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 15));

        return ApiResponse::success('360 Reviews retrieved successfully', $reviews);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cycle_id' => 'required|integer|exists:review_cycles,id',
            'employee_id' => 'required|integer|exists:employees,id',
            'manager_id' => 'required|integer|exists:users,id',
            'feeders_required' => 'sometimes|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $validated['status'] = 'pending';
        $validated['feeders_required'] = $validated['feeders_required'] ?? 3;

        $review = Review360::create($validated);

        return ApiResponse::success('360 Review created successfully', $review->load(['cycle', 'employee', 'manager']), 201);
    }

    public function show($id): JsonResponse
    {
        $review = Review360::with(['cycle', 'employee', 'manager', 'feeders.feeder'])->findOrFail($id);
        return ApiResponse::success('360 Review retrieved successfully', $review);
    }

    public function assignFeeders(Request $request, $id): JsonResponse
    {
        $review = Review360::findOrFail($id);

        $validated = $request->validate([
            'feeder_ids' => 'required|array|min:1',
            'feeder_ids.*' => 'integer|exists:users,id',
            'feeder_type' => 'required|string|in:peer,subordinate,manager,cross_functional',
        ]);

        foreach ($validated['feeder_ids'] as $feederId) {
            Review360Feeder::updateOrCreate(
                ['review_360_id' => $id, 'feeder_id' => $feederId],
                [
                    'feeder_type' => $validated['feeder_type'],
                    'status' => 'pending',
                ]
            );
        }

        $review->update(['feeders_received' => 0]);

        return ApiResponse::success('Feeders assigned successfully', $review->fresh(['feeders']));
    }

    public function submitFeederFeedback(Request $request, $reviewId, $feederId): JsonResponse
    {
        $feeder = Review360Feeder::where('review_360_id', $reviewId)
            ->where('feeder_id', $feederId)
            ->firstOrFail();

        $validated = $request->validate([
            'feedback' => 'required|string',
            'competency_ratings' => 'sometimes|array',
            'rating' => 'sometimes|integer|min:1|max:5',
        ]);

        $feeder->submitFeedback(
            $validated['feedback'],
            $validated['competency_ratings'] ?? null,
            $validated['rating'] ?? null
        );

        // Update received count
        $review = Review360::findOrFail($reviewId);
        $receivedCount = Review360Feeder::where('review_360_id', $reviewId)
            ->where('status', 'submitted')
            ->count();
        $review->update(['feeders_received' => $receivedCount]);

        return ApiResponse::success('Feedback submitted successfully', $feeder->fresh());
    }

    public function submitSelfAssessment(Request $request, $id): JsonResponse
    {
        $review = Review360::findOrFail($id);

        $validated = $request->validate([
            'self_assessment' => 'required|string',
        ]);

        $review->update($validated);

        return ApiResponse::success('Self assessment submitted', $review->fresh());
    }

    public function submitManagerAssessment(Request $request, $id): JsonResponse
    {
        $review = Review360::findOrFail($id);

        $validated = $request->validate([
            'manager_notes' => 'nullable|string',
            'manager_competency_ratings' => 'sometimes|array',
            'overall_score' => 'sometimes|numeric|min:0|max:100',
        ]);

        $review->update($validated);

        return ApiResponse::success('Manager assessment submitted', $review->fresh());
    }

    public function completeReview($id): JsonResponse
    {
        $review = Review360::findOrFail($id);

        $review->markComplete();

        return ApiResponse::success('360 Review marked as completed', $review->fresh());
    }

    public function submitForReview($id): JsonResponse
    {
        $review = Review360::findOrFail($id);

        if ($review->status !== 'in_progress') {
            return ApiResponse::error('Review must be in progress to submit', null, 422);
        }

        $review->submitForReview();

        return ApiResponse::success('360 Review submitted for review', $review->fresh());
    }

    public function approveReview($id): JsonResponse
    {
        $review = Review360::findOrFail($id);

        if ($review->status !== 'reviewed') {
            return ApiResponse::error('Review must be reviewed before approval', null, 422);
        }

        $review->approve();

        return ApiResponse::success('360 Review approved', $review->fresh());
    }

    public function getFeederStatus($id): JsonResponse
    {
        $review = Review360::with('feeders.feeder')->findOrFail($id);

        $feeders = $review->feeders->map(function ($feeder) {
            return [
                'id' => $feeder->id,
                'feeder_name' => $feeder->feeder->name,
                'feeder_email' => $feeder->feeder->email,
                'type' => $feeder->feeder_type,
                'status' => $feeder->status,
                'submitted_at' => $feeder->submitted_at,
            ];
        });

        return ApiResponse::success('Feeder status retrieved', [
            'completion_percentage' => $review->getCompletionPercentage(),
            'feeders_required' => $review->feeders_required,
            'feeders_received' => $review->feeders_received,
            'feeders' => $feeders,
        ]);
    }
}
