<?php

namespace App\Modules\System\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SystemController extends Controller
{
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
