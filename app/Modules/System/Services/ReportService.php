<?php

namespace App\Modules\System\Services;

use App\Modules\System\Models\Report;
use App\Modules\System\Models\Task;
use App\Modules\System\Repositories\ReportRepository;
use App\Modules\System\Jobs\ProcessExportJob;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ReportService
{
    protected ReportRepository $repository;

    public function __construct(ReportRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get paginated reports.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedReports(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    /**
     * Request a new report.
     *
     * @param array $data
     * @return Report
     */
    public function requestReport(array $data): Report
    {
        $data['user_id'] = auth()->id() ?? 1;
        $data['status'] = 'pending';

        // Create the generic task first
        $task = Task::create([
            'user_id' => $data['user_id'],
            'type' => 'report_generation',
            'status' => 'pending',
            'payload' => $data,
            'message' => 'Waiting to process report...',
        ]);

        $data['task_id'] = $task->id;
        $report = $this->repository->create($data);
        
        $task->update(['metadata' => ['report_id' => $report->id]]);

        ProcessExportJob::dispatch($report, $task);

        return $report;
    }

    /**
     * Get report detail with temporary download URL if applicable.
     *
     * @param Report $report
     * @return array
     */
    public function getReportDetail(Report $report): array
    {
        $data = $report->toArray();

        if ($report->status === 'completed' && $report->file_path) {
            $diskName = config('filesystems.default') === 'local' ? 'local' : 'gcs';
            $disk = Storage::disk($diskName);

            try {
                $data['download_url'] = $disk->temporaryUrl(
                    $report->file_path,
                    now()->addHours(1)
                );
            } catch (\RuntimeException $e) {
                // Fallback for disks that don't support temporary URLs (like local)
                $data['download_url'] = url('/storage/' . $report->file_path);
            }
        }

        return $data;
    }
}
