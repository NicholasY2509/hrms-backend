<?php

namespace App\Modules\System\Repositories;

use App\Modules\System\Models\Report;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReportRepository
{
    /**
     * Get paginated reports.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Report::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new report.
     *
     * @param array $data
     * @return Report
     */
    public function create(array $data): Report
    {
        return Report::create($data);
    }

    /**
     * Find a report by ID.
     *
     * @param int $id
     * @return Report|null
     */
    public function find(int $id): ?Report
    {
        return Report::find($id);
    }
}
