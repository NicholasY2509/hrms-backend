<?php

namespace App\Modules\ApprovalWorkflow\Seeders;

use App\Modules\ApprovalWorkflow\Models\ApprovalStepType;
use Illuminate\Database\Seeder;

class ApprovalStepTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'slug' => 'supervisor',
                'name' => 'Direct Supervisor',
                'needs_target' => false,
                'description' => 'Automatically resolves to the requester\'s supervisor.'
            ],
            [
                'slug' => 'dept_head',
                'name' => 'Department Head',
                'needs_target' => false,
                'description' => 'Automatically resolves to the head of the requester\'s department.'
            ],
            [
                'slug' => 'group',
                'name' => 'Approval Group',
                'needs_target' => true,
                'description' => 'Requires selecting a predefined pool of employees (e.g. HRD Staff).'
            ],
            [
                'slug' => 'employee',
                'name' => 'Specific Employee',
                'needs_target' => true,
                'description' => 'Requires selecting a specific employee by name/NIK.'
            ],
            [
                'slug' => 'work_position',
                'name' => 'Work Position',
                'needs_target' => true,
                'description' => 'Automatically resolves to the employee(s) holding the selected position.'
            ],
        ];

        foreach ($types as $type) {
            ApprovalStepType::updateOrCreate(['slug' => $type['slug']], $type);
        }
    }
}
