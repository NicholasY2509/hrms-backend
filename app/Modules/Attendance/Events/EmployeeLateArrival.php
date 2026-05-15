<?php

namespace App\Modules\Attendance\Events;

use App\Modules\Attendance\Models\Attendance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmployeeLateArrival
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Attendance $attendance
    ) {}
}
