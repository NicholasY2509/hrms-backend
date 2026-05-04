<?php

namespace App\Modules\Disciplinary\Repositories;

use App\Modules\Disciplinary\Models\WarningLetter;
use Illuminate\Pagination\LengthAwarePaginator;

class WarningLetterRepository
{
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = WarningLetter::query()->with([
            'employee', 
            'warningLetterType'
        ]);

        if (isset($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (isset($filters['warning_letter_type_id'])) {
            $query->where('warning_letter_type_id', $filters['warning_letter_type_id']);
        }

        return $query->latest('warning_at')->paginate($perPage);
    }

    public function findById(int $id): ?WarningLetter
    {
        return WarningLetter::with([
            'employee', 
            'warningLetterType'
        ])->find($id);
    }

    public function create(array $data): WarningLetter
    {
        return WarningLetter::create($data);
    }

    public function update(WarningLetter $warningLetter, array $data): bool
    {
        return $warningLetter->update($data);
    }

    public function delete(WarningLetter $warningLetter): bool
    {
        return $warningLetter->delete();
    }
}
