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
    protected $signature = 'leave:audit-excel {file : Path to the excel file} {--saldo-col= : Column name for the balance, e.g. saldo_awal} {--year= : The year to focus the audit on (e.g. 2025 or 2026). If empty, shows both}';
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
        $targetYear = $this->option('year');

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
        $currentYear = date('Y');

        $results = [];
        $hasDiscrepancyGlobal = false;

        $this->output->progressStart(count($rows));

        foreach ($rows as $row) {
            $this->output->progressAdvance();
            
            $nik = $row[$nikCol] ?? null;
            $startBalance = (float)($row[$saldoCol] ?? 0);

            if (!$nik) continue;

            $employee = Employee::with('position')->where('employee_id_number', $nik)->first();
            if (!$employee) {
                continue; // Employee not found in system
            }

            $positionName = $employee->position->name ?? '-';

            // Get all logs starting from Feb 3rd of this year
            $logs = AnnualLeave::where('employee_id', $employee->id)
                ->where('created_at', '>=', $currentYear . '-02-03 00:00:00')
                ->orderBy('created_at', 'asc')
                ->get();

            $tambah2025 = 0;
            $tambah2026 = 0;
            $potong2025 = 0;
            $potong2026 = 0;
            
            $expected2025 = $startBalance;
            $expected2026 = 1; 

            foreach ($logs as $log) {
                $details = is_array($log->deduction_details) ? $log->deduction_details : json_decode($log->deduction_details, true);
                $amountTotal = (float)$log->total;
                $status = strtolower($log->status);

                if (!empty($details)) {
                    foreach ($details as $year => $amount) {
                        $amount = (float) $amount;
                        if ($status === 'tambah') {
                            if ((int)$year === 2025) { $tambah2025 += $amount; $expected2025 += $amount; }
                            if ((int)$year === 2026) { $tambah2026 += $amount; $expected2026 += $amount; }
                        } elseif ($status === 'potong') {
                            if ((int)$year === 2025) { $potong2025 += $amount; $expected2025 -= $amount; }
                            if ((int)$year === 2026) { $potong2026 += $amount; $expected2026 -= $amount; }
                        }
                    }
                } else {
                    if ($status === 'tambah') {
                        $tambah2026 += $amountTotal;
                        $expected2026 += $amountTotal;
                    } elseif ($status === 'potong') {
                        if ($expected2025 > 0) {
                            $deduct25 = min($amountTotal, $expected2025);
                            $potong2025 += $deduct25;
                            $expected2025 -= $deduct25;
                            $amountTotal -= $deduct25;
                        }
                        if ($amountTotal > 0) {
                            $potong2026 += $amountTotal;
                            $expected2026 -= $amountTotal;
                        }
                    }
                }
            }

            $actual2025 = (float) $employee->annual_leave_2;
            $actual2026 = (float) $employee->annual_leave_3;
            
            $diff2025 = $actual2025 - $expected2025;
            $diff2026 = $actual2026 - $expected2026;

            if ($targetYear == '2025') {
                $hasDiff = abs($diff2025) > 0.01;
                $rowResult = [
                    'NIK' => $nik,
                    'Name' => $employee->full_name ?? $employee->name,
                    'Position' => $positionName,
                    'Start (AL2)' => $startBalance,
                    '+ 2025' => $tambah2025,
                    '- 2025' => $potong2025,
                    'Exp 2025' => $expected2025,
                    'Act 2025' => $actual2025,
                    'Diff 2025' => $diff2025
                ];
            } elseif ($targetYear == '2026') {
                $hasDiff = abs($diff2026) > 0.01;
                $rowResult = [
                    'NIK' => $nik,
                    'Name' => $employee->full_name ?? $employee->name,
                    'Position' => $positionName,
                    '+ 2026' => $tambah2026,
                    '- 2026' => $potong2026,
                    'Exp 2026' => $expected2026,
                    'Act 2026' => $actual2026,
                    'Diff 2026' => $diff2026
                ];
            } else {
                $hasDiff = abs($diff2025) > 0.01 || abs($diff2026) > 0.01;
                $rowResult = [
                    'NIK' => $nik,
                    'Name' => $employee->full_name ?? $employee->name,
                    'Position' => $positionName,
                    'Start' => $startBalance,
                    '+25' => $tambah2025,
                    '-25' => $potong2025,
                    '+26' => $tambah2026,
                    '-26' => $potong2026,
                    'Exp25' => $expected2025,
                    'Act25' => $actual2025,
                    'Diff25' => $diff2025,
                    'Exp26' => $expected2026,
                    'Act26' => $actual2026,
                    'Diff26' => $diff2026
                ];
            }

            if ($hasDiff) {
                $hasDiscrepancyGlobal = true;
                $results[] = $rowResult;
            }
        }

        $this->output->progressFinish();

        $yearLabel = $targetYear ?: 'ALL';

        if (!$hasDiscrepancyGlobal) {
            $this->info("\nAll good! No discrepancies found between Excel+Logs and Actual Balances for {$yearLabel}.");
            return self::SUCCESS;
        }

        $this->error("\nFound " . count($results) . " employee(s) with balance discrepancies for {$yearLabel}:");
        
        if (count($results) > 0) {
            $headers = array_keys($results[0]);
            $this->table($headers, $results);
            
            // Save report to CSV
            if (!File::isDirectory(storage_path('logs'))) {
                File::makeDirectory(storage_path('logs'), 0777, true, true);
            }
            
            $reportPath = storage_path("logs/leave_discrepancy_report_{$yearLabel}_" . date('Ymd_His') . '.csv');
            $handle = fopen($reportPath, 'w');
            fputcsv($handle, $headers);
            foreach ($results as $r) {
                fputcsv($handle, $r);
            }
            fclose($handle);

            $this->info("Detailed discrepancy report saved to: {$reportPath}");
        }

        return self::SUCCESS;
    }
}
