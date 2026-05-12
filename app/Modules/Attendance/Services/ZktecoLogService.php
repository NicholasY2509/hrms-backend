<?php

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\Models\ZktecoMachine;
use App\Modules\Attendance\Models\ZktecoAttendance;
use App\Modules\System\Traits\HasTaskProgress;
use TADPHP\TADFactory;
use Throwable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ZktecoLogService
{
    use HasTaskProgress;

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
        $this->updateProgress(10, "Menghubungkan ke mesin {$machine->name} ({$machine->ip_address})...");

        try {
            $tadFactory = new TADFactory([
                'ip'        => $machine->ip_address,
                'udp_port'  => $machine->port,
                'soap_port' => $machine->soap_port,
                'encoding'  => 'utf-8',
            ]);

            $tad = $tadFactory->get_instance();
            
            $this->updateProgress(30, "Mengambil log absensi ({$startDate} s/d {$endDate})...");
            
            // TADPHP filtering is often unreliable depending on machine version, 
            // but we'll try it. Fallback is usually fetching all and filtering in PHP.
            $response = $tad->get_att_log([
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            $logs = $response->to_array();

            if (!isset($logs['Row']) || empty($logs['Row'])) {
                return ['upserted' => 0];
            }

            $rows = is_array($logs['Row']) && isset($logs['Row']['PIN']) ? [$logs['Row']] : $logs['Row'];
            $total = count($rows);
            
            $this->updateProgress(60, "Memproses {$total} log absensi...");

            $upsertData = [];
            $now = now();

            foreach ($rows as $item) {
                $timestamp = $item['DateTime'];
                $dt = Carbon::parse($timestamp);
                
                // Final safeguard filtering
                if ($dt->format('Y-m-d') < $startDate || $dt->format('Y-m-d') > $endDate) {
                    continue;
                }

                $upsertData[] = [
                    'uid' => $item['PIN'],
                    'timestamp' => $timestamp,
                    'attendance_at' => $dt->format('Y-m-d'),
                    'zkteco_machine_id' => $machine->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $this->updateProgress(80, "Menyimpan data ke database...");

            return DB::transaction(function () use ($upsertData) {
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

        } catch (Throwable $e) {
            if ($this->task) {
                $this->failTask("Gagal sinkronisasi mesin {$machine->name}: " . $e->getMessage());
            }
            throw $e;
        }
    }
}
