<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LookupStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            $this->lookupRequestField() => ['required', 'string', 'max:255'],
        ];
    }

    public function lookupRequestField(): string
    {
        $routeName = (string) $this->route()?->getName();

        foreach (config('lookups', []) as $config) {
            $prefix = $config['route'] ?? null;
            if ($prefix && str_starts_with($routeName, $prefix.'.')) {
                return $config['request_field'];
            }
        }

        return 'name';
    }
}
