<?php

namespace App\Modules\Career\Exports;

use App\Modules\Career\Models\Career;

class CareerExport
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Career::query()->with([
            'employee',
            'careerType',
            'beforeEmployeeStatus',
            'afterEmployeeStatus',
            'beforeWorkPosition',
            'afterWorkPosition',
            'beforeTeam',
            'afterTeam',
            'beforeDepartment',
            'afterDepartment',
            'beforeWorkLocation',
            'afterWorkLocation',
            'approvalRequest.steps'
        ]);

        if (isset($this->filters['id'])) {
            $query->where('id', $this->filters['id']);
        }

        return $query;
    }
}
