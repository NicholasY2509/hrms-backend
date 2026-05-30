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

            $totalTambah = 0;
            $totalPotong = 0;

            foreach ($logs as $log) {
                if (strtolower($log->status) === 'tambah') {
                    $totalTambah += (float)$log->total;
                } elseif (strtolower($log->status) === 'potong') {
                    $totalPotong += (float)$log->total;
                }
            }

            $expectedBalance = $startBalance + $totalTambah - $totalPotong;
            $actualBalance = (float) $employee->annual_leave_2 + (float) $employee->annual_leave_3;
            $diff = $actualBalance - $expectedBalance;

            if (abs($diff) > 0.01) { // Tolerance for floating point comparison
                $hasDiscrepancy = true;
                $results[] = [
                    'NIK' => $nik,
                    'Name' => $employee->full_name ?? $employee->name,
                    'Excel Start' => $startBalance,
                    '+ Logs' => $totalTambah,
                    '- Logs' => $totalPotong,
                    'Expected' => $expectedBalance,
                    'Actual' => $actualBalance,
                    'Diff' => $diff
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
            ['NIK', 'Name', 'Excel Start', '+ Logs', '- Logs', 'Expected', 'Actual', 'Diff'],
            $results
        );

        // Save report to CSV
        if (!File::isDirectory(storage_path('logs'))) {
            File::makeDirectory(storage_path('logs'), 0777, true, true);
        }
        
        $reportPath = storage_path('logs/leave_discrepancy_report_' . date('Ymd_His') . '.csv');
        $handle = fopen($reportPath, 'w');
        fputcsv($handle, ['NIK', 'Name', 'Excel Start', '+ Logs', '- Logs', 'Expected', 'Actual', 'Diff']);
        foreach ($results as $r) {
            fputcsv($handle, $r);
        }
        fclose($handle);

        $this->info("Detailed discrepancy report saved to: {$reportPath}");

        return self::SUCCESS;
    }
}
