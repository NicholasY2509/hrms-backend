<?php

namespace App\Modules\ApprovalWorkflow\Events;

use App\Modules\ApprovalWorkflow\Models\ApprovalRequestStep;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApprovalStepActionable
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ApprovalRequestStep $step
    ) {}
}
