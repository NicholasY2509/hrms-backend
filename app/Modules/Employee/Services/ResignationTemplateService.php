<?php

namespace App\Modules\Employee\Services;

use App\Modules\Employee\Models\Resignation;

class ResignationTemplateService
{
    /**
     * Get the template data for a resignation letter.
     */
    public function getTemplateData(Resignation $resignation): array
    {
        $employee = $resignation->employee;

        return [
            'title' => 'Surat Pengunduran Diri - ' . $employee->full_name,
            'subject' => 'Pengunduran Diri',
            'salutation' => 'Dengan Hormat,',
            'opening' => 'Saya yang bertanda tangan di bawah ini,',
            'main_content' => "Dengan ini mengajukan pengunduran diri sebagai karyawan PT. Deltamas Surya Indah Mulia dikarenakan {$resignation->reason}. Pengunduran diri saya efektif pada tanggal " . \Carbon\Carbon::parse($resignation->effective_date)->translatedFormat('d F Y') . ".",
            'closing' => 'Saya ucapkan terima kasih kepada PT. Deltamas Surya Indah Mulia yang telah memberikan kesempatan dan kepercayaan kepada saya. Semoga kedepannya PT. Deltamas Surya Indah Mulia akan semakin maju dan sukses.',
            'footer' => 'Demikian surat pengunduran diri ini saya buat dengan sadar dan tanpa paksaan dari siapapun dan pihak manapun.',
            'signatures' => [
                'sender' => $employee->full_name,
                'supervisor' => 'Kepala Department',
                'hrd' => 'HRD & GA'
            ]
        ];
    }
}
