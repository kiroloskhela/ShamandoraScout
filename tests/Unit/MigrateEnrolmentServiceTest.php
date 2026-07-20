<?php

namespace Tests\Unit;

use App\Domain\Enrolment\MigrateEnrolmentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class MigrateEnrolmentServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('NewUsersInformation');

        Schema::create('NewUsersInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->unsignedInteger('QetaaID');
            $table->boolean('IsApproved')->default(false);
        });
    }

    public function test_one_failure_does_not_stop_other_migrations(): void
    {
        DB::table('NewUsersInformation')->insert([
            ['PersonID' => 1, 'QetaaID' => 10, 'IsApproved' => 1],
            ['PersonID' => 2, 'QetaaID' => 10, 'IsApproved' => 1],
            ['PersonID' => 3, 'QetaaID' => 10, 'IsApproved' => 1],
        ]);

        $service = $this->partialMock(MigrateEnrolmentService::class, function ($mock) {
            $mock->shouldReceive('migrateOneById')
                ->with(1)->once()->andReturn(101)
                ->shouldReceive('migrateOneById')
                ->with(2)->once()->andThrow(new RuntimeException('duplicate key'))
                ->shouldReceive('migrateOneById')
                ->with(3)->once()->andReturn(103);
        });

        $result = $service->migrateApprovedForQetaa(10);

        $this->assertSame(2, $result->migrated_count);
        $this->assertSame(1, $result->failed_count);
        $this->assertCount(1, $result->failures);
        $this->assertSame(2, $result->failures[0]['person_id']);
        $this->assertSame('duplicate key', $result->failures[0]['message']);
    }
}
