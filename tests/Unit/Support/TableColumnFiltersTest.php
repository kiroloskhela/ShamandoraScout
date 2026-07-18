<?php

namespace Tests\Unit\Support;

use App\Support\TableColumnFilters;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class TableColumnFiltersTest extends TestCase
{
    public function test_from_request_keeps_allowed_keys_only(): void
    {
        $req = Request::create('/', 'GET', [
            'f' => [
                'QetaaName' => 'أشبال',
                'Hack' => 'x',
                'SanaMarhalaName' => '  ',
            ],
        ]);

        $filters = TableColumnFilters::fromRequest($req, ['QetaaName', 'SanaMarhalaName']);

        $this->assertSame(['QetaaName' => 'أشبال'], $filters);
    }

    public function test_sql_equals_builds_and_bindings(): void
    {
        $frag = TableColumnFilters::sqlEquals(
            ['QetaaName' => 'أشبال', 'SanaMarhalaName' => 'أولى'],
            [
                'QetaaName' => 'q.QetaaName',
                'SanaMarhalaName' => 'sm.SanaMarhalaName',
            ]
        );

        $this->assertSame('q.QetaaName = ? AND sm.SanaMarhalaName = ?', $frag['sql']);
        $this->assertSame(['أشبال', 'أولى'], $frag['bindings']);
    }
}
