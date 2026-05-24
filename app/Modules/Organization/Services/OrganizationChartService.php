<?php

namespace App\Modules\Organization\Services;

use App\Modules\Organization\Models\PositionHierarchyMatrix;
use App\Modules\Organization\Models\WorkPosition;
use Illuminate\Support\Collection;

class OrganizationChartService
{
    /**
     * Retrieve the position hierarchy matrix for the organization chart.
     * 
     * @return array
     */
    public function getChartData(): array
    {
        // Fetch all matrices with their relationships
        $matrices = PositionHierarchyMatrix::with([
            'department', 
            'workPosition', 
            'supervisorWorkPosition',
            'workLocation'
        ])->get();

        // Also fetch all positions so we can build nodes even for those without a supervisor defined
        // or those who are only supervisors
        $allPositions = WorkPosition::all()->keyBy('id');

        $nodes = [];
        $edges = [];

        // Build edges based on the matrix and collect referenced positions
        $referencedPositionIds = [];

        foreach ($matrices as $matrix) {
            $childId = $matrix->work_position_id;
            $parentId = $matrix->supervisor_work_position_id;
            
            $referencedPositionIds[$childId] = true;
            if ($parentId) {
                $referencedPositionIds[$parentId] = true;

                $edgeId = "edge_{$parentId}_{$childId}";
                $edges[] = [
                    'id' => $edgeId,
                    'source' => (string) $parentId,
                    'target' => (string) $childId,
                    'department_id' => $matrix->department_id,
                    'department_name' => $matrix->department?->name,
                ];
            }
        }

        // Build nodes for all referenced positions
        foreach ($referencedPositionIds as $positionId => $val) {
            if (isset($allPositions[$positionId])) {
                $position = $allPositions[$positionId];
                $nodes[] = [
                    'id' => (string) $position->id,
                    'type' => 'positionNode',
                    'data' => [
                        'label' => $position->name,
                        'alias' => $position->alias,
                    ]
                ];
            }
        }

        return [
            'nodes' => array_values($nodes),
            'edges' => $edges,
        ];
    }
}
