<?php

namespace App\Modules\System\Services;

use App\Modules\System\Models\Task;
use App\Modules\System\Repositories\TaskRepository;

class TaskService
{
    protected $repository;

    public function __construct(TaskRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new task.
     *
     * @param string $type
     * @param string $message
     * @param array $payload
     * @param int|null $userId
     * @return Task
     */
    public function createTask(string $type, string $message, array $payload = [], ?int $userId = null): Task
    {
        return $this->repository->create([
            'user_id' => $userId ?? auth()->id(),
            'type' => $type,
            'status' => 'pending',
            'message' => $message,
            'payload' => $payload,
        ]);
    }

    /**
     * Update task progress.
     *
     * @param int $taskId
     * @param int $progress
     * @param string $message
     * @param array $metadata
     * @return bool
     */
    public function updateProgress(int $taskId, int $progress, string $message, array $metadata = []): bool
    {
        return $this->repository->update($taskId, [
            'progress' => $progress,
            'message' => $message,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get paginated tasks with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginated(array $filters, int $perPage = 15)
    {
        return $this->repository->getPaginated($filters, $perPage);
    }
}
