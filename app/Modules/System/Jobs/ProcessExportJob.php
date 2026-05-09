<?php

namespace App\Modules\System\Jobs;

use App\Modules\System\Models\Report;
use App\Modules\Employee\Exports\EmployeeExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Modules\System\Events\ReportProgressUpdated;

class ProcessExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $report;
    public $timeout = 600; // 10 minutes timeout for heavy exports

    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    public function updateProgress($progress, $message = null, $status = 'processing')
    {
        $this->report->update([
            'status' => $status,
            'progress' => $progress,
            'current_message' => $message ?? $this->report->current_message
        ]);

        broadcast(new ReportProgressUpdated($this->report));
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

            broadcast(new ReportProgressUpdated($this->report));
        }
    }

    public function handle()
    {
        $this->updateProgress(10, 'Memulai proses export...');
        sleep(2); // SIMULATED DELAY

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
            sleep(2); // SIMULATED DELAY

            // Lookup the export configuration from the registry
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
                // PDF might run out of memory for huge records, chunking might be needed for view generation too, 
                // but DOMPDF usually loads it all into one view.
                $data = $query->get();
                $pdf = Pdf::loadView($viewName, ['data' => $data]);
                Storage::disk($disk)->put($fullPath, $pdf->output());
                
            } elseif ($format === 'txt') {
                $this->updateProgress(40, 'Mulai menulis file teks...');
                
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
                
            } else {
                $this->updateProgress(40, 'Menghasilkan file ' . strtoupper($format) . ' (Excel/CSV)...');
                
                // Pass job to exporter for real-time progress if supported
                if (method_exists($exportClass, 'setJob')) {
                    $exportClass->setJob($this, $totalRecords);
                }
                
                // Excel and CSV chunk automatically via FromQuery
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

            broadcast(new ReportProgressUpdated($this->report));

        } catch (\Exception $e) {
            $this->updateProgress(0, 'Error: ' . $e->getMessage(), 'failed');
            throw $e;
        }
    }
}
