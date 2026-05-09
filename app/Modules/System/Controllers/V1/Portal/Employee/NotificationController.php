<?php

namespace App\Modules\System\Controllers\V1\Portal\Employee;

use App\Http\Controllers\Controller;
use App\Modules\System\Resources\NotificationResource;
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

    /**
     * Get the authenticated user's notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        $query = $user->notifications();

        if ($request->has('unread_only') && $request->unread_only == 'true') {
            $query = $user->unreadNotifications();
        }

        $notifications = $query->paginate($request->get('per_page', 20));

        return $this->successResponse(
            NotificationResource::collection($notifications)->response()->getData(true), 
            'Notifications retrieved successfully'
        );
    }

    /**
     * Get the count of unread notifications.
     */
    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        return $this->successResponse([
            'unread_count' => $user->unreadNotifications()->count()
        ], 'Unread count retrieved successfully');
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(string $id): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        $notification = $user->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return $this->successResponse(null, 'Notification marked as read');
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        $user->unreadNotifications->markAsRead();

        return $this->successResponse(null, 'All notifications marked as read');
    }
}
