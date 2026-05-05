<?php

use App\Modules\ApprovalWorkflow\Models\ApprovalRequest;
use App\Modules\ApprovalWorkflow\Models\ApprovalRequestStep;
use App\Modules\Employee\Models\Employee;
use Illuminate\Support\Facades\DB;

$requestId = 7886;
$employeeId = 111;

$employee = Employee::find($employeeId);
if (!$employee) {
    echo "Employee not found\n";
    exit;
}

$workPositionId = $employee->work_position_id ?? null;
$groupIds = DB::table('approval_group_employees')
    ->where('employee_id', $employeeId)
    ->pluck('approval_group_id')
    ->toArray();

echo "Employee: {$employee->full_name} (ID: {$employeeId})\n";
echo "Work Position ID: " . ($workPositionId ?? 'null') . "\n";
echo "Group IDs: " . implode(', ', $groupIds) . "\n";

$steps = ApprovalRequestStep::where('approval_request_id', $requestId)->get();
echo "\nSteps for Request {$requestId}:\n";
foreach ($steps as $step) {
    echo "- Step ID: {$step->id}, Sequence: {$step->sequence}, Type: {$step->approver_type}, Approver ID: {$step->approver_id}, Status: {$step->status}\n";
}

$query = ApprovalRequestStep::where('approval_request_id', $requestId)
    ->where(function ($query) use ($employeeId, $groupIds, $workPositionId) {
        $query->where(function ($q) use ($employeeId) {
            $q->whereIn('approver_type', ['user', 'employee', 'supervisor', 'dept_head'])
              ->where('approver_id', $employeeId);
        })
        ->orWhere(function ($q) use ($groupIds) {
            $q->where('approver_type', 'group')
              ->whereIn('approver_id', $groupIds);
        })
        ->when($workPositionId, function ($q) use ($workPositionId) {
            $q->orWhere(function ($inner) use ($workPositionId) {
                $inner->where('approver_type', 'work_position')
                      ->where('approver_id', $workPositionId);
            });
        });
    });

echo "\nSQL Query: " . $query->toSql() . "\n";
echo "Bindings: " . implode(', ', $query->getBindings()) . "\n";

$result = $query->get();
echo "\nMatching Steps: " . $result->count() . "\n";
foreach ($result as $step) {
    echo "- Match: Step ID: {$step->id}\n";
}
