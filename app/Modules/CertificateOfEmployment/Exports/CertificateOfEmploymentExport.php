<?php

namespace App\Modules\CertificateOfEmployment\Exports;

use App\Modules\CertificateOfEmployment\Models\CertificateOfEmployment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CertificateOfEmploymentExport implements FromQuery, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = CertificateOfEmployment::query()
            ->with(['employee', 'employee.position', 'employee.latestResignation']);

        if (!empty($this->filters['id'])) {
            $query->where('id', $this->filters['id']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Document No',
            'NIK',
            'Employee Name',
            'Position',
            'Request Date',
            'Status',
        ];
    }

    public function map($coe): array
    {
        return [
            $coe->id,
            $coe->document_no,
            $coe->employee?->nik,
            $coe->employee?->full_name,
            $coe->employee?->position?->name,
            $coe->request_date,
            $coe->status,
        ];
    }
}
