<?php

namespace App\Modules\Overtime\Services;

use App\Modules\Overtime\Models\Overtime;
use App\Modules\Overtime\Models\OvertimeApproval;
use App\Modules\Overtime\Models\OvertimeAttachment;
use App\Modules\Overtime\Models\OvertimeType;
use App\Modules\Overtime\Repositories\OvertimeRepository;
use App\Modules\Employee\Models\Employee;
use App\Services\StorageService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OvertimeService
{
    protected OvertimeRepository $repository;

    public function __construct(OvertimeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Store a new overtime request.
     *
     * @param array $data
     * @return Overtime
     */
    public function storeOvertime(array $data)
    {
        return DB::transaction(function () use ($data) {
            $employee = Employee::findOrFail($data['employee_id']);
            
            $startDateTime = str_contains($data['start_time'], '-') 
                ? Carbon::parse($data['start_time']) 
                : Carbon::parse($data['date'] . ' ' . $data['start_time']);
                
            $finishDateTime = str_contains($data['finish_time'], '-') 
                ? Carbon::parse($data['finish_time']) 
                : Carbon::parse($data['date'] . ' ' . $data['finish_time']);
            
            if ($finishDateTime->lessThan($startDateTime)) {
                $finishDateTime->addDay();
            }

            $totalMinutes = $startDateTime->diffInMinutes($finishDateTime);
            
            $type = $data['type'] ?? Overtime::TYPE_GENERAL;

            if ($type === Overtime::TYPE_HOLIDAY) {
                $breakStart1 = Carbon::parse($startDateTime->format('Y-m-d') . ' 12:00:00');
                $breakEnd1 = Carbon::parse($startDateTime->format('Y-m-d') . ' 13:00:00');
                
                $overlap1 = max(0, min($finishDateTime->timestamp, $breakEnd1->timestamp) - max($startDateTime->timestamp, $breakStart1->timestamp));
                
                $overlap2 = 0;
                if ($finishDateTime->timestamp > $startDateTime->copy()->addDay()->startOfDay()->timestamp) {
                    $breakStart2 = $breakStart1->copy()->addDay();
                    $breakEnd2 = $breakEnd1->copy()->addDay();
                    $overlap2 = max(0, min($finishDateTime->timestamp, $breakEnd2->timestamp) - max($startDateTime->timestamp, $breakStart2->timestamp));
                }
                
                $totalMinutes -= (($overlap1 + $overlap2) / 60);
            }

            $totalMinutesRounded = floor($totalMinutes / 60) * 60;
            $hours = floor($totalMinutesRounded / 60);
            $minutes = 0; 
            $totalTimeStr = sprintf('%02d:%02d', $hours, $minutes);

            $codeType = $type === Overtime::TYPE_HOLIDAY ? 'NTN' : ($type === Overtime::TYPE_DAC ? 'DAC' : 'UMUM');

            $overtime = Overtime::create([
                'date' => $data['date'],
                'document_no' => $this->generateDocumentNo($codeType, $data['date']),
                'type' => $type,
                'employee_id' => $employee->id,
                'department_id' => $employee->department_id,
                'work_position_id' => $employee->work_position_id,
                'overtime_type_id' => $data['overtime_type_id'] ?? null,
                'start_time' => $startDateTime,
                'finish_time' => $finishDateTime,
                'total_time' => $totalTimeStr,
                'note' => $data['note'] ?? null,
                'estimated_overtime_price' => $data['estimated_cost'] ?? 0,
                // 'real_overtime_price' => $data['estimated_cost'] ?? 0,
            ]);

            if (!empty($data['attachments'])) {
                foreach ($data['attachments'] as $file) {
                    $path = $file instanceof UploadedFile 
                        ? StorageService::store($file, 'overtimes')
                        : $file;

                    OvertimeAttachment::create([
                        'overtime_id' => $overtime->id,
                        'path' => $path,
                    ]);
                }
            }

            return $overtime->load(['employee', 'overtime_type', 'approvalRequest.steps']);
        });
    }

    /**
     * Generate document number.
     *
     * @param string $type
     * @param string $date
     * @return string
     */
    public function generateDocumentNo(string $type, string $date)
    {
        $carbonDate = Carbon::parse($date);
        $year = $carbonDate->format('Y');
        $month = $carbonDate->format('m');
        $prefix = "L/{$year}/{$month}/" . strtoupper($type) . "/";

        $lastRecord = Overtime::where('document_no', 'like', $prefix . '%')
            ->orderByDesc('document_no')
            ->first();

        $nextNumber = 1;
        if ($lastRecord) {
            $parts = explode('/', $lastRecord->document_no);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Settle (Close) an overtime request.
     *
     * @param Overtime $overtime
     * @param float $realizationCost
     * @return Overtime
     */
    public function settleOvertime(Overtime $overtime, $realizationCost)
    {
        $overtime->update([
            'real_overtime_price' => $realizationCost,
            'settled_at' => Carbon::now(),
        ]);

        return $overtime;
    }

    /**
     * Update an overtime request (Management Portal).
     *
     * @param Overtime $overtime
     * @param array $data
     * @return Overtime
     */
    public function updateOvertime(Overtime $overtime, array $data)
    {
        // Automatically resolve estimated price if type is changed but price is not
        if (isset($data['overtime_type_id']) && !isset($data['estimated_overtime_price'])) {
            $overtimeType = OvertimeType::find($data['overtime_type_id']);
            if ($overtimeType) {
                $data['estimated_overtime_price'] = $overtimeType->price;
            }
        }

        $overtime->update($data);

        return $overtime->fresh(['employee', 'overtime_type', 'approvalRequest.steps']);
    }

    /**
     * Get paginated overtime requests for a specific user.
     *
     * @param int $employeeId
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserOvertimes(int $employeeId, int $perPage = 10)
    {
        return $this->repository->paginate(['employee_id' => $employeeId], $perPage);
    }

    /**
     * Get pending overtime requests for dashboard.
     *
     * @param int $employeeId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingRequests(int $employeeId, int $limit = 5)
    {
        return $this->repository->getPendingByEmployee($employeeId, $limit);
    }
}
