<?php

namespace App\Modules\Organization\Services;

use App\Modules\Organization\Models\Team;
use Illuminate\Support\Facades\DB;

class TeamService
{
    public function createTeam(array $data): Team
    {
        return DB::transaction(function () use ($data) {
            return Team::create($data);
        });
    }

    public function updateTeam(Team $team, array $data): Team
    {
        return DB::transaction(function () use ($team, $data) {
            $team->update($data);
            return $team;
        });
    }

    public function deleteTeam(Team $team): bool
    {
        return DB::transaction(function () use ($team) {
            return $team->delete();
        });
    }
}
