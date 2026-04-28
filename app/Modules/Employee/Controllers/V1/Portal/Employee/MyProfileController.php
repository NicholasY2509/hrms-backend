<?php

namespace App\Modules\Employee\Controllers\V1\Portal\Employee;

use App\Http\Controllers\Controller;
use App\Modules\Employee\Resources\EmployeeResource;
use App\Modules\Employee\Services\EmployeeService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @group Employee
 * @subgroup Employee Portal
 *
 * API for employee self-service.
 */
class MyProfileController extends Controller
{
    use ApiResponses;

    protected EmployeeService $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    /**
     * Get the authenticated user's profile.
     *
     * @response {
     *  "status": "Success",
     *  "message": "Profile retrieved successfully",
     *  "data": {
     *      "id": 1,
     *      "nik": "12345",
     *      "name": "John Doe",
     *      "job_title": "Software Engineer",
     *      "department": "Engineering",
     *      "email": "john@example.com",
     *      "photo_url": null,
     *      "join_date": "2023-01-01",
     *      "supervisor": {
     *          "id": 2,
     *          "name": "Jane Smith",
     *          "nik": "67890"
     *      }
     *  }
     * }
     */
    public function profile(): JsonResponse
    {
        $userId = Auth::id();
        $employee = $this->employeeService->getProfile($userId);

        if (!$employee) {
            return $this->errorResponse('Employee profile not found', 404);
        }

        return $this->successResponse(new EmployeeResource($employee), 'Profile retrieved successfully');
    }
}
