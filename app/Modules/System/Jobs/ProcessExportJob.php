<?php

namespace App\Modules\System\Jobs;

use App\Modules\System\Models\Report;
use App\Modules\System\Models\Task;
use App\Modules\System\Traits\HasTaskProgress;
use App\Modules\Employee\Exports\EmployeeExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ProcessExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasTaskProgress;

    public $report;
    public $timeout = 600; // 10 minutes timeout for heavy exports

    public function __construct(Report $report, Task $task = null)
    {
        $this->report = $report;
        $this->task = $task;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        if ($this->report) {
            $this->report->update([
                'status' => 'failed',
                'current_message' => 'Export gagal: ' . $exception->getMessage()
            ]);
        }

        $this->failTask('Export gagal: ' . $exception->getMessage());
    }

    public function handle()
    {
        ini_set('memory_limit', '1024M');

        $this->updateProgress(10, 'Memulai proses export...');
        register_shutdown_function(function() {
            if ($this->task) {
                $this->task->refresh();
                if ($this->task->status === 'processing') {
                    $error = error_get_last();
                    $message = 'Export terhenti tiba-tiba (kemungkinan kehabisan memori).';
                    
                    if ($error && str_contains($error['message'], 'Allowed memory size')) {
                        $message = 'Export gagal karena file terlalu besar untuk diproses (Memory Exhausted).';
                    } elseif ($error) {
                        $message .= ' Error: ' . $error['message'];
                    }
                    
                    $this->failTask($message);
                }
            }
        });

        try {
            $format = strtolower($this->report->format);
            $fileName = 'exports/' . $this->report->type . '_' . $this->report->id . '_' . time();
            
            // Map format to extension
            $extensions = [
                'excel' => 'xlsx',
                'csv' => 'csv',
                'pdf' => 'pdf',
                'txt' => 'txt'
            ];
            $ext = $extensions[$format] ?? 'xlsx';
            $fullPath = $fileName . '.' . $ext;

            $this->updateProgress(30, 'Mengambil data dari database...');

            $config = config("reports.map.{$this->report->type}");

            if (!$config) {
                throw new \Exception("Tipe export '{$this->report->type}' tidak terdaftar di config/reports.php");
            }

            $exportClass = new $config['class']($this->report->filters ?? []);
            $viewName = $config['view'];

            $disk = config('filesystems.default') === 'local' ? 'local' : 'gcs';
            $query = $exportClass->query();
            $totalRecords = $query->count();
            
            $this->updateProgress(35, "Menemukan $totalRecords data untuk diexport...");

            if ($format === 'pdf') {
                $this->updateProgress(40, 'Merender file PDF...');
                $data = $query->get();
                $pdf = Pdf::loadView($viewName, [
                    'data' => $data,
                    'report' => $this->report,
                    'filters' => $this->report->filters ?? [],
                    'meta' => method_exists($exportClass, 'getMeta') ? $exportClass->getMeta() : []
                ]);
                Storage::disk($disk)->put($fullPath, $pdf->output());
                
            } elseif ($format === 'txt') {
                $this->updateProgress(40, 'Mulai menulis file teks...');
                
                if (isset($config['txt_view'])) {
                    $data = $query->get();
                    $viewData = [
                        'data' => $data,
                        'report' => $this->report,
                        'filters' => $this->report->filters ?? []
                    ];
                    $txtContent = view($config['txt_view'], $viewData)->render();
                    Storage::disk($disk)->put($fullPath, $txtContent);
                } else {
                    $headers = implode(" | ", $exportClass->headings()) . "\n" . str_repeat("-", 80) . "\n";
                    Storage::disk($disk)->put($fullPath, $headers);
                    
                    $processed = 0;
                    $chunkSize = 1000;
                    
                    $query->chunk($chunkSize, function ($records) use (&$processed, $totalRecords, $exportClass, $disk, $fullPath) {
                        $txtContent = "";
                        foreach($records as $row) {
                            $mapped = $exportClass->map($row);
                            $txtContent .= implode(" | ", $mapped) . "\n";
                        }
                        Storage::disk($disk)->append($fullPath, $txtContent);
                        
                        $processed += $records->count();
                        $percent = 40 + round(($processed / max(1, $totalRecords)) * 50); // Scale from 40% to 90%
                        
                        $this->updateProgress($percent, "Menulis baris $processed dari $totalRecords...");
                    });
                }
                
            } else {
                $this->updateProgress(40, 'Menghasilkan file ' . strtoupper($format) . ' (Excel/CSV)...');
                
                if (method_exists($exportClass, 'setJob')) {
                    $exportClass->setJob($this, $totalRecords);
                }
                
                Excel::store($exportClass, $fullPath, $disk);
            }

            $this->updateProgress(95, 'Menyelesaikan file...');

            $this->report->update([
                'status' => 'completed',
                'progress' => 100,
                'current_message' => 'Export selesai',
                'file_path' => $fullPath,
                'completed_at' => now()
            ]);

            $this->completeTask('Export selesai', ['file_path' => $fullPath]);

        } catch (\Exception $e) {
            $this->report->update(['status' => 'failed']);
            $this->failTask('Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
