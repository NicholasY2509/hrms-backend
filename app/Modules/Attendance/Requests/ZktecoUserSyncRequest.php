<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ZktecoUserSyncRequest extends FormRequest
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
        ];
    }

    /**
     * Get query parameters for documentation.
     */
    public function bodyParameters(): array
    {
        return [
            'zkteco_machine_id' => [
                'description' => 'The ID of the ZKTeco machine to sync users from.',
                'example' => 1,
            ],
        ];
    }
}
