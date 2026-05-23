<?php

namespace App\Modules\Organization\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\Organization\Models\PositionHierarchyMatrix;
use App\Modules\Organization\Requests\PositionHierarchyMatrixRequest;
use App\Modules\Organization\Resources\PositionHierarchyMatrixResource;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PositionHierarchyMatrixController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $query = PositionHierarchyMatrix::with([
            'workLocation', 
            'department', 
            'workPosition', 
            'supervisorWorkPosition'
        ]);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->whereHas('workLocation', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('department', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('workPosition', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('supervisorWorkPosition', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->has('work_location_id')) {
            $query->where('work_location_id', $request->work_location_id);
        }

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $perPage = $request->get('per_page', 15);
        $matrices = $query->paginate($perPage);

        return $this->successResponse(
            PositionHierarchyMatrixResource::collection($matrices)->response()->getData(true),
            'Position hierarchy matrices retrieved successfully'
        );
    }

    public function store(PositionHierarchyMatrixRequest $request): JsonResponse
    {
        $matrix = PositionHierarchyMatrix::create($request->validated());

        return $this->successResponse(
            new PositionHierarchyMatrixResource($matrix->load(['workLocation', 'department', 'workPosition', 'supervisorWorkPosition'])),
            'Position hierarchy matrix created successfully',
            201
        );
    }

    public function show(PositionHierarchyMatrix $positionHierarchyMatrix): JsonResponse
    {
        return $this->successResponse(
            new PositionHierarchyMatrixResource($positionHierarchyMatrix->load(['workLocation', 'department', 'workPosition', 'supervisorWorkPosition'])),
            'Position hierarchy matrix retrieved successfully'
        );
    }

    public function update(PositionHierarchyMatrixRequest $request, PositionHierarchyMatrix $positionHierarchyMatrix): JsonResponse
    {
        $positionHierarchyMatrix->update($request->validated());

        return $this->successResponse(
            new PositionHierarchyMatrixResource($positionHierarchyMatrix->load(['workLocation', 'department', 'workPosition', 'supervisorWorkPosition'])),
            'Position hierarchy matrix updated successfully'
        );
    }

    public function destroy(PositionHierarchyMatrix $positionHierarchyMatrix): JsonResponse
    {
        $positionHierarchyMatrix->delete();

        return $this->successResponse(null, 'Position hierarchy matrix deleted successfully');
    }
}
