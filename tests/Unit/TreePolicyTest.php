<?php

namespace Tests\Unit;

use App\Models\User;
use App\Policies\TreePolicy;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TreePolicyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('GroupQetaa');
        Schema::dropIfExists('PersonGroup');
        Schema::dropIfExists('PersonInformation');

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
            $table->string('ShamandoraCode')->nullable();
        });

        Schema::create('PersonGroup', function (Blueprint $table) {
            $table->increments('PersonGroupID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('GroupID');
        });

        Schema::create('GroupQetaa', function (Blueprint $table) {
            $table->increments('GroupQetaaID');
            $table->unsignedInteger('GroupID');
            $table->unsignedInteger('QetaaID');
        });
    }

    public function test_user_cannot_manage_unserved_qetaa(): void
    {
        $user = User::create([
            'FirstName' => 'A',
            'SecondName' => 'B',
            'ThirdName' => 'C',
            'ShamandoraCode' => 'T1',
        ]);

        DB::table('PersonGroup')->insert(['PersonID' => $user->PersonID, 'GroupID' => 1]);
        DB::table('GroupQetaa')->insert(['GroupID' => 1, 'QetaaID' => 5]);

        $policy = new TreePolicy;

        $this->assertTrue($policy->manageQetaa($user, 5));
        $this->assertFalse($policy->manageQetaa($user, 99));
    }

    public function test_manage_group_requires_served_qetaa(): void
    {
        $user = User::create([
            'FirstName' => 'A',
            'SecondName' => 'B',
            'ThirdName' => 'C',
            'ShamandoraCode' => 'T2',
        ]);

        DB::table('PersonGroup')->insert(['PersonID' => $user->PersonID, 'GroupID' => 2]);
        DB::table('GroupQetaa')->insert(['GroupID' => 2, 'QetaaID' => 7]);
        DB::table('GroupQetaa')->insert(['GroupID' => 9, 'QetaaID' => 99]);

        $policy = new TreePolicy;

        $this->assertTrue($policy->manageGroup($user, 2));
        $this->assertFalse($policy->manageGroup($user, 9));
    }
}
