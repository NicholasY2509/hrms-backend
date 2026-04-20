<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ApplicationException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     */
    public function render($request): JsonResponse
    {
        return response()->json([
            'status' => 'Error',
            'message' => $this->getMessage(),
            'data' => null
        ], $this->getCode() ?: 400);
    }
}
