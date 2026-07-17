<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeasonEventBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_type' => ['required', 'in:PERSON,GUEST,FAMILY'],
            'person_id' => ['nullable', 'integer', 'exists:PersonInformation,PersonID'],
            'guest_id' => ['nullable', 'integer', 'exists:Guests,GuestID'],
            'family_id' => ['nullable', 'integer', 'exists:FamilyMembers,FamilyID'],
            'first_payment_date' => ['required', 'date'],
            'first_payment_amount' => ['required', 'numeric'],
            'is_not_able_to_pay_all' => ['nullable', 'in:0,1'],
            'special_case_type' => ['nullable', 'in:NONE,AKHOH_RAB,HAS_BROTHERS,OTHER'],
            'discount_amount' => ['nullable', 'numeric'],
            'special_case_note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'booking_type.required' => 'يجب اختيار نوع الحجز.',
            'booking_type.in' => 'نوع الحجز غير صحيح.',
            'first_payment_date.required' => 'تاريخ أول دفعة مطلوب.',
            'first_payment_date.date' => 'تاريخ أول دفعة غير صحيح.',
            'first_payment_amount.required' => 'يجب إدخال مبلغ أول دفعة.',
            'first_payment_amount.min' => 'يجب أن يكون مبلغ أول دفعة أكبر من صفر.',
            'discount_amount.min' => 'الخصم لا يمكن أن يكون أقل من صفر.',
            'special_case_note.max' => 'الملاحظات يجب ألا تتجاوز 500 حرف.',
        ];
    }
}
