<?php

namespace App\Modules\System\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\System\Models\PassportClient;
use App\Modules\System\Models\PassportRole;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group System Configuration
 *
 * Manage Global Passport roles that are assigned to all new employees.
 */
class GlobalPassportRoleController extends Controller
{
    use ApiResponses;

    /**
     * Get global passport roles.
     */
    public function index(): JsonResponse
    {
        $globalRoles = PassportRole::with('client')->where('is_global', true)->get();
        return $this->successResponse($globalRoles, 'Global roles retrieved');
    }

    /**
     * Set global passport roles.
     * 
     * @bodyParam roles array required Array of role objects fetched from Passport.
     * @bodyParam roles[].id integer required Passport role ID. Example: 1
     * @bodyParam roles[].client_id integer required Passport client ID. Example: 2
     * @bodyParam roles[].name string required Role name. Example: Employee
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'roles' => 'array',
            'roles.*.id' => 'required|integer',
            'roles.*.client_id' => 'required|integer',
            'roles.*.name' => 'required|string',
        ]);

        $incomingRoleIds = collect($request->input('roles', []))->pluck('id')->toArray();

        // 1. Remove is_global from any roles that are not in the incoming payload
        PassportRole::whereNotIn('passport_role_id', $incomingRoleIds)->update(['is_global' => false]);

        $updatedRoles = [];

        // 2. Upsert the incoming roles and set them to is_global = true
        foreach ($request->input('roles', []) as $roleData) {
            // Ensure client exists
            PassportClient::updateOrCreate(
                ['passport_client_id' => $roleData['client_id']],
                ['name' => $roleData['client_name'] ?? 'Client ' . $roleData['client_id']]
            );

            // Ensure role exists and is global
            $role = PassportRole::updateOrCreate(
                ['passport_role_id' => $roleData['id']],
                [
                    'passport_client_id' => $roleData['client_id'],
                    'name' => $roleData['name'],
                    'is_global' => true,
                ]
            );

            $updatedRoles[] = $role;
        }

        return $this->successResponse($updatedRoles, 'Global passport roles updated successfully');
    }
}
