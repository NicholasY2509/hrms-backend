<?php

namespace App\Modules\Disciplinary\Services;

use App\Modules\Disciplinary\Models\WarningLetter;
use Illuminate\Support\Str;

class WarningLetterTemplateService
{
    /**
     * Get the template data for a warning letter.
     */
    public function getTemplateData(WarningLetter $warningLetter): array
    {
        $employee = $warningLetter->employee;
        $warningCount = $employee->warning_letters()
            ->where('id', '<=', $warningLetter->id)
            ->count();

        switch ($warningCount) {
            case 1:
                $level = 'PERINGATAN PERTAMA';
                $content = 'Surat Peringatan ini merupakan Surat Peringatan Pertama dan berlaku untuk 6 (enam) bulan, apabila kesalahan serupa maupun kesalahan yang lain (tidak disiplin) masih terulang lagi, maka kepada Saudara, perusahaan akan mengeluarkan Surat Peringatan Kedua dan Ketiga selanjutnya Perusahaan akan mengambil tindakan kepada Saudara sesuai dengan peraturan yang berlaku.';
                break;

            case 2:
                $level = 'PERINGATAN KEDUA';
                $content = 'Surat Peringatan ini merupakan Surat Peringatan Kedua dan berlaku untuk 6 (enam) bulan, apabila kesalahan serupa maupun kesalahan yang lain (tidak disiplin) masih terulang lagi, maka kepada Saudara, perusahaan akan mengeluarkan Surat Peringatan Ketiga. Perusahaan akan mengambil tindakan kepada Saudara sesuai dengan peraturan yang berlaku.';
                break;

            case 3:
                $level = 'PERINGATAN KETIGA';
                $content = 'Surat Peringatan ini merupakan Surat Peringatan Ketiga dan berlaku untuk 6 (enam) bulan, apabila kesalahan serupa maupun kesalahan yang lain (tidak disiplin) masih terulang lagi, maka kepada Saudara, perusahaan akan mengambil tindakan memberhentikan Saudara tanpa ganti rugi apapun.';
                break;

            default:
                $level = 'SURAT PERINGATAN';
                $content = 'Surat Peringatan ini berlaku untuk 6 (enam) bulan, apabila kesalahan serupa maupun kesalahan yang lain (tidak disiplin) masih terulang lagi, maka kepada Saudara, perusahaan akan mengambil tindakan kepada Saudara sesuai dengan peraturan yang berlaku.';
                break;
        }

        return [
            'title' => 'Surat Peringatan - ' . $employee->full_name,
            'level' => $level,
            'level_headline' => Str::headline($level),
            'content' => $content,
            'legal_points' => [
                'Peraturan Perusahaan yang berkaitan dengan kedisiplinan karyawan.',
                'Bahwa nama karyawan tersebut di bawah ini tidak melaksanakan Peraturan Perusahaan dan khususnya Surat Edaran No.001/Kpts-SE/DTM/X/2009 Mengenai Kode dan Pengaturan Kehadiran.',
                'Bahwa menimbang pentingnya disiplin kerja untuk menunjang aktivitas dan kelangsungan perusahaan.'
            ]
        ];
    }
}
