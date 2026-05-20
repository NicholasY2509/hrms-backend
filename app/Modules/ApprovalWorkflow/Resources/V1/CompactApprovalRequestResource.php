<?php

namespace App\Modules\ApprovalWorkflow\Resources\V1;

use App\Modules\Overtime\Models\Overtime;
use App\Modules\UnpaidLeave\Models\UnpaidLeave;
use App\Modules\Career\Models\Career;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompactApprovalRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $approvable = $this->approvable;
        $employee = $approvable?->employee;

        return [
            'id' => $this->id,
            'reference_number' => $this->reference_number,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'approvable_type' => $this->approvable_type,
            'category' => $this->getCategory(),
            'requester' => $employee ? [
                'id' => $employee->id,
                'name' => $employee->full_name,
                'avatar' => $employee->profile_url,
                'department' => $employee->department?->name,
                'position' => $employee->position?->name,
            ] : null,
            'summary' => $this->getSummary(),
        ];
    }

    protected function getCategory(): ?string
    {
        if (!$this->relationLoaded('approvable')) {
            return null;
        }

        $model = $this->approvable;

        if (!$model) {
            return null;
        }

        if ($model instanceof Overtime) {
            return 'Overtime';
        }

        if ($model instanceof UnpaidLeave) {
            return $model->unpaid_leave_type->name ?? 'Unpaid Leave';
        }

        if ($model instanceof Career) {
            return 'Career Transition';
        }

        // Fallback for other types
        return preg_replace('/(?<!^)[A-Z]/', ' $0', class_basename($this->approvable_type));
    }

    protected function getSummary(): ?string
    {
        $model = $this->approvable;
        if (!$model) return null;

        if ($model instanceof Overtime) {
            $date = $this->formatDate($model->date, 'd M Y');
            $startTime = $model->start_time;
            $finishTime = $model->finish_time;
            return "{$startTime} - {$finishTime} ({$model->total_time}) di tanggal {$date}";
        }

        if ($model instanceof UnpaidLeave) {
            $days = $model->total_days ?? 0;
            $start = $this->formatDate($model->start_date, 'd M');
            $end = $this->formatDate($model->end_date, 'd M');
            return "{$days} hari ({$start} - {$end})";
        }

        if ($model instanceof Career) {
            return "Transisi untuk " . ($model->employee?->full_name ?? 'Employee');
        }

        return $this->reference_number;
    }

    protected function formatDate($date, string $format): string
    {
        if (!$date) return '-';
        
        try {
            return \Illuminate\Support\Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return is_string($date) ? $date : '-';
        }
    }
}
