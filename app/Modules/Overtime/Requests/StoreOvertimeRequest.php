<?php

namespace App\Modules\Overtime\Requests;

use App\Modules\Overtime\Models\Overtime;
use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class StoreOvertimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'type' => 'required|in:' . Overtime::TYPE_GENERAL . ',' . Overtime::TYPE_DAC . ',' . Overtime::TYPE_HOLIDAY,
            'overtime_type_id' => 'nullable|exists:overtime_types,id',
            'start_time' => 'required|string',
            'finish_time' => 'required|string',
            'note' => 'nullable|string',
            'estimated_cost' => 'nullable|numeric',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $startTime = $this->input('start_time');
            $finishTime = $this->input('finish_time');
            $date = $this->input('date');

            if ($startTime && $finishTime && $date) {
                $start = str_contains($startTime, '-') ? Carbon::parse($startTime) : Carbon::parse("$date $startTime");
                $finish = str_contains($finishTime, '-') ? Carbon::parse($finishTime) : Carbon::parse("$date $finishTime");

                if ($finish->lessThan($start)) {
                    $finish->addDay();
                }

                $diffInMinutes = $start->diffInMinutes($finish);

                // Minimal 2 hours (120 minutes) for all except DAC
                if ($this->input('type') !== Overtime::TYPE_DAC && $diffInMinutes < 120) {
                    $validator->errors()->add('finish_time', 'Total waktu lembur tidak boleh kurang dari 2 jam!');
                }

                // Maximal 4 hours for non-holiday and non-DAC
                if (!in_array($this->input('type'), [Overtime::TYPE_HOLIDAY, Overtime::TYPE_DAC]) && $diffInMinutes > 240) {
                    $validator->errors()->add('finish_time', 'Total waktu lembur tidak boleh lebih dari 4 jam!');
                }
            }
        });
    }
}
