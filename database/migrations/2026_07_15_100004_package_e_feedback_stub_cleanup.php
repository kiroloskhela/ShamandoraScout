<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Package E: drop unused stub `feedback` table if empty; document unused `users`.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!$this->isMySql()) {
            // MySQL production schema hardening only: the case-insensitive
            // table-name collision this migration cleans up is a MySQL
            // characteristic. No-op on other drivers (e.g. sqlite under
            // PHPUnit/RefreshDatabase).
            return;
        }

        // On case-insensitive MySQL, `feedback` and `Feedback` collide.
        // Only drop the snake_case stub when it has no real survey columns / no rows.
        if (Schema::hasTable('feedback') && !Schema::hasColumn('feedback', 'program_rating')) {
            $count = DB::table('feedback')->count();
            if ($count === 0) {
                Schema::drop('feedback');
            }
        }

        // `users` remains (Laravel scaffolding) but is unused for auth.
        // Documented in context/; intentionally not dropped here to avoid
        // breaking any accidental external references.
    }

    public function down(): void
    {
        if (!$this->isMySql()) {
            return;
        }

        if (!Schema::hasTable('feedback')) {
            Schema::create('feedback', function ($table) {
                $table->id();
                $table->timestamps();
            });
        }
    }

    private function isMySql(): bool
    {
        return in_array(
            DB::connection($this->getConnection())->getDriverName(),
            ['mysql', 'mariadb'],
            true
        );
    }
};
