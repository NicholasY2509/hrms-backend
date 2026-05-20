<?php

namespace App\Modules\Attendance\Exports;

use App\Modules\Attendance\Repositories\AttendanceRepository;
use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyAttendanceExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithEvents
{
    protected $filters;
    protected $repository;
    protected $job;
    protected $totalRecords;
    protected $processedCount = 0;

    public function __construct(array $filters, AttendanceRepository $repository = null)
    {
        $this->filters = $filters;
        $this->repository = $repository ?? app(AttendanceRepository::class);
    }

    public function setJob($job, $totalRecords)
    {
        $this->job = $job;
        $this->totalRecords = $totalRecords;
    }

    public function query()
    {
        return $this->repository->getExportQuery($this->filters);
    }

    public function headings(): array
    {
        return [
            'Nama Karyawan',
            'Jabatan',
            'Departemen',
            'NIK',
            'Tanggal',
            'Jam Kerja',
            'Clock In',
            'Lokasi Masuk',
            'Clock Out',
            'Lokasi Keluar',
            'Terlambat',
            'Status'
        ];
    }

    public function map($row): array
    {
        $this->processedCount++;
        
        if ($this->job && $this->totalRecords > 0 && $this->processedCount % 100 === 0) {
            $percent = 40 + round(($this->processedCount / $this->totalRecords) * 50);
            $this->job->updateProgress(min($percent, 90), "Menulis baris {$this->processedCount} dari {$this->totalRecords}...");
        }

        $employee = $row->attendance_working_hour->employee ?? null;
        $workingHour = $row->attendance_working_hour->working_hour ?? null;
        
        return [
            $employee ? $employee->full_name : '-',
            $employee && $employee->position ? $employee->position->name : '-',
            $employee && $employee->department ? $employee->department->name : '-',
            $employee ? $employee->nik : '-',
            $row->attendance_working_hour ? $row->attendance_working_hour->attendance_at : '-',
            $workingHour ? $workingHour->name . " (" . $workingHour->start_time . " - " . $workingHour->end_time . ")" : '-',
            $row->incoming_scan ?: '-',
            $row->incoming_location ? $row->incoming_location->name : '-',
            $row->outgoing_scan ?: '-',
            $row->outgoing_location ? $row->outgoing_location->name : '-',
            $row->late_time ? $row->late_time . " menit" : '0 menit',
            $row->attendance_status ? $row->attendance_status->name : '-'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
            // Use SQL aggregation instead of fetching all records into memory
            $summary = $this->query()
                ->join('attendance_working_hours', 'attendances.attendance_working_hour_id', '=', 'attendance_working_hours.id')
                ->join('employees', 'attendance_working_hours.employee_id', '=', 'employees.id')
                ->join('departments', 'employees.department_id', '=', 'departments.id')
                ->select('departments.name', DB::raw('count(*) as total'))
                ->groupBy('departments.name')
                ->orderBy('departments.name')
                ->pluck('total', 'name');
            
            $totalRecords = $summary->sum();
            $lastRow = $totalRecords + 2; 
            
            $sheet->setCellValue('A' . $lastRow, 'RINGKASAN PER DEPARTEMEN');
            $sheet->getStyle('A' . $lastRow)->getFont()->setBold(true);
            
            $rowNum = $lastRow + 1;
            foreach ($summary as $dept => $count) {
                $sheet->setCellValue('A' . $rowNum, $dept);
                $sheet->setCellValue('B' . $rowNum, $count);
                $rowNum++;
            }
        },
    ];
}

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
