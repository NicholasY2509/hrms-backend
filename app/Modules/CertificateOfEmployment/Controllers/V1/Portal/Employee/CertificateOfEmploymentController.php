<?php

namespace App\Modules\CertificateOfEmployment\Controllers\V1\Portal\Employee;

use App\Http\Controllers\Controller;
use App\Modules\CertificateOfEmployment\Resources\CertificateOfEmploymentResource;
use App\Modules\CertificateOfEmployment\Services\CertificateOfEmploymentService;
use App\Modules\CertificateOfEmployment\Repositories\CertificateOfEmploymentRepository;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @group Certificate of Employment
 * @subgroup Employee Portal
 */
class CertificateOfEmploymentController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected CertificateOfEmploymentService $service,
        protected CertificateOfEmploymentRepository $repository
    ) {}

    /**
     * My CoE requests.
     */
    public function index(Request $request): JsonResponse
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return $this->errorResponse('Data karyawan tidak ditemukan', 404);
        }

        $certificates = $this->repository->getPaginated(
            array_merge($request->all(), ['employee_id' => $employee->id]),
            $request->input('per_page', 15)
        );

        return $this->successResponse(
            CertificateOfEmploymentResource::collection($certificates)->response()->getData(true),
            'My Certificate of Employment records retrieved successfully'
        );
    }

    /**
     * Request a CoE.
     */
    public function store(): JsonResponse
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return $this->errorResponse('Data karyawan tidak ditemukan', 404);
        }

        $certificate = $this->service->request($employee);

        return $this->successResponse(
            new CertificateOfEmploymentResource($certificate),
            'Pengajuan Surat Keterangan Kerja berhasil dibuat',
            201
        );
    }
}
