<?php

namespace App\Modules\Overtime\Exports;

use App\Modules\Overtime\Models\Overtime;
use App\Modules\Employee\Models\Employee;
use App\Modules\Employee\Models\Supervisor;

use App\Modules\Organization\Models\DepartmentHead;

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

        // Special Rule: Position 62 (e.g. Security) -> Sign by Position 63 (e.g. Chief Security)
        $isPos62 = (isset($this->filters['work_position_id']) && (int)$this->filters['work_position_id'] === 62);
        if (!$isPos62 && $this->query()->exists()) {
            // Check if there are ANY employees in the result set that are NOT position 62
            $hasOther = $this->query()->whereHas('employee', function($q) {
                $q->where('work_position_id', '!=', 62);
            })->exists();
            $isPos62 = !$hasOther;
        }

        if ($isPos62) {
            $locId = $this->filters['work_location_id'] ?? $this->query()->first()?->employee?->work_location_id;
            
            // Try specific location first, then any location
            $chief = Employee::where('work_position_id', 63)
                ->where('work_location_id', $locId)
                ->first() 
                ?? Employee::where('work_position_id', 63)->first();

            if ($chief) {
                $meta['dept_head_name'] = $chief->full_name;
                return $meta;
            }
        }

        // Try to resolve Dept Head if department is filtered
        if (isset($this->filters['department_id'])) {
            $deptId = $this->filters['department_id'];
            $locationId = $this->filters['work_location_id'] ?? null;

            // If location not filtered, pick from first result
            if (!$locationId) {
                $first = $this->query()->first();
                $locationId = $first?->employee?->work_location_id;
            }

            if ($locationId) {
                $head = DepartmentHead::where('department_id', (int)$deptId)
                    ->where(function($q) use ($locationId) {
                        $q->where('work_location_id', $locationId)
                          ->orWhereNull('work_location_id');
                    })
                    ->with('employee')
                    ->orderBy('work_location_id', 'desc') // Specific location first
                    ->first();

                $meta['dept_head_name'] = $head?->employee?->full_name;
            }
        }

        return $meta;
    }
}
