<?php

namespace App\Modules\System\Traits;

use App\Modules\System\Events\TaskProgressUpdated;
use App\Modules\System\Models\Task;

trait HasTaskProgress
{
    protected ?Task $task = null;

    public function setTask(Task $task): void
    {
        $this->task = $task;
    }

    public function updateProgress(int $progress, string $message = null, array $metadata = null): void
    {
        if (!$this->task) {
            return;
        }

        $this->task->update([
            'progress' => $progress,
            'message' => $message ?? $this->task->message,
            'metadata' => $metadata ? array_merge($this->task->metadata ?? [], $metadata) : $this->task->metadata,
        ]);

        event(new TaskProgressUpdated($this->task));
    }

    public function completeTask(string $message = null, array $metadata = null): void
    {
        if (!$this->task) {
            return;
        }

        $this->task->update([
            'status' => 'completed',
            'progress' => 100,
            'message' => $message ?? 'Task completed successfully',
            'metadata' => $metadata ? array_merge($this->task->metadata ?? [], $metadata) : $this->task->metadata,
            'completed_at' => now(),
        ]);

        event(new TaskProgressUpdated($this->task));
    }

    public function failTask(string $message, array $metadata = null): void
    {
        if (!$this->task) {
            return;
        }

        $this->task->update([
            'status' => 'failed',
            'message' => $message,
            'metadata' => $metadata ? array_merge($this->task->metadata ?? [], $metadata) : $this->task->metadata,
            'completed_at' => now(),
        ]);

        event(new TaskProgressUpdated($this->task));
    }
}
