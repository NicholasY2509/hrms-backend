<?php

namespace App\Modules\Audit\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetAuditLogsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'log_name' => 'nullable|string',
            'event' => 'nullable|string',
            'causer_id' => 'nullable|integer',
            'subject_type' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    /**
     * Define query parameters for Scribe documentation.
     */
    public function queryParameters(): array
    {
        return [
            'log_name' => [
                'description' => 'Filter by log name (e.g., default, auth).',
                'example' => 'default',
            ],
            'event' => [
                'description' => 'Filter by event type.',
                'example' => 'updated',
            ],
            'causer_id' => [
                'description' => 'Filter by the ID of the user who performed the action.',
                'example' => 831,
            ],
            'subject_type' => [
                'description' => 'Filter by the model class name.',
                'example' => 'App\\\\Modules\\\\Organization\\\\Models\\\\Department',
            ],
            'start_date' => [
                'description' => 'Filter logs from this date.',
                'example' => '2024-01-01',
            ],
            'end_date' => [
                'description' => 'Filter logs until this date.',
                'example' => '2024-12-31',
            ],
        ];
    }
}
