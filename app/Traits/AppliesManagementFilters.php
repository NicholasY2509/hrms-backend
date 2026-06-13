<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait AppliesManagementFilters
{
    /**
     * Apply department and team filters based on the user's remote roles.
     *
     * @param array $filters The validated request filters
     * @param Request $request The incoming HTTP request
     * @return array
     */
    protected function applyManagementFilters(array $filters, Request $request): array
    {
        $user = $request->user();
        if (!$user) {
            return $filters;
        }

        $userRoles = (array) ($user->remote_roles ?? []);

        if (in_array('Department Head', $userRoles) && $user->employee) {
            $filters['department_id'] = $user->employee->department_id;
        }

        if (in_array('Supervisor', $userRoles) && $user->employee) {
            $filters['team_id'] = $user->employee->team_id;
        }

        return $filters;
    }
}
