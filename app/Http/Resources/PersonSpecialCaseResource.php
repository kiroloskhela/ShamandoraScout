<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonSpecialCaseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'SpecialCaseID' => (int) $this->SpecialCaseID,
            'PersonID' => (int) $this->PersonID,
            'ServentID' => (int) $this->ServentID,
            'CaseDate' => $this->CaseDate,
            'Note' => $this->Note,
            'PersonName' => $this->PersonName ?? null,
            'ServentName' => $this->ServentName ?? null,
        ];
    }
}
