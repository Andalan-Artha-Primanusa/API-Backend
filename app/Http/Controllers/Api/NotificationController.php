<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'category' => 'sometimes|string|max:100',
            'type' => 'sometimes|string|max:100',
            'unread_only' => 'sometimes|boolean',
        ]);

        $query = UserNotification::with('sender:id,name,email')
            ->where('user_id', $request->user()->id)
            ->latest();

        if (!empty($validated['category'])) {
            $query->where('category', $validated['category']);
        }

        if (!empty($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        if (!empty($validated['unread_only'])) {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate($validated['per_page'] ?? 15);

        return ApiResponse::success('Notifications retrieved successfully', $notifications);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = UserNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return ApiResponse::success('Unread notification count', [
            'count' => $count,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $notification = UserNotification::with('sender:id,name,email')->find($id);

        if (!$notification) {
            return ApiResponse::error('Notification not found', null, 404);
        }

        if ($notification->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return ApiResponse::error('Forbidden', 'You cannot access this notification', 403);
        }

        return ApiResponse::success('Notification detail', $notification);
    }

    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $notification = UserNotification::find($id);

        if (!$notification) {
            return ApiResponse::error('Notification not found', null, 404);
        }

        if ($notification->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return ApiResponse::error('Forbidden', 'You cannot modify this notification', 403);
        }

        $notification->markAsRead();

        return ApiResponse::success('Notification marked as read', $notification->fresh('sender:id,name,email'));
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        UserNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return ApiResponse::success('All notifications marked as read');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $notification = UserNotification::find($id);

        if (!$notification) {
            return ApiResponse::error('Notification not found', null, 404);
        }

        if ($notification->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return ApiResponse::error('Forbidden', 'You cannot delete this notification', 403);
        }

        $deleted = $notification->toArray();
        $notification->delete();

        return ApiResponse::success('Notification deleted', $deleted);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isHR() && !$user->isManager()) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'type' => 'required|string|max:100',
            'category' => 'nullable|string|max:100',
            'data' => 'nullable|array',
        ]);

        $notifications = [];

        foreach ($validated['user_ids'] as $userId) {
            $notifications[] = UserNotification::create([
                'user_id' => $userId,
                'sender_user_id' => $user->id,
                'title' => $validated['title'],
                'message' => $validated['message'],
                'type' => $validated['type'],
                'category' => $validated['category'] ?? null,
                'data' => $validated['data'] ?? null,
            ]);
        }

        return ApiResponse::success('Notification(s) created successfully', $notifications, 201);
    }

    public function broadcast(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isHR()) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'type' => 'required|string|max:100',
            'category' => 'nullable|string|max:100',
            'data' => 'nullable|array',
        ]);

        $recipientIds = User::query()
            ->where('id', '!=', $user->id)
            ->pluck('id')
            ->all();

        $notifications = [];

        foreach ($recipientIds as $recipientId) {
            $notifications[] = UserNotification::create([
                'user_id' => $recipientId,
                'sender_user_id' => $user->id,
                'title' => $validated['title'],
                'message' => $validated['message'],
                'type' => $validated['type'],
                'category' => $validated['category'] ?? 'broadcast',
                'data' => $validated['data'] ?? null,
            ]);
        }

        return ApiResponse::success('Broadcast notification created successfully', [
            'count' => count($notifications),
        ], 201);
    }
}