<?php

namespace App\Modules\Leave\Repositories;

use App\Modules\Leave\Models\AnnualLeave;

class AnnualLeaveRepository
{
    /**
     * Create a new annual leave record.
     */
    public function create(array $data): AnnualLeave
    {
        return AnnualLeave::create($data);
    }

    /**
     * Find an annual leave record by ID.
     */
    public function find(int $id): ?AnnualLeave
    {
        return AnnualLeave::find($id);
    }
}
