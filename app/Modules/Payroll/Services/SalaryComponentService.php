<?php

namespace App\Modules\Payroll\Services;

use App\Modules\Payroll\Repositories\SalaryComponentRepository;
use App\Modules\Payroll\Models\SalaryComponent;
use Illuminate\Database\Eloquent\Collection;

class SalaryComponentService
{
    public function __construct(
        protected SalaryComponentRepository $repository
    ) {}

    public function getAllComponents(): Collection
    {
        return $this->repository->all();
    }

    public function createComponent(array $data): SalaryComponent
    {
        return $this->repository->create($data);
    }

    public function updateComponent(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function deleteComponent(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
