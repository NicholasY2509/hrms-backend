<?php

namespace App\Modules\Organization\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkPositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'alias' => ['nullable', 'string', 'max:255'],
            'prefix' => ['nullable', 'string', 'max:10'],
            'uang_makan' => ['required', 'numeric', 'min:0'],
            'potongan_uang_makan' => ['required', 'numeric', 'min:0'],
            'uang_transport' => ['required', 'numeric', 'min:0'],
            'potongan_uang_transport' => ['required', 'numeric', 'min:0'],
            'tunjangan_jabatan' => ['nullable', 'numeric', 'min:0'],
            'tunjangan_kerajinan' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'pengalaman' => ['nullable', 'string'],
            'lokasi' => ['nullable', 'string'],
            'criteria' => ['nullable', 'array'],
            'criteria.*.name' => ['required', 'string', 'max:255'],
        ];
    }
}
