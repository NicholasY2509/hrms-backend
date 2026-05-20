<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttendanceSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'attendance_clock_in_start_minutes',
                'value' => '60',
                'type' => 'integer',
                'group' => 'attendance',
                'description' => 'Maksimal menit sebelum shift dimulai untuk bisa absen masuk (Default: 60)',
            ],
            [
                'key' => 'attendance_clock_in_end_minutes',
                'value' => '60',
                'type' => 'integer',
                'group' => 'attendance',
                'description' => 'Maksimal menit sebelum shift berakhir untuk bisa absen masuk (Default: 60)',
            ],
            [
                'key' => 'attendance_clock_out_end_hours',
                'value' => '5',
                'type' => 'integer',
                'group' => 'attendance',
                'description' => 'Maksimal jam setelah shift berakhir untuk bisa absen pulang (Default: 5)',
            ],
            [
                'key' => 'attendance_earth_radius_meters',
                'value' => '6371000',
                'type' => 'integer',
                'group' => 'attendance',
                'description' => 'Jari-jari bumi dalam meter untuk perhitungan jarak (Default: 6371000)',
            ],
            [
                'key' => 'attendance_min_gap_minutes',
                'value' => '30',
                'type' => 'integer',
                'group' => 'attendance',
                'description' => 'Minimal selang waktu (menit) antara absen masuk dan pulang untuk mencegah double tap (Default: 30)',
            ],
            [
                'key' => 'attendance_auto_tap_time',
                'value' => '12:00:00',
                'type' => 'string',
                'group' => 'attendance',
                'description' => 'Jam pada hari berikutnya dimana sistem otomatis menutup absen (TAP) jika lupa absen pulang (Default: 12:00:00)',
            ],
            [
                'key' => 'attendance_night_shift_crossover_time',
                'value' => '12:00:00',
                'type' => 'string',
                'group' => 'attendance',
                'description' => 'Jam batas pergantian logika hari untuk shift malam (Default: 12:00:00)',
            ],
            [
                'key' => 'attendance_night_shift_end_time',
                'value' => '15:00:00',
                'type' => 'string',
                'group' => 'attendance',
                'description' => 'Jam batas maksimal scan pulang pada hari berikutnya untuk shift malam (Default: 15:00:00)',
            ],
            // === CALCULATION SETTINGS ===
            [
                'key' => 'attendance_status_alpha_id',
                'value' => '2',
                'type' => 'integer',
                'group' => 'calculation',
                'description' => 'Status ID untuk Mangkir/Alpha',
            ],
            [
                'key' => 'attendance_status_sick_id',
                'value' => '3',
                'type' => 'integer',
                'group' => 'calculation',
                'description' => 'Status ID untuk Sakit',
            ],
            [
                'key' => 'attendance_status_permission_id',
                'value' => '4',
                'type' => 'integer',
                'group' => 'calculation',
                'description' => 'Status ID untuk Izin',
            ],
            [
                'key' => 'attendance_status_leave_id',
                'value' => '5',
                'type' => 'integer',
                'group' => 'calculation',
                'description' => 'Status ID untuk Cuti Tahunan',
            ],
            [
                'key' => 'attendance_status_ooo_id',
                'value' => '7',
                'type' => 'integer',
                'group' => 'calculation',
                'description' => 'Status ID untuk Dinas Luar Kota',
            ],
            [
                'key' => 'attendance_status_tap_id',
                'value' => '8',
                'type' => 'integer',
                'group' => 'calculation',
                'description' => 'Status ID untuk TAP (Lupa Absen Pulang)',
            ],
            [
                'key' => 'attendance_status_late_id',
                'value' => '9',
                'type' => 'integer',
                'group' => 'calculation',
                'description' => 'Status ID untuk Terlambat',
            ],
            [
                'key' => 'attendance_status_holiday_id',
                'value' => '10',
                'type' => 'integer',
                'group' => 'calculation',
                'description' => 'Status ID untuk Hari Libur',
            ],
            [
                'key' => 'attendance_status_off_id',
                'value' => '11',
                'type' => 'integer',
                'group' => 'calculation',
                'description' => 'Status ID untuk Hari Off',
            ],
            [
                'key' => 'attendance_status_present_id',
                'value' => '12',
                'type' => 'integer',
                'group' => 'calculation',
                'description' => 'Status ID untuk Hadir (Tepat Waktu)',
            ],
            [
                'key' => 'attendance_status_late_tap_id',
                'value' => '13',
                'type' => 'integer',
                'group' => 'calculation',
                'description' => 'Status ID untuk Terlambat & TAP',
            ],
            [
                'key' => 'attendance_leave_type_paid_id',
                'value' => '9',
                'type' => 'integer',
                'group' => 'calculation',
                'description' => 'UnpaidLeaveType ID untuk Cuti Tahunan',
            ],
            [
                'key' => 'attendance_leave_type_sick_id',
                'value' => '10',
                'type' => 'integer',
                'group' => 'calculation',
                'description' => 'UnpaidLeaveType ID untuk Sakit',
            ],
            [
                'key' => 'attendance_leave_type_ooo_id',
                'value' => '21',
                'type' => 'integer',
                'group' => 'calculation',
                'description' => 'UnpaidLeaveType ID untuk Dinas Luar Kota',
            ],
            [
                'key' => 'attendance_calc_night_midpoint',
                'value' => '12:00:00',
                'type' => 'string',
                'group' => 'calculation',
                'description' => 'Midpoint (Noon) untuk memisahkan scan masuk/pulang shift malam',
            ],
            [
                'key' => 'attendance_calc_night_end_buffer',
                'value' => '15:00:00',
                'type' => 'string',
                'group' => 'calculation',
                'description' => 'Batas maksimal (jam) scan pulang shift malam pada hari berikutnya',
            ],
            [
                'key' => 'attendance_calc_min_gap',
                'value' => '30',
                'type' => 'integer',
                'group' => 'calculation',
                'description' => 'Minimal selisih menit antara scan masuk dan pulang',
            ],
        ];

        foreach ($settings as $setting) {
            \Illuminate\Support\Facades\DB::table('attendance_settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
