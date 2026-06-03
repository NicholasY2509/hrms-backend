<?php

namespace App\Modules\System\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Services\PassportApiService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group System Configuration
 *
 * Proxy endpoints to fetch data directly from Passport system.
 */
class PassportDataController extends Controller
{
    use ApiResponses;

    protected PassportApiService $passportApiService;

    public function __construct(PassportApiService $passportApiService)
    {
        $this->passportApiService = $passportApiService;
    }

    /**
     * Get list of Passport clients.
     */
    public function clients(): JsonResponse
    {
        $response = $this->passportApiService->getClients();
        return response()->json($response, isset($response['status']) && $response['status'] === false ? 500 : 200);
    }

    /**
     * Get list of Passport roles.
     * 
     * @queryParam client_id integer Filter roles by client ID. Example: 1
     */
    public function roles(Request $request): JsonResponse
    {
        $response = $this->passportApiService->getRoles($request->query('client_id'));
        return response()->json($response, isset($response['status']) && $response['status'] === false ? 500 : 200);
    }
}
