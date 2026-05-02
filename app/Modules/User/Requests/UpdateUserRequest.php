<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam email string The email of the user. Example: user@example.com
 * @bodyParam password string The password of the user. Example: secret123
 * @bodyParam employee_id integer The ID of the employee to link to the user. Example: 1
 */
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');
        
        // Ensure the ID is properly extracted, whether it's an integer or the Model instance itself due to route model binding
        $userId = is_object($userId) ? $userId->id : $userId;

        return [
            'email' => ['sometimes', 'required', 'email', 'unique:users,email,' . $userId],
            'password' => ['nullable', 'string', 'min:8'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
        ];
    }
}
