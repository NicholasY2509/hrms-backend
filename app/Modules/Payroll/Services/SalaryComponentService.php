<?php

namespace App\Modules\Payroll\Services;

use App\Modules\Payroll\Repositories\SalaryComponentRepository;
use App\Modules\Payroll\Models\SalaryComponent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class SalaryComponentService
{
    public function __construct(
        protected SalaryComponentRepository $repository
    ) {}

    public function getAllComponents(): Collection
    {
        return Cache::rememberForever('payroll_salary_components', function () {
            return $this->repository->all();
        });
    }

    public function createComponent(array $data): SalaryComponent
    {
        $component = $this->repository->create($data);
        Cache::forget('payroll_salary_components');
        return $component;
    }

    public function updateComponent(int $id, array $data): bool
    {
        $updated = $this->repository->update($id, $data);
        if ($updated) {
            Cache::forget('payroll_salary_components');
        }
        return $updated;
    }

    public function deleteComponent(int $id): bool
    {
        $deleted = $this->repository->delete($id);
        if ($deleted) {
            Cache::forget('payroll_salary_components');
        }
        return $deleted;
    }
}
