<?php

namespace App\Modules\Notifications\Repositories;

use App\Modules\Notifications\Models\Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NotificationRepository
{
    /**
     * Get user notifications with pagination.
     */
    public function getForUser($user, int $perPage = 20, bool $unreadOnly = false): LengthAwarePaginator
    {
        $query = $unreadOnly ? $user->unreadNotifications() : $user->notifications();
        return $query->paginate($perPage);
    }

    /**
     * Get unread count for user.
     */
    public function getUnreadCount($user): int
    {
        return $user->unreadNotifications()->count();
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead($user, string $id): bool
    {
        $notification = $user->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
            return true;
        }
        return false;
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead($user): void
    {
        $user->unreadNotifications->markAsRead();
    }
}
