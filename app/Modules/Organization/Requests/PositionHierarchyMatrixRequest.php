<?php

namespace App\Modules\Organization\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PositionHierarchyMatrixRequest extends FormRequest
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
            'work_location_id' => [
                'nullable', 
                'integer', 
                'exists:work_locations,id'
            ],
            'department_id' => [
                'required', 
                'integer', 
                'exists:departments,id'
            ],
            'work_position_id' => [
                'required', 
                'integer', 
                'exists:work_positions,id'
            ],
            'supervisor_work_position_id' => [
                'required', 
                'integer', 
                'exists:work_positions,id'
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $id = $this->route('position_hierarchy_matrix');
            $locationId = $this->input('work_location_id');
            $deptId = $this->input('department_id');
            $posId = $this->input('work_position_id');
            
            $query = \App\Modules\Organization\Models\PositionHierarchyMatrix::where('department_id', $deptId)
                ->where('work_position_id', $posId);
                
            if ($locationId === null) {
                $query->whereNull('work_location_id');
            } else {
                $query->where('work_location_id', $locationId);
            }

            if ($id) {
                $query->where('id', '!=', $id);
            }

            if ($query->exists()) {
                $validator->errors()->add('work_position_id', 'The combination of location, department, and position already exists in the matrix.');
            }
        });
    }
}
