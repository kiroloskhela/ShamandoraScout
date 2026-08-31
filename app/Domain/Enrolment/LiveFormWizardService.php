<?php

namespace App\Domain\Enrolment;

use App\Support\LookupCache;

class LiveFormWizardService
{
    public function step2Lookups(): array
    {
        return [
            'marahel' => LookupCache::all('Marhala'),
            'rotab' => LookupCache::all('RotbaInformation'),
            'questionTypes' => LookupCache::all('QuestionsTypes'),
            'blood' => LookupCache::all('BloodType'),
            'manateq' => LookupCache::all('Manteqa'),
            'districts' => LookupCache::all('Districts'),
            'faculties' => LookupCache::all('Faculty'),
            'universities' => LookupCache::all('University'),
        ];
    }
}
