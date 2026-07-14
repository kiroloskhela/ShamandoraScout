<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Package A-E (database/migrations/2026_07_15_*.php) are MySQL production
 * schema hardenings: they introspect information_schema and run MySQL-only
 * DDL. This test runs the full migration suite against sqlite (as PHPUnit
 * does via RefreshDatabase) and asserts those migrations no-op cleanly
 * instead of throwing / breaking CI.
 */
class Wave2MigrationsSqliteGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_wave2_package_migrations_run_cleanly_on_sqlite(): void
    {
        $this->assertSame('sqlite', DB::connection()->getDriverName());

        $wave2Migrations = DB::table('migrations')
            ->where('migration', 'like', '2026_07_15_%')
            ->pluck('migration');

        $this->assertCount(
            7,
            $wave2Migrations,
            'Expected all 7 Package A-E Wave 2 migrations to have recorded as run.'
        );
    }

    public function test_mysql_only_tables_are_not_created_on_sqlite(): void
    {
        // These migrations only ALTER pre-existing production tables (created
        // out-of-band by schema.sql on MySQL); they must not create them on
        // other drivers when guarded/no-op.
        $this->assertFalse(Schema::hasTable('PersonInformation'));
        $this->assertFalse(Schema::hasTable('Roles'));
        $this->assertFalse(Schema::hasTable('GroupTable'));
    }
}
