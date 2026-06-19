<?php

namespace App\Modules\Overtime\Services;

use App\Modules\Employee\Models\Employee;
use Illuminate\Support\Collection;

class OvertimeTemplateService
{
    /**
     * Get the data required for the overtime report.
     */
    public function getTemplateData(Collection $overtimes): array
    {
        $first = $overtimes->first();
        $deptNames = $overtimes->pluck('employee.department.name')->unique();

        $totalSeconds = $overtimes->reduce(function ($carry, $item) {
            if (empty($item->total_time) || !str_contains($item->total_time, ':')) return $carry;
            list($hours, $minutes) = explode(':', $item->total_time);
            return $carry + ($hours * 3600) + ($minutes * 60);
        }, 0);

        return [
            'signatures' => $this->resolveSignatures($deptNames, $overtimes),
            'totals' => [
                'count' => $overtimes->count(),
                'price' => $overtimes->sum(fn($o) => (float)($o->real_overtime_price ?? 0)),
                'hours' => round($totalSeconds / 3600, 2)
            ],
            'department_name' => $first?->employee?->department?->name ?? '-',
            'show_som' => $deptNames->intersect(['GR', 'BP', 'General Repair', 'Body & Paint'])->isNotEmpty(),
            'is_hcd' => $deptNames->contains('HCD'),
        ];
    }

    /**
     * Resolve the names for signatures based on work positions.
     */
    protected function resolveSignatures(Collection $deptNames, Collection $overtimes): array
    {
        // Dynamic lookups for key management positions
        $branchHead = Employee::whereHas('position', fn($q) => $q->where('name', 'BRANCH HEAD'))
            ->where('work_employee_status_id', 1)
            ->first();

        $adh = Employee::whereHas('position', fn($q) => $q->where('name', 'ADM & FINANCE HEAD'))
            ->where('work_employee_status_id', 1)
            ->first();

        $hrd = Employee::whereHas('position', fn($q) => $q->where('name', 'HR & GA'))
            ->where('work_employee_status_id', 1)
            ->first();

        $som = Employee::whereHas('position', fn($q) => $q->where('name', 'SERVICE OPERATIONAL MANAGER'))
            ->where('work_employee_status_id', 1)
            ->first();

        $deptHeadName = null;
        if ($overtimes->isNotEmpty()) {
            $employeePositionIds = $overtimes->pluck('employee.work_position_id')->filter()->unique();
            if ($employeePositionIds->isNotEmpty() && $employeePositionIds->diff([26, 62])->isEmpty()) {
                $specialDeptHead = Employee::where('work_position_id', 63)
                    ->where('work_employee_status_id', 1)
                    ->first();
                
                if ($specialDeptHead) {
                    $deptHeadName = $specialDeptHead->alias ?? $specialDeptHead->full_name;
                }
            }
        }

        return [
            'branch_manager' => $branchHead?->alias ?? $branchHead?->full_name ?? 'NICHOLAS BOEDIMAN', // Fallback to legacy if not found
            'adh' => $adh?->alias ?? $adh?->full_name ?? 'YANNY SUGIANTO',
            'hrd' => $hrd?->alias ?? $hrd?->full_name ?? 'SITI MAHARANI RAMBE',
            'som' => $som?->alias ?? $som?->full_name ?? 'SUWARNO',
            'dept_head' => $deptHeadName,
        ];
    }
}
