<?php

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\Jobs\SyncZktecoAttendancesJob;
use App\Modules\System\Services\TaskService;
use App\Modules\System\Models\Task;
use App\Modules\Attendance\Models\ZktecoMachine;
use App\Modules\Attendance\Models\ZktecoAttendance;
use App\Modules\System\Traits\HasTaskProgress;
use TADPHP\TADFactory;
use Throwable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ZktecoLogService
{
    use HasTaskProgress;

    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Initiate a background logs synchronization.
     *
     * @param ZktecoMachine $machine
     * @param string $startDate
     * @param string $endDate
     * @return Task
     */
    public function initiateSync(ZktecoMachine $machine, string $startDate, string $endDate): Task
    {
        try {
            $tadFactory = new TADFactory([
                'ip'              => $machine->ip_address,
                'udp_port'        => $machine->port,
                'soap_port'       => $machine->soap_port,
                'encoding'        => 'utf-8',
                'connection_type' => 'soap',
            ]);
            
            $tad = $tadFactory->get_instance();
            
            if ($tad->is_alive()) {
                Log::info("Connection to ZKTeco machine {$machine->name} ({$machine->ip_address}:{$machine->soap_port}) was successful.");
            } else {
                Log::warning("Connection to ZKTeco machine {$machine->name} ({$machine->ip_address}:{$machine->soap_port}) failed: Machine is not alive.");
            }
        } catch (Throwable $e) {
            Log::error("Connection to ZKTeco machine {$machine->name} ({$machine->ip_address}:{$machine->soap_port}) failed with error: " . $e->getMessage());
        }

        $task = $this->taskService->createTask(
            'zkteco_attendance_sync',
            "Menunggu antrian sinkronisasi log absensi dari {$machine->name}...",
            [
                'zkteco_machine_id' => $machine->id,
                'machine_name' => $machine->name,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        );

        SyncZktecoAttendancesJob::dispatch($machine, $startDate, $endDate, $task);

        return $task;
    }

    /**
     * Synchronize attendance logs from a ZKTeco machine for a specific date range.
     *
     * @param ZktecoMachine $machine
     * @param string $startDate
     * @param string $endDate
     * @return array
     * @throws Throwable
     */
    public function syncLogs(ZktecoMachine $machine, string $startDate, string $endDate): array
    {
        $this->updateProgress(10, "Menghubungkan ke mesin {$machine->name} ({$machine->ip_address}:{$machine->soap_port})...");

        try {
            $tadFactory = new TADFactory([
                'ip'              => $machine->ip_address,
                'udp_port'        => $machine->port,
                'soap_port'       => $machine->soap_port,
                'encoding'        => 'utf-8',
                'connection_type' => 'soap', // Force SOAP (TCP) to avoid UDP fragmentation
            ]);

            $tad = $tadFactory->get_instance();
            
            $this->updateProgress(30, "Mengambil log absensi ({$startDate} s/d {$endDate})...");
            
            // TADPHP does not accept start_date/end_date parameters directly in get_att_log().
            // We fetch the logs first, and then filter by date range using filter_by_date().
            $attLogs = $tad->get_att_log();
            $filteredLogs = $attLogs->filter_by_date([
                'start' => $startDate,
                'end'   => $endDate,
            ]);
            
            $logs = $filteredLogs->to_array();

            // DEBUG LOG: What exactly is TADPHP returning?
            \Illuminate\Support\Facades\Log::debug("TADPHP Raw Logs Output for {$machine->name}:", [
                'raw_array' => $logs,
                'is_soap' => class_exists('SoapClient') // verify soap is actually available
            ]);

            if (!isset($logs['Row']) || empty($logs['Row'])) {
                if ($this->task) {
                    $this->completeTask("Tidak ada data log absensi yang ditemukan pada periode tersebut.");
                }
                return ['upserted' => 0];
            }

            $rows = is_array($logs['Row']) && (isset($logs['Row']['PIN']) || isset($logs['Row']['PIN2'])) ? [$logs['Row']] : $logs['Row'];
            $total = count($rows);
            
            $this->updateProgress(60, "Memproses {$total} log absensi...");

            $upsertData = [];
            $now = now();

            foreach ($rows as $item) {
                $uid = $item['PIN'] ?? $item['PIN2'] ?? null;
                if (!$uid) {
                    continue;
                }

                $timestamp = $item['DateTime'] ?? null;
                if (!$timestamp) {
                    continue;
                }

                $dt = Carbon::parse($timestamp)->startOfMinute();
                
                // Final safeguard filtering
                if ($dt->format('Y-m-d') < $startDate || $dt->format('Y-m-d') > $endDate) {
                    continue;
                }

                $upsertData[] = [
                    'uid' => $uid,
                    'timestamp' => $dt->format('Y-m-d H:i:s'),
                    'attendance_at' => $dt->format('Y-m-d'),
                    'zkteco_machine_id' => $machine->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $this->updateProgress(80, "Menyimpan data ke database...");

            $result = DB::transaction(function () use ($upsertData) {
                $chunks = array_chunk($upsertData, 500);
                $upsertedCount = 0;
                foreach ($chunks as $chunk) {
                    // Use upsert to avoid duplicates (PIN + DateTime + MachineID should be unique)
                    $upsertedCount += ZktecoAttendance::upsert(
                        $chunk, 
                        ['uid', 'timestamp', 'zkteco_machine_id'], 
                        ['updated_at']
                    );
                }

                return ['upserted' => $upsertedCount];
            });

            if ($this->task) {
                $this->completeTask("Sinkronisasi log selesai. Berhasil menyinkronkan {$result['upserted']} data log absensi.");
            }

            return $result;

        } catch (Throwable $e) {
            if ($this->task) {
                $this->failTask("Gagal sinkronisasi mesin {$machine->name}: " . $e->getMessage());
            }
            throw $e;
        }
    }
}
