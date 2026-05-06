<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam search string Filter by name or UID. Example: Budi
 * @queryParam zkteco_machine_id int Filter by machine ID. Example: 1
 * @queryParam per_page int Results per page. Default: 15. Example: 15
 */
class ZktecoUserIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'zkteco_machine_id' => 'nullable|integer|exists:zkteco_machines,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
