<?php

namespace App\Modules\Employee\Services;

use App\Modules\Employee\Repositories\EmployeeTaxProfileRepository;
use App\Modules\Employee\Models\EmployeeTaxProfile;

class EmployeeTaxProfileService
{
    public function __construct(
        protected EmployeeTaxProfileRepository $repository
    ) {}

    public function getTaxProfile(int $employeeId): ?EmployeeTaxProfile
    {
        return $this->repository->findByEmployee($employeeId);
    }

    public function updateTaxProfile(int $employeeId, array $data): EmployeeTaxProfile
    {
        return $this->repository->updateOrCreate($employeeId, $data);
    }
}
