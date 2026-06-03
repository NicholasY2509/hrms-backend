<?php

namespace App\Modules\Organization\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\Organization\Models\WorkPosition;
use App\Modules\System\Models\PassportClient;
use App\Modules\System\Models\PassportRole;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Organization Configuration
 *
 * Manage Passport roles mapping for Work Positions.
 */
class WorkPositionPassportRoleController extends Controller
{
    use ApiResponses;

    /**
     * Get passport roles for a work position.
     */
    public function index($id): JsonResponse
    {
        $workPosition = WorkPosition::with(['passportRoles.client'])->findOrFail($id);
        
        return $this->successResponse($workPosition->passportRoles, 'Roles retrieved');
    }

    /**
     * Sync passport roles for a work position.
     * 
     * @bodyParam roles array required Array of role objects fetched from Passport.
     * @bodyParam roles[].id integer required Passport role ID. Example: 1
     * @bodyParam roles[].client_id integer required Passport client ID. Example: 2
     * @bodyParam roles[].name string required Role name. Example: Manager
     */
    public function store(Request $request, $id): JsonResponse
    {
        $request->validate([
            'roles' => 'array',
            'roles.*.id' => 'required|integer',
            'roles.*.client_id' => 'required|integer',
            'roles.*.name' => 'required|string',
        ]);

        $workPosition = WorkPosition::findOrFail($id);
        $roleIds = [];

        foreach ($request->input('roles', []) as $roleData) {
            // Ensure client exists in local DB
            PassportClient::updateOrCreate(
                ['passport_client_id' => $roleData['client_id']],
                ['name' => $roleData['client_name'] ?? 'Client ' . $roleData['client_id']]
            );

            // Ensure role exists in local DB
            $role = PassportRole::updateOrCreate(
                ['passport_role_id' => $roleData['id']],
                [
                    'passport_client_id' => $roleData['client_id'],
                    'name' => $roleData['name'],
                ]
            );

            $roleIds[] = $role->id;
        }

        $workPosition->passportRoles()->sync($roleIds);

        return $this->successResponse(
            $workPosition->passportRoles()->get(), 
            'Passport roles mapped to work position successfully'
        );
    }
}
