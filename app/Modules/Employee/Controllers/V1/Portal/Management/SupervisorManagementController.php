<?php

namespace App\Modules\Employee\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Employee\Repositories\SupervisorRepository;
use App\Modules\Employee\Requests\StoreSupervisorRequest;
use App\Modules\Employee\Requests\UpdateSupervisorRequest;
use App\Modules\Employee\Resources\SupervisorResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupervisorManagementController extends Controller
{
    protected $repository;

    public function __construct(SupervisorRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search']);
        $perPage = $request->get('per_page', 15);
        
        $supervisors = $this->repository->getPaginated($filters, $perPage);
        
        return SupervisorResource::collection($supervisors);
    }

    public function store(StoreSupervisorRequest $request): JsonResponse
    {
        $supervisor = $this->repository->create($request->validated());
        
        return response()->json([
            'status' => 'Success',
            'message' => 'Supervisor created successfully',
            'data' => new SupervisorResource($supervisor->load('employee'))
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $supervisor = $this->repository->findById($id);
        
        if (!$supervisor) {
            return response()->json(['message' => 'Supervisor not found'], 404);
        }
        
        return response()->json([
            'data' => new SupervisorResource($supervisor)
        ]);
    }

    public function update(UpdateSupervisorRequest $request, $id): JsonResponse
    {
        $success = $this->repository->update($id, $request->validated());
        
        if (!$success) {
            return response()->json(['message' => 'Supervisor not found'], 404);
        }
        
        $supervisor = $this->repository->findById($id);
        
        return response()->json([
            'status' => 'Success',
            'message' => 'Supervisor updated successfully',
            'data' => new SupervisorResource($supervisor)
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $success = $this->repository->delete($id);
        
        if (!$success) {
            return response()->json(['message' => 'Supervisor not found'], 404);
        }
        
        return response()->json([
            'status' => 'Success',
            'message' => 'Supervisor deleted successfully'
        ]);
    }
}
