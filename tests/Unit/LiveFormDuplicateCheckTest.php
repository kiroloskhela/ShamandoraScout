<?php

namespace Tests\Unit;

use App\Domain\Enrolment\LiveFormDuplicateCheck;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDOException;
use Tests\TestCase;

class LiveFormDuplicateCheckTest extends TestCase
{
    private LiveFormDuplicateCheck $check;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'PersonInformation',
            'NewUsersInformation',
            'NewUsersInformationWaitinglist',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('RaqamQawmy')->nullable();
        });

        Schema::create('NewUsersInformation', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('PersonID')->nullable();
            $table->string('RaqamQawmy')->nullable();
        });

        Schema::create('NewUsersInformationWaitinglist', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('PersonID')->nullable();
            $table->string('RaqamQawmy')->nullable();
        });

        $this->check = new LiveFormDuplicateCheck;
    }

    public function test_exists_finds_waiting_list_raqam(): void
    {
        DB::table('NewUsersInformationWaitinglist')->insert([
            'PersonID' => 1,
            'RaqamQawmy' => '29501011234567',
        ]);

        $this->assertTrue($this->check->exists('29501011234567'));
        $this->assertFalse($this->check->exists('29999999999999'));
    }

    public function test_exists_finds_person_and_new_users(): void
    {
        DB::table('PersonInformation')->insert([
            'RaqamQawmy' => '111',
        ]);
        DB::table('NewUsersInformation')->insert([
            'PersonID' => 2,
            'RaqamQawmy' => '222',
        ]);

        $this->assertTrue($this->check->exists('111'));
        $this->assertTrue($this->check->exists('222'));
    }

    public function test_is_unique_violation_detects_mysql_style_errors(): void
    {
        $pdo = new PDOException('SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry');
        $pdo->errorInfo = ['23000', 1062, 'Duplicate entry'];

        $e = new QueryException('mysql', 'insert into x', [], $pdo);

        $this->assertTrue($this->check->isUniqueViolation($e));
    }
}
