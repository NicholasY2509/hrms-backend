<?php

namespace App\Modules\CertificateOfEmployment\Services;

use App\Modules\CertificateOfEmployment\Models\CertificateOfEmployment;
use App\Modules\Employee\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ApplicationException;

class CertificateOfEmploymentService
{
    /**
     * Create a new CoE request for an employee.
     */
    public function request(Employee $employee, bool $isManagement = false): CertificateOfEmployment
    {
        return DB::transaction(function () use ($employee, $isManagement) {
            // 1. Validate resignation status
            $latestResignation = $employee->latestResignation; // Assuming relationship exists
            
            if (!$latestResignation || !in_array($latestResignation->status, ['Approved', 'Settled'])) {
                throw new ApplicationException('Karyawan belum memiliki data pengunduran diri yang disetujui!', 400);
            }

            $effectiveDate = Carbon::parse($latestResignation->effective_date);
            $today = Carbon::today();

            // 2. Validate date range (1-6 months after effective date)
            // Skip for management if needed
            if (!$isManagement) {
                $minDate = $effectiveDate->copy()->addMonth();
                $maxDate = $effectiveDate->copy()->addMonths(6);

                if (!$today->between($minDate, $maxDate)) {
                    throw new ApplicationException('Pengajuan Surat Keterangan Kerja tidak bisa diajukan dibawah 1 bulan dari tanggal resign maupun diatas 6 bulan dari tanggal resign!', 400);
                }
            }

            // 3. Check for existing CoE
            $existing = CertificateOfEmployment::where('employee_id', $employee->id)->first();
            if ($existing) {
                throw new ApplicationException('Karyawan telah pernah mengajukan Surat Keterangan Kerja!', 400);
            }

            // 4. Create record
            return CertificateOfEmployment::create([
                'document_no' => $this->generateDocumentNo(),
                'employee_id' => $employee->id,
                'work_position_id' => $employee->work_position_id,
                'request_date' => $today->toDateString(),
            ]);
        });
    }

    /**
     * Settle the CoE.
     */
    public function settle(CertificateOfEmployment $coe): CertificateOfEmployment
    {
        if ($coe->settled_at) {
            return $coe;
        }

        $coe->update([
            'settled_at' => now(),
        ]);

        return $coe->refresh();
    }

    /**
     * Generate the document number (e.g., 001/Srt-Ket/DTM/V/2026).
     */
    public function generateDocumentNo(): string
    {
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('n');

        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $romanMonth = $romanMonths[$month];

        $lastRecord = CertificateOfEmployment::where('document_no', 'like', "%/Srt-Ket/DTM/%/{$year}")
            ->orderByRaw('CAST(SUBSTRING_INDEX(document_no, "/", 1) AS UNSIGNED) DESC')
            ->first();

        $nextNumber = 1;
        if ($lastRecord) {
            $lastNumber = (int) explode('/', $lastRecord->document_no)[0];
            $nextNumber = $lastNumber + 1;
        }

        $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return "{$formattedNumber}/Srt-Ket/DTM/{$romanMonth}/{$year}";
    }
}
