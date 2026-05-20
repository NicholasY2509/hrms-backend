<?php

namespace App\Modules\Employee\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeVehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vehicle_name' => $this->vehicle_name,
            'vehicle_year' => $this->vehicle_year,
            'plate_number' => $this->plate_number,
            'vehicle_owner' => $this->vehicle_owner,
        ];
    }
}
