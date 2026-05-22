<?php

namespace App\Modules\Attendance\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\StorageService;

class MobileScanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'type' => $this['type'] ?? null,
            'time' => $this['time'] ?? null,
            'latitude' => $this['latitude'] ?? null,
            'longitude' => $this['longitude'] ?? null,
            'photo' => isset($this['photo']) ? StorageService::url($this['photo']) : null,
        ];
    }
}
