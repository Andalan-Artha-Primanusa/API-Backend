<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnterpriseAtsController extends Controller
{
    public function scheduleInterview(Request $request, int $candidateId): JsonResponse
    {
        $validated = $request->validate([
            'interview_type' => 'sometimes|string|max:100',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'sometimes|integer|min:15|max:480',
            'mode' => 'sometimes|string|max:50',
            'location' => 'nullable|string|max:255',
            'meeting_link' => 'nullable|string|max:1000',
        ]);

        if (!DB::table('candidates')->where('id', $candidateId)->exists()) {
            return ApiResponse::error('Candidate not found', null, 404);
        }

        $id = DB::table('interview_schedules')->insertGetId([
            'candidate_id' => $candidateId,
            'interview_type' => $validated['interview_type'] ?? 'technical',
            'scheduled_at' => $validated['scheduled_at'],
            'duration_minutes' => $validated['duration_minutes'] ?? 60,
            'mode' => $validated['mode'] ?? 'online',
            'location' => $validated['location'] ?? null,
            'meeting_link' => $validated['meeting_link'] ?? null,
            'status' => 'scheduled',
            'created_by' => $request->user()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ApiResponse::success('Interview scheduled successfully', DB::table('interview_schedules')->where('id', $id)->first(), 201);
    }

    public function evaluateInterview(Request $request, int $interviewId): JsonResponse
    {
        $validated = $request->validate([
            'score' => 'nullable|numeric|min:0|max:100',
            'recommendation' => 'nullable|string|max:100',
            'strengths' => 'nullable|string|max:5000',
            'concerns' => 'nullable|string|max:5000',
            'notes' => 'nullable|string|max:5000',
        ]);

        $interview = DB::table('interview_schedules')->where('id', $interviewId)->first();
        if (!$interview) {
            return ApiResponse::error('Interview schedule not found', null, 404);
        }

        $id = DB::table('interview_evaluations')->insertGetId([
            'interview_schedule_id' => $interviewId,
            'candidate_id' => $interview->candidate_id,
            'evaluator_user_id' => $request->user()->id,
            'score' => $validated['score'] ?? null,
            'recommendation' => $validated['recommendation'] ?? null,
            'strengths' => $validated['strengths'] ?? null,
            'concerns' => $validated['concerns'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('interview_schedules')->where('id', $interviewId)->update([
            'status' => 'completed',
            'updated_at' => now(),
        ]);

        return ApiResponse::success('Interview evaluated successfully', DB::table('interview_evaluations')->where('id', $id)->first(), 201);
    }

    public function createOffer(Request $request, int $candidateId): JsonResponse
    {
        $validated = $request->validate([
            'job_opening_id' => 'nullable|integer|exists:job_openings,id',
            'offered_salary' => 'nullable|numeric|min:0',
            'joining_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string|max:5000',
        ]);

        if (!DB::table('candidates')->where('id', $candidateId)->exists()) {
            return ApiResponse::error('Candidate not found', null, 404);
        }

        $id = DB::table('offer_letters')->insertGetId([
            'candidate_id' => $candidateId,
            'job_opening_id' => $validated['job_opening_id'] ?? null,
            'offered_salary' => $validated['offered_salary'] ?? null,
            'joining_date' => $validated['joining_date'] ?? null,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'status' => 'draft',
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ApiResponse::success('Offer letter created successfully', DB::table('offer_letters')->where('id', $id)->first(), 201);
    }

    public function updateOfferStatus(Request $request, int $offerId): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:draft,sent,accepted,rejected,expired,cancelled',
        ]);

        if (!DB::table('offer_letters')->where('id', $offerId)->exists()) {
            return ApiResponse::error('Offer letter not found', null, 404);
        }

        DB::table('offer_letters')->where('id', $offerId)->update([
            'status' => $validated['status'],
            'updated_at' => now(),
        ]);

        return ApiResponse::success('Offer status updated successfully', DB::table('offer_letters')->where('id', $offerId)->first());
    }

    public function upsertBackgroundCheck(Request $request, int $candidateId): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,in_progress,passed,failed,cancelled',
            'requested_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'result_notes' => 'nullable|string|max:5000',
        ]);

        if (!DB::table('candidates')->where('id', $candidateId)->exists()) {
            return ApiResponse::error('Candidate not found', null, 404);
        }

        DB::table('background_checks')->updateOrInsert(
            ['candidate_id' => $candidateId],
            [
                'status' => $validated['status'],
                'requested_at' => $validated['requested_at'] ?? null,
                'completed_at' => $validated['completed_at'] ?? null,
                'result_notes' => $validated['result_notes'] ?? null,
                'verified_by' => $request->user()->id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return ApiResponse::success('Background check updated successfully', DB::table('background_checks')->where('candidate_id', $candidateId)->first());
    }

    public function talentPoolIndex(Request $request): JsonResponse
    {
        $data = DB::table('talent_pool_entries as t')
            ->leftJoin('candidates as c', 'c.id', '=', 't.candidate_id')
            ->leftJoin('job_openings as j', 'j.id', '=', 'c.job_opening_id')
            ->select('t.*', 'c.full_name', 'c.email', 'c.phone', 'c.current_stage', 'j.title as opening_title')
            ->orderByDesc('t.created_at')
            ->paginate($request->integer('per_page', 15));

        return ApiResponse::success('Talent pool retrieved successfully', $data);
    }

    public function addToTalentPool(Request $request, int $candidateId): JsonResponse
    {
        $validated = $request->validate([
            'pool_tag' => 'nullable|string|max:100',
            'status' => 'sometimes|string|in:active,inactive,hired,blacklisted',
            'notes' => 'nullable|string|max:5000',
        ]);

        if (!DB::table('candidates')->where('id', $candidateId)->exists()) {
            return ApiResponse::error('Candidate not found', null, 404);
        }

        DB::table('talent_pool_entries')->updateOrInsert(
            ['candidate_id' => $candidateId],
            [
                'pool_tag' => $validated['pool_tag'] ?? null,
                'status' => $validated['status'] ?? 'active',
                'notes' => $validated['notes'] ?? null,
                'added_by' => $request->user()->id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return ApiResponse::success('Candidate added to talent pool successfully', DB::table('talent_pool_entries')->where('candidate_id', $candidateId)->first());
    }
}
