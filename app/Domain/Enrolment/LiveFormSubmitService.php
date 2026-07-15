<?php

namespace App\Domain\Enrolment;

use App\Support\NewEnrolmentIdentity;
use App\Support\ShamandoraCode;
use Illuminate\Support\Facades\DB;

/**
 * Liveform final submit: capacity decision, PersonID allocation, and question inserts.
 *
 * Callers must already be inside a DB transaction when calling persistSubmission().
 */
class LiveFormSubmitService
{
    public function __construct(
        private LiveFormCapacityService $capacity,
    ) {
    }

    /**
     * Insert a new-enrolment row into $table and return the PersonID assigned to it.
     *
     * Requires Package A's AUTO_INCREMENT surrogate `id` (no MAX+1 fallback).
     * PersonID and ShamandoraCode are set to mirror the minted id.
     */
    public function allocateNewEnrolmentRecord(string $table, array $data): int
    {
        NewEnrolmentIdentity::assertSurrogateAutoIncrementId($table);

        // PersonID has no default and can't be NULL; ShamandoraCode is
        // varchar(10) — use a 10-char placeholder until the real SH- code is set.
        $data['PersonID'] = 0;
        $data['ShamandoraCode'] = bin2hex(random_bytes(5));
        $data['CreatedAt'] = $data['CreatedAt'] ?? now();

        $id = DB::table($table)->insertGetId($data, 'id');

        DB::table($table)->where('id', $id)->update([
            'PersonID' => $id,
            'ShamandoraCode' => ShamandoraCode::forPersonId($id),
        ]);

        return $id;
    }

    /**
     * Decide waiting-list vs main table, allocate the person row, and insert answers.
     *
     * @param  iterable<int, object>  $questions  MarhalaEntryQuestions rows (QuestionID)
     * @param  array<int|string, mixed>  $answers  keyed by QuestionID
     * @return array{person_id: int, is_waiting_list: bool}
     */
    public function persistSubmission(
        array $personData,
        int $qetaaId,
        int $sanaMarhalaId,
        iterable $questions,
        array $answers,
    ): array {
        $isWaitingList = $this->capacity->shouldUseWaitingList($qetaaId, $sanaMarhalaId);

        $targetTable = $isWaitingList ? 'NewUsersInformationWaitinglist' : 'NewUsersInformation';
        $questionsTable = $isWaitingList
            ? 'NewUsersPersonEntryQuestionsWaitinglist'
            : 'NewUsersPersonEntryQuestions';

        $personId = $this->allocateNewEnrolmentRecord($targetTable, $personData);

        foreach ($questions as $question) {
            DB::table($questionsTable)->insert([
                'PersonID' => $personId,
                'QuestionID' => $question->QuestionID,
                'Answer' => $answers[$question->QuestionID] ?? null,
            ]);
        }

        return [
            'person_id' => $personId,
            'is_waiting_list' => $isWaitingList,
        ];
    }
}
