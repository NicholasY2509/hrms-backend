<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AttendanceWorkingHourImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file'        => ['required', 'file', 'mimes:xls,xlsx'],
            'month'       => ['required'],
            'upload_type' => ['required', 'in:non_security,security'],
            'day_type'    => ['required_if:upload_type,non_security', 'in:Weekday,Weekend'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required'        => 'File tidak boleh kosong!',
            'file.file'            => 'File tidak valid!',
            'file.mimes'           => 'File harus berformat XLS atau XLSX!',
            'month.required'       => 'Bulan tidak boleh kosong!',
            'upload_type.required' => 'Tipe Upload tidak boleh kosong!',
            'upload_type.in'       => 'Tipe Upload tidak valid!',
            'day_type.required_if' => 'Tipe Hari tidak boleh kosong untuk upload Non-Security!',
            'day_type.in'          => 'Tipe Hari tidak valid!',
        ];
    }
}
