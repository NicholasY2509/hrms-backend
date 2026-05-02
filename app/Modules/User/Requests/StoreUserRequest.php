<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam email string required The email of the user. Example: user@example.com
 * @bodyParam password string required The password of the user. Example: secret123
 * @bodyParam employee_id integer The ID of the employee to link to the user. Example: 1
 */
class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
        ];
    }
}
