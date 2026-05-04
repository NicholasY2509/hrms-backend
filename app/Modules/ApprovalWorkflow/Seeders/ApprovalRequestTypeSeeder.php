<?php

namespace App\Modules\ApprovalWorkflow\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\ApprovalWorkflow\Models\ApprovalRequestType;

class ApprovalRequestTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Unpaid Leave',
                'slug' => 'unpaid-leave',
                'model_class' => 'App\Modules\Leave\Models\UnpaidLeave',
                'description' => 'Requests for leave without pay.',
                'is_active' => true,
            ],
            [
                'name' => 'Overtime',
                'slug' => 'overtime',
                'model_class' => 'App\Modules\Overtime\Models\Overtime',
                'description' => 'Requests for extra working hours.',
                'is_active' => true,
            ],
            [
                'name' => 'Loan',
                'slug' => 'loan',
                'model_class' => 'App\Modules\Payroll\Models\EmployeeLoan',
                'description' => 'Requests for employee financial loans.',
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            ApprovalRequestType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}
