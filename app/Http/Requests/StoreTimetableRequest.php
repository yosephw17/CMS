<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTimetableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration' => 'required|integer|min:1',
            'lunch_start' => 'required|date_format:H:i',
            'lunch_end' => 'required|date_format:H:i|after:lunch_start',
            'gap_duration' => 'nullable|integer|min:0',
        ];
    }
}
