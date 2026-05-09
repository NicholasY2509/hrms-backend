<?php

namespace App\Modules\Audit\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Services\AuditLogService;
use App\Modules\Audit\Resources\AuditLogResource;
use App\Modules\Audit\Requests\GetAuditLogsRequest;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

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

    public function index(GetAuditLogsRequest $request): JsonResponse
    {
        $logs = $this->auditLogService->getLogs($request->validated());
        
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
