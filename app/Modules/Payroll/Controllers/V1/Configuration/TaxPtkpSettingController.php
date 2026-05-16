<?php

namespace App\Modules\Payroll\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\Payroll\Models\TaxPtkpSetting;
use App\Modules\Payroll\Resources\V1\TaxPtkpSettingResource;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Payroll Configuration
 */
class TaxPtkpSettingController extends Controller
{
    use ApiResponses;

    /**
     * Get list of all PTKP settings.
     */
    public function index(): JsonResponse
    {
        $settings = TaxPtkpSetting::with('ter_category')->get();

        return $this->successResponse(
            TaxPtkpSettingResource::collection($settings),
            'Tax PTKP settings retrieved'
        );
    }
}
