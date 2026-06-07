<?php

namespace App\Modules\System\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\System\Models\SystemSetting;
use App\Modules\System\Resources\SystemSettingResource;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

/**
 * @group Configuration
 * 
 * Manage global system settings and business rules.
 */
class SystemSettingController extends Controller
{
    use ApiResponses;

    /**
     * List Settings
     * 
     * Get a list of system settings, optionally filtered by group.
     * 
     * @queryParam group string Filter by setting group (e.g., approval, system).
     */
    public function index(Request $request)
    {
        $group = $request->query('group');
        
        $settings = SystemSetting::query()
            ->where('is_public', true)
            ->when($group, function ($query, $group) {
                $query->where('group', $group);
            })
            ->get();

        return $this->successResponse(
            SystemSettingResource::collection($settings),
            'Settings retrieved successfully'
        );
    }

    /**
     * Bulk Update Settings
     * 
     * Update multiple setting values at once.
     * 
     * @bodyParam settings object required Key-value pairs of settings to update.
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        $settingsData = $request->settings;
        $settings = SystemSetting::whereIn('key', array_keys($settingsData))->get()->keyBy('key');

        \Illuminate\Support\Facades\DB::transaction(function () use ($settingsData, $settings) {
            foreach ($settingsData as $key => $value) {
                if ($setting = $settings->get($key)) {
                    $setting->update([
                        'value' => is_array($value) ? json_encode($value) : (string) $value,
                    ]);
                }
            }
        });

        return $this->successResponse(
            null,
            'Settings updated successfully'
        );
    }
}
