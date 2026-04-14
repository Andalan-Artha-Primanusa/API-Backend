<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\UserNotification;
use App\Traits\HasEmployee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HrServiceRequestController extends Controller
{
    use HasEmployee;

    public function slaSummary(Request $request): JsonResponse
    {
        if (!($request->user()->isAdmin() || $request->user()->isHR() || $request->user()->isManager())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'days' => 'sometimes|integer|min:1|max:365',
            'category' => 'sometimes|string|max:100',
            'priority' => 'sometimes|string|in:low,medium,high,urgent',
        ]);

        $days = (int) ($validated['days'] ?? 30);
        $fromDate = now()->subDays($days - 1)->startOfDay();
        $toDate = now()->endOfDay();

        $baseQuery = \App\Models\HrServiceRequest::query()
            ->whereBetween('created_at', [$fromDate, $toDate]);

        if (!empty($validated['category'])) {
            $baseQuery->where('category', $validated['category']);
        }

        if (!empty($validated['priority'])) {
            $baseQuery->where('priority', $validated['priority']);
        }

        $total = (clone $baseQuery)->count();
        $resolved = (clone $baseQuery)
            ->whereIn('status', [
                \App\Models\HrServiceRequest::STATUS_RESOLVED,
                \App\Models\HrServiceRequest::STATUS_CLOSED,
            ])
            ->count();

        $open = (clone $baseQuery)
            ->whereIn('status', [
                \App\Models\HrServiceRequest::STATUS_OPEN,
                \App\Models\HrServiceRequest::STATUS_IN_PROGRESS,
                \App\Models\HrServiceRequest::STATUS_WAITING_FOR_EMPLOYEE,
            ])
            ->count();

        $overdue = (clone $baseQuery)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereNotIn('status', [
                \App\Models\HrServiceRequest::STATUS_RESOLVED,
                \App\Models\HrServiceRequest::STATUS_CLOSED,
                \App\Models\HrServiceRequest::STATUS_CANCELLED,
            ])
            ->count();

        $avgResolutionHours = (clone $baseQuery)
            ->whereNotNull('resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
            ->value('avg_hours');

        $byPriority = (clone $baseQuery)
            ->selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->orderByDesc('total')
            ->get();

        $byCategory = (clone $baseQuery)
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        return ApiResponse::success('Helpdesk SLA summary retrieved successfully', [
            'window' => [
                'days' => $days,
                'from' => $fromDate->toDateTimeString(),
                'to' => $toDate->toDateTimeString(),
            ],
            'summary' => [
                'total_tickets' => $total,
                'resolved_tickets' => $resolved,
                'open_tickets' => $open,
                'overdue_tickets' => $overdue,
                'resolution_rate_percent' => $total > 0 ? round(($resolved / $total) * 100, 2) : 0,
                'average_resolution_hours' => $avgResolutionHours !== null ? round((float) $avgResolutionHours, 2) : 0,
            ],
            'by_priority' => $byPriority,
            'by_category' => $byCategory,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        if (!($request->user()->isAdmin() || $request->user()->isHR() || $request->user()->isManager())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'status' => 'sometimes|string|in:open,in_progress,waiting_for_employee,resolved,closed,cancelled',
            'priority' => 'sometimes|string|in:low,medium,high,urgent',
            'category' => 'sometimes|string|max:100',
            'search' => 'sometimes|string|max:255',
        ]);

        $query = \App\Models\HrServiceRequest::with([
            'employee.user.profile',
            'creator:id,name,email',
            'assignee:id,name,email',
            'comments.user:id,name,email',
        ])->latest();

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['priority'])) {
            $query->where('priority', $validated['priority']);
        }

        if (!empty($validated['category'])) {
            $query->where('category', $validated['category']);
        }

        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($builder) use ($search) {
                $builder->where('ticket_number', 'like', '%' . $search . '%')
                    ->orWhere('subject', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        return ApiResponse::success('Helpdesk requests retrieved successfully', $query->paginate($validated['per_page'] ?? 15));
    }

    public function myRequests(Request $request): JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();

        $requests = \App\Models\HrServiceRequest::with(['assignee:id,name,email', 'comments.user:id,name,email'])
            ->where('employee_id', $employee->id)
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return ApiResponse::success('My helpdesk requests retrieved successfully', $requests);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'category' => 'required|string|max:100',
            'priority' => 'sometimes|string|in:low,medium,high,urgent',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'due_at' => 'nullable|date',
        ]);

        if ($user->isAdmin() || $user->isHR() || $user->isManager()) {
            if (empty($validated['employee_id'])) {
                return ApiResponse::error('Validation error', ['employee_id' => ['The employee_id field is required.']], 422);
            }

            $employee = !empty($validated['employee_id'])
                ? Employee::findOrFail($validated['employee_id'])
                : $user->employee;
        } else {
            $employee = $this->getAuthenticatedEmployee();

            if (!empty($validated['employee_id']) && (int) $validated['employee_id'] !== $employee->id) {
                return ApiResponse::error('Forbidden', 'You can only create requests for your own employee record', 403);
            }
        }

        $ticketNumber = 'HR-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));

        $requestTicket = \App\Models\HrServiceRequest::create([
            'employee_id' => $employee->id,
            'created_by' => $user->id,
            'ticket_number' => $ticketNumber,
            'category' => $validated['category'],
            'priority' => $validated['priority'] ?? \App\Models\HrServiceRequest::PRIORITY_MEDIUM,
            'status' => \App\Models\HrServiceRequest::STATUS_OPEN,
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'due_at' => $validated['due_at'] ?? null,
        ]);

        UserNotification::create([
            'user_id' => $employee->user_id,
            'sender_user_id' => $user->id,
            'title' => 'Helpdesk request created',
            'message' => 'Your HR request has been submitted with ticket ' . $ticketNumber,
            'type' => 'hr_request.created',
            'category' => 'helpdesk',
            'data' => [
                'request_id' => $requestTicket->id,
                'ticket_number' => $ticketNumber,
                'status' => $requestTicket->status,
            ],
        ]);

        return ApiResponse::success('Helpdesk request created successfully', $requestTicket->load(['employee.user.profile', 'creator:id,name,email', 'assignee:id,name,email']), 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $ticket = \App\Models\HrServiceRequest::with([
            'employee.user.profile',
            'creator:id,name,email',
            'assignee:id,name,email',
            'comments.user:id,name,email',
        ])->find($id);

        if (!$ticket) {
            return ApiResponse::error('Helpdesk request not found', null, 404);
        }

        if (!$this->canAccessTicket($request, $ticket)) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        return ApiResponse::success('Helpdesk request detail', $ticket);
    }

    public function assign(Request $request, int $id): JsonResponse
    {
        if (!($request->user()->isAdmin() || $request->user()->isHR())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'due_at' => 'nullable|date',
            'status' => 'sometimes|string|in:open,in_progress,waiting_for_employee,resolved,closed,cancelled',
        ]);

        $ticket = \App\Models\HrServiceRequest::with('employee.user')->find($id);

        if (!$ticket) {
            return ApiResponse::error('Helpdesk request not found', null, 404);
        }

        $ticket->update([
            'assigned_to' => $validated['assigned_to'],
            'due_at' => $validated['due_at'] ?? $ticket->due_at,
            'status' => $validated['status'] ?? \App\Models\HrServiceRequest::STATUS_IN_PROGRESS,
        ]);

        UserNotification::create([
            'user_id' => $ticket->employee->user_id,
            'sender_user_id' => $request->user()->id,
            'title' => 'Helpdesk request assigned',
            'message' => 'Your HR request ' . $ticket->ticket_number . ' is being handled by the HR team.',
            'type' => 'hr_request.assigned',
            'category' => 'helpdesk',
            'data' => [
                'request_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'status' => $ticket->status,
            ],
        ]);

        return ApiResponse::success('Helpdesk request assigned successfully', $ticket->fresh(['employee.user.profile', 'creator:id,name,email', 'assignee:id,name,email']));
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        if (!($request->user()->isAdmin() || $request->user()->isHR())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:open,in_progress,waiting_for_employee,resolved,closed,cancelled',
            'resolution_note' => 'nullable|string|max:5000',
        ]);

        $ticket = \App\Models\HrServiceRequest::with('employee.user')->find($id);

        if (!$ticket) {
            return ApiResponse::error('Helpdesk request not found', null, 404);
        }

        $updateData = [
            'status' => $validated['status'],
            'resolution_note' => $validated['resolution_note'] ?? $ticket->resolution_note,
        ];

        if (in_array($validated['status'], [\App\Models\HrServiceRequest::STATUS_RESOLVED, \App\Models\HrServiceRequest::STATUS_CLOSED], true)) {
            $updateData['resolved_at'] = now();
        }

        $ticket->update($updateData);

        if ($ticket->employee?->user) {
            UserNotification::create([
                'user_id' => $ticket->employee->user_id,
                'sender_user_id' => $request->user()->id,
                'title' => 'Helpdesk request updated',
                'message' => 'Your HR request ' . $ticket->ticket_number . ' status changed to ' . $ticket->status,
                'type' => 'hr_request.status_changed',
                'category' => 'helpdesk',
                'data' => [
                    'request_id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'status' => $ticket->status,
                ],
            ]);
        }

        return ApiResponse::success('Helpdesk request status updated successfully', $ticket->fresh(['employee.user.profile', 'creator:id,name,email', 'assignee:id,name,email']));
    }

    public function comment(Request $request, int $id): JsonResponse
    {
        $ticket = \App\Models\HrServiceRequest::with('employee.user')->find($id);

        if (!$ticket) {
            return ApiResponse::error('Helpdesk request not found', null, 404);
        }

        if (!$this->canAccessTicket($request, $ticket)) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'is_internal' => 'sometimes|boolean',
        ]);

        $comment = \App\Models\HrServiceRequestComment::create([
            'hr_service_request_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
            'is_internal' => $validated['is_internal'] ?? false,
        ]);

        if (!$comment->is_internal && $ticket->employee?->user_id !== $request->user()->id) {
            UserNotification::create([
                'user_id' => $ticket->employee->user_id,
                'sender_user_id' => $request->user()->id,
                'title' => 'Helpdesk comment added',
                'message' => 'A new comment has been added to your HR request ' . $ticket->ticket_number,
                'type' => 'hr_request.commented',
                'category' => 'helpdesk',
                'data' => [
                    'request_id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                ],
            ]);
        }

        return ApiResponse::success('Comment added successfully', $comment->load('user:id,name,email'), 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if (!($request->user()->isAdmin() || $request->user()->isHR())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $ticket = \App\Models\HrServiceRequest::with('comments')->find($id);

        if (!$ticket) {
            return ApiResponse::error('Helpdesk request not found', null, 404);
        }

        $deleted = $ticket->toArray();
        $ticket->delete();

        return ApiResponse::success('Helpdesk request deleted successfully', $deleted);
    }

    protected function canAccessTicket(Request $request, \App\Models\HrServiceRequest $ticket): bool
    {
        $user = $request->user();

        if ($user->isAdmin() || $user->isHR() || $user->isManager()) {
            return true;
        }

        return $ticket->employee?->user_id === $user->id;
    }
}