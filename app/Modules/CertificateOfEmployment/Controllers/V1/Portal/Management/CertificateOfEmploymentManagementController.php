<?php

namespace App\Modules\CertificateOfEmployment\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\CertificateOfEmployment\Models\CertificateOfEmployment;
use App\Modules\CertificateOfEmployment\Requests\ManagementCoeRequest;
use App\Modules\CertificateOfEmployment\Resources\CertificateOfEmploymentResource;
use App\Modules\CertificateOfEmployment\Services\CertificateOfEmploymentService;
use App\Modules\CertificateOfEmployment\Repositories\CertificateOfEmploymentRepository;
use App\Modules\Employee\Models\Employee;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Modules\System\Services\ReportService;

/**
 * @group Certificate of Employment
 * @subgroup Management Portal
 */
class CertificateOfEmploymentManagementController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected CertificateOfEmploymentService $service,
        protected CertificateOfEmploymentRepository $repository,
        protected ReportService $reportService
    ) {}

    /**
     * List CoE requests.
     */
    public function index(Request $request): JsonResponse
    {
        $certificates = $this->repository->getPaginated(
            $request->all(),
            $request->input('per_page', 15)
        );

        return $this->successResponse(
            CertificateOfEmploymentResource::collection($certificates)->response()->getData(true),
            'Certificate of Employment records retrieved successfully'
        );
    }

    /**
     * Create CoE request (Management).
     */
    public function store(ManagementCoeRequest $request): JsonResponse
    {
        $employee = Employee::findOrFail($request->query('employee_id'));
        
        $certificate = $this->service->request($employee, true);

        return $this->successResponse(
            new CertificateOfEmploymentResource($certificate),
            'Pengajuan Surat Keterangan Kerja berhasil dibuat',
            201
        );
    }

    /**
     * Get CoE details.
     */
    public function show(CertificateOfEmployment $certificateOfEmployment): JsonResponse
    {
        return $this->successResponse(
            new CertificateOfEmploymentResource($certificateOfEmployment->load([
                'employee',
                'work_position',
                'approvalRequest.steps.actor',
                'approvalRequest.steps.approver',
                'approvalRequest.steps.group.employees'
            ])),
            'Certificate of Employment details retrieved'
        );
    }

    /**
     * Settle CoE.
     */
    public function settle(CertificateOfEmployment $certificateOfEmployment): JsonResponse
    {
        $settledCoe = $this->service->settle($certificateOfEmployment);

        return $this->successResponse(
            new CertificateOfEmploymentResource($settledCoe),
            'Certificate of Employment finalized successfully'
        );
    }

    /**
     * Export CoE to PDF.
     */
    public function export(CertificateOfEmployment $certificateOfEmployment): JsonResponse
    {
        $report = $this->reportService->requestReport([
            'type' => 'certificate_of_employment',
            'format' => 'pdf',
            'name' => 'Surat Keterangan Kerja - ' . $certificateOfEmployment->employee?->full_name,
            'filters' => [
                'id' => $certificateOfEmployment->id
            ]
        ]);

        return $this->successResponse(
            $report,
            'Proses pembuatan Surat Keterangan Kerja sedang berlangsung.',
            202
        );
    }
}
