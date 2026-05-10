<?php

namespace App\Modules\Attendance\Jobs;

use App\Modules\Attendance\Services\AttendanceCalculationService;
use App\Modules\System\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;
use App\Modules\System\Traits\HasTaskProgress;

class ProcessAttendanceCalculationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasTaskProgress;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    protected string $startDate;
    protected string $endDate;

    public function __construct(Task $task, string $startDate, string $endDate)
    {
        $this->task = $task;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function handle(AttendanceCalculationService $service): void
    {
        $this->updateProgress(0, 'Memulai kalkulasi kehadiran...', ['start_date' => $this->startDate, 'end_date' => $this->endDate]);
        
        // Register shutdown function to catch process timeouts or fatal errors
        register_shutdown_function(function() {
            $this->task->refresh();
            if ($this->task->status === 'processing') {
                $error = error_get_last();
                $message = 'Kalkulasi terhenti tiba-tiba (kemungkinan timeout atau kehabisan memori).';
                if ($error) {
                    $message .= ' Error: ' . $error['message'];
                }
                $this->failTask($message);
            }
        });

        $service->setTask($this->task);
        $service->calculate($this->startDate, $this->endDate);

        // Success is usually handled inside service->calculate calling completeTask, 
        // but we can add a final safeguard here if needed.
    }

    public function failed(Throwable $exception): void
    {
        $message = $exception->getMessage();
        
        // Friendly message for memory exhaustion
        if (str_contains($message, 'Allowed memory size')) {
            $message = 'Kalkulasi gagal karena kehabisan memori. Coba kurangi rentang tanggal';
        }

        // Friendly message for timeout
        if (str_contains($message, 'exceeded the timeout') || str_contains($message, 'Timed out')) {
            $message = 'Kalkulasi dihentikan karena terlalu lama. Coba gunakan rentang tanggal yang lebih pendek.';
        }

        $this->failTask($message);
    }
}
