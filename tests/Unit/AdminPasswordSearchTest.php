<?php

namespace Tests\Unit;

use App\Support\LikeSearch;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminPasswordSearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('PersonPhoneNumbers');
        Schema::dropIfExists('PersonInformation');

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('ShamandoraCode')->nullable();
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
            $table->string('FourthName')->nullable();
            $table->string('RaqamQawmy')->nullable();
        });

        Schema::create('PersonPhoneNumbers', function (Blueprint $table) {
            $table->unsignedInteger('PersonID')->primary();
            $table->string('PersonPersonalMobileNumber')->nullable();
            $table->string('FatherMobileNumber')->nullable();
            $table->string('MotherMobileNumber')->nullable();
        });
    }

    public function test_flexible_match_finds_by_father_phone_digits(): void
    {
        $id = DB::table('PersonInformation')->insertGetId([
            'ShamandoraCode' => 'SH-99',
            'FirstName' => 'أحمد',
            'SecondName' => 'محمد',
            'ThirdName' => 'علي',
            'FourthName' => 'حسن',
        ]);

        DB::table('PersonPhoneNumbers')->insert([
            'PersonID' => $id,
            'PersonPersonalMobileNumber' => '+201011111111',
            'FatherMobileNumber' => '01099998888',
            'MotherMobileNumber' => null,
        ]);

        $found = DB::table('PersonInformation as pi')
            ->leftJoin('PersonPhoneNumbers as ppn', 'ppn.PersonID', '=', 'pi.PersonID')
            ->where(function ($q) {
                LikeSearch::applyFlexiblePersonMatch($q, '01099998888', 'pi', 'ppn');
            })
            ->value('pi.PersonID');

        $this->assertSame($id, (int) $found);
    }

    public function test_flexible_match_finds_by_partial_name(): void
    {
        $id = DB::table('PersonInformation')->insertGetId([
            'ShamandoraCode' => 'SH-1',
            'FirstName' => 'كريم',
            'SecondName' => 'نبيل',
            'ThirdName' => 'فؤاد',
            'FourthName' => '',
        ]);

        DB::table('PersonPhoneNumbers')->insert([
            'PersonID' => $id,
            'PersonPersonalMobileNumber' => null,
            'FatherMobileNumber' => null,
            'MotherMobileNumber' => null,
        ]);

        $found = DB::table('PersonInformation as pi')
            ->leftJoin('PersonPhoneNumbers as ppn', 'ppn.PersonID', '=', 'pi.PersonID')
            ->where(function ($q) {
                LikeSearch::applyFlexiblePersonMatch($q, 'نبيل', 'pi', 'ppn');
            })
            ->value('pi.PersonID');

        $this->assertSame($id, (int) $found);
    }
}
