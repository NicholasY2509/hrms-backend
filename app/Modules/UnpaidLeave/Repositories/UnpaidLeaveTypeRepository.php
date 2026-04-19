<?php

namespace App\Modules\UnpaidLeave\Repositories;

use App\Modules\UnpaidLeave\Models\UnpaidLeaveType;
use Illuminate\Database\Eloquent\Collection;

class UnpaidLeaveTypeRepository
{
    /**
     * Get all unpaid leave types.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return UnpaidLeaveType::all();
    }
}
