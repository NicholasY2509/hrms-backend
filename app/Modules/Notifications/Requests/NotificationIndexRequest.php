<?php

namespace App\Modules\Notifications\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationIndexRequest extends FormRequest
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
            'unread_only' => 'nullable|string|in:true,false',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    /**
     * @queryParam unread_only boolean Filter to only unread notifications. Example: true
     * @queryParam per_page integer Number of items per page. Example: 20
     */
    public function queryParameters(): array
    {
        return [
            'unread_only' => [
                'description' => 'Filter to only unread notifications.',
                'example' => 'true',
            ],
            'per_page' => [
                'description' => 'Number of items per page.',
                'example' => 20,
            ],
        ];
    }
}
