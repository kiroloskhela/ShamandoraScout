<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'PersonID' => (int) $this->PersonID,
            'ShamandoraCode' => $this->ShamandoraCode,
            'FirstName' => $this->FirstName,
            'SecondName' => $this->SecondName,
            'ThirdName' => $this->ThirdName,
            'FourthName' => $this->FourthName,
            'full_name' => $this->full_name ?? trim(
                "{$this->FirstName} {$this->SecondName} {$this->ThirdName} {$this->FourthName}"
            ),
            'QetaaName' => $this->QetaaName,
            'ScoutJoiningYear' => $this->ScoutJoiningYear,
            'SanaMarhalaName' => $this->SanaMarhalaName,
            'RaqamQawmy' => $this->RaqamQawmy,
            'PersonPersonalMobileNumber' => $this->PersonPersonalMobileNumber,
            'QetaaID' => $this->QetaaID,
            'GroupPersonID' => $this->GroupPersonID,
            'HasAnsweredQuestions' => $this->HasAnsweredQuestions,
            'SanaMarhalaID' => $this->SanaMarhalaID,
            'PersonSystemImagePath' => $this->PersonSystemImagePath ?? null,
        ];
    }
}
