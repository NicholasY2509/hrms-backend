<?php

namespace App\Modules\System\Services;

use App\Modules\System\Models\Report;
use App\Modules\System\Repositories\ReportRepository;
use App\Modules\System\Repositories\TaskRepository;
use App\Modules\System\Jobs\ProcessExportJob;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ReportService
{
    protected ReportRepository $repository;
    protected TaskRepository $taskRepository;

    public function __construct(ReportRepository $repository, TaskRepository $taskRepository)
    {
        $this->repository = $repository;
        $this->taskRepository = $taskRepository;
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

        $task = $this->taskRepository->create([
            'user_id' => $data['user_id'],
            'type' => 'report_generation',
            'status' => 'pending',
            'payload' => $data,
            'message' => 'Waiting to process report...',
        ]);

        $data['task_id'] = $task->id;
        $data['name'] = $this->generateReportName($data);
        $data['document_no'] = $this->generateDocumentNo($data['type']);
        $report = $this->repository->create($data);
        
        $this->taskRepository->update($task->id, ['metadata' => ['report_id' => $report->id]]);

        ProcessExportJob::dispatch($report, $task);

        return $report;
    }

    /**
     * Generate a unique document number for the report.
     * Format: REP/YYYYMMDD/SEQ/CODE
     *
     * @param string $type
     * @return string
     */
    private function generateDocumentNo(string $type): string
    {
        if ($type === 'overtime') {
            $month = now()->month;
            $year = now()->year;
            
            $reportCount = \App\Modules\System\Models\Report::where('type', 'overtime')
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->count();
                
            $legacyCount = \Illuminate\Support\Facades\DB::table('overtime_exports')
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->count();
                
            $count = $reportCount + $legacyCount;
            $sequence = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
            
            $romanMonths = [
                1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
                7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
            ];
            $romanMonth = $romanMonths[$month];
            
            return "{$sequence}/{$romanMonth}/DTM/{$year}";
        }

        $date = now()->format('Ymd');
        $code = config("reports.map.{$type}.code", 'GEN');
        
        // Count reports generated today to get the sequence
        $count = \App\Modules\System\Models\Report::whereDate('created_at', now()->toDateString())->count();
        $sequence = str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        return "REP/{$date}/{$sequence}/{$code}";
    }

    /**
     * Generate a detailed report name based on the request data.
     *
     * @param array $data
     * @return string
     */
    private function generateReportName(array $data): string
    {
        $baseName = !empty($data['name']) ? $data['name'] : ucwords(str_replace('_', ' ', $data['type']));
        $filters = $data['filters'] ?? [];
        $parts = [];

        // Add date range if available
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $parts[] = date('d M Y', strtotime($filters['start_date'])) . ' - ' . date('d M Y', strtotime($filters['end_date']));
        } elseif (!empty($filters['start_date'])) {
            $parts[] = 'From ' . date('d M Y', strtotime($filters['start_date']));
        } elseif (!empty($filters['month']) && !empty($filters['year'])) {
            $monthName = date('F', mktime(0, 0, 0, (int)$filters['month'], 10));
            $parts[] = $monthName . ' ' . $filters['year'];
        }

        $format = strtoupper($data['format'] ?? 'PDF');
        
        $fullName = $baseName;
        if (!empty($parts)) {
            $fullName .= ' (' . implode(', ', $parts) . ')';
        }
        
        $fullName .= ' - ' . $format;

        return $fullName;
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
                $data['download_url'] = url('/storage/' . $report->file_path);
            }
        }

        return $data;
    }
}
