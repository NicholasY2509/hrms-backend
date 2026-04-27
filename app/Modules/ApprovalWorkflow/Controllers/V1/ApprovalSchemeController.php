<?php

namespace App\Modules\ApprovalWorkflow\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Modules\ApprovalWorkflow\Services\ApprovalSchemeService;
use App\Modules\ApprovalWorkflow\Resources\V1\ApprovalSchemeResource;
use App\Modules\ApprovalWorkflow\Requests\V1\ApprovalSchemeIndexRequest;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Approval Workflow
 * @subgroup Schemes
 */
class ApprovalSchemeController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected ApprovalSchemeService $service
    ) {}

    /**
     * List all approval schemes (Categories).
     */
    public function index(Request $request): JsonResponse
    {
        $schemes = $this->service->paginateSchemes(
            $request->only('search'),
            $request->input('per_page', 15)
        );
        
        $resource = ApprovalSchemeResource::collection($schemes);
        
        return $this->successResponse(
            $resource->response()->getData(true), 
            'Schemes retrieved'
        );
    }

    /**
     * Get a specific scheme with all its rules.
     */
    public function show($id): JsonResponse
    {
        $scheme = $this->service->getSchemeDetails($id);
        if (!$scheme) return $this->errorResponse('Scheme not found', 404);
        
        return $this->successResponse(
            new ApprovalSchemeResource($scheme), 
            'Scheme details retrieved'
        );
    }

    /**
     * Create a new approval scheme.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'model_class' => 'required|string|unique:approval_schemes,model_class',
            'description' => 'nullable|string',
        ]);

        $scheme = $this->service->createScheme($validated);
        
        return $this->successResponse(
            new ApprovalSchemeResource($scheme), 
            'Scheme created', 
            201
        );
    }

    /**
     * Update an approval scheme.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($this->service->updateScheme($id, $validated)) {
            return $this->successResponse(null, 'Scheme updated');
        }

        return $this->errorResponse('Scheme not found', 404);
    }

    /**
     * Delete an approval scheme.
     */
    public function destroy($id): JsonResponse
    {
        if ($this->service->deleteScheme($id)) {
            return $this->successResponse(null, 'Scheme deleted');
        }

        return $this->errorResponse('Scheme not found', 404);
    }
}
