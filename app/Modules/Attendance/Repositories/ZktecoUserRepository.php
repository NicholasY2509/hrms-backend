<?php

namespace App\Modules\Attendance\Repositories;

use App\Modules\Attendance\Models\ZktecoUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ZktecoUserRepository
{
    /**
     * Upsert ZKTeco machine users.
     *
     * @param array $data
     * @return int
     */
    public function upsert(array $data): int
    {
        if (empty($data)) {
            return 0;
        }

        // We use upsert for performance. 
        // It assumes (uid, zkteco_machine_id) is unique.
        return ZktecoUser::upsert(
            $data,
            ['uid', 'zkteco_machine_id'],
            ['name', 'updated_at']
        );
    }

    /**
     * Delete users for a specific machine that are not in the provided UIDs.
     *
     * @param int $machineId
     * @param array $uids
     * @return int
     */
    public function deleteMissing(int $machineId, array $uids): int
    {
        return ZktecoUser::where('zkteco_machine_id', $machineId)
            ->whereNotIn('uid', $uids)
            ->delete();
    }
}
