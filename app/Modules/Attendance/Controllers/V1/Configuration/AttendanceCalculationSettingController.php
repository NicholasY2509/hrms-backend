<?php

namespace App\Modules\Attendance\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Models\AttendanceSetting;
use App\Modules\Attendance\Requests\UpdateAttendanceSettingsRequest;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
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
        $settings = AttendanceSetting::where('group', 'calculation')->get();

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
            foreach ($request->validated('settings') as $key => $value) {
                $setting = AttendanceSetting::where('key', $key)
                    ->where('group', 'calculation')
                    ->first();
                
                if ($setting) {
                    $setting->value = $value;
                    $setting->save();
                    $updatedSettings[] = $setting;
                }
            }

            DB::commit();

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
