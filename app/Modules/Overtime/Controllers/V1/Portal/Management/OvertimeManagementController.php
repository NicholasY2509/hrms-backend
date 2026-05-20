<?php

namespace App\Modules\Overtime\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Overtime\Repositories\OvertimeRepository;
use App\Modules\Overtime\Requests\SettleOvertimeRequest;
use App\Modules\Overtime\Requests\UpdateOvertimeRequest;
use App\Modules\Overtime\Requests\V1\GetOvertimeManagementRequest;
use App\Modules\Overtime\Resources\V1\OvertimeResource;
use App\Modules\Overtime\Services\OvertimeService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Modules\System\Services\ReportService;
use App\Modules\Overtime\Services\OvertimeTemplateService;

/**
 * @group Overtime
 * @subgroup Management Portal
 * 
 * Endpoints for HR/Management to monitor all employee overtime requests.
 */
class OvertimeManagementController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected OvertimeRepository $repository,
        protected OvertimeService $service,
        protected ReportService $reportService
    ) {}

    /**
     * List all employee overtime requests.
     */
    public function index(GetOvertimeManagementRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = $request->query('per_page', 15);

        $overtimes = $this->repository->paginate($filters, $perPage);

        return $this->successResponse(
            OvertimeResource::collection($overtimes)->response()->getData(true),
            'All employee overtime requests retrieved successfully.'
        );
    }

    /**
     * Show overtime detail with approval progress.
     */
    public function show(int $id): JsonResponse
    {
        $overtime = $this->repository->find($id);

        if (!$overtime) {
            return $this->errorResponse('Overtime request not found.', 404);
        }

        return $this->successResponse(
            new OvertimeResource($overtime),
            'Overtime detail retrieved.'
        );
    }
    /**
     * Settle (Close) an overtime request.
     * 
     * @bodyParam real_overtime_price numeric required The actual cost to be paid.
     * 
     * @response 200 {
     *  "status": "Success",
     *  "message": "Overtime request settled successfully.",
     *  "data": {...}
     * }
     */
    public function settle(int $id, SettleOvertimeRequest $request): JsonResponse
    {
        $overtime = $this->repository->find($id);

        if (!$overtime) {
            return $this->errorResponse('Overtime request not found.', 404);
        }

        if ($overtime->settled_at) {
            return $this->errorResponse('Overtime request is already settled.', 400);
        }

        $settledOvertime = $this->service->settleOvertime($overtime, $request->real_overtime_price);

        return $this->successResponse(
            new OvertimeResource($settledOvertime),
            'Overtime request settled successfully.'
        );
    }
    /**
     * Update an overtime request (Classification/Adjustment).
     * 
     * @bodyParam overtime_type_id int optional The DAC category.
     * @bodyParam estimated_overtime_price numeric optional Manual estimated price.
     * @bodyParam note string optional Adjust note.
     * 
     * @response 200 {
     *  "status": "Success",
     *  "message": "Overtime request updated successfully.",
     *  "data": {...}
     * }
     */
    public function update(int $id, UpdateOvertimeRequest $request): JsonResponse
    {
        $overtime = $this->repository->find($id);

        if (!$overtime) {
            return $this->errorResponse('Overtime request not found.', 404);
        }

        $updatedOvertime = $this->service->updateOvertime($overtime, $request->validated());

        return $this->successResponse(
            new OvertimeResource($updatedOvertime),
            'Overtime request updated successfully.'
        );
    }

    /**
     * Export overtime requests to PDF.
     * 
     * @queryParam start_date date optional Filter by start date.
     * @queryParam end_date date optional Filter by end date.
     * @queryParam department_id int optional Filter by department.
     */
    public function export(GetOvertimeManagementRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $filters['is_settled'] = true;
        
        $report = $this->reportService->requestReport([
            'type' => 'overtime',
            'format' => 'pdf',
            'name' => 'Form Pengajuan Lembur - ' . now()->format('YmdHis'),
            'filters' => $filters
        ]);

        return $this->successResponse(
            $report,
            'Proses pembuatan dokumen lembur sedang berlangsung.',
            202
        );
    }
}
