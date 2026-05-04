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
            ]
        ];

        foreach ($settings as $setting) {
            \Illuminate\Support\Facades\DB::table('attendance_settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
