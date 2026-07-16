<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'SeasonEventID' => ['required', 'integer', 'exists:SeasonEvent,SeasonEventID'],
            'attendance' => ['required', 'array'],
            'attendance.*.status' => ['required', 'in:present,absent,excused'],
            'attendance.*.excuse' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
