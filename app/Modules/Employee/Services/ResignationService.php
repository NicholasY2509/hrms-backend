<?php

namespace App\Modules\Employee\Services;

use App\Modules\Employee\Models\Resignation;
use App\Modules\Employee\Repositories\ResignationRepository;
use App\Services\StorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ResignationService
{
    public function __construct(
        protected ResignationRepository $repository
    ) {}

    public function createResignation(array $data, ?UploadedFile $attachment = null): Resignation
    {
        return DB::transaction(function () use ($data, $attachment) {
            if ($attachment) {
                $data['attachment'] = StorageService::store($attachment, 'resignations');
            }

            return $this->repository->create($data);
        });
    }

    public function updateResignation(Resignation $resignation, array $data, ?UploadedFile $attachment = null): Resignation
    {
        return DB::transaction(function () use ($resignation, $data, $attachment) {
            if ($attachment) {
                $data['attachment'] = StorageService::store($attachment, 'resignations');
            }

            $this->repository->update($resignation, $data);
            return $resignation->refresh();
        });
    }

    public function deleteResignation(Resignation $resignation): bool
    {
        return DB::transaction(function () use ($resignation) {
            return $this->repository->delete($resignation);
        });
    }

    public function settle(Resignation $resignation, ?string $finalEffectiveDate = null): Resignation
    {
        return DB::transaction(function () use ($resignation, $finalEffectiveDate) {
            if ($resignation->settled_at) {
                return $resignation;
            }

            $effectiveDate = $finalEffectiveDate ?? $resignation->effective_date;

            // 1. Update Employee Status and Resign Date
            $resignation->employee->update([
                'work_employee_status_id' => 2, // Resign
                'resign_date' => $effectiveDate,
            ]);

            // 2. Mark Resignation as Settled
            $resignation->update([
                'effective_date' => $effectiveDate,
                'settled_at' => now(),
                'confirmed_at' => $resignation->confirmed_at ?? now(),
            ]);

            return $resignation->refresh();
        });
    }
}
