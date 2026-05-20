<?php

namespace App\Modules\Disciplinary\Services;

use App\Modules\Disciplinary\Models\WarningLetter;
use App\Modules\Disciplinary\Repositories\WarningLetterRepository;
use App\Services\StorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class WarningLetterService
{
    public function __construct(
        protected WarningLetterRepository $repository
    ) {}

    public function createWarningLetter(array $data, ?UploadedFile $attachment = null): WarningLetter
    {
        return DB::transaction(function () use ($data, $attachment) {
            if ($attachment) {
                $data['attachment'] = StorageService::store($attachment, 'warning_letters');
            }

            return $this->repository->create($data);
        });
    }

    public function updateWarningLetter(WarningLetter $warningLetter, array $data, ?UploadedFile $attachment = null): WarningLetter
    {
        return DB::transaction(function () use ($warningLetter, $data, $attachment) {
            if ($attachment) {
                // Optionally delete the old attachment here
                $data['attachment'] = StorageService::store($attachment, 'warning_letters');
            }

            $this->repository->update($warningLetter, $data);
            return $warningLetter->refresh();
        });
    }

    public function deleteWarningLetter(WarningLetter $warningLetter): bool
    {
        return DB::transaction(function () use ($warningLetter) {
            return $this->repository->delete($warningLetter);
        });
    }

    public function settle(WarningLetter $warningLetter): WarningLetter
    {
        if ($warningLetter->settled_at) {
            return $warningLetter;
        }

        return DB::transaction(function () use ($warningLetter) {
            $warningLetter->update([
                'settled_at' => now(),
                'confirmed_at' => $warningLetter->confirmed_at ?? now(),
            ]);

            return $warningLetter->refresh();
        });
    }

    /**
     * Get paginated warning letters for a specific employee.
     */
    public function getEmployeeWarningLetters(int $employeeId, array $filters = [], int $perPage = 15)
    {
        $filters['employee_id'] = $employeeId;
        return $this->repository->getPaginated($filters, $perPage);
    }

    /**
     * Get detailed warning letter for a specific employee.
     */
    public function getEmployeeWarningLetterDetail(int $id, int $employeeId): ?WarningLetter
    {
        $warningLetter = $this->repository->findById($id);

        if (!$warningLetter || $warningLetter->employee_id !== $employeeId) {
            return null;
        }

        return $warningLetter;
    }
}
