<?php

namespace App\Modules\System\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Modules\System\Models\Report;
use App\Modules\System\Requests\V1\ReportStoreRequest;
use App\Modules\System\Resources\V1\ReportResource;
use App\Modules\System\Services\ReportService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group System Configuration
 *
 * API for managing system-generated reports and exports.
 */
class ReportController extends Controller
{
    use ApiResponses;

    protected ReportService $service;

    public function __construct(ReportService $service)
    {
        $this->service = $service;
    }

    /**
     * List Reports
     *
     * Get a paginated list of all export requests.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $reports = $this->service->getPaginatedReports();

        return $this->successResponse(
            ReportResource::collection($reports)->response()->getData(true),
            'Reports retrieved successfully'
        );
    }

    /**
     * Request Report
     *
     * Create a new report export request.
     *
     * @param ReportStoreRequest $request
     * @return JsonResponse
     */
    public function store(ReportStoreRequest $request): JsonResponse
    {
        $report = $this->service->requestReport($request->validated());

        return $this->successResponse(
            new ReportResource($report),
            'Export request submitted successfully',
            202
        );
    }

    /**
     * Show Report
     *
     * Get details of a specific report, including download URL if completed.
     *
     * @param Report $report
     * @return JsonResponse
     */
    public function show(Report $report): JsonResponse
    {
        $data = $this->service->getReportDetail($report);

        return $this->successResponse(
            new ReportResource($data),
            'Report details retrieved successfully'
        );
    }
}
