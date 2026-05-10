<?php

namespace App\Modules\Attendance\Jobs;

use App\Modules\Attendance\Models\ZktecoMachine;
use App\Modules\Attendance\Services\ZktecoUserService;
use App\Modules\System\Models\Task;
use App\Modules\System\Traits\HasTaskProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SyncZktecoUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasTaskProgress;

    protected $machine;
    public $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(ZktecoMachine $machine, Task $task = null)
    {
        $this->machine = $machine;
        $this->task = $task;
    }

    /**
     * Execute the job.
     */
    public function handle(ZktecoUserService $service): void
    {
        $this->setTask($this->task);

        try {
            $service->setTask($this->task);
            $service->syncFromMachine($this->machine);
        } catch (Throwable $e) {
            $this->failTask($e->getMessage());
            throw $e;
        }
    }
}
