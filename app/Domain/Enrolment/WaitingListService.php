<?php

namespace App\Domain\Enrolment;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Waiting-list promote / decline DB work for NewUsersInformationWaitinglist.
 */
class WaitingListService
{
    /**
     * Promote a waiting-list person into NewUsersInformation (and their questions).
     *
     * Package A surrogate `id` is stripped so AUTO_INCREMENT PKs do not collide.
     * PersonID is kept as the business key so linked questions stay valid.
     *
     * @throws RuntimeException when the person is missing or RaqamQawmy already enrolled
     */
    public function migrate(int $personId): void
    {
        DB::transaction(function () use ($personId) {
            $person = DB::table('NewUsersInformationWaitinglist')
                ->where('PersonID', $personId)
                ->lockForUpdate()
                ->first();

            if (!$person) {
                throw new RuntimeException('الشخص غير موجود في قائمة الانتظار');
            }

            $alreadyExists = DB::table('NewUsersInformation')
                ->where('RaqamQawmy', $person->RaqamQawmy)
                ->exists();

            if ($alreadyExists) {
                throw new RuntimeException('الرقم القومي موجود بالفعل في قائمة التسجيل');
            }

            $row = (array) $person;
            unset($row['id']);
            DB::table('NewUsersInformation')->insert($row);

            $waitingQuestions = DB::table('NewUsersPersonEntryQuestionsWaitinglist')
                ->where('PersonID', $personId)
                ->get();

            foreach ($waitingQuestions as $q) {
                DB::table('NewUsersPersonEntryQuestions')->updateOrInsert(
                    ['PersonID' => $q->PersonID, 'QuestionID' => $q->QuestionID],
                    ['Answer' => $q->Answer]
                );
            }

            DB::table('NewUsersPersonEntryQuestionsWaitinglist')->where('PersonID', $personId)->delete();
            DB::table('NewUsersInformationWaitinglist')->where('PersonID', $personId)->delete();
        });
    }

    /**
     * Decline (delete) a person from the waiting list and their answers.
     *
     * @throws RuntimeException when the person is missing
     */
    public function decline(int $personId): void
    {
        DB::transaction(function () use ($personId) {
            $person = DB::table('NewUsersInformationWaitinglist')
                ->where('PersonID', $personId)
                ->first();

            if (!$person) {
                throw new RuntimeException('الشخص غير موجود في قائمة الانتظار');
            }

            DB::table('NewUsersPersonEntryQuestionsWaitinglist')->where('PersonID', $personId)->delete();
            DB::table('NewUsersInformationWaitinglist')->where('PersonID', $personId)->delete();
        });
    }
}
