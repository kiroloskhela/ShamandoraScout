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
        $this->assertSame(100, mb_strlen(LikeSearch::term(str_repeat('x', 150))));
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

    public function test_person_phone_columns_include_parents(): void
    {
        $cols = LikeSearch::personPhoneColumns('ppn');
        $this->assertSame([
            'ppn.PersonPersonalMobileNumber',
            'ppn.FatherMobileNumber',
            'ppn.MotherMobileNumber',
        ], $cols);
    }

    public function test_phone_digit_variants_cover_eg_formats(): void
    {
        $variants = LikeSearch::phoneDigitVariants('201012345678');
        $this->assertContains('01012345678', $variants);
        $this->assertContains('+201012345678', $variants);
        $this->assertContains('1012345678', $variants);
    }

    public function test_sql_flexible_or_adds_digit_variants_for_phones(): void
    {
        $fragment = LikeSearch::sqlFlexibleOr(
            ['pi.FirstName'],
            '01012345678',
            ['ppn.PersonPersonalMobileNumber'],
        );

        $this->assertGreaterThan(1, count($fragment['bindings']));
        $this->assertStringContainsString('ppn.PersonPersonalMobileNumber LIKE ?', $fragment['sql']);
    }

    public function test_directory_columns_include_id_and_parent_phones(): void
    {
        $cols = LikeSearch::personDirectoryColumns();
        $this->assertContains('CAST(pi.PersonID AS CHAR)', $cols);
        $this->assertContains('ppn.FatherMobileNumber', $cols);
        $this->assertContains('ppn.MotherMobileNumber', $cols);
    }

    public function test_identity_fields_put_cast_in_raw_for_query_builder(): void
    {
        $fields = LikeSearch::personIdentityFields('pi', 'ppn');
        $this->assertContains('pi.PersonID', $fields['columns']);
        $this->assertNotContains('CAST(pi.PersonID AS CHAR)', $fields['columns']);
        $this->assertContains('CAST(pi.PersonID AS CHAR)', $fields['raw']);
        $this->assertContains('ppn.FatherMobileNumber', $fields['columns']);
    }
}
