<?php

namespace App\Modules\Career\Services;

use App\Modules\Career\Models\Career;
use App\Modules\Career\Repositories\CareerRepository;
use Illuminate\Support\Facades\DB;

class CareerService
{
    public function __construct(
        protected CareerRepository $repository
    ) {}

    public function createCareer(array $data): Career
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });
    }

    public function updateCareer(Career $career, array $data): Career
    {
        return DB::transaction(function () use ($career, $data) {
            $this->repository->update($career, $data);
            return $career->refresh();
        });
    }

    public function deleteCareer(Career $career): bool
    {
        return DB::transaction(function () use ($career) {
            // Depending on the Approvable trait, deleting it might also delete approval requests
            // but for now, we just delete the model.
            return $this->repository->delete($career);
        });
    }

    public function settle(Career $career): Career
    {
        if ($career->settled_at) {
            return $career;
        }

        return DB::transaction(function () use ($career) {
            $employee = $career->employee;

            // Update employee fields with the "after" values
            $employee->update([
                'employee_status_id' => $career->after_employee_status_id ?? $employee->employee_status_id,
                'work_position_id' => $career->after_work_position_id ?? $employee->work_position_id,
                'work_location_id' => $career->after_work_location_id ?? $employee->work_location_id,
                'department_id' => $career->after_department_id ?? $employee->department_id,
                'team_id' => $career->after_team_id ?? $employee->team_id,
            ]);

            // Mark career change as settled
            $career->update([
                'settled_at' => now(),
                'confirmed_at' => $career->confirmed_at ?? now(),
            ]);

            return $career->refresh();
        });
    }
}
