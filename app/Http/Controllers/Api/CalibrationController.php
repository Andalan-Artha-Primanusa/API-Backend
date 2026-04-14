<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\CalibrationSession;
use App\Models\CalibrationParticipant;
use App\Models\CalibrationEmployeeReview;
use App\Models\Review360;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalibrationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CalibrationSession::query();

        if ($request->has('cycle_id')) {
            $query->where('cycle_id', $request->integer('cycle_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->string('status'));
        }

        $sessions = $query->with(['cycle', 'facilitator', 'participants'])
            ->orderByDesc('scheduled_at')
            ->paginate($request->integer('per_page', 15));

        return ApiResponse::success('Calibration sessions retrieved', $sessions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cycle_id' => 'required|integer|exists:review_cycles,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date_time',
        ]);

        $validated['status'] = 'scheduled';
        $validated['facilitator_id'] = $request->user()->id;

        $session = CalibrationSession::create($validated);

        return ApiResponse::success('Calibration session created', $session->load(['cycle', 'facilitator']), 201);
    }

    public function show($id): JsonResponse
    {
        $session = CalibrationSession::with(['cycle', 'facilitator', 'participants.manager', 'employeeReviews'])
            ->findOrFail($id);

        return ApiResponse::success('Calibration session retrieved', $session);
    }

    public function addParticipants(Request $request, $id): JsonResponse
    {
        $session = CalibrationSession::findOrFail($id);

        $validated = $request->validate([
            'manager_ids' => 'required|array|min:1',
            'manager_ids.*' => 'integer|exists:users,id',
            'role' => 'required|string|in:facilitator,participant,observer',
        ]);

        foreach ($validated['manager_ids'] as $managerId) {
            CalibrationParticipant::updateOrCreate(
                ['calibration_session_id' => $id, 'manager_id' => $managerId],
                ['role' => $validated['role']]
            );
        }

        $session->update(['participants_count' => $session->participants()->count()]);

        return ApiResponse::success('Participants added', $session->fresh(['participants.manager']));
    }

    public function addReviewsForCalibration(Request $request, $id): JsonResponse
    {
        $session = CalibrationSession::findOrFail($id);

        $validated = $request->validate([
            'review_360_ids' => 'required|array|min:1',
            'review_360_ids.*' => 'integer|exists:review_360s,id',
        ]);

        foreach ($validated['review_360_ids'] as $reviewId) {
            $review = Review360::findOrFail($reviewId);

            CalibrationEmployeeReview::updateOrCreate(
                ['calibration_session_id' => $id, 'review_360_id' => $reviewId],
                [
                    'employee_id' => $review->employee_id,
                    'initial_score' => $review->overall_score,
                ]
            );
        }

        return ApiResponse::success('Reviews added for calibration', $session->fresh(['employeeReviews']));
    }

    public function startSession($id): JsonResponse
    {
        $session = CalibrationSession::findOrFail($id);

        if ($session->status !== 'scheduled') {
            return ApiResponse::error('Session must be scheduled to start', null, 422);
        }

        $session->start();

        return ApiResponse::success('Calibration session started', $session->fresh());
    }

    public function calibrateEmployee(Request $request, $sessionId, $calibrationReviewId): JsonResponse
    {
        $calibrationReview = CalibrationEmployeeReview::findOrFail($calibrationReviewId);

        if ($calibrationReview->calibration_session_id != $sessionId) {
            return ApiResponse::error('Review does not belong to this session', null, 422);
        }

        $validated = $request->validate([
            'calibrated_score' => 'required|numeric|min:0|max:100',
            'rating_category' => 'required|string|in:exceeds,meets,developing,needs_improvement',
            'discussion_notes' => 'nullable|string',
            'aligned' => 'sometimes|boolean',
        ]);

        $calibrationReview->update($validated);

        return ApiResponse::success('Employee review calibrated', $calibrationReview->fresh());
    }

    public function getCalibrationReport($id): JsonResponse
    {
        $session = CalibrationSession::with(['employeeReviews.employee', 'employeeReviews.review360'])
            ->findOrFail($id);

        $report = [
            'session_name' => $session->name,
            'status' => $session->status,
            'total_reviews' => $session->employeeReviews->count(),
            'calibration_data' => $session->employeeReviews->map(function ($calReview) {
                return [
                    'employee_id' => $calReview->employee_id,
                    'employee_name' => $calReview->employee->user->name ?? 'N/A',
                    'initial_score' => $calReview->initial_score,
                    'calibrated_score' => $calReview->calibrated_score,
                    'rating_category' => $calReview->rating_category,
                    'aligned' => $calReview->aligned,
                ];
            }),
            'score_distribution' => [
                'exceeds' => $session->employeeReviews->where('rating_category', 'exceeds')->count(),
                'meets' => $session->employeeReviews->where('rating_category', 'meets')->count(),
                'developing' => $session->employeeReviews->where('rating_category', 'developing')->count(),
                'needs_improvement' => $session->employeeReviews->where('rating_category', 'needs_improvement')->count(),
            ],
        ];

        return ApiResponse::success('Calibration report retrieved', $report);
    }

    public function completeSession($id): JsonResponse
    {
        $session = CalibrationSession::findOrFail($id);

        if ($session->status !== 'in_progress') {
            return ApiResponse::error('Session must be in progress to complete', null, 422);
        }

        $session->complete();

        return ApiResponse::success('Calibration session completed', $session->fresh());
    }

    public function destroy($id): JsonResponse
    {
        $session = CalibrationSession::findOrFail($id);

        if ($session->status !== 'scheduled') {
            return ApiResponse::error('Only scheduled sessions can be deleted', null, 422);
        }

        $session->delete();

        return ApiResponse::success('Calibration session deleted');
    }
}
