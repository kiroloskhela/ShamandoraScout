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
        if (!Schema::hasTable('feedback')) {
            Schema::create('feedback', function ($table) {
                $table->id();
                $table->timestamps();
            });
        }
    }
};
