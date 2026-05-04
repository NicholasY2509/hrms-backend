<?php

namespace App\Modules\Organization\Services;

use App\Modules\Organization\Models\WorkLocation;
use Illuminate\Support\Facades\DB;

class WorkLocationService
{
    /**
     * Create a new work location.
     */
    public function createWorkLocation(array $data): WorkLocation
    {
        return DB::transaction(function () use ($data) {
            return WorkLocation::create($data);
        });
    }

    /**
     * Update an existing work location.
     */
    public function updateWorkLocation(WorkLocation $workLocation, array $data): WorkLocation
    {
        return DB::transaction(function () use ($workLocation, $data) {
            $workLocation->update($data);
            return $workLocation;
        });
    }

    /**
     * Delete a work location.
     */
    public function deleteWorkLocation(WorkLocation $workLocation): bool
    {
        return DB::transaction(function () use ($workLocation) {
            return $workLocation->delete();
        });
    }
}
