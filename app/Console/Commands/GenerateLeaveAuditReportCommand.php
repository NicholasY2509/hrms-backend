<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GenerateLeaveAuditReportCommand extends Command
{
    protected $signature = 'leave:audit-report';
    protected $description = 'Generate an Excel report comparing Implied Balances (AL2 & AL3) against an uploaded Excel file and 0 baseline';

    public function handle()
    {
        $filePath = $this->ask('Masukkan absolute path ke file Excel yang diupload pada 4 Februari:');

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan di path: {$filePath}");
            return;
        }

        $this->info('Membaca file Excel...');

        $importClass = new class implements ToCollection {
            public function collection(Collection $rows) { return $rows; }
        };

        $sheets = Excel::toCollection($importClass, $filePath);
        $rows = $sheets->first();

        if (!$rows || $rows->count() < 4) {
            $this->error("Format file tidak valid atau data kosong. Pastikan data dimulai di baris ke-4.");
            return;
        }

        $currentYear = date('Y');
        $lastYear = $currentYear - 1;

        $headers = ['NIK', 'Nama', "Excel AL2 ({$lastYear})", "System Current AL2", "Implied Feb 2nd AL2", "Selisih AL2", "System Current AL3", "Implied Jan 1st AL3", "Selisih AL3"];
        $exportData = [];
        $tableData = []; 

        $bar = $this->output->createProgressBar($rows->count() - 3);
        $bar->start();

        $discrepancyCount = 0;

        for ($i = 3; $i < $rows->count(); $i++) {
            $row = $rows[$i];
            
            $nik = trim($row[0]);
            $excelAL2 = (float) $row[4];

            if (empty($nik)) {
                $bar->advance();
                continue;
            }

            $employee = DB::table('employees')
                ->where('employee_id_number', $nik)
                ->whereNull('deleted_at')
                ->first();

            if (!$employee) {
                $rowData = [$nik, 'TIDAK DITEMUKAN', $excelAL2, 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A'];
                $exportData[] = $rowData;
                $tableData[] = $rowData;
                $discrepancyCount++;
                $bar->advance();
                continue;
            }

            $currentAL2 = (float) $employee->annual_leave_2;
            $currentAL3 = (float) $employee->annual_leave_3;

            // Fetch from Jan 1st for AL3 checking
            $leaves = DB::table('annual_leaves')
                ->where('employee_id', $employee->id)
                ->whereNull('deleted_at')
                ->where('created_at', '>=', "{$currentYear}-01-01 00:00:00")
                ->get();

            $totalAddedAL2_fromFeb2 = 0;
            $totalDeductedAL2_fromFeb2 = 0;
            
            $totalAddedAL3_fromJan1 = 0;
            $totalDeductedAL3_fromJan1 = 0;

            foreach ($leaves as $leave) {
                $details = json_decode($leave->deduction_details, true);
                if (!is_array($details) || empty($details)) {
                    $yearFallback = $leave->annual_leave_year ?? $currentYear;
                    $details = [
                        $yearFallback => (float) $leave->total
                    ];
                }

                $isFromFeb2 = $leave->created_at >= "{$currentYear}-02-02 00:00:00";

                if ($leave->status === 'Tambah') {
                    if ($isFromFeb2) {
                        $totalAddedAL2_fromFeb2 += (float) ($details[$lastYear] ?? 0);
                    }
                    $totalAddedAL3_fromJan1 += (float) ($details[$currentYear] ?? 0);
                } elseif ($leave->status === 'Potong') {
                    if ($isFromFeb2) {
                        $totalDeductedAL2_fromFeb2 += (float) ($details[$lastYear] ?? 0);
                    }
                    $totalDeductedAL3_fromJan1 += (float) ($details[$currentYear] ?? 0);
                }
            }

            $impliedFeb2AL2 = $currentAL2 - $totalAddedAL2_fromFeb2 + $totalDeductedAL2_fromFeb2;
            $selisihAL2 = $impliedFeb2AL2 - $excelAL2;

            $impliedJan1AL3 = $currentAL3 - $totalAddedAL3_fromJan1 + $totalDeductedAL3_fromJan1;
            $selisihAL3 = $impliedJan1AL3 - 0;

            $rowData = [
                $nik,
                $employee->full_name ?? '',
                $excelAL2,
                $currentAL2,
                $impliedFeb2AL2,
                $selisihAL2,
                $currentAL3,
                $impliedJan1AL3,
                $selisihAL3
            ];

            $exportData[] = $rowData;

            if ($selisihAL2 != 0 || $selisihAL3 != 0) {
                $discrepancyCount++;
                $tableData[] = $rowData;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        
        if (count($tableData) > 0) {
            $this->warn("Daftar Pegawai dengan Selisih (Discrepancy AL2 atau AL3):");
            $this->table($headers, $tableData);
        } else {
            $this->info("Luar biasa! Tidak ada selisih saldo AL2 maupun AL3 untuk pegawai-pegawai tersebut.");
        }

        $exportClass = new class($exportData, $headers) implements FromCollection, WithHeadings {
            private $data;
            private $headers;
            public function __construct($data, $headers) {
                $this->data = collect($data);
                $this->headers = $headers;
            }
            public function collection() { return $this->data; }
            public function headings(): array { return $this->headers; }
        };

        $excelFile = 'leave_discrepancy_report.xlsx';
        Excel::store($exportClass, $excelFile);
        
        $this->newLine();
        $this->info("Laporan lengkap berhasil diexport ke Excel: storage/app/{$excelFile}");
        if ($discrepancyCount > 0) {
            $this->warn("Terdapat total {$discrepancyCount} record dengan selisih saldo (ditampilkan di tabel atas).");
        }
    }
}
