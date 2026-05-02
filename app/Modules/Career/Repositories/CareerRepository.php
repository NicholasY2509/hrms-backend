<?php

namespace App\Modules\Career\Repositories;

use App\Modules\Career\Models\Career;
use Illuminate\Pagination\LengthAwarePaginator;

class CareerRepository
{
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Career::query()->with([
            'employee', 
            'careerType', 
            'beforeWorkPosition', 
            'afterWorkPosition',
            'beforeDepartment',
            'afterDepartment',
            'beforeTeam',
            'afterTeam'
        ]);

        if (isset($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (isset($filters['career_type_id'])) {
            $query->where('career_type_id', $filters['career_type_id']);
        }

        return $query->latest('career_at')->paginate($perPage);
    }

    public function findById(int $id): ?Career
    {
        return Career::with([
            'employee', 
            'careerType', 
            'beforeWorkPosition', 
            'afterWorkPosition',
            'beforeDepartment',
            'afterDepartment',
            'beforeTeam',
            'afterTeam'
        ])->find($id);
    }

    public function create(array $data): Career
    {
        return Career::create($data);
    }

    public function update(Career $career, array $data): bool
    {
        return $career->update($data);
    }

    public function delete(Career $career): bool
    {
        return $career->delete();
    }
}
