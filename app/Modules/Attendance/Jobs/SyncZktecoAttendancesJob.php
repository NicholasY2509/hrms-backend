<?php

namespace App\Modules\Attendance\Jobs;

use App\Modules\Attendance\Models\ZktecoMachine;
use App\Modules\Attendance\Services\ZktecoLogService;
use App\Modules\System\Models\Task;
use App\Modules\System\Traits\HasTaskProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SyncZktecoAttendancesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasTaskProgress;

    protected $machine;
    protected $startDate;
    protected $endDate;
    public $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(ZktecoMachine $machine, string $startDate, string $endDate, ?Task $task = null)
    {
        $this->machine = $machine;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->task = $task;
    }

    /**
     * Execute the job.
     */
    public function handle(ZktecoLogService $service): void
    {
        $this->setTask($this->task);

        try {
            $service->setTask($this->task);
            $service->syncLogs($this->machine, $this->startDate, $this->endDate);
        } catch (Throwable $e) {
            $this->failTask($e->getMessage());
            throw $e;
        }
    }
}
