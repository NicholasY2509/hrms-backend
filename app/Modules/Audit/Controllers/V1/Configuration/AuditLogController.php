<?php

namespace App\Modules\Audit\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Services\AuditLogService;
use App\Modules\Audit\Resources\AuditLogResource;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Configuration
 * @subgroup Audit Logs
 */
class AuditLogController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * List Activity Logs
     * 
     * Get a paginated list of all system activities.
     * 
     * @queryParam log_name string Filter by log category (e.g. default, auth). Example: default
     * @queryParam event string Filter by event type (created, updated, deleted). Example: updated
     * @queryParam per_page int Results per page. Example: 15
     */
    public function index(Request $request): JsonResponse
    {
        $logs = $this->auditLogService->getLogs($request->all());
        
        return $this->successResponse(
            AuditLogResource::collection($logs)->response()->getData(true),
            'Audit logs retrieved successfully'
        );
    }

    /**
     * View Activity Detail
     * 
     * Get detailed information about a specific activity log.
     */
    public function show(int $id): JsonResponse
    {
        $log = $this->auditLogService->getLogDetail($id);

        if (!$log) {
            return $this->errorResponse('Audit log not found', 404);
        }

        return $this->successResponse(
            new AuditLogResource($log),
            'Audit log detail retrieved successfully'
        );
    }
}
