<?php

namespace App\Modules\Payroll\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Modules\Employee\Models\Employee;
use App\Modules\Payroll\Models\SalaryComponent;
use App\Modules\Payroll\Models\EmployeeSalary;
use App\Modules\Payroll\Models\EmployeeTaxProfile;

class PayrollRecoverySeeder extends Seeder
{
    public function run(): void
    {
        // Move truncate outside transaction to avoid implicit commits in MySQL
        DB::table('employee_salaries')->truncate();

        DB::transaction(function () {
            // 1. Restore Basic Salary Components
            $components = [
                ['name' => 'Gaji Pokok', 'code' => 'GAPOK', 'category' => 'allowance', 'type' => 'fixed', 'default_amount' => 0],
                ['name' => 'Uang Makan', 'code' => 'MAKAN', 'category' => 'allowance', 'type' => 'calculated', 'default_amount' => 0],
                ['name' => 'Uang Transport', 'code' => 'TRANSPORT', 'category' => 'allowance', 'type' => 'calculated', 'default_amount' => 0],
            ];

            foreach ($components as $comp) {
                \App\Modules\Payroll\Models\SalaryComponent::updateOrCreate(['code' => $comp['code']], $comp);
            }

            // 2. Seed Tax Masters
            $terA = \App\Modules\Payroll\Models\TaxTerCategory::updateOrCreate(['name' => 'A'], ['description' => 'TK/0, TK/1, K/0']);
            $terB = \App\Modules\Payroll\Models\TaxTerCategory::updateOrCreate(['name' => 'B'], ['description' => 'TK/2, TK/3, K/1, K/2']);
            $terC = \App\Modules\Payroll\Models\TaxTerCategory::updateOrCreate(['name' => 'C'], ['description' => 'K/3']);

            $ptkpData = [
                ['code' => 'TK/0', 'name' => 'Tidak Kawin Tanpa Tanggungan', 'amount' => 54000000, 'ter_category_id' => $terA->id],
                ['code' => 'TK/1', 'name' => 'Tidak Kawin 1 Tanggungan', 'amount' => 58500000, 'ter_category_id' => $terA->id],
                ['code' => 'TK/2', 'name' => 'Tidak Kawin 2 Tanggungan', 'amount' => 63000000, 'ter_category_id' => $terB->id],
                ['code' => 'TK/3', 'name' => 'Tidak Kawin 3 Tanggungan', 'amount' => 67500000, 'ter_category_id' => $terB->id],
                ['code' => 'K/0', 'name' => 'Kawin Tanpa Tanggungan', 'amount' => 58500000, 'ter_category_id' => $terA->id],
                ['code' => 'K/1', 'name' => 'Kawin 1 Tanggungan', 'amount' => 63000000, 'ter_category_id' => $terB->id],
                ['code' => 'K/2', 'name' => 'Kawin 2 Tanggungan', 'amount' => 67500000, 'ter_category_id' => $terB->id],
                ['code' => 'K/3', 'name' => 'Kawin 3 Tanggungan', 'amount' => 72000000, 'ter_category_id' => $terC->id],
            ];

            foreach ($ptkpData as $p) {
                \App\Modules\Payroll\Models\TaxPtkpSetting::updateOrCreate(['code' => $p['code']], $p);
            }

            // 3. Migrate from temp_employee_salaries (Original Data)
            $originalSalaries = DB::table('temp_employee_salaries')->get();
            $restoredCount = 0;

            foreach ($originalSalaries as $original) {
                if (!$original->employee_id) continue;
                
                // Ensure employee exists
                $employee = Employee::find($original->employee_id);
                if (!$employee) continue;
                
                EmployeeSalary::create([
                    'employee_id' => $original->employee_id,
                    'bpjs_base_amount' => $original->amount ?? 0,
                    'actual_base_amount' => $original->real_amount ?? 0,
                    'effective_date' => $original->created_at ?? now(),
                    'reason' => 'Restored from original employee_salaries backup',
                    'is_active' => true,
                    'created_at' => $original->created_at,
                    'updated_at' => $original->updated_at,
                ]);
                $restoredCount++;
            }

            // 4. Restore Tax Profiles from legacy logic
            $allEmployees = Employee::all();
            $taxRestoredCount = 0;

            foreach ($allEmployees as $employee) {
                // Determine Marital Code
                $maritalStatus = DB::table('marital_statuses')->find($employee->marital_status_id);
                $prefix = 'TK';
                if ($maritalStatus) {
                    $name = strtolower($maritalStatus->name);
                    if (str_contains($name, 'nikah') || str_contains($name, 'kawin')) {
                        $prefix = 'K';
                    }
                }

                // Count Dependents
                $dependents = DB::table('employee_families')
                    ->where('employee_id', $employee->id)
                    ->where('is_dependents', 1)
                    ->count();
                
                $ptkpCode = "$prefix/$dependents";
                if ($dependents > 3) $ptkpCode = "$prefix/3";

                $ptkpSetting = \App\Modules\Payroll\Models\TaxPtkpSetting::where('code', $ptkpCode)->first();
                
                if ($ptkpSetting) {
                    EmployeeTaxProfile::updateOrCreate(
                        ['employee_id' => $employee->id],
                        [
                            'ptkp_setting_id' => $ptkpSetting->id,
                            'tax_method' => $employee->is_pph21 ? 'gross' : 'net',
                        ]
                    );
                    $taxRestoredCount++;
                }
            }

            echo "Restored $restoredCount salaries and $taxRestoredCount tax profiles.\n";
        });
    }
}
