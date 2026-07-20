<?php

namespace App\Http\Controllers;

use App\Domain\Enrolment\MigrateEnrolmentService;

class MigrateNewEnrolments extends Controller
{
    public function migrateAll(MigrateEnrolmentService $service)
    {
        $result = $service->migrateAllApproved();

        return view('person.migrate-new-enrolments-status', [
            'result' => $result,
        ]);
    }

    public function migrate($qetaaID, MigrateEnrolmentService $service)
    {
        $result = $service->migrateApprovedForQetaa((int) $qetaaID);

        return view('person.migrate-new-enrolments-status', [
            'result' => $result,
        ]);
    }
}
