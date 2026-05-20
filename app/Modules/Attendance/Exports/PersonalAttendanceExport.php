<?php

namespace App\Modules\Attendance\Exports;

use App\Modules\Attendance\Repositories\AttendanceRepository;
use App\Modules\Employee\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromQuery;


class PersonalAttendanceExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithCustomStartCell, WithEvents
{
    protected $filters;
    protected $repository;
    protected $employee;
    protected $summary;
    protected $job;
    protected $totalRecords;
    protected $processedCount = 0;

    public function __construct(array $filters, AttendanceRepository $repository = null)
    {
        $this->filters = $filters;
        $this->repository = $repository ?? app(AttendanceRepository::class);
        $this->employee = Employee::with(['department', 'position', 'user_employee'])->find($filters['employee_id']);
        
        if ($this->employee && $this->employee->user_employee) {
            $this->summary = $this->repository->getSummary(
                $this->employee->user_employee->user_id, 
                $filters['start_date'], 
                $filters['end_date']
            );
        }
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

    public function startCell(): string
    {
        return 'A8';
    }

    public function headings(): array
    {
        return [
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

        $workingHour = $row->attendance_working_hour->working_hour ?? null;
        
        return [
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
                
                // Employee Details
                $sheet->setCellValue('A1', 'Detail Karyawan');
                $sheet->setCellValue('A2', 'Nama: ' . $this->employee->full_name);
                $sheet->setCellValue('A3', 'NIK: ' . $this->employee->nik);
                $sheet->setCellValue('A4', 'Departemen: ' . ($this->employee->department ? $this->employee->department->name : '-'));
                
                // Summary
                $sheet->setCellValue('D1', 'Ringkasan Kehadiran');
                $row = 2;
                foreach ($this->summary as $s) {
                    $sheet->setCellValue('D' . $row, $s->name . ': ' . $s->count);
                    $row++;
                }

                $sheet->getStyle('A1:D1')->getFont()->setBold(true);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            8 => ['font' => ['bold' => true]],
        ];
    }
}
