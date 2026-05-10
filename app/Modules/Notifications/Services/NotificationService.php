<?php

namespace App\Modules\Notifications\Services;

use App\Modules\Notifications\Repositories\NotificationRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationService
{
    protected $repository;

    public function __construct(NotificationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get user notifications.
     */
    public function getUserNotifications($user, array $params): LengthAwarePaginator
    {
        $perPage = $params['per_page'] ?? 20;
        $unreadOnly = isset($params['unread_only']) && $params['unread_only'] === 'true';

        return $this->repository->getForUser($user, (int) $perPage, $unreadOnly);
    }

    /**
     * Get unread notifications count.
     */
    public function getUnreadCount($user): int
    {
        return $this->repository->getUnreadCount($user);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($user, string $id): bool
    {
        return $this->repository->markAsRead($user, $id);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead($user): void
    {
        $this->repository->markAllAsRead($user);
    }
}
