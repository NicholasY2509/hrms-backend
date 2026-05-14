<?php

namespace App\Modules\Employee\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateNikRequest extends FormRequest
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
            'work_position_id' => 'required|exists:work_positions,id',
        ];
    }

    /**
     * Get query parameters for documentation.
     */
    public function queryParameters(): array
    {
        return [
            'work_position_id' => [
                'description' => 'The ID of the work position to base the NIK generation on.',
                'example' => 1,
            ],
        ];
    }
}
