<?php

namespace App\Modules\Overtime\Exports;

use App\Modules\Overtime\Models\Overtime;
use App\Modules\Employee\Models\Employee;
use App\Modules\Employee\Models\Supervisor;

class OvertimeExport
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Overtime::query()->with([
            'employee.position',
            'employee.department',
            'employee.work_location',
        ]);

        return $query->filter($this->filters);
    }

    /**
     * Additional data for the report (Meta).
     */
    public function getMeta(): array
    {
        $meta = [
            'filters' => $this->filters,
            'document_no' => $this->filters['document_no'] ?? '-',
            'dept_head_name' => null
        ];

        // Try to resolve Dept Head if department is filtered
        if (isset($this->filters['department_id'])) {
            $first = $this->query()->first();
            if ($first && $first->employee) {
                $supervisor = Supervisor::find($first->employee->supervisor_id);
                if ($supervisor) {
                    $meta['dept_head_name'] = $supervisor->employee?->full_name;
                }
            }
        }

        return $meta;
    }
}
