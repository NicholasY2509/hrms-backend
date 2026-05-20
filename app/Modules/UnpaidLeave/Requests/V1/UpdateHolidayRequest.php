<?php

namespace App\Modules\UnpaidLeave\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'name' => 'sometimes|required|string|max:255',
            'date' => 'sometimes|required|date|unique:holidays,date,' . $id,
            'description' => 'nullable|string',
            'is_half_day' => 'boolean',
        ];
    }
}
