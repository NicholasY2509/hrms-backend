<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ZktecoAttendanceSyncRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'zkteco_machine_id' => ['required', 'exists:zkteco_machines,id'],
            'start_date'        => ['required', 'date', 'date_format:Y-m-d'],
            'end_date'          => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }

    /**
     * Get body parameters for documentation.
     */
    public function bodyParameters(): array
    {
        return [
            'zkteco_machine_id' => [
                'description' => 'The ID of the ZKTeco machine to sync attendance logs from.',
                'example' => 1,
            ],
            'start_date' => [
                'description' => 'The start date for syncing logs (YYYY-MM-DD).',
                'example' => '2026-05-19',
            ],
            'end_date' => [
                'description' => 'The end date for syncing logs (YYYY-MM-DD).',
                'example' => '2026-05-19',
            ],
        ];
    }
}
