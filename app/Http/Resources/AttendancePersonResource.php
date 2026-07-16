<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendancePersonResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'PersonID' => (int) ($this['PersonID'] ?? $this->PersonID ?? 0),
            'PersonName' => $this['PersonName'] ?? $this->PersonName ?? '',
            'PhoneNumber' => $this['PhoneNumber'] ?? $this->PhoneNumber ?? '',
            'QetaaName' => $this['QetaaName'] ?? $this->QetaaName ?? '',
            'SanaMarhalaName' => $this['SanaMarhalaName'] ?? $this->SanaMarhalaName ?? '',
            'Status' => $this['Status'] ?? $this->Status ?? 'absent',
            'Excuse' => $this['Excuse'] ?? $this->Excuse ?? null,
        ];
    }
}
