<?php

namespace App\Domain\Enrolment;

use App\Support\ShamandoraCode;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Waiting-list promote / decline DB work for NewUsersInformationWaitinglist.
 */
class WaitingListService
{
    public function __construct(
        private readonly LiveFormSubmitService $submit,
    ) {}

    /**
     * Promote a waiting-list person into NewUsersInformation (and their questions).
     *
     * Remints Package A `id` / `PersonID` / ShamandoraCode (same as liveform) so
     * `PersonID === id` always holds and collisions across tables are avoided.
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

            if (! $person) {
                throw new RuntimeException('الشخص غير موجود في قائمة الانتظار');
            }

            $alreadyExists = DB::table('NewUsersInformation')
                ->where('RaqamQawmy', $person->RaqamQawmy)
                ->exists();

            if ($alreadyExists) {
                throw new RuntimeException('الرقم القومي موجود بالفعل في قائمة التسجيل');
            }

            $row = (array) $person;
            unset($row['id'], $row['PersonID'], $row['ShamandoraCode']);

            $newId = $this->submit->allocateNewEnrolmentRecord('NewUsersInformation', $row);

            $waitingQuestions = DB::table('NewUsersPersonEntryQuestionsWaitinglist')
                ->where('PersonID', $personId)
                ->get();

            foreach ($waitingQuestions as $q) {
                DB::table('NewUsersPersonEntryQuestions')->updateOrInsert(
                    ['PersonID' => $newId, 'QuestionID' => $q->QuestionID],
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

            if (! $person) {
                throw new RuntimeException('الشخص غير موجود في قائمة الانتظار');
            }

            DB::table('NewUsersPersonEntryQuestionsWaitinglist')->where('PersonID', $personId)->delete();
            DB::table('NewUsersInformationWaitinglist')->where('PersonID', $personId)->delete();
        });
    }

    /**
     * One-off repair: remint PersonID/ShamandoraCode to match surrogate id when safe.
     *
     * @return array{fixed: int, skipped: int}
     */
    public function repairMismatchedPersonIds(string $table = 'NewUsersInformation'): array
    {
        $fixed = 0;
        $skipped = 0;
        $questionsTable = $table === 'NewUsersInformationWaitinglist'
            ? 'NewUsersPersonEntryQuestionsWaitinglist'
            : 'NewUsersPersonEntryQuestions';

        $rows = DB::table($table)->whereColumn('id', '!=', 'PersonID')->orderBy('id')->get();

        foreach ($rows as $row) {
            $oldPersonId = (int) $row->PersonID;
            $newId = (int) $row->id;

            $collision = DB::table($table)
                ->where('PersonID', $newId)
                ->where('id', '!=', $newId)
                ->exists();

            if ($collision) {
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($table, $questionsTable, $oldPersonId, $newId) {
                DB::table($questionsTable)
                    ->where('PersonID', $oldPersonId)
                    ->update(['PersonID' => $newId]);

                DB::table($table)->where('id', $newId)->update([
                    'PersonID' => $newId,
                    'ShamandoraCode' => ShamandoraCode::forPersonId($newId),
                ]);
            });

            $fixed++;
        }

        return ['fixed' => $fixed, 'skipped' => $skipped];
    }
}
