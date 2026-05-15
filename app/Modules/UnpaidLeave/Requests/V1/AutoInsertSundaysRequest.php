<?php

namespace App\Modules\UnpaidLeave\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class AutoInsertSundaysRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];
    }
}
