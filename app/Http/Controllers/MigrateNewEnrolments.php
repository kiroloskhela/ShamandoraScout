<?php

namespace App\Http\Controllers;

use App\Domain\Enrolment\MigrateEnrolmentService;
use App\Http\Controllers\Controller;
use Throwable;

class MigrateNewEnrolments extends Controller
{
    public function migrateAll(MigrateEnrolmentService $service)
    {
        try {
            $service->migrateAllApproved();
        } catch (Throwable $e) {
            return view('person.entry-error');
        }

        return view('person.migrate-new-enrolments-status');
    }

    public function migrate($qetaaID, MigrateEnrolmentService $service)
    {
        try {
            $service->migrateApprovedForQetaa((int) $qetaaID);
        } catch (Throwable $e) {
            return view('person.entry-error');
        }

        return view('person.migrate-new-enrolments-status');
    }
}
