<?php

namespace App\Modules\Attendance\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Models\AttendanceSetting;
use App\Modules\Attendance\Requests\UpdateAttendanceSettingsRequest;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * @group Attendance Configuration
 *
 * APIs for managing system-wide attendance settings
 */
class AttendanceSettingController extends Controller
{
    use ApiResponses;

    /**
     * Get Attendance Settings
     *
     * Retrieves all global settings related to the attendance module.
     */
    public function index()
    {
        $settings = AttendanceSetting::where('group', 'attendance')->get();

        return $this->successResponse(
            $settings,
            'Attendance settings retrieved successfully.'
        );
    }

    /**
     * Update Attendance Settings
     *
     * Bulk updates the attendance settings based on the provided key-value pairs.
     */
    public function update(UpdateAttendanceSettingsRequest $request)
    {
        try {
            DB::beginTransaction();

            $updatedSettings = [];
            foreach ($request->validated('settings') as $key => $value) {
                $setting = AttendanceSetting::where('key', $key)->first();
                if ($setting) {
                    $setting->value = $value;
                    $setting->save();
                    $updatedSettings[] = $setting;
                }
            }

            DB::commit();

            return $this->successResponse(
                $updatedSettings,
                'Attendance settings updated successfully.'
            );
        } catch (Throwable $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
