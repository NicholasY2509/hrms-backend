<?php

namespace App\Modules\Employee\Exports;

use App\Modules\Employee\Models\Resignation;

class ResignationExport
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Resignation::query()->with([
            'employee.position',
            'employee.work_location'
        ]);

        if (isset($this->filters['id'])) {
            $query->where('id', $this->filters['id']);
        }

        return $query;
    }
}
