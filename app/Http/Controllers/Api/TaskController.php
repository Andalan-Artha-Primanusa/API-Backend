<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Task::with(['assignedBy.profile', 'assignedTo.profile']);

        if (!$user->isAdmin() && !$user->isHR() && !$user->isSuperAdmin()) {
            $query->where('assigned_to', $user->id);
        }

        $status = $request->query('status');
        $priority = $request->query('priority');
        $search = $request->query('search');

        if ($status) {
            $query->where('status', $status);
        }
        if ($priority) {
            $query->where('priority', $priority);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tasks = $query->latest()->paginate($request->integer('per_page', 10));
        return ApiResponse::success('Tasks retrieved', $tasks);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'required|integer|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $task = Task::create([
            ...$validated,
            'assigned_by' => $user->id,
            'status' => 'pending',
        ]);

        $task->load(['assignedBy.profile', 'assignedTo.profile']);
        return ApiResponse::success('Task created successfully', $task, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $task = Task::with(['assignedBy.profile', 'assignedTo.profile'])->findOrFail($id);

        if ($task->assigned_to !== $user->id && !$user->isAdmin() && !$user->isHR() && !$user->isSuperAdmin()) {
            return ApiResponse::error('Forbidden', null, 403);
        }

        return ApiResponse::success('Task detail', $task);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $task = Task::findOrFail($id);

        if ($task->assigned_to !== $user->id && !$user->isAdmin() && !$user->isHR() && !$user->isSuperAdmin()) {
            return ApiResponse::error('Forbidden', null, 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'status' => 'sometimes|in:pending,in_progress,completed,cancelled',
            'due_date' => 'nullable|date',
            'completion_notes' => 'nullable|string',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'completed') {
            $validated['completed_at'] = now();
        }

        $task->update($validated);
        $task->load(['assignedBy.profile', 'assignedTo.profile']);
        return ApiResponse::success('Task updated successfully', $task);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $task = Task::findOrFail($id);

        if (!$user->isAdmin() && !$user->isHR() && !$user->isSuperAdmin()) {
            return ApiResponse::error('Forbidden', null, 403);
        }

        $task->delete();
        return ApiResponse::success('Task deleted successfully');
    }

    public function myTasks(Request $request): JsonResponse
    {
        $user = $request->user();
        $baseQuery = Task::where('assigned_to', $user->id);

        $summary = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
        ];

        $tasks = (clone $baseQuery)->with(['assignedBy.profile'])
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return ApiResponse::success('My tasks', [
            'tasks' => $tasks,
            'summary' => $summary,
        ]);
    }
}
