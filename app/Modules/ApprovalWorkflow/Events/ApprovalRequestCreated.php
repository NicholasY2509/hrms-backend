<?php

namespace App\Modules\ApprovalWorkflow\Events;

use App\Modules\ApprovalWorkflow\Models\ApprovalRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApprovalRequestCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ApprovalRequest $request
    ) {}
}
