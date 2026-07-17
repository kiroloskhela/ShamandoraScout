<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustodyRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'qetaa_id' => ['nullable', 'integer'],
            'event_type_id' => ['nullable', 'integer'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'user_note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
