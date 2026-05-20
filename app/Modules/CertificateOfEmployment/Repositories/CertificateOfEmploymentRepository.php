<?php

namespace App\Modules\CertificateOfEmployment\Repositories;

use App\Modules\CertificateOfEmployment\Models\CertificateOfEmployment;
use Illuminate\Pagination\LengthAwarePaginator;

class CertificateOfEmploymentRepository
{
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = CertificateOfEmployment::query()->with([
            'employee',
            'work_position',
            'approvalRequest.steps'
        ]);

        if (isset($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id_number', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function findById(string $id): ?CertificateOfEmployment
    {
        return CertificateOfEmployment::with([
            'employee',
            'work_position',
            'approvalRequest.steps'
        ])->find($id);
    }
}
