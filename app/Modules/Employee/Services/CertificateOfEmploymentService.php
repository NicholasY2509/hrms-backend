<?php

namespace App\Modules\Employee\Services;

use App\Modules\Employee\Models\CertificateOfEmployment;
use App\Modules\Employee\Repositories\CertificateOfEmploymentRepository;
use App\Services\StorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CertificateOfEmploymentService
{
    public function __construct(
        protected CertificateOfEmploymentRepository $repository
    ) {}

    public function createCertificate(array $data, ?UploadedFile $attachment = null): CertificateOfEmployment
    {
        return DB::transaction(function () use ($data, $attachment) {
            if ($attachment) {
                $data['attachment'] = StorageService::store($attachment, 'certificate_of_employments');
            }

            return $this->repository->create($data);
        });
    }

    public function updateCertificate(CertificateOfEmployment $certificate, array $data, ?UploadedFile $attachment = null): CertificateOfEmployment
    {
        return DB::transaction(function () use ($certificate, $data, $attachment) {
            if ($attachment) {
                $data['attachment'] = StorageService::store($attachment, 'certificate_of_employments');
            }

            $this->repository->update($certificate, $data);
            return $certificate->refresh();
        });
    }

    public function deleteCertificate(CertificateOfEmployment $certificate): bool
    {
        return DB::transaction(function () use ($certificate) {
            return $this->repository->delete($certificate);
        });
    }
}
