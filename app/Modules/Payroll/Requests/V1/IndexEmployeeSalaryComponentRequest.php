<?php

namespace App\Modules\Payroll\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class IndexEmployeeSalaryComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer'],
        ];
    }

    /**
     * @queryParam employee_id int required The ID of the employee to filter components by.
     */
    public function queryParameters(): array
    {
        return [];
    }
}
