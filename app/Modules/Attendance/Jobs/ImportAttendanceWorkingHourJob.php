<?php

namespace App\Modules\Attendance\Jobs;

use App\Modules\System\Models\Task;
use App\Modules\System\Traits\HasTaskProgress;
use App\Modules\Attendance\Imports\AttendanceWorkingHourNonSecurityImport;
use App\Modules\Attendance\Imports\AttendanceWorkingHourSecurityImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ImportAttendanceWorkingHourJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasTaskProgress;

    protected int $taskId;
    protected string $filePath;
    protected string $month;
    protected string $uploadType;
    protected ?string $dayType;

    public function __construct(int $taskId, string $filePath, string $month, string $uploadType, ?string $dayType = null)
    {
        $this->taskId = $taskId;
        $this->filePath = $filePath;
        $this->month = $month;
        $this->uploadType = $uploadType;
        $this->dayType = $dayType;
    }

    public function handle()
    {
        $task = Task::find($this->taskId);
        if (!$task) return;

        $this->setTask($task);
        $this->updateProgress(0, 'Memulai import jadwal kerja...');

        try {
            if ($this->uploadType === 'non_security') {
                Excel::import(new AttendanceWorkingHourNonSecurityImport($this->month, $this->dayType), $this->filePath);
            } else {
                Excel::import(new AttendanceWorkingHourSecurityImport($this->month), $this->filePath);
            }

            $this->completeTask('Import jadwal kerja berhasil diselesaikan.');
        } catch (Throwable $e) {
            $this->failTask('Gagal mengimport jadwal kerja: ' . $e->getMessage());
        }
    }
}
