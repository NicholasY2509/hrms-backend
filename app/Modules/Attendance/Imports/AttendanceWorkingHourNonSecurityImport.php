<?php

namespace App\Modules\Attendance\Imports;

use App\Exceptions\ApplicationException;
use App\Modules\Attendance\Models\AttendanceWorkingHour;
use App\Modules\Employee\Models\Employee;
use App\Modules\Attendance\Models\WorkingHour;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

class AttendanceWorkingHourNonSecurityImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected $month;
    protected $day_type;

    public function __construct($month, $day_type)
    {
        $this->month    = $month;
        $this->day_type = $day_type;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            $startOfMonth = Carbon::parse($this->month)->firstOfMonth();
            $endOfMonth   = Carbon::parse($this->month)->endOfMonth();

            $dates = [];

            foreach (CarbonPeriod::create($startOfMonth, $endOfMonth)->toArray() as $value) {
                $date = Carbon::parse($value)->format('Y-m-d');

                if ($this->day_type == 'Weekday' && $value->dayOfWeek != 6 && $value->dayOfWeek != 7) {
                    $dates[] = $date;
                }

                if ($this->day_type == 'Weekend' && ($value->dayOfWeek == 6 || $value->dayOfWeek == 7)) {
                    $dates[] = $date;
                }
            }

            $niks = $rows->pluck('nik')->unique()->toArray();
            $jamKerjas = $rows->pluck('jam_kerja')->unique()->toArray();

            $employees = Employee::query()->whereIn('employee_id_number', $niks)->get()->keyBy('employee_id_number');
            $working_hours = WorkingHour::query()->whereIn('name', $jamKerjas)->get()->keyBy('name');

            $employeeIds = $employees->pluck('id')->toArray();
            $existingAttendances = AttendanceWorkingHour::query()
                ->whereIn('employee_id', $employeeIds)
                ->whereIn('attendance_at', $dates)
                ->get();

            $existingAttendanceMap = [];
            foreach ($existingAttendances as $attendance) {
                $existingAttendanceMap[$attendance->employee_id][$attendance->attendance_at] = $attendance;
            }

            $newAttendancesMap = [];

            foreach ($rows as $row) {
                $employee_id_number = $row['nik'];
                $working_hour_name  = $row['jam_kerja'];

                $employee = $employees->get($employee_id_number);
                if (!$employee) {
                    throw new ApplicationException("NIK Pegawai : $employee_id_number tidak ditemukan!", 400);
                }

                $working_hour = $working_hours->get($working_hour_name);
                if (!$working_hour) {
                    throw new ApplicationException("Jam Kerja : $working_hour_name tidak ditemukan!", 400);
                }

                foreach ($dates as $date) {
                    $existing = $existingAttendanceMap[$employee->id][$date] ?? null;

                    if ($existing) {
                        if ($existing->working_hour_id != $working_hour->id) {
                            $existing->working_hour_id = $working_hour->id;
                            $existing->save();
                        }
                    } else {
                        $key = $employee->id . '_' . $date;
                        $newAttendancesMap[$key] = [
                            'employee_id'     => $employee->id,
                            'attendance_at'   => $date,
                            'working_hour_id' => $working_hour->id,
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
