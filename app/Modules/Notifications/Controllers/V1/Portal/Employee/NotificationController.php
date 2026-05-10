<?php

namespace App\Modules\Notifications\Controllers\V1\Portal\Employee;

use App\Http\Controllers\Controller;
use App\Modules\Notifications\Requests\NotificationIndexRequest;
use App\Modules\Notifications\Resources\NotificationResource;
use App\Modules\Notifications\Services\NotificationService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @group Notifications
 *
 * API for managing user notifications.
 */
class NotificationController extends Controller
{
    use ApiResponses;

    protected $service;

    public function __construct(NotificationService $service)
    {
        $this->service = $service;
    }

    /**
     * Get User Notifications
     * 
     * Get a paginated list of notifications for the authenticated user.
     * 
     * @response {
     *  "success": true,
     *  "message": "Notifications retrieved successfully",
     *  "data": {
     *      "data": [
     *          {
     *              "id": "uuid",
     *              "type": "App\\Notifications\\ExampleNotification",
     *              "data": {},
     *              "read_at": null,
     *              "created_at": "2024-01-01T00:00:00.000000Z",
     *              "created_at_human": "1 minute ago"
     *          }
     *      ],
     *      "links": {},
     *      "meta": {}
     *  }
     * }
     */
    public function index(NotificationIndexRequest $request): JsonResponse
    {
        $user = Auth::user();
        
        $notifications = $this->service->getUserNotifications($user, $request->validated());

        return $this->successResponse(
            NotificationResource::collection($notifications)->response()->getData(true), 
            'Notifications retrieved successfully'
        );
    }

    /**
     * Get Unread Count
     * 
     * Get the count of unread notifications for the authenticated user.
     * 
     * @response {
     *  "success": true,
     *  "message": "Unread count retrieved successfully",
     *  "data": {
     *      "unread_count": 5
     *  }
     * }
     */
    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();
        
        return $this->successResponse([
            'unread_count' => $this->service->getUnreadCount($user)
        ], 'Unread count retrieved successfully');
    }

    /**
     * Mark as Read
     * 
     * Mark a specific notification as read.
     * 
     * @urlParam id string required The ID of the notification. Example: 1
     * @response {
     *  "success": true,
     *  "message": "Notification marked as read",
     *  "data": null
     * }
     */
    public function markAsRead(string $id): JsonResponse
    {
        $user = Auth::user();
        
        $this->service->markAsRead($user, $id);

        return $this->successResponse(null, 'Notification marked as read');
    }

    /**
     * Mark All as Read
     * 
     * Mark all unread notifications as read.
     * 
     * @response {
     *  "success": true,
     *  "message": "All notifications marked as read",
     *  "data": null
     * }
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        
        $this->service->markAllAsRead($user);

        return $this->successResponse(null, 'All notifications marked as read');
    }
}
