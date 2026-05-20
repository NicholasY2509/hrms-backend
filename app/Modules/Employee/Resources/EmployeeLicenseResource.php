<?php

namespace App\Modules\Employee\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeLicenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'license_number' => $this->license_number,
            'driver_license_type' => [
                'id' => $this->driver_license_type->id,
                'name' => $this->driver_license_type->name
            ]
        ];
    }
}
