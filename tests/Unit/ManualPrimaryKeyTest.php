<?php

namespace Tests\Unit;

use App\Support\ManualPrimaryKey;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ManualPrimaryKeyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('ManualPkProbe');
        Schema::create('ManualPkProbe', function (Blueprint $table) {
            $table->unsignedInteger('ProbeID');
            $table->string('Name')->nullable();
        });
    }

    public function test_next_starts_at_one_when_empty(): void
    {
        $this->assertSame(1, ManualPrimaryKey::next('ManualPkProbe', 'ProbeID'));
    }

    public function test_next_increments_from_max(): void
    {
        DB::table('ManualPkProbe')->insert(['ProbeID' => 7, 'Name' => 'a']);
        DB::table('ManualPkProbe')->insert(['ProbeID' => 3, 'Name' => 'b']);

        $this->assertSame(8, ManualPrimaryKey::next('ManualPkProbe', 'ProbeID'));
    }
}
