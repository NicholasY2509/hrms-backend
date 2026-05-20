<?php

namespace App\Modules\System\Events;

use App\Modules\System\Models\Report;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportProgressUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $report;

    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('reports.' . $this->report->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'report.progress';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->report->id,
            'status' => $this->report->status,
            'progress' => $this->report->progress,
            'current_message' => $this->report->current_message,
        ];
    }
}
