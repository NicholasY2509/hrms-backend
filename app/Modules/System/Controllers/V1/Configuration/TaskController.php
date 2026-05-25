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

    /**
     * Clear Queue
     *
     * Clear all pending jobs in the queue.
     *
     * @return JsonResponse
     */
    public function clearQueue(): JsonResponse
    {
        \Illuminate\Support\Facades\Artisan::call('queue:clear');
        $this->taskService->cancelPendingTasks();
        return $this->successResponse([], 'Queue cleared and pending tasks marked as failed.');
    }

    /**
     * Restart Queue
     *
     * Restart the queue workers.
     *
     * @return JsonResponse
     */
    public function restartQueue(): JsonResponse
    {
        // We use exec to run the supervisorctl command. 
        // Note: The web server user (e.g. www-data) must have sudo privileges without a password for this command.
        exec('sudo supervisorctl restart hrms-api-queue:* 2>&1', $output, $return_var);

        if ($return_var !== 0) {
            return $this->errorResponse('Failed to restart queue workers: ' . implode("\n", $output), 500);
        }

        return $this->successResponse([], 'Queue workers restarted successfully via supervisor.');
    }
}
