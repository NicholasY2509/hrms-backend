<?php

namespace App\Modules\UnpaidLeave\Services;

use App\Modules\UnpaidLeave\Repositories\UnpaidLeaveTypeRepository;
use Illuminate\Database\Eloquent\Collection;

class UnpaidLeaveTypeService
{
    private UnpaidLeaveTypeRepository $repository;

    public function __construct(UnpaidLeaveTypeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all unpaid leave types.
     *
     * @return Collection
     */
    public function getAllTypes(): Collection
    {
        return $this->repository->getAll();
    }
}
