<?php

namespace App\Modules\Employee\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'initial_name' => ['nullable', 'string', 'max:50', 'unique:employees,initial_name'],
            'employee_id_number' => ['required', 'string', 'max:255', 'unique:employees,employee_id_number'],
            'id_card_number' => ['nullable', 'string', 'max:255'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'work_position_id' => ['required', 'exists:work_positions,id'],
            'work_location_id' => ['required', 'exists:work_locations,id'],
            'employee_status_id' => ['nullable', 'exists:employee_statuses,id'],
            'work_employee_status_id' => ['required', 'exists:work_employee_statuses,id'],
            'supervisor_id' => ['nullable', 'exists:employees,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'gender_id' => ['nullable', 'integer'],
            'marital_status_id' => ['nullable', 'integer'],
            'religion_id' => ['nullable', 'integer'],
            'blood_group_id' => ['nullable', 'integer'],
            'place_birth' => ['nullable', 'string', 'max:255'],
            'date_birth' => ['nullable', 'date'],
            'join_date' => ['required', 'date'],
            'resign_date' => ['nullable', 'date'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'handphone' => ['nullable', 'string', 'max:20'],
            'current_address' => ['nullable', 'string'],
            'residence_address' => ['nullable', 'string'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'ktp' => ['nullable', 'string', 'max:1000'],
            'kartu_keluarga' => ['nullable', 'string', 'max:1000'],
            'ijazah' => ['nullable', 'string', 'max:1000'],
            'file_pendukung' => ['nullable', 'array'],
            'file_pendukung.*' => ['string', 'max:1000'],
            'avatar' => ['required', 'file', 'image', 'max:2048'],
        ];
    }

    public function messages()
    {
        return [
            'department_id.exists' => 'Departemen tidak ditemukan.',
            'work_position_id.exists' => 'Jabatan tidak ditemukan.',
            'work_location_id.exists' => 'Lokasi kerja tidak ditemukan.',
            'work_employee_status_id.exists' => 'Status karyawan tidak ditemukan.',
            'supervisor_id.exists' => 'Supervisor tidak ditemukan.',
            'team_id.exists' => 'Tim tidak ditemukan.',
            'email.unique' => 'Email sudah terdaftar.',
            'initial_name.unique' => 'Inisial nama sudah digunakan.',
            'ktp.mimes' => 'KTP harus berupa file PDF, JPG, JPEG, atau PNG.',
            'kartu_keluarga.mimes' => 'Kartu Keluarga harus berupa file PDF, JPG, JPEG, atau PNG.',
            'ijazah.mimes' => 'Ijazah harus berupa file PDF, JPG, JPEG, atau PNG.',
            'file_pendukung.*.mimes' => 'File pendukung harus berupa file PDF, JPG, JPEG, atau PNG.',
            'avatar.mimes' => 'Avatar harus berupa file gambar.',
        ];
    }
}
