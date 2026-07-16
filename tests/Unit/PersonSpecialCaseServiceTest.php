<?php

namespace Tests\Unit;

use App\Domain\SpecialCase\PersonSpecialCaseService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PersonSpecialCaseServiceTest extends TestCase
{
    private PersonSpecialCaseService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('PersonSpecialCase');

        Schema::create('PersonSpecialCase', function (Blueprint $table) {
            $table->increments('SpecialCaseID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('ServentID');
            $table->timestamp('CaseDate');
            $table->text('Note')->nullable();
        });

        $this->service = new PersonSpecialCaseService();
    }

    public function test_create_inserts_special_case(): void
    {
        $specialCaseId = $this->service->create(100, 200, 'Medical exemption');

        $this->assertSame(1, $specialCaseId);

        $case = DB::table('PersonSpecialCase')->where('SpecialCaseID', $specialCaseId)->first();
        $this->assertNotNull($case);
        $this->assertSame(100, (int) $case->PersonID);
        $this->assertSame(200, (int) $case->ServentID);
        $this->assertSame('Medical exemption', $case->Note);
        $this->assertNotNull($case->CaseDate);
    }

    public function test_create_allows_null_note(): void
    {
        $specialCaseId = $this->service->create(101, 201, null);

        $case = DB::table('PersonSpecialCase')->where('SpecialCaseID', $specialCaseId)->first();
        $this->assertNotNull($case);
        $this->assertNull($case->Note);
    }
}
