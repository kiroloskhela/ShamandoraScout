<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('PersonExamMark')) {
            return;
        }

        if (Schema::hasColumn('PersonExamMark', 'SeasonID')) {
            return;
        }

        Schema::table('PersonExamMark', function (Blueprint $table) {
            $table->unsignedInteger('SeasonID')->nullable();
            $table->index('SeasonID', 'idx_exam_season');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('PersonExamMark') || ! Schema::hasColumn('PersonExamMark', 'SeasonID')) {
            return;
        }

        Schema::table('PersonExamMark', function (Blueprint $table) {
            $table->dropIndex('idx_exam_season');
            $table->dropColumn('SeasonID');
        });
    }
};
