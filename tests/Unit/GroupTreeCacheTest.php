<?php

namespace Tests\Unit;

use App\Domain\OrgTree\GroupTreeService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GroupTreeCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['GroupQetaa', 'PersonGroup', 'GroupTable', 'GroupType'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('GroupType', function (Blueprint $table) {
            $table->unsignedInteger('GroupTypeID')->primary();
            $table->string('GroupTypeName')->nullable();
        });

        Schema::create('GroupTable', function (Blueprint $table) {
            $table->unsignedInteger('GroupID')->primary();
            $table->unsignedInteger('GroupTypeID');
            $table->unsignedInteger('IncludedUnderGroupID')->default(0);
            $table->string('GroupName')->nullable();
        });

        Schema::create('GroupQetaa', function (Blueprint $table) {
            $table->increments('GroupQetaaID');
            $table->unsignedInteger('GroupID');
            $table->unsignedInteger('QetaaID');
        });

        Schema::create('PersonGroup', function (Blueprint $table) {
            $table->increments('PersonGroupID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('GroupID');
        });

        DB::table('GroupType')->insert([
            'GroupTypeID' => 2,
            'GroupTypeName' => 'Team',
        ]);
        DB::table('GroupTable')->insert([
            'GroupID' => 10,
            'GroupTypeID' => 2,
            'IncludedUnderGroupID' => 0,
            'GroupName' => 'Old',
        ]);

        Cache::flush();
    }

    public function test_warm_uses_cache_until_bust(): void
    {
        $tree = new GroupTreeService();
        $this->assertSame('Team Old', $tree->parentsPathString(10));

        DB::table('GroupTable')->where('GroupID', 10)->update(['GroupName' => 'Direct']);

        $cached = new GroupTreeService();
        $this->assertSame('Team Old', $cached->parentsPathString(10));

        $cached->bustCache();
        $fresh = new GroupTreeService();
        $this->assertSame('Team Direct', $fresh->parentsPathString(10));
    }

    public function test_rename_busts_in_memory_and_shared_cache(): void
    {
        $tree = new GroupTreeService();
        $this->assertSame('Team Old', $tree->parentsPathString(10));

        $tree->renameGroup(10, 'Renamed');
        $this->assertSame('Team Renamed', $tree->parentsPathString(10));

        $other = new GroupTreeService();
        $this->assertSame('Team Renamed', $other->parentsPathString(10));
    }
}
