<?php

namespace App\Modules\Employee\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupervisorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id|unique:supervisors,employee_id',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan wajib dipilih',
            'employee_id.exists' => 'Karyawan tidak ditemukan',
            'employee_id.unique' => 'Karyawan ini sudah terdaftar sebagai atasan',
        ];
    }
}
