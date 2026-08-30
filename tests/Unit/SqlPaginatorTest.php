<?php

namespace Tests\Unit;

use App\Support\SqlPaginator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SqlPaginatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('sql_paginator_demo');
        Schema::create('sql_paginator_demo', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        foreach (range(1, 30) as $i) {
            DB::table('sql_paginator_demo')->insert(['name' => "row-{$i}"]);
        }
    }

    public function test_paginates_raw_sql_results(): void
    {
        $page = SqlPaginator::paginate(
            'SELECT id, name FROM sql_paginator_demo ORDER BY id ASC',
            [],
            10
        );

        $this->assertSame(30, $page->total());
        $this->assertCount(10, $page->items());
        $this->assertSame(1, (int) $page->items()[0]->id);
    }

    public function test_uses_optional_count_sql(): void
    {
        $page = SqlPaginator::paginate(
            'SELECT id, name FROM sql_paginator_demo ORDER BY id ASC',
            [],
            10,
            'SELECT 7 AS aggregate',
        );

        $this->assertSame(7, $page->total());
        $this->assertCount(10, $page->items());
    }
}
