<?php

namespace Tests\Unit;

use App\Domain\Enrolment\WaitingListService;
use App\Support\ShamandoraCode;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WaitingListMigrateRemintTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'sqlite') {
            $this->markTestSkipped('sqlite only');
        }

        foreach ([
            'NewUsersPersonEntryQuestions',
            'NewUsersPersonEntryQuestionsWaitinglist',
            'NewUsersInformation',
            'NewUsersInformationWaitinglist',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('NewUsersInformationWaitinglist', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('PersonID');
            $table->string('ShamandoraCode', 10)->nullable();
            $table->string('FirstName')->nullable();
            $table->string('RaqamQawmy')->nullable();
        });

        Schema::create('NewUsersInformation', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('PersonID');
            $table->string('ShamandoraCode', 10)->nullable();
            $table->string('FirstName')->nullable();
            $table->string('RaqamQawmy')->nullable();
        });

        Schema::create('NewUsersPersonEntryQuestionsWaitinglist', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QuestionID');
            $table->string('Answer')->nullable();
        });

        Schema::create('NewUsersPersonEntryQuestions', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QuestionID');
            $table->string('Answer')->nullable();
        });
    }

    public function test_promote_remints_person_id_to_match_surrogate_id(): void
    {
        // Waiting list: id=1 PersonID=1; NewUsers already has id=1 so promote must get id=2
        DB::table('NewUsersInformation')->insert([
            'PersonID' => 1,
            'ShamandoraCode' => ShamandoraCode::forPersonId(1),
            'FirstName' => 'Existing',
            'RaqamQawmy' => '11111111111111',
        ]);

        DB::table('NewUsersInformationWaitinglist')->insert([
            'PersonID' => 1,
            'ShamandoraCode' => ShamandoraCode::forPersonId(1),
            'FirstName' => 'Waiting',
            'RaqamQawmy' => '22222222222222',
        ]);

        DB::table('NewUsersPersonEntryQuestionsWaitinglist')->insert([
            'PersonID' => 1,
            'QuestionID' => 9,
            'Answer' => 'yes',
        ]);

        app(WaitingListService::class)->migrate(1);

        $row = DB::table('NewUsersInformation')->where('RaqamQawmy', '22222222222222')->first();
        $this->assertNotNull($row);
        $this->assertSame((int) $row->id, (int) $row->PersonID);
        $this->assertSame(ShamandoraCode::forPersonId((int) $row->id), $row->ShamandoraCode);
        $this->assertSame(0, DB::table('NewUsersInformationWaitinglist')->count());
        $this->assertSame(
            1,
            DB::table('NewUsersPersonEntryQuestions')->where('PersonID', $row->PersonID)->where('Answer', 'yes')->count()
        );
    }
}
