<?php

namespace App\Modules\System\Repositories;

use App\Modules\System\Models\Task;

class TaskRepository
{
    /**
     * Create a new task.
     *
     * @param array $data
     * @return Task
     */
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    /**
     * Find a task by its ID.
     *
     * @param int $id
     * @return Task|null
     */
    public function find(int $id): ?Task
    {
        return Task::find($id);
    }

    /**
     * Update a task.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $task = $this->find($id);
        if (!$task) {
            return false;
        }

        return $task->update($data);
    }
}
