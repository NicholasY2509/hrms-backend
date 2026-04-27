<?php

namespace App\Modules\Organization\Services;

use App\Modules\Organization\Models\Department;
use Illuminate\Support\Facades\DB;

class DepartmentService
{
    public function createDepartment(array $data): Department
    {
        return DB::transaction(function () use ($data) {
            return Department::create($data);
        });
    }

    public function updateDepartment(Department $department, array $data): Department
    {
        return DB::transaction(function () use ($department, $data) {
            $department->update($data);
            return $department;
        });
    }

    public function deleteDepartment(Department $department): bool
    {
        return DB::transaction(function () use ($department) {
            return $department->delete();
        });
    }
}
