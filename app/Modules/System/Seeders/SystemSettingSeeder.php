<?php

namespace App\Modules\System\Seeders;

use App\Modules\System\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'approval_overtime_auto_reject_days',
                'value' => '3',
                'type' => 'integer',
                'group' => 'approval',
                'description' => 'Batas hari untuk auto-reject lembur yang belum disetujui.',
            ],
            [
                'key' => 'approval_unpaid_leave_auto_reject_days',
                'value' => '3',
                'type' => 'integer',
                'group' => 'approval',
                'description' => 'Batas hari untuk auto-reject cuti/izin yang belum disetujui.',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
