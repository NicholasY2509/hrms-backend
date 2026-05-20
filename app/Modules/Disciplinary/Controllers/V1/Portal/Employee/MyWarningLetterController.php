<?php

namespace App\Modules\Disciplinary\Controllers\V1\Portal\Employee;

use App\Http\Controllers\Controller;
use App\Modules\Disciplinary\Requests\MyWarningLetterIndexRequest;
use App\Modules\Disciplinary\Resources\WarningLetterResource;
use App\Modules\Disciplinary\Services\WarningLetterService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @group Disciplinary
 * @subgroup Employee Portal
 */
class MyWarningLetterController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected WarningLetterService $service
    ) {}

    /**
     * List employee warning letters.
     * 
     * Get a paginated list of warning letters for the authenticated employee.
     * 
     * @response {
     *   "status": "Success",
     *   "message": "Warning letters retrieved successfully",
     *   "data": {
     *     "data": [
     *       {
     *         "id": 1,
     *         "document_no": "SP-001",
     *         "name": "Surat Peringatan 1",
     *         "employee_id": 1,
     *         "employee": {
     *           "id": 1,
     *           "name": "John Doe",
     *           "nik": "NIK-001"
     *         },
     *         "warning_letter_type_id": 1,
     *         "warning_letter_type": {
     *           "id": 1,
     *           "name": "SP 1 (First Warning)"
     *         },
     *         "warning_at": "2026-05-19",
     *         "start_date": "2026-05-19",
     *         "end_date": "2026-11-19",
     *         "note": "Frequent tardiness",
     *         "attachment": null,
     *         "attachment_url": null,
     *         "status": "Approved",
     *         "confirmed_at": "2026-05-19 08:00:00",
     *         "settled_at": null,
     *         "created_at": "2026-05-19 08:00:00",
     *         "updated_at": "2026-05-19 08:00:00"
     *       }
     *     ],
     *     "links": {
     *       "first": "http://localhost/api/v1/portal/employee/my-warning-letters?page=1",
     *       "last": "http://localhost/api/v1/portal/employee/my-warning-letters?page=1",
     *       "prev": null,
     *       "next": null
     *     },
     *     "meta": {
     *       "current_page": 1,
     *       "from": 1,
     *       "last_page": 1,
     *       "path": "http://localhost/api/v1/portal/employee/my-warning-letters",
     *       "per_page": 15,
     *       "to": 1,
     *       "total": 1
     *     }
     *   }
     * }
     */
    public function index(MyWarningLetterIndexRequest $request): JsonResponse
    {
        $employeeId = Auth::user()?->user_employee?->employee_id;

        if (!$employeeId) {
            return $this->errorResponse('Employee record not found for this user.', 404);
        }

        $warningLetters = $this->service->getEmployeeWarningLetters(
            $employeeId,
            $request->validated(),
            $request->input('per_page', 15)
        );

        return $this->successResponse(
            WarningLetterResource::collection($warningLetters)->response()->getData(true),
            'Warning letters retrieved successfully'
        );
    }

    /**
     * Get employee warning letter.
     * 
     * Get detailed information about a specific warning letter of the authenticated employee.
     * 
     * @response {
     *   "status": "Success",
     *   "message": "Warning letter details retrieved",
     *   "data": {
     *     "id": 1,
     *     "document_no": "SP-001",
     *     "name": "Surat Peringatan 1",
     *     "employee_id": 1,
     *     "employee": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "nik": "NIK-001"
     *     },
     *     "warning_letter_type_id": 1,
     *     "warning_letter_type": {
     *       "id": 1,
     *       "name": "SP 1 (First Warning)"
     *     },
     *     "warning_at": "2026-05-19",
     *     "start_date": "2026-05-19",
     *     "end_date": "2026-11-19",
     *     "note": "Frequent tardiness",
     *     "attachment": null,
     *     "attachment_url": null,
     *     "status": "Approved",
     *     "confirmed_at": "2026-05-19 08:00:00",
     *     "settled_at": null,
     *     "created_at": "2026-05-19 08:00:00",
     *     "updated_at": "2026-05-19 08:00:00"
     *   }
     * }
     */
    public function show(int $id): JsonResponse
    {
        $employeeId = Auth::user()?->user_employee?->employee_id;

        if (!$employeeId) {
            return $this->errorResponse('Employee record not found for this user.', 404);
        }

        $warningLetter = $this->service->getEmployeeWarningLetterDetail($id, $employeeId);

        if (!$warningLetter) {
            return $this->errorResponse('Warning letter not found or access denied.', 404);
        }

        return $this->successResponse(
            new WarningLetterResource($warningLetter),
            'Warning letter details retrieved'
        );
    }
}
