<?php

namespace App\Modules\ApprovalWorkflow\Repositories;

use App\Modules\ApprovalWorkflow\Models\ApprovalScheme;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApprovalSchemeRepository
{
    /**
     * Get paginated schemes with a summary of their rules.
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ApprovalScheme::query()->withCount(['rules', 'positionRules', 'departmentRules']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('model_class', 'like', "%{$search}%");
        }

        return $query->paginate($perPage);
    }

    /**
     * Find a scheme by ID with all its rules and steps.
     */
    public function findWithDetails(int $id): ?ApprovalScheme
    {
        return ApprovalScheme::with([
            'rules.steps.group', 
            'rules.steps.employee',
            'rules.steps.workPosition',
            'rules.workPosition',
            'rules.workLocation',
            'rules.department',
        ])->find($id);
    }

    public function create(array $data): ApprovalScheme
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $scheme = ApprovalScheme::create($data);
            
            // Automatically create a default rule for this scheme
            $scheme->rules()->create([
                'is_default' => true,
                'is_active' => true,
            ]);

            return $scheme;
        });
    }

    public function find(int $id): ?ApprovalScheme
    {
        return ApprovalScheme::find($id);
    }

    public function update(ApprovalScheme $scheme, array $data): bool
    {
        return $scheme->update($data);
    }

    public function delete(int $id): bool
    {
        return ApprovalScheme::destroy($id) > 0;
    }
}
