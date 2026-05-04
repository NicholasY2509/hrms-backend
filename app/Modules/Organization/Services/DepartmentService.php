<?php

namespace App\Modules\Organization\Services;

use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\DepartmentHead;
use Illuminate\Support\Facades\DB;

class DepartmentService
{
    public function createDepartment(array $data): Department
    {
        return DB::transaction(function () use ($data) {
            $department = Department::create(['name' => $data['name']]);

            $this->syncHeads($department, $data['heads'] ?? []);

            return $department->load('heads.employee', 'heads.workLocation');
        });
    }

    public function updateDepartment(Department $department, array $data): Department
    {
        return DB::transaction(function () use ($department, $data) {
            $department->update(['name' => $data['name']]);

            if (array_key_exists('heads', $data)) {
                $this->syncHeads($department, $data['heads'] ?? []);
            }

            return $department->load('heads.employee', 'heads.workLocation');
        });
    }

    public function deleteDepartment(Department $department): bool
    {
        return DB::transaction(function () use ($department) {
            return $department->delete();
        });
    }

    /**
     * Sync department head assignments for all work locations.
     * Replaces existing assignments with the provided set.
     */
    protected function syncHeads(Department $department, array $heads): void
    {
        $department->heads()->delete();

        foreach ($heads as $head) {
            $department->heads()->create([
                'work_location_id' => $head['work_location_id'],
                'employee_id' => $head['employee_id'],
            ]);
        }
    }
}
