<?php

namespace App\Modules\System\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * @group System Configuration
 *
 * Manage local roles and their associated permissions.
 */
class RoleController extends Controller
{
    use ApiResponses;

    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->get();
        return $this->successResponse($roles, 'Roles retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'api'
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return $this->successResponse($role->load('permissions'), 'Role created successfully', 201);
    }

    public function show($id): JsonResponse
    {
        $role = Role::with('permissions')->findOrFail($id);
        return $this->successResponse($role, 'Role details retrieved');
    }

    public function update(Request $request, $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->update([
            'name' => $request->name,
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return $this->successResponse($role->load('permissions'), 'Role updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $role = Role::findOrFail($id);
        
        // Don't allow deleting the IT role to prevent locking out super admins
        if ($role->name === 'IT') {
            return $this->errorResponse('Cannot delete the IT role', 403);
        }

        $role->delete();
        return $this->successResponse(null, 'Role deleted successfully');
    }

    public function getPermissions(): JsonResponse
    {
        $permissions = Permission::all();
        return $this->successResponse($permissions, 'Permissions retrieved');
    }
}
