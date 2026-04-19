<?php

namespace App\Modules\UnpaidLeave\Repositories;

use App\Modules\UnpaidLeave\Models\UnpaidLeaveApproval;

class UnpaidLeaveApprovalRepository
{
    /**
     * Create a new unpaid leave approval record.
     * 
     * @param array $data
     * @return UnpaidLeaveApproval
     */
    public function create(array $data): UnpaidLeaveApproval
    {
        return UnpaidLeaveApproval::create($data);
    }
}
