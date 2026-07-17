<?php

namespace App\Domain\Enrolment;

use Illuminate\Support\Facades\DB;

class LiveFormWizardService
{
    public function step2Lookups(): array
    {
        return [
            'marahel' => DB::table('Marhala')->get(),
            'rotab' => DB::table('RotbaInformation')->get(),
            'questionTypes' => DB::table('QuestionsTypes')->get(),
            'blood' => DB::table('BloodType')->get(),
            'manateq' => DB::table('Manteqa')->get(),
            'districts' => DB::table('Districts')->get(),
            'faculties' => DB::table('Faculty')->get(),
            'universities' => DB::table('University')->get(),
        ];
    }
}
