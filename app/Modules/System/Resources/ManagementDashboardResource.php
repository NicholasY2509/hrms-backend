<?php

namespace App\Modules\System\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ManagementDashboardResource extends JsonResource
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
            'workforce_overview' => $this->resource['workforce_overview'],
            'attendance_productivity' => $this->resource['attendance_productivity'],
            'attrition_retention' => $this->resource['attrition_retention'],
            'payroll_insights' => $this->resource['payroll_insights'],
            'pending_requests_count' => $this->resource['pending_requests_count'],
        ];
    }
}
