<?php

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\Models\ZktecoMachine;
use App\Modules\Attendance\Repositories\ZktecoUserRepository;
use App\Modules\Attendance\Jobs\SyncZktecoUsersJob;
use App\Modules\System\Services\TaskService;
use App\Modules\System\Traits\HasTaskProgress;
use TADPHP\TADFactory;
use Throwable;
use Illuminate\Support\Facades\DB;

class ZktecoUserService
{
    use HasTaskProgress;

    protected $repository;
    protected $taskService;

    public function __construct(ZktecoUserRepository $repository, TaskService $taskService)
    {
        $this->repository = $repository;
        $this->taskService = $taskService;
    }

    /**
     * Initiate a background synchronization.
     *
     * @param ZktecoMachine $machine
     * @return \App\Modules\System\Models\Task
     */
    public function initiateSync(ZktecoMachine $machine): \App\Modules\System\Models\Task
    {
        $task = $this->taskService->createTask(
            'zkteco_user_sync',
            "Menunggu antrian untuk sinkronisasi user dari {$machine->name}...",
            [
                'zkteco_machine_id' => $machine->id,
                'machine_name' => $machine->name,
            ]
        );

        SyncZktecoUsersJob::dispatch($machine, $task);

        return $task;
    }

    /**
     * Synchronize users from a ZKTeco machine.
     *
     * @param ZktecoMachine $machine
     * @return array
     * @throws Throwable
     */
    public function syncFromMachine(ZktecoMachine $machine): array
    {
        $this->updateProgress(10, "Menghubungkan ke mesin fingerprint {$machine->name} ({$machine->ip_address})...");

        try {
            $tadFactory = new TADFactory([
                'ip'        => $machine->ip_address,
                'udp_port'  => $machine->port,
                'soap_port' => $machine->soap_port,
                'encoding'  => 'utf-8',
            ]);

            $tad = $tadFactory->get_instance();
            
            $this->updateProgress(30, "Mengambil data user dari mesin fingerprint...");
            $response = $tad->get_all_user_info();
            $users = $response->to_array();

            if (!isset($users['Row']) || empty($users['Row'])) {
                $this->completeTask("Tidak ada data user yang ditemukan di mesin fingerprint.");
                return [
                    'upserted' => 0,
                    'deleted' => 0,
                ];
            }

            $rows = is_array($users['Row']) && isset($users['Row']['PIN2']) ? [$users['Row']] : $users['Row'];
            $total = count($rows);
            
            $this->updateProgress(50, "Memproses {$total} data user...");

            $upsertData = [];
            $uids = [];
            $now = now();

            foreach ($rows as $item) {
                $uid = $item['PIN2'];
                $uids[$uid] = $uid;
                
                $upsertData[$uid] = [
                    'uid' => $uid,
                    'name' => $item['Name'] ?: null,
                    'zkteco_machine_id' => $machine->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $upsertData = array_values($upsertData);
            $uids = array_values($uids);

            $this->updateProgress(70, "Menyimpan data ke database...");

            return DB::transaction(function () use ($machine, $upsertData, $uids) {
                $chunks = array_chunk($upsertData, 500);
                $upsertedCount = 0;
                foreach ($chunks as $chunk) {
                    $upsertedCount += $this->repository->upsert($chunk);
                }

                $deletedCount = $this->repository->deleteMissing($machine->id, $uids);
                
                $this->completeTask("Sinkronisasi selesai. {$upsertedCount} user berhasil ditambahkan/diupdate, {$deletedCount} user berhasil dihapus.");

                return [
                    'upserted' => $upsertedCount,
                    'deleted' => $deletedCount,
                ];
            });

        } catch (Throwable $e) {
            $this->failTask("Gagal menghubungkan ke mesin fingerprint: " . $e->getMessage());
            throw $e;
        }
    }
}
