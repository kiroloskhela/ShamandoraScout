<?php

namespace Tests\Feature;

use App\Domain\Enrolment\LiveFormQetaaResolver;
use App\Http\Controllers\GroupPersonController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GroupPersonKhademPickerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['PersonQetaa', 'PersonInformation', 'GroupRole', 'GroupTable', 'GroupType'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
            $table->string('ShamandoraCode')->nullable();
        });
        Schema::create('PersonQetaa', function (Blueprint $table) {
            $table->increments('PersonQetaaID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID');
        });
        Schema::create('GroupType', function (Blueprint $table) {
            $table->unsignedInteger('GroupTypeID')->primary();
            $table->string('GroupTypeName')->nullable();
        });
        Schema::create('GroupTable', function (Blueprint $table) {
            $table->unsignedInteger('GroupID')->primary();
            $table->unsignedInteger('GroupTypeID')->nullable();
            $table->unsignedInteger('IncludedUnderGroupID')->default(0);
            $table->string('GroupName')->nullable();
        });
        Schema::create('GroupRole', function (Blueprint $table) {
            $table->increments('GroupRoleID');
            $table->string('GroupRoleName')->nullable();
            $table->boolean('isKhademRole')->default(0);
        });
    }

    public function test_khadem_picker_includes_qada_and_eadad_qada_only(): void
    {
        $qada = $this->person('Qada', 'Q7');
        $eadad = $this->person('Eadad', 'Q10');
        $cub = $this->person('Cub', 'C2');

        DB::table('PersonQetaa')->insert([
            ['PersonID' => $qada->PersonID, 'QetaaID' => LiveFormQetaaResolver::QETAA_QADA],
            ['PersonID' => $eadad->PersonID, 'QetaaID' => LiveFormQetaaResolver::QETAA_EADAD_QADA],
            ['PersonID' => $cub->PersonID, 'QetaaID' => LiveFormQetaaResolver::QETAA_ASHBAL],
        ]);

        $view = app(GroupPersonController::class)->createKhadem();
        $ids = collect($view->getData()['persons'])->pluck('PersonID')->all();

        $this->assertContains($qada->PersonID, $ids);
        $this->assertContains($eadad->PersonID, $ids);
        $this->assertNotContains($cub->PersonID, $ids);
    }

    private function person(string $first, string $code): User
    {
        return User::create([
            'FirstName' => $first,
            'SecondName' => 'A',
            'ThirdName' => 'X',
            'ShamandoraCode' => $code,
        ]);
    }
}
