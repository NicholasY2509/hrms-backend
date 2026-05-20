<?php

namespace App\Modules\Attendance\Exports;

use App\Modules\Attendance\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class TeamAttendanceExport implements FromQuery, WithHeadings, WithStyles, WithMapping
{
    protected $filters;
    protected $job;
    protected $totalRecords;
    protected $processedCount = 0;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function setJob($job, $totalRecords)
    {
        $this->job = $job;
        $this->totalRecords = $totalRecords;
    }

    public function query()
    {
        $startDate = $this->filters['start_date'];
        $endDate = $this->filters['end_date'];
        $statuses = ['Terlambat', 'Izin', 'Absen', 'Sakit', 'Cuti', 'Training', 'Dinas Luar Kota', 'Hadir'];

        $query = DB::table('attendances')
            ->join('attendance_working_hours', 'attendances.attendance_working_hour_id', '=', 'attendance_working_hours.id')
            ->join('employees', 'attendance_working_hours.employee_id', '=', 'employees.id')
            ->join('teams', 'employees.team_id', '=', 'teams.id')
            ->join('attendance_statuses', 'attendances.attendance_status_id', '=', 'attendance_statuses.id')
            ->whereBetween('attendance_working_hours.attendance_at', [$startDate, $endDate])
            ->whereNull('attendances.deleted_at')
            ->whereNull('employees.deleted_at')
            ->whereNull('teams.deleted_at');

        if (!empty($this->filters['team_id'])) {
            $query->whereIn('employees.team_id', $this->filters['team_id']);
        }
        if (!empty($this->filters['department_id'])) {
            $query->whereIn('employees.department_id', $this->filters['department_id']);
        }
        if (!empty($this->filters['work_position_id'])) {
            $query->whereIn('employees.work_position_id', $this->filters['work_position_id']);
        }
        if (!empty($this->filters['attendance_status_id'])) {
            $query->whereIn('attendances.attendance_status_id', $this->filters['attendance_status_id']);
        }

        $query->select(
            DB::raw("'Team' as group_type"),
            'teams.name as group_name',
            DB::raw("COUNT(DISTINCT employees.id) as headcount")
        )->groupBy('teams.name')
         ->orderBy('teams.name');

        foreach ($statuses as $status) {
            $alias = str_replace(' ', '_', $status);
            $query->addSelect(DB::raw("COUNT(CASE WHEN attendance_statuses.name = '{$status}' THEN 1 END) as `{$alias}`"));
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Nama Tim',
            'Total Karyawan',
            'Terlambat',
            'Izin',
            'Absen',
            'Sakit',
            'Cuti',
            'Training',
            'Dinas Luar Kota',
            'Hadir'
        ];
    }

    public function map($row): array
    {
        $this->processedCount++;
        
        return [
            $row->group_name,
            $row->headcount,
            $row->Terlambat,
            $row->Izin,
            $row->Absen,
            $row->Sakit,
            $row->Cuti,
            $row->Training,
            $row->Dinas_Luar_Kota,
            $row->Hadir
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
