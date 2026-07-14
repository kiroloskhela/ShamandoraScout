<?php

namespace Tests\Unit\Support;

use App\Support\LikeSearch;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LikeSearchTest extends TestCase
{
    public function test_term_trims_and_enforces_min_length(): void
    {
        $this->assertNull(LikeSearch::term('  '));
        $this->assertNull(LikeSearch::term('a', 2));
        $this->assertSame('ab', LikeSearch::term(' ab ', 2));
    }

    public function test_from_request_accepts_q_or_search(): void
    {
        $req = Request::create('/', 'GET', ['search' => 'kiro']);
        $this->assertSame('kiro', LikeSearch::fromRequest($req));

        $req2 = Request::create('/', 'GET', ['q' => 'scout']);
        $this->assertSame('scout', LikeSearch::fromRequest($req2));
    }

    public function test_wildcard_escapes_percent_and_underscore(): void
    {
        $this->assertSame('%100\\%\\_off%', LikeSearch::wildcard('100%_off'));
    }

    public function test_sql_or_builds_clause_and_bindings(): void
    {
        $fragment = LikeSearch::sqlOr(['a', 'b'], 'x');
        $this->assertSame('(a LIKE ? OR b LIKE ?)', $fragment['sql']);
        $this->assertSame(['%x%', '%x%'], $fragment['bindings']);
    }

    public function test_apply_or_on_query_builder(): void
    {
        $grammar = DB::connection()->getQueryGrammar();
        $processor = DB::connection()->getPostProcessor();
        $builder = new Builder(DB::connection(), $grammar, $processor);
        $builder->from('PersonInformation as pi');

        LikeSearch::applyOr($builder, 'test', ['pi.FirstName', 'pi.ShamandoraCode']);

        $sql = $builder->toSql();
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertCount(2, $builder->getBindings());
    }
}
