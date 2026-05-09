<?php

namespace App\Modules\Attendance\Exports;

use App\Modules\Attendance\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Events\AfterChunk;
use Illuminate\Database\Eloquent\Builder;

class AttendanceExport implements FromQuery, WithHeadings, WithMapping, WithEvents
{
    protected $filters;
    protected $job;
    protected $totalRecords = 0;
    protected $processedCount = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function setJob($job, $totalRecords)
    {
        $this->job = $job;
        $this->totalRecords = $totalRecords;
    }

    public function registerEvents(): array
    {
        return []; // AfterChunk is for imports, we'll use map() for exports
    }

    public function query()
    {
        $query = Attendance::query()
            ->with([
                'attendance_working_hour.employee',
                'attendance_status'
            ]);

        if (!empty($this->filters['start_date'])) {
            $query->whereHas('attendance_working_hour', function (Builder $q) {
                $q->where('attendance_at', '>=', $this->filters['start_date']);
            });
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereHas('attendance_working_hour', function (Builder $q) {
                $q->where('attendance_at', '<=', $this->filters['end_date']);
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'NIK',
            'Nama Karyawan',
            'Tanggal',
            'Jam Masuk',
            'Jam Keluar',
            'Status Kehadiran',
            'Terlambat (menit)',
            'Pulang Cepat (menit)',
        ];
    }

    public function map($row): array
    {
        $this->processedCount++;

        // Update progress every 500 rows
        if ($this->job && $this->totalRecords > 0 && $this->processedCount % 500 === 0) {
            $percent = 40 + round(($this->processedCount / $this->totalRecords) * 50);
            $this->job->updateProgress(
                min($percent, 90), 
                "Menulis baris {$this->processedCount} dari {$this->totalRecords}..."
            );
        }

        $employee = $row->attendance_working_hour->employee ?? null;
        return [
            $row->id,
            $employee ? $employee->nik : '-',
            $employee ? $employee->full_name : '-',
            $row->attendance_working_hour ? $row->attendance_working_hour->attendance_at : '-',
            $row->incoming_scan,
            $row->outgoing_scan,
            $row->attendance_status ? $row->attendance_status->name : '-',
            $row->late_time,
            $row->early_time,
        ];
    }
}
