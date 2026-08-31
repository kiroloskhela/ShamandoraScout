<?php

namespace Tests\Unit;

use App\Support\LookupCache;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class LookupCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('BloodType');
        Schema::create('BloodType', function (Blueprint $table) {
            $table->increments('BloodTypeID');
            $table->string('BloodTypeName');
        });

        Cache::flush();
    }

    public function test_unknown_table_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        LookupCache::all('PersonInformation');
    }

    public function test_all_returns_rows_and_survives_db_change_until_forget(): void
    {
        DB::table('BloodType')->insert(['BloodTypeName' => 'A+']);

        $this->assertSame(['A+'], LookupCache::all('BloodType')->pluck('BloodTypeName')->all());

        DB::table('BloodType')->insert(['BloodTypeName' => 'O-']);
        $this->assertSame(['A+'], LookupCache::all('BloodType')->pluck('BloodTypeName')->all());

        LookupCache::forget('BloodType');
        $this->assertEqualsCanonicalizing(
            ['A+', 'O-'],
            LookupCache::all('BloodType')->pluck('BloodTypeName')->all()
        );
    }

    public function test_mutating_a_returned_row_does_not_pollute_the_cache(): void
    {
        DB::table('BloodType')->insert(['BloodTypeName' => 'B+']);

        $first = LookupCache::all('BloodType')->first();
        $first->BloodTypeName = 'mutated';

        $this->assertSame('B+', LookupCache::all('BloodType')->first()->BloodTypeName);
    }
}
