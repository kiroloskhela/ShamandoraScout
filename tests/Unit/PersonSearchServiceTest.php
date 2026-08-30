<?php

namespace Tests\Unit;

use App\Domain\Person\PersonSearchService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PersonSearchServiceTest extends TestCase
{
    private PersonSearchService $search;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();
        $this->search = new PersonSearchService;
    }

    public function test_paginate_all_persons_limits_page_and_does_not_multiply_by_questions(): void
    {
        $answeredId = $this->insertPerson('Answered', 'A1');
        $plainId = $this->insertPerson('Plain', 'P1');
        DB::table('PersonQetaa')->insert([
            ['PersonID' => $answeredId, 'QetaaID' => 1],
            ['PersonID' => $plainId, 'QetaaID' => 1],
        ]);
        DB::table('PersonEntryQuestions')->insert([
            ['PersonID' => $answeredId, 'QuestionID' => 1, 'Answer' => 'yes'],
            ['PersonID' => $answeredId, 'QuestionID' => 2, 'Answer' => 'no'],
            ['PersonID' => $answeredId, 'QuestionID' => 3, 'Answer' => 'maybe'],
        ]);

        $page = $this->search->paginateAllPersons(null, [], 25);

        $this->assertSame(2, $page->total());
        $this->assertCount(2, $page->items());

        $byId = collect($page->items())->keyBy('PersonID');
        $this->assertSame('نعم', $byId[$answeredId]->HasAnsweredQuestions);
        $this->assertSame('لا', $byId[$plainId]->HasAnsweredQuestions);
    }

    public function test_scoped_search_does_not_return_out_of_scope_person(): void
    {
        $viewerId = $this->insertPerson('Viewer', 'V1');
        $insideId = $this->insertPerson('Inside', 'I1');
        $outsideId = $this->insertPerson('OutsideUnique', 'O1');

        DB::table('PersonGroup')->insert(['PersonID' => $viewerId, 'GroupID' => 1]);
        DB::table('GroupQetaa')->insert(['GroupID' => 1, 'QetaaID' => 1]);
        DB::table('PersonQetaa')->insert([
            ['PersonID' => $insideId, 'QetaaID' => 1],
            ['PersonID' => $outsideId, 'QetaaID' => 2],
        ]);

        $page = $this->search->paginateScopedToPerson($viewerId);
        $ids = collect($page->items())->pluck('PersonID')->all();

        $this->assertContains($insideId, $ids);
        $this->assertNotContains($outsideId, $ids);
        $this->assertNotContains($viewerId, $ids);

        $insidePage = $this->search->paginateScopedToPerson($viewerId, 'Inside');
        $this->assertSame([$insideId], collect($insidePage->items())->pluck('PersonID')->all());
    }

    public function test_scoped_filter_options_only_include_visible_sectors(): void
    {
        $viewerId = $this->insertPerson('Viewer', 'V2');
        $insideId = $this->insertPerson('Inside', 'I2');
        $outsideId = $this->insertPerson('Outside', 'O2');

        DB::table('PersonGroup')->insert(['PersonID' => $viewerId, 'GroupID' => 2]);
        DB::table('GroupQetaa')->insert(['GroupID' => 2, 'QetaaID' => 1]);
        DB::table('PersonQetaa')->insert([
            ['PersonID' => $insideId, 'QetaaID' => 1],
            ['PersonID' => $outsideId, 'QetaaID' => 2],
        ]);

        $options = $this->search->directoryFilterOptions($viewerId);

        $this->assertContains('Alpha', $options['QetaaName']);
        $this->assertNotContains('Beta', $options['QetaaName']);
    }

    public function test_paginate_all_persons_pages_results(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $id = $this->insertPerson('P'.$i, sprintf('C%02d', $i));
            DB::table('PersonQetaa')->insert(['PersonID' => $id, 'QetaaID' => 1]);
        }

        $page = $this->search->paginateAllPersons(null, [], 5);

        $this->assertSame(12, $page->total());
        $this->assertCount(5, $page->items());
    }

    private function insertPerson(string $firstName, string $code): int
    {
        return (int) DB::table('PersonInformation')->insertGetId([
            'FirstName' => $firstName,
            'SecondName' => 'N',
            'ThirdName' => 'T',
            'FourthName' => 'F',
            'ShamandoraCode' => $code,
            'ScoutJoiningYear' => 2020,
            'RaqamQawmy' => null,
        ]);
    }

    private function createSchema(): void
    {
        foreach ([
            'PersonEntryQuestions',
            'PersonPhoneNumbers',
            'PersonSanaMarhala',
            'SanaMarhala',
            'PersonQetaa',
            'Qetaa',
            'GroupQetaa',
            'PersonGroup',
            'PersonInformation',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
            $table->string('FourthName')->nullable();
            $table->string('ShamandoraCode')->nullable();
            $table->integer('ScoutJoiningYear')->nullable();
            $table->string('RaqamQawmy')->nullable();
        });
        Schema::create('PersonGroup', function (Blueprint $table) {
            $table->increments('PersonGroupRoleID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('GroupID');
        });
        Schema::create('GroupQetaa', function (Blueprint $table) {
            $table->unsignedInteger('GroupID');
            $table->unsignedInteger('QetaaID');
        });
        Schema::create('Qetaa', function (Blueprint $table) {
            $table->increments('QetaaID');
            $table->string('QetaaName')->nullable();
        });
        Schema::create('PersonQetaa', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID');
        });
        Schema::create('SanaMarhala', function (Blueprint $table) {
            $table->increments('SanaMarhalaID');
            $table->string('SanaMarhalaName')->nullable();
        });
        Schema::create('PersonSanaMarhala', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('SanaMarhalaID');
        });
        Schema::create('PersonPhoneNumbers', function (Blueprint $table) {
            $table->unsignedInteger('PersonID')->primary();
            $table->string('PersonPersonalMobileNumber')->nullable();
            $table->string('FatherMobileNumber')->nullable();
            $table->string('MotherMobileNumber')->nullable();
        });
        Schema::create('PersonEntryQuestions', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QuestionID');
            $table->string('Answer')->nullable();
        });

        DB::table('Qetaa')->insert([
            ['QetaaID' => 1, 'QetaaName' => 'Alpha'],
            ['QetaaID' => 2, 'QetaaName' => 'Beta'],
        ]);
    }
}
