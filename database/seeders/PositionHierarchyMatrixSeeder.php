<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Modules\Employee\Models\Employee;
use App\Modules\Organization\Models\PositionHierarchyMatrix;

class PositionHierarchyMatrixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::with(['supervisor.employee'])
            ->whereNotNull('supervisor_id')
            ->where('work_employee_status_id', 1)
            ->get();

        // Clear existing rules to allow clean re-seed
        PositionHierarchyMatrix::truncate();

        $matrixRules = [];

        foreach ($employees as $employee) {
            if (!$employee->supervisor || !$employee->supervisor->employee) {
                continue;
            }

            $locId = $employee->work_location_id;
            $deptId = $employee->department_id;
            $posId = $employee->work_position_id;
            $supPosId = $employee->supervisor->employee->work_position_id;

            // Only map if all components are present
            if ($locId && $deptId && $posId && $supPosId) {
                // If department is NOT BP(2) or GR(4), make the rule global (null location)
                $isGlobal = !in_array($deptId, [2, 4]);
                $finalLocId = $isGlobal ? null : $locId;
                
                $key = ($isGlobal ? "global" : $locId) . "-{$deptId}-{$posId}";
                $matrixRules[$key] = [
                    'work_location_id' => $finalLocId,
                    'department_id' => $deptId,
                    'work_position_id' => $posId,
                    'supervisor_work_position_id' => $supPosId,
                ];
            }
        }

        $inserted = 0;
        foreach ($matrixRules as $rule) {
            PositionHierarchyMatrix::updateOrCreate(
                [
                    'work_location_id' => $rule['work_location_id'],
                    'department_id' => $rule['department_id'],
                    'work_position_id' => $rule['work_position_id'],
                ],
                [
                    'supervisor_work_position_id' => $rule['supervisor_work_position_id'],
                ]
            );
            $inserted++;
        }

        $this->command->info("PositionHierarchyMatrixSeeder: Successfully inserted/updated {$inserted} active matrix rules.");
    }
}
