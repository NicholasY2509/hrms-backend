<?php

namespace App\Modules\Employee\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Employee\Models\CertificateOfEmployment;
use App\Modules\Employee\Requests\CertificateOfEmploymentIndexRequest;
use App\Modules\Employee\Requests\CertificateOfEmploymentRequest;
use App\Modules\Employee\Resources\CertificateOfEmploymentResource;
use App\Modules\Employee\Services\CertificateOfEmploymentService;
use App\Modules\Employee\Repositories\CertificateOfEmploymentRepository;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Employee Management
 * @subgroup Management Portal
 */
class CertificateOfEmploymentController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected CertificateOfEmploymentService $service,
        protected CertificateOfEmploymentRepository $repository
    ) {}

    /**
     * List certificate of employments.
     * 
     * Get a paginated list of certificate of employments.
     */
    public function index(CertificateOfEmploymentIndexRequest $request): JsonResponse
    {
        $certificates = $this->repository->getPaginated(
            $request->validated(),
            $request->input('per_page', 15)
        );

        return $this->successResponse(
            CertificateOfEmploymentResource::collection($certificates)->response()->getData(true),
            'Certificates retrieved successfully'
        );
    }

    /**
     * Create certificate of employment.
     * 
     * Store a new certificate of employment request.
     */
    public function store(CertificateOfEmploymentRequest $request): JsonResponse
    {
        $data = $request->validated();
        $attachment = $request->file('attachment');
        
        $certificate = $this->service->createCertificate($data, $attachment);

        return $this->successResponse(
            new CertificateOfEmploymentResource($certificate),
            'Certificate created successfully',
            201
        );
    }

    /**
     * Get certificate of employment.
     * 
     * Get detailed information about a specific certificate.
     */
    public function show(string $id): JsonResponse
    {
        $certificate = $this->repository->findById($id);

        if (!$certificate) {
            return $this->errorResponse('Certificate not found', 404);
        }

        return $this->successResponse(
            new CertificateOfEmploymentResource($certificate),
            'Certificate details retrieved'
        );
    }

    /**
     * Update certificate of employment.
     * 
     * Update the details of an existing certificate.
     */
    public function update(CertificateOfEmploymentRequest $request, string $id): JsonResponse
    {
        $certificate = $this->repository->findById($id);

        if (!$certificate) {
            return $this->errorResponse('Certificate not found', 404);
        }

        $data = $request->validated();
        $attachment = $request->file('attachment');

        $updatedCertificate = $this->service->updateCertificate($certificate, $data, $attachment);

        return $this->successResponse(
            new CertificateOfEmploymentResource($updatedCertificate),
            'Certificate updated successfully'
        );
    }

    /**
     * Delete certificate of employment.
     * 
     * Remove a certificate.
     */
    public function destroy(string $id): JsonResponse
    {
        $certificate = $this->repository->findById($id);

        if (!$certificate) {
            return $this->errorResponse('Certificate not found', 404);
        }

        $this->service->deleteCertificate($certificate);

        return $this->successResponse(null, 'Certificate deleted successfully');
    }
}
