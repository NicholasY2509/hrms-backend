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
            ->leftJoin('teams', 'employees.team_id', '=', 'teams.id')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->join('attendance_statuses', 'attendances.attendance_status_id', '=', 'attendance_statuses.id')
            ->whereBetween('attendance_working_hours.attendance_at', [$startDate, $endDate])
            ->whereNull('attendances.deleted_at')
            ->whereNull('employees.deleted_at')
            ->where(function($q) {
                $q->whereNull('teams.deleted_at')
                  ->orWhereNull('teams.id');
            })
            ->where(function($q) {
                $q->whereNotNull('employees.team_id')
                  ->orWhere('departments.name', 'GR')
                  ->orWhere('departments.name', 'BP')
                  ->orWhereIn('employees.work_position_id', [26, 62, 63]);
            });

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

        $groupCase = "
            CASE 
                WHEN employees.work_position_id IN (26, 62, 63) THEN 'Department Security'
                WHEN departments.name = 'GR' THEN 'Department GR'
                WHEN departments.name = 'BP' THEN 'Department BP'
                ELSE teams.name 
            END
        ";

        $query->select(
            DB::raw("'Team' as group_type"),
            DB::raw("{$groupCase} as group_name"),
            DB::raw("COUNT(DISTINCT employees.id) as headcount")
        )->groupByRaw($groupCase)
         ->orderByRaw($groupCase);

        DB::statement("SET SESSION group_concat_max_len = 1000000");

        foreach ($statuses as $status) {
            $alias = str_replace(' ', '_', $status);
            $query->addSelect(DB::raw("COUNT(CASE WHEN attendance_statuses.name = '{$status}' THEN 1 END) as `{$alias}`"));
            
            if ($status !== 'Hadir') {
                $query->addSelect(DB::raw("GROUP_CONCAT(DISTINCT CASE WHEN attendance_statuses.name = '{$status}' THEN employees.full_name END SEPARATOR '\n') as `list_{$alias}`"));
            }
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Nama Tim',
            'Total Karyawan',
            'Terlambat',
            'List Terlambat',
            'Izin',
            'List Izin',
            'Absen',
            'List Absen',
            'Sakit',
            'List Sakit',
            'Cuti',
            'List Cuti',
            'Training',
            'List Training',
            'Dinas Luar Kota',
            'List Dinas Luar Kota',
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
            $row->list_Terlambat ?? '',
            $row->Izin,
            $row->list_Izin ?? '',
            $row->Absen,
            $row->list_Absen ?? '',
            $row->Sakit,
            $row->list_Sakit ?? '',
            $row->Cuti,
            $row->list_Cuti ?? '',
            $row->Training,
            $row->list_Training ?? '',
            $row->Dinas_Luar_Kota,
            $row->list_Dinas_Luar_Kota ?? '',
            $row->Hadir
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A:Q')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A:Q')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
