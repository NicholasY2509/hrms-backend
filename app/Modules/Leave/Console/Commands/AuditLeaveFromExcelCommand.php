<?php

namespace App\Modules\Leave\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Leave\Imports\AuditLeaveImport;
use App\Modules\Employee\Models\Employee;
use App\Modules\Leave\Models\AnnualLeave;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class AuditLeaveFromExcelCommand extends Command
{
    protected $signature = 'leave:audit-excel {file : Path to the excel file} {--saldo-col= : Column name for the balance, e.g. saldo_awal}';
    protected $description = 'Audit leave balances based on an initial Excel file and this year\'s logs.';

    public function handle()
    {
        $filePath = $this->argument('file');
        
        // Handle absolute or relative paths
        if (!File::exists($filePath)) {
            $filePath = base_path($filePath);
            if (!File::exists($filePath)) {
                $this->error("File not found: {$this->argument('file')}");
                return self::FAILURE;
            }
        }

        $saldoCol = $this->option('saldo-col');

        $this->info("Reading Excel file...");
        $import = new AuditLeaveImport();
        Excel::import($import, $filePath);

        $rows = $import->data;
        if (empty($rows)) {
            $this->error("Excel file is empty or could not be parsed.");
            return self::FAILURE;
        }

        $headers = array_keys($rows[0]);
        $this->info("Found columns in Excel: " . implode(', ', $headers));

        // Auto-detect NIK column
        $nikCol = null;
        foreach (['nik', 'employee_id_number', 'employee_id'] as $col) {
            if (in_array($col, $headers)) {
                $nikCol = $col;
                break;
            }
        }

        if (!$nikCol) {
            $this->error("Could not find 'nik' or 'employee_id_number' column in the Excel file.");
            return self::FAILURE;
        }

        // Auto-detect saldo column if not provided
        if (!$saldoCol) {
            foreach (['saldo', 'sisa_cuti', 'sisa', 'balance', 'saldo_awal', 'total'] as $col) {
                if (in_array($col, $headers)) {
                    $saldoCol = $col;
                    break;
                }
            }
        }

        if (!$saldoCol || !in_array($saldoCol, $headers)) {
            $this->error("Could not determine the balance column. Please specify with --saldo-col=");
            return self::FAILURE;
        }

        $this->info("Using NIK column: [{$nikCol}], Saldo column: [{$saldoCol}]");

        $currentYear = Carbon::now()->year;
        
        $results = [];
        $hasDiscrepancy = false;

        $this->output->progressStart(count($rows));

        foreach ($rows as $row) {
            $this->output->progressAdvance();
            
            $nik = $row[$nikCol] ?? null;
            $startBalance = (float)($row[$saldoCol] ?? 0);

            if (!$nik) continue;

            $employee = Employee::where('employee_id_number', $nik)->first();
            if (!$employee) {
                continue; // Employee not found in system
            }

            // Get all logs for this year (since the Excel is the starting balance for this year)
            $logs = AnnualLeave::where('employee_id', $employee->id)
                ->whereYear('created_at', $currentYear)
                ->orderBy('created_at', 'asc')
                ->get();

            $tambah2025 = 0;
            $tambah2026 = 0;
            $potong2025 = 0;
            $potong2026 = 0;

            foreach ($logs as $log) {
                $details = is_array($log->deduction_details) ? $log->deduction_details : json_decode($log->deduction_details, true);
                if (!is_array($details)) continue;

                foreach ($details as $year => $amount) {
                    $amount = (float) $amount;
                    if (strtolower($log->status) === 'tambah') {
                        if ((int)$year === 2025) $tambah2025 += $amount;
                        if ((int)$year === 2026) $tambah2026 += $amount;
                    } elseif (strtolower($log->status) === 'potong') {
                        if ((int)$year === 2025) $potong2025 += $amount;
                        if ((int)$year === 2026) $potong2026 += $amount;
                    }
                }
            }

            $expected2025 = $startBalance + $tambah2025 - $potong2025;
            $expected2026 = 0 + $tambah2026 - $potong2026;
            
            $actual2025 = (float) $employee->annual_leave_2;
            $actual2026 = (float) $employee->annual_leave_3;
            
            $diff2025 = $actual2025 - $expected2025;
            $diff2026 = $actual2026 - $expected2026;

            if (abs($diff2025) > 0.01 || abs($diff2026) > 0.01) { // Tolerance for floating point comparison
                $hasDiscrepancy = true;
                $results[] = [
                    'NIK' => $nik,
                    'Name' => $employee->full_name ?? $employee->name,
                    'Excel Start' => $startBalance,
                    '+ 2025' => $tambah2025,
                    '- 2025' => $potong2025,
                    '+ 2026' => $tambah2026,
                    '- 2026' => $potong2026,
                    'Exp 2025' => $expected2025,
                    'Act 2025' => $actual2025,
                    'Diff 2025' => $diff2025,
                    'Exp 2026' => $expected2026,
                    'Act 2026' => $actual2026,
                    'Diff 2026' => $diff2026
                ];
            }
        }

        $this->output->progressFinish();

        if (!$hasDiscrepancy) {
            $this->info("\nAll good! No discrepancies found between Excel+Logs and Actual Balances.");
            return self::SUCCESS;
        }

        $this->error("\nFound " . count($results) . " employee(s) with balance discrepancies:");
        $this->table(
            ['NIK', 'Name', 'Start', '+25', '-25', '+26', '-26', 'Exp25', 'Act25', 'Diff25', 'Exp26', 'Act26', 'Diff26'],
            $results
        );

        // Save report to CSV
        if (!File::isDirectory(storage_path('logs'))) {
            File::makeDirectory(storage_path('logs'), 0777, true, true);
        }
        
        $reportPath = storage_path('logs/leave_discrepancy_report_' . date('Ymd_His') . '.csv');
        $handle = fopen($reportPath, 'w');
        fputcsv($handle, ['NIK', 'Name', 'Excel Start', '+ 2025', '- 2025', '+ 2026', '- 2026', 'Expected 2025', 'Actual 2025', 'Diff 2025', 'Expected 2026', 'Actual 2026', 'Diff 2026']);
        foreach ($results as $r) {
            fputcsv($handle, $r);
        }
        fclose($handle);

        $this->info("Detailed discrepancy report saved to: {$reportPath}");

        return self::SUCCESS;
    }
}
