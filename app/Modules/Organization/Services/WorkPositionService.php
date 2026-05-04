<?php

namespace App\Modules\Organization\Services;

use App\Modules\Organization\Models\WorkPosition;
use App\Modules\Organization\Models\WorkPositionCriteria;
use Illuminate\Support\Facades\DB;

class WorkPositionService
{
    public function createWorkPosition(array $data): WorkPosition
    {
        return DB::transaction(function () use ($data) {
            $workPosition = WorkPosition::create($data);

            if (isset($data['criteria'])) {
                foreach ($data['criteria'] as $item) {
                    $workPosition->criteria()->create([
                        'name' => $item['name']
                    ]);
                }
            }

            return $workPosition;
        });
    }

    public function updateWorkPosition(WorkPosition $workPosition, array $data): WorkPosition
    {
        return DB::transaction(function () use ($workPosition, $data) {
            $workPosition->update($data);

            if (isset($data['criteria'])) {
                $workPosition->criteria()->delete();
                foreach ($data['criteria'] as $item) {
                    $workPosition->criteria()->create([
                        'name' => $item['name']
                    ]);
                }
            }

            return $workPosition;
        });
    }

    public function deleteWorkPosition(WorkPosition $workPosition): bool
    {
        return DB::transaction(function () use ($workPosition) {
            $workPosition->criteria()->delete();
            return $workPosition->delete();
        });
    }
}
