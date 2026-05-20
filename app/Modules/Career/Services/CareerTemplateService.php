<?php

namespace App\Modules\Career\Services;

use App\Modules\Career\Models\Career;

class CareerTemplateService
{
    /**
     * Get the template data for a career transition letter/form.
     */
    public function getTemplateData(Career $career): array
    {
        $employee = $career->employee;

        return [
            'title' => 'Formulir Transisi Karir',
            'type' => $career->careerType?->name ?? 'Transisi Karir',
            'employee_name' => $employee->full_name,
            'nik' => $employee->employee_id_number,
            'join_date' => $employee->join_date,
            'effective_date' => $career->career_at,
            'note' => $career->note,
            'comparisons' => [
                'Status Kerja' => [
                    'before' => $career->beforeEmployeeStatus?->name ?? '-',
                    'after' => $career->afterEmployeeStatus?->name ?? '-'
                ],
                'Posisi Kerja' => [
                    'before' => $career->beforeWorkPosition?->name ?? '-',
                    'after' => $career->afterWorkPosition?->name ?? '-'
                ],
                'Tim' => [
                    'before' => $career->beforeTeam?->name ?? '-',
                    'after' => $career->afterTeam?->name ?? '-'
                ],
                'Department' => [
                    'before' => $career->beforeDepartment?->name ?? '-',
                    'after' => $career->afterDepartment?->name ?? '-'
                ],
                'Lokasi Kerja' => [
                    'before' => $career->beforeWorkLocation?->name ?? '-',
                    'after' => $career->afterWorkLocation?->name ?? '-'
                ]
            ]
        ];
    }
}
