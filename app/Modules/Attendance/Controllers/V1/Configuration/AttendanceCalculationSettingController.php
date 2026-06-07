<?php

namespace App\Modules\Attendance\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Models\AttendanceSetting;
use App\Modules\Attendance\Requests\UpdateAttendanceSettingsRequest;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * @group Attendance Configuration
 * @subgroup Calculation Settings
 */
class AttendanceCalculationSettingController extends Controller
{
    use ApiResponses;

    /**
     * Get Calculation Settings
     * 
     * Retrieves settings specific to the attendance calculation logic (e.g. status IDs, shift windows).
     */
    public function index(): JsonResponse
    {
        $settings = Cache::rememberForever('attendance_settings_calculation', function () {
            return AttendanceSetting::where('group', 'calculation')->get();
        });

        return $this->successResponse(
            $settings,
            'Attendance calculation settings retrieved successfully.'
        );
    }

    /**
     * Update Calculation Settings
     * 
     * Bulk updates the calculation-specific settings.
     */
    public function update(UpdateAttendanceSettingsRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $updatedSettings = [];
            $settingsData = $request->validated('settings');
            
            $settings = AttendanceSetting::whereIn('key', array_keys($settingsData))
                ->where('group', 'calculation')
                ->get()
                ->keyBy('key');
                
            foreach ($settingsData as $key => $value) {
                if ($setting = $settings->get($key)) {
                    $setting->value = $value;
                    $setting->save();
                    $updatedSettings[] = $setting;
                }
            }

            DB::commit();
            
            Cache::forget('attendance_settings_calculation');

            return $this->successResponse(
                $updatedSettings,
                'Attendance calculation settings updated successfully.'
            );
        } catch (Throwable $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
