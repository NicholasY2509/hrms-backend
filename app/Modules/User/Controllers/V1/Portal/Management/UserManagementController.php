<?php

namespace App\Modules\User\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\User\Models\User;
use App\Modules\User\Requests\ListUserRequest;
use App\Modules\User\Requests\StoreUserRequest;
use App\Modules\User\Requests\UpdateUserRequest;
use App\Modules\User\Resources\UserResource;
use App\Modules\User\Services\UserService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group User
 * @subgroup Management Portal
 */
class UserManagementController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected UserService $service
    ) {}

    /**
     * List users.
     * 
     * Get a paginated list of users with optional search.
     */
    public function index(ListUserRequest $request): JsonResponse
    {
        $users = $this->service->listUsers(
            $request->input('per_page', 15),
            $request->validated()
        );

        return $this->successResponse(
            UserResource::collection($users)->response()->getData(true),
            'Users retrieved successfully'
        );
    }

    /**
     * Create user.
     * 
     * Store a new user in the system.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->service->createUser($request->validated());

        return $this->successResponse(
            new UserResource($user),
            'User created successfully',
            201
        );
    }

    /**
     * Get user.
     * 
     * Get detailed information about a specific user.
     */
    public function show(User $user): JsonResponse
    {
        return $this->successResponse(
            new UserResource($user->load(['employee'])),
            'User details retrieved'
        );
    }

    /**
     * Update user.
     * 
     * Update the details of an existing user.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $updatedUser = $this->service->updateUser($user, $request->validated());

        return $this->successResponse(
            new UserResource($updatedUser),
            'User updated successfully'
        );
    }

    /**
     * Delete user.
     * 
     * Remove a user from the system.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->service->deleteUser($user);

        return $this->successResponse(null, 'User deleted successfully');
    }
}
