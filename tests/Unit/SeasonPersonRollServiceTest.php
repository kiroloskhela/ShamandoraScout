<?php

namespace Tests\Unit;

use App\Domain\Enrolment\LiveFormQetaaResolver;
use App\Domain\Season\ActiveSeason;
use App\Domain\Season\SeasonPersonRollService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SeasonPersonRollServiceTest extends TestCase
{
    private SeasonPersonRollService $service;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'season_person_roll_snapshots',
            'season_person_roll_batches',
            'PersonGroup',
            'PersonQetaa',
            'PersonSanaMarhala',
            'PersonInformation',
            'SanaMarhala',
            'Qetaa',
            'Season',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('Season', function (Blueprint $table) {
            $table->increments('SeasonID');
            $table->string('SeasonName')->nullable();
            $table->integer('SeasonYear');
            $table->unsignedTinyInteger('IsActive')->default(0);
        });

        Schema::create('Qetaa', function (Blueprint $table) {
            $table->increments('QetaaID');
            $table->string('QetaaName');
        });

        Schema::create('SanaMarhala', function (Blueprint $table) {
            $table->increments('SanaMarhalaID');
            $table->unsignedInteger('SanaID');
            $table->unsignedInteger('MarhalaID');
            $table->string('SanaMarhalaName');
        });

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
            $table->string('Gender')->nullable();
        });

        Schema::create('PersonSanaMarhala', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('SanaMarhalaID');
            $table->primary(['PersonID', 'SanaMarhalaID']);
        });

        Schema::create('PersonQetaa', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID');
            $table->primary(['PersonID', 'QetaaID']);
        });

        Schema::create('PersonGroup', function (Blueprint $table) {
            $table->increments('PersonGroupRoleID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('GroupID');
            $table->unsignedInteger('GroupRoleID');
        });

        Schema::create('season_person_roll_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('season_id');
            $table->unsignedInteger('ran_by')->nullable();
            $table->string('status', 32)->default('applied');
            $table->unsignedInteger('persons_count')->default(0);
            $table->unsignedInteger('qetaa_changed_count')->default(0);
            $table->unsignedInteger('groups_cleared_count')->default(0);
            $table->timestamps();
        });

        Schema::create('season_person_roll_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id');
            $table->unsignedInteger('person_id');
            $table->unsignedInteger('old_sana_marhala_id')->nullable();
            $table->unsignedInteger('new_sana_marhala_id')->nullable();
            $table->unsignedInteger('old_qetaa_id')->nullable();
            $table->unsignedInteger('new_qetaa_id')->nullable();
            $table->json('cleared_person_group_json')->nullable();
            $table->string('jump_type', 32)->nullable();
            $table->timestamps();
        });

        foreach ([
            [1, 'براعم'], [2, 'أشبال'], [3, 'متقدم'], [4, 'رائدات'], [5, 'جوالة'],
            [6, 'مرشدات'], [7, 'قادة'], [8, 'كشافة'], [9, 'زهرات'], [10, 'اعداد قادة'],
        ] as [$id, $name]) {
            DB::table('Qetaa')->insert(['QetaaID' => $id, 'QetaaName' => $name]);
        }

        $ladder = [
            [3, 1, 2, 'أولى ابتدائي'],
            [4, 2, 2, 'ثانية ابتدائي'],
            [5, 3, 2, 'ثالثة ابتدائي'],
            [6, 4, 2, 'رابعة ابتدائي'],
            [7, 5, 2, 'خامسة ابتدائي'],
            [8, 6, 2, 'سادسة ابتدائي'],
            [9, 1, 3, 'أولى اعدادي'],
            [10, 2, 3, 'ثانية اعدادي'],
            [11, 3, 3, 'ثالثة اعدادي'],
            [12, 1, 4, 'أولى ثانوي'],
            [13, 2, 4, 'ثانية ثانوي'],
            [14, 3, 4, 'ثالثة ثانوي'],
            [15, 1, 5, 'أولى جامعة'],
            [16, 2, 5, 'ثانية جامعة'],
            [17, 3, 5, 'ثالثة جامعة'],
            [18, 4, 5, 'رابعة جامعة'],
            [19, 5, 5, 'خامسة جامعة'],
            [20, 6, 5, 'سادسة جامعة'],
            [21, 1, 6, 'خريج'],
        ];
        foreach ($ladder as [$id, $sana, $marhala, $name]) {
            DB::table('SanaMarhala')->insert([
                'SanaMarhalaID' => $id,
                'SanaID' => $sana,
                'MarhalaID' => $marhala,
                'SanaMarhalaName' => $name,
            ]);
        }

        DB::table('Season')->insert([
            ['SeasonID' => 1, 'SeasonName' => 'موسم 2026', 'SeasonYear' => 2026, 'IsActive' => 1],
            ['SeasonID' => 2, 'SeasonName' => 'موسم 2027', 'SeasonYear' => 2027, 'IsActive' => 0],
        ]);

        $this->service = new SeasonPersonRollService(new LiveFormQetaaResolver);
    }

    public function test_activate_keeps_single_active_season(): void
    {
        (new ActiveSeason)->activate(2);

        $this->assertSame(0, (int) DB::table('Season')->where('SeasonID', 1)->value('IsActive'));
        $this->assertSame(1, (int) DB::table('Season')->where('SeasonID', 2)->value('IsActive'));
        $this->assertSame(2, (new ActiveSeason)->id());
    }

    public function test_baraem_to_ashbal_cross_jump(): void
    {
        $plan = $this->service->planPerson(4, 1, 'Male');

        $this->assertFalse($plan['skip']);
        $this->assertSame(5, $plan['new_sana_marhala_id']);
        $this->assertSame(2, $plan['new_qetaa_id']);
        $this->assertSame(SeasonPersonRollService::JUMP_QETAA_CROSS, $plan['jump_type']);
    }

    public function test_ashbal_female_to_zahrat_band(): void
    {
        $plan = $this->service->planPerson(4, 1, 'Female');

        $this->assertSame(5, $plan['new_sana_marhala_id']);
        $this->assertSame(9, $plan['new_qetaa_id']);
    }

    public function test_elementary_to_prep_cross_jump(): void
    {
        $plan = $this->service->planPerson(8, 2, 'Male');

        $this->assertSame(9, $plan['new_sana_marhala_id']);
        $this->assertSame(8, $plan['new_qetaa_id']);
        $this->assertSame(SeasonPersonRollService::JUMP_QETAA_CROSS, $plan['jump_type']);
    }

    public function test_prep_to_secondary_cross_jump(): void
    {
        $plan = $this->service->planPerson(11, 6, 'Female');

        $this->assertSame(12, $plan['new_sana_marhala_id']);
        $this->assertSame(4, $plan['new_qetaa_id']);
        $this->assertSame(SeasonPersonRollService::JUMP_QETAA_CROSS, $plan['jump_type']);
    }

    public function test_secondary_3_to_university_and_eadad_qada(): void
    {
        $plan = $this->service->planPerson(14, 3, 'Male');

        $this->assertSame(15, $plan['new_sana_marhala_id']);
        $this->assertSame(10, $plan['new_qetaa_id']);
        $this->assertSame(SeasonPersonRollService::JUMP_TO_EADAD_QADA, $plan['jump_type']);
    }

    public function test_frozen_eadad_qada_academic_only(): void
    {
        $plan = $this->service->planPerson(15, 10, 'Female');

        $this->assertSame(16, $plan['new_sana_marhala_id']);
        $this->assertSame(10, $plan['new_qetaa_id']);
        $this->assertSame(SeasonPersonRollService::JUMP_SANA_ONLY, $plan['jump_type']);
    }

    public function test_frozen_qada_and_jawwala(): void
    {
        $qada = $this->service->planPerson(16, 7, 'Male');
        $jawwala = $this->service->planPerson(16, 5, 'Female');

        $this->assertSame(7, $qada['new_qetaa_id']);
        $this->assertSame(5, $jawwala['new_qetaa_id']);
        $this->assertSame(17, $qada['new_sana_marhala_id']);
    }

    public function test_graduate_is_noop(): void
    {
        $plan = $this->service->planPerson(21, 7, 'Male');

        $this->assertTrue($plan['skip']);
        $this->assertSame('already_graduate', $plan['skip_reason']);
    }

    public function test_apply_clears_groups_and_rollback_restores(): void
    {
        DB::table('PersonInformation')->insert([
            'PersonID' => 100,
            'FirstName' => 'Test',
            'SecondName' => 'Person',
            'ThirdName' => 'One',
            'Gender' => 'Male',
        ]);
        DB::table('PersonSanaMarhala')->insert(['PersonID' => 100, 'SanaMarhalaID' => 4]);
        DB::table('PersonQetaa')->insert(['PersonID' => 100, 'QetaaID' => 1]);
        DB::table('PersonGroup')->insert([
            'PersonID' => 100,
            'GroupID' => 55,
            'GroupRoleID' => 1,
        ]);

        $result = $this->service->apply(1, 100);
        $batchId = $result['batch_id'];

        $this->assertSame(5, (int) DB::table('PersonSanaMarhala')->where('PersonID', 100)->value('SanaMarhalaID'));
        $this->assertSame(2, (int) DB::table('PersonQetaa')->where('PersonID', 100)->value('QetaaID'));
        $this->assertSame(0, DB::table('PersonGroup')->where('PersonID', 100)->count());

        $this->service->rollback($batchId);

        $this->assertSame(4, (int) DB::table('PersonSanaMarhala')->where('PersonID', 100)->value('SanaMarhalaID'));
        $this->assertSame(1, (int) DB::table('PersonQetaa')->where('PersonID', 100)->value('QetaaID'));
        $this->assertSame(1, DB::table('PersonGroup')->where('PersonID', 100)->count());
        $this->assertSame(55, (int) DB::table('PersonGroup')->where('PersonID', 100)->value('GroupID'));
        $this->assertSame('rolled_back', DB::table('season_person_roll_batches')->where('id', $batchId)->value('status'));
    }

    public function test_second_apply_blocked_until_rollback(): void
    {
        DB::table('PersonInformation')->insert([
            'PersonID' => 200,
            'FirstName' => 'A',
            'SecondName' => 'B',
            'ThirdName' => 'C',
            'Gender' => 'Male',
        ]);
        DB::table('PersonSanaMarhala')->insert(['PersonID' => 200, 'SanaMarhalaID' => 12]);
        DB::table('PersonQetaa')->insert(['PersonID' => 200, 'QetaaID' => 3]);

        $this->service->apply(1, null);

        $preview = $this->service->preview(1);
        $this->assertNotNull($preview['blocked_reason']);

        $this->expectException(\RuntimeException::class);
        $this->service->apply(1, null);
    }
}
