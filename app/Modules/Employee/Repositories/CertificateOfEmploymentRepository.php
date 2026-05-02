<?php

namespace App\Modules\Employee\Repositories;

use App\Modules\Employee\Models\CertificateOfEmployment;
use Illuminate\Pagination\LengthAwarePaginator;

class CertificateOfEmploymentRepository
{
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = CertificateOfEmployment::query()->with([
            'employee', 
            'workPosition'
        ]);

        if (isset($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        return $query->latest('request_date')->paginate($perPage);
    }

    public function findById(string $id): ?CertificateOfEmployment
    {
        return CertificateOfEmployment::with([
            'employee', 
            'workPosition'
        ])->find($id);
    }

    public function create(array $data): CertificateOfEmployment
    {
        return CertificateOfEmployment::create($data);
    }

    public function update(CertificateOfEmployment $certificate, array $data): bool
    {
        return $certificate->update($data);
    }

    public function delete(CertificateOfEmployment $certificate): bool
    {
        return $certificate->delete();
    }
}
