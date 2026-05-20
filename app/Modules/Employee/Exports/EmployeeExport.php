<?php

namespace App\Modules\Employee\Exports;

use App\Modules\Employee\Models\Employee;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeeExport implements FromQuery, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        return Employee::query()
            ->with(['department', 'position', 'work_location'])
            ->filter($this->filters);
    }

    public function headings(): array
    {
        return [
            'ID',
            'NIK',
            'Nama Depan',
            'Nama Belakang',
            'Email',
            'Telepon',
            'Departemen',
            'Jabatan',
            'Lokasi',
            'Tanggal Bergabung',
        ];
    }

    public function map($employee): array
    {
        return [
            $employee->id,
            $employee->nik,
            $employee->first_name,
            $employee->last_name,
            $employee->email,
            $employee->phone,
            $employee->department?->name,
            $employee->position?->name,
            $employee->work_location?->name,
            $employee->join_date,
        ];
    }
}
