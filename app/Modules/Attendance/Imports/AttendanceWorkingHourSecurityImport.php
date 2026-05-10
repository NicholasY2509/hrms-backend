<?php

namespace App\Modules\Attendance\Imports;

use App\Exceptions\ApplicationException;
use App\Modules\Attendance\Models\AttendanceWorkingHour;
use App\Modules\Employee\Models\Employee;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

class AttendanceWorkingHourSecurityImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected $month;

    public function __construct($month)
    {
        $this->month = $month;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            $start_date = Carbon::parse($this->month)->startOfMonth();
            $end_date = Carbon::parse($this->month)->endOfMonth();
            
            $niks = $rows->pluck('nik')->unique()->toArray();
            if (empty($niks)) {
                $niks = $rows->pluck('NIK')->unique()->toArray();
            }

            $employees = Employee::query()->whereIn('employee_id_number', $niks)->get()->keyBy('employee_id_number');
            $employeeIds = $employees->pluck('id')->toArray();
            
            $dates = CarbonPeriod::create($start_date, $end_date)->toArray();
            $dateStrings = [];
            foreach ($dates as $date) {
                $dateStrings[] = Carbon::parse($date)->format('Y-m-d');
            }

            $existingAttendances = AttendanceWorkingHour::query()
                ->whereIn('employee_id', $employeeIds)
                ->whereIn('attendance_at', $dateStrings)
                ->get();
            
            $existingAttendanceMap = [];
            foreach ($existingAttendances as $attendance) {
                $existingAttendanceMap[$attendance->employee_id][$attendance->attendance_at] = $attendance;
            }

            $newAttendancesMap = [];

            foreach ($rows as $row) {
                $nik = $row['nik'] ?? $row['NIK'] ?? null;
                if (!$nik) continue;

                $employee = $employees->get($nik);
                if (!$employee) {
                    throw new ApplicationException("NIK : {$nik} tidak ditemukan!", 400);
                }

                foreach ($dates as $date) {
                    $attendance_at = Carbon::parse($date)->format('Y-m-d');
                    $day_number = Carbon::parse($date)->format('d');
                    $day_number_no_padding = (int)$day_number;

                    $working_hour_name = $row[$day_number] ?? $row[$day_number_no_padding] ?? null;

                    if ($working_hour_name === null) {
                        throw new ApplicationException("Kolom tanggal {$day_number} tidak ditemukan dalam file Excel!", 400);
                    }

                    // Trim whitespace and convert to uppercase for consistency
                    $working_hour_name = trim(strtoupper($working_hour_name));
                    $working_hour_id = null;

                    switch ($working_hour_name) {
                        case 'P':
                            // Security Coordinator
                            if ($employee->work_position_id == 63) {
                                if (Carbon::parse($attendance_at)->isSaturday()) {
                                    $working_hour_id = 19;
                                } else {
                                    $working_hour_id = 12;
                                }
                            }

                            // Security
                            if ($employee->work_position_id == 62) {
                                $working_hour_id = 3;
                            }

                            // Danru
                            if ($employee->work_position_id == 26) {
                                // Balai Kota
                                if ($employee->work_location_id == 1) {
                                    $working_hour_id = 4;
                                } else {
                                    // SM Raja
                                    $working_hour_id = 54;
                                }
                            }
                            break;

                        case 'M':
                            $working_hour_id = 30;
                            break;

                        case 'OFF':
                            $working_hour_id = 52;
                            break;

                        default:
                            throw new ApplicationException("Jam Kerja : '$working_hour_name' tidak ditemukan! (NIK: {$nik}, Tanggal: {$attendance_at})", 400);
                    }

                    if (!$working_hour_id) {
                        throw new ApplicationException("Jam Kerja atas nama {$employee->full_name} tidak ditemukan!", 400);
                    }

                    $existing = $existingAttendanceMap[$employee->id][$attendance_at] ?? null;

                    if ($existing) {
                        if ($existing->working_hour_id != $working_hour_id) {
                            $existing->working_hour_id = $working_hour_id;
                            $existing->save();
                        }
                    } else {
                        $key = $employee->id . '_' . $attendance_at;
                        $newAttendancesMap[$key] = [
                            'employee_id'     => $employee->id,
                            'attendance_at'   => $attendance_at,
                            'working_hour_id' => $working_hour_id,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ];
                    }
                }
            }

            if (count($newAttendancesMap) > 0) {
                $chunks = array_chunk(array_values($newAttendancesMap), 1000);
                foreach ($chunks as $chunk) {
                    AttendanceWorkingHour::insert($chunk);
                }
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();

            throw new ApplicationException($e->getMessage(), 400);
        }
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
