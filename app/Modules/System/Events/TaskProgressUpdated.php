<?php

namespace App\Modules\System\Events;

use App\Modules\System\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskProgressUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('tasks.' . $this->task->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'task.progress';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->task->id,
            'type' => $this->task->type,
            'status' => $this->task->status,
            'progress' => $this->task->progress,
            'message' => $this->task->message,
            'metadata' => $this->task->metadata,
        ];
    }
}
