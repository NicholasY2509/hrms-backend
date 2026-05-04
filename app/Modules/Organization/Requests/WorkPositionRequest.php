<?php

namespace App\Modules\Organization\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam name string required The name of the position. Example: Senior Developer
 * @bodyParam alias string The alias of the position. Example: SR-DEV
 * @bodyParam prefix string Short prefix for employee IDs. Example: SD
 * @bodyParam uang_makan number required Daily meal allowance. Example: 50000
 * @bodyParam potongan_uang_makan number required Deduction if absent. Example: 50000
 * @bodyParam uang_transport number required Daily transport allowance. Example: 25000
 * @bodyParam potongan_uang_transport number required Deduction if absent. Example: 25000
 * @bodyParam tunjangan_jabatan number Job title allowance. Example: 1000000
 * @bodyParam tunjangan_kerajinan number attendance bonus. Example: 500000
 * @bodyParam description string Job description.
 * @bodyParam pengalaman string Required experience.
 * @bodyParam lokasi string Work location preference.
 * @bodyParam criteria array List of assessment criteria.
 * @bodyParam criteria.*.name string required Name of the criteria.
 */
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
