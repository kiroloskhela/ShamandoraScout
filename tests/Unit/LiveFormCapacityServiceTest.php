<?php

namespace Tests\Unit;

use App\Domain\Enrolment\LiveFormCapacityService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LiveFormCapacityServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('NewUsersInformation');
        Schema::dropIfExists('MarhalaLiveFormLimit');

        Schema::create('MarhalaLiveFormLimit', function (Blueprint $table) {
            $table->unsignedInteger('QetaaID');
            $table->unsignedInteger('SanaMarhalaID');
            $table->integer('Year')->default(0);
            $table->integer('MaxLimit');
        });

        Schema::create('NewUsersInformation', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('PersonID')->default(0);
            $table->unsignedInteger('QetaaID');
            $table->unsignedInteger('SanaMarhalaID');
        });
    }

    public function test_waiting_list_when_no_limit_row(): void
    {
        DB::beginTransaction();
        $this->assertTrue((new LiveFormCapacityService())->shouldUseWaitingList(1, 2));
        DB::rollBack();
    }

    public function test_waiting_list_when_at_capacity(): void
    {
        DB::table('MarhalaLiveFormLimit')->insert([
            'QetaaID' => 1,
            'SanaMarhalaID' => 2,
            'Year' => 0,
            'MaxLimit' => 2,
        ]);
        DB::table('NewUsersInformation')->insert([
            ['PersonID' => 1, 'QetaaID' => 1, 'SanaMarhalaID' => 2],
            ['PersonID' => 2, 'QetaaID' => 1, 'SanaMarhalaID' => 2],
        ]);

        DB::beginTransaction();
        $this->assertTrue((new LiveFormCapacityService())->shouldUseWaitingList(1, 2));
        DB::rollBack();
    }

    public function test_main_list_when_under_capacity(): void
    {
        DB::table('MarhalaLiveFormLimit')->insert([
            'QetaaID' => 1,
            'SanaMarhalaID' => 2,
            'Year' => 0,
            'MaxLimit' => 5,
        ]);
        DB::table('NewUsersInformation')->insert([
            'PersonID' => 1,
            'QetaaID' => 1,
            'SanaMarhalaID' => 2,
        ]);

        DB::beginTransaction();
        $this->assertFalse((new LiveFormCapacityService())->shouldUseWaitingList(1, 2));
        DB::rollBack();
    }
}
