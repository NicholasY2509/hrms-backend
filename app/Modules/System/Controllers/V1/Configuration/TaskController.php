<?php

namespace App\Modules\System\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\System\Requests\V1\TaskIndexRequest;
use App\Modules\System\Resources\V1\TaskResource;
use App\Modules\System\Services\TaskService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group System Configuration
 * @subgroup Tasks
 *
 * APIs for managing and viewing system tasks.
 */
class TaskController extends Controller
{
    use ApiResponses;

    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Get Paginated Tasks
     *
     * Retrieve a paginated list of system tasks with optional filters.
     *
     * @param TaskIndexRequest $request
     * @return JsonResponse
     */
    public function index(TaskIndexRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = $request->input('per_page', 15);

        $paginator = $this->taskService->getPaginated($filters, $perPage);

        $data = TaskResource::collection($paginator)->response()->getData(true);

        return $this->successResponse($data, 'Tasks retrieved successfully.');
    }
}
