<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * GET /api/notifications
     */
    public function index(): AnonymousResourceCollection
    {
        return NotificationResource::collection(
            $this->notificationService->list()
        );
    }

    /**
     * GET /api/notifications/unread-count
     */
    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'count' => $this->notificationService->unreadCount(),
        ]);
    }

    /**
     * PATCH /api/notifications/{id}/read
     */
    public function markRead(string $id): JsonResponse
    {
        $this->notificationService->markRead($id);

        return response()->json(['message' => 'Marked as read.']);
    }

    /**
     * PATCH /api/notifications/read-all
     */
    public function markAllRead(): JsonResponse
    {
        $this->notificationService->markAllRead();
        
        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
