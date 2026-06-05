<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CheckStartOfYearAL3Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:check-al3';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check employees whose implied Jan 1st AL3 is not 0, sorted by department, and export to Excel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Implied Jan 1st AL3 for all employees...');
        
        $currentYear = date('Y');

        $employees = DB::table('employees')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->whereNull('employees.deleted_at')
            ->where('employees.work_employee_status_id', 1)
            ->select('employees.*', 'departments.name as department_name')
            ->get();

        $results = [];

        $bar = $this->output->createProgressBar(count($employees));
        $bar->start();

        foreach ($employees as $employee) {
            $currentAL3 = (float) $employee->annual_leave_3;

            $leaves = DB::table('annual_leaves')
                ->where('employee_id', $employee->id)
                ->whereNull('deleted_at')
                ->where('created_at', '>=', "{$currentYear}-01-01 00:00:00")
                ->get();

            $totalAddedAL3 = 0;
            $totalDeductedAL3 = 0;

            foreach ($leaves as $leave) {
                $details = json_decode($leave->deduction_details, true);
                if (!is_array($details) || empty($details)) {
                    $yearFallback = $leave->annual_leave_year ?? $currentYear;
                    $details = [
                        $yearFallback => (float) $leave->total
                    ];
                }

                if ($leave->status === 'Tambah') {
                    $totalAddedAL3 += (float) ($details[$currentYear] ?? 0);
                } elseif ($leave->status === 'Potong') {
                    $totalDeductedAL3 += (float) ($details[$currentYear] ?? 0);
                }
            }

            $impliedJan1AL3 = $currentAL3 - $totalAddedAL3 + $totalDeductedAL3;

            if ($impliedJan1AL3 != 0) {
                $results[] = [
                    'department' => $employee->department_name ?? 'N/A',
                    'nik' => $employee->employee_id_number,
                    'name' => $employee->full_name,
                    'current_al3' => $currentAL3,
                    'implied_start_al3' => $impliedJan1AL3,
                ];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if (empty($results)) {
            $this->info("Bagus! Semua karyawan memiliki Saldo Awal AL3 = 0 di tanggal 1 Januari.");
            return;
        }

        // Urutkan berdasarkan department
        usort($results, function ($a, $b) {
            return strcmp($a['department'], $b['department']);
        });

        $this->warn("Ditemukan " . count($results) . " karyawan dengan Saldo Awal AL3 tidak sama dengan 0:");

        $headers = ['Department', 'NIK', 'Nama', 'Current AL3', 'Implied Start AL3'];
        
        $tableData = array_map(function ($row) {
            return [
                $row['department'],
                $row['nik'],
                $row['name'],
                $row['current_al3'],
                $row['implied_start_al3'],
            ];
        }, $results);

        $this->table($headers, $tableData);

        // Export ke Excel
        $exportClass = new class($tableData, $headers) implements FromCollection, WithHeadings {
            private $data;
            private $headers;
            public function __construct($data, $headers) {
                $this->data = collect($data);
                $this->headers = $headers;
            }
            public function collection() { return $this->data; }
            public function headings(): array { return $this->headers; }
        };

        $excelFile = 'al3_error_report.xlsx';
        Excel::store($exportClass, $excelFile);
        
        $this->newLine();
        $this->info("Laporan AL3 ini berhasil diexport ke Excel: storage/app/{$excelFile}");
    }
}
