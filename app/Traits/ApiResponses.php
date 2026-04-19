<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponses
{
    /**
     * Send a success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function successResponse($data, string $message = 'Success', int $code = 200): JsonResponse
    {
        $response = [
            'status' => 'Success',
            'message' => $message,
        ];

        if (is_array($data) && isset($data['data']) && (isset($data['links']) || isset($data['meta']))) {
            $response['data'] = $data['data'];
            if (isset($data['links'])) $response['links'] = $data['links'];
            if (isset($data['meta'])) $response['meta'] = $data['meta'];
        } else {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Send an error response.
     *
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function errorResponse(string $message, int $code = 400): JsonResponse
    {
        return response()->json([
            'status' => 'Error',
            'message' => $message,
            'data' => null
        ], $code);
    }
}
