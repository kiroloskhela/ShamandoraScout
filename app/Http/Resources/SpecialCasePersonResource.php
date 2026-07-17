<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpecialCasePersonResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'PersonID' => (int) $this->PersonID,
            'ShamandoraCode' => $this->ShamandoraCode,
            'PersonName' => $this->PersonName,
            'FirstName' => $this->FirstName,
            'SecondName' => $this->SecondName,
            'ThirdName' => $this->ThirdName,
            'FourthName' => $this->FourthName,
            'QetaaName' => $this->QetaaName ?? null,
            'ScoutJoiningYear' => $this->ScoutJoiningYear ?? null,
            'SanaMarhalaName' => $this->SanaMarhalaName ?? null,
            'RaqamQawmy' => $this->RaqamQawmy ?? null,
            'PersonPersonalMobileNumber' => $this->PersonPersonalMobileNumber ?? null,
            'QetaaID' => $this->QetaaID ?? null,
            'GroupPersonID' => $this->GroupPersonID ?? null,
            'HasAnsweredQuestions' => $this->HasAnsweredQuestions ?? null,
            'SanaMarhalaID' => $this->SanaMarhalaID ?? null,
        ];
    }
}
