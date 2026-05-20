<?php

namespace App\Modules\System\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SystemController extends Controller
{
    /**
     * Test connectivity to the Passport Auth server.
     */
    public function testPassport(Request $request)
    {
        $url = rtrim(config('services.auth_server.url'), '/') . '/api/v1/user/profile';
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status' => 'Error',
                'message' => 'No bearer token provided in request for testing.',
                'target_url' => $url
            ], 400);
        }

        try {
            $startTime = microtime(true);
            $response = Http::withToken($token)
                ->acceptJson()
                ->get($url);
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            return response()->json([
                'status' => 'Success',
                'connectivity' => [
                    'url' => $url,
                    'http_status' => $response->status(),
                    'duration_ms' => $duration,
                    'successful' => $response->successful(),
                ],
                'response_body' => $response->json(),
                'raw_body' => $response->successful() ? 'OMITTED' : $response->body(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Error',
                'message' => 'Connection failed: ' . $e->getMessage(),
                'url' => $url,
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Get system-wide mobile app configuration including force-update requirements.
     */
    public function appConfig(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'min_android_version' => config('app.min_android_version', '1.0.0'),
                'latest_android_version' => config('app.latest_android_version', '1.0.0'),
                'force_update' => (bool) config('app.force_update', false),
                'play_store_url' => config('app.play_store_url', 'https://play.google.com/store/apps/details?id=com.deltamas.hrms_flutter'),
            ]
        ]);
    }
}
