<?php

namespace App\Modules\Organization\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\Organization\Models\WorkPosition;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Organization Configuration
 *
 * Manage roles mapping for Work Positions.
 */
class WorkPositionRoleController extends Controller
{
    use ApiResponses;

    /**
     * Get roles for a work position.
     */
    public function index($id): JsonResponse
    {
        $workPosition = WorkPosition::with('roles.permissions')->findOrFail($id);
        
        return $this->successResponse($workPosition->roles, 'Roles retrieved');
    }

    /**
     * Sync roles for a work position.
     * 
     * @bodyParam roles array required Array of role names.
     * @bodyParam roles[] string required Role name. Example: Manager
     */
    public function store(Request $request, $id): JsonResponse
    {
        $request->validate([
            'roles' => 'array',
            'roles.*' => 'required|string|exists:roles,name',
        ]);

        $workPosition = WorkPosition::findOrFail($id);
        
        $workPosition->syncRoles($request->input('roles', []));

        return $this->successResponse(
            $workPosition->roles()->get(), 
            'Roles mapped to work position successfully'
        );
    }
}
