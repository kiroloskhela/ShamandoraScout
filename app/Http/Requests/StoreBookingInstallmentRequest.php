<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreBookingInstallmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('eventBooking.update');
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'يجب إدخال مبلغ الدفعة.',
            'amount.integer' => 'مبلغ الدفعة يجب أن يكون رقمًا صحيحًا بدون قروش.',
            'amount.min' => 'يجب أن يكون مبلغ الدفعة أكبر من صفر.',
        ];
    }
}
