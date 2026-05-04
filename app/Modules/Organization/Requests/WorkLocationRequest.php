<?php

namespace App\Modules\Organization\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam name string required The name of the location. Example: Head Office
 */
class WorkLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}
