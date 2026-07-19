<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('person_id')) {
            $this->merge([
                'person_id' => preg_replace('/\D+/', '', (string) $this->input('person_id')),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'person_id' => ['required', 'string', 'regex:/^\d+$/', 'max:20'],
            'person_password' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'person_id.required' => __('Please enter your person ID.'),
            'person_id.regex' => __('Person ID must contain numbers only.'),
            'person_id.max' => __('Person ID must contain numbers only.'),
            'person_password.required' => __('Please enter your password.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'person_id' => __('Person ID'),
            'person_password' => __('Password'),
        ];
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @return array<string, mixed>
     */
    public function getCredentials(): array
    {
        return $this->only('person_id', 'person_password');
    }
}
