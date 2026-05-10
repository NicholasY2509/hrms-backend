<?php

namespace App\Modules\System\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // If the resource is passed as an array (from service), return as is
        if (is_array($this->resource)) {
            return $this->resource;
        }

        return parent::toArray($request);
    }
}
