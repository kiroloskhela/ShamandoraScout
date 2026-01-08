<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('Documents')) {
            Schema::table('Documents', function (Blueprint $table) {
                if (!Schema::hasColumn('Documents', 'DocumentName')) {
                    $table->string('DocumentName')->nullable()->after('DocumentLink');
                }
                if (!Schema::hasColumn('Documents', 'DocumentPath')) {
                    $table->string('DocumentPath')->nullable()->after('DocumentName');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('Documents')) {
            Schema::table('Documents', function (Blueprint $table) {
                if (Schema::hasColumn('Documents', 'DocumentPath')) {
                    $table->dropColumn('DocumentPath');
                }
                if (Schema::hasColumn('Documents', 'DocumentName')) {
                    $table->dropColumn('DocumentName');
                }
            });
        }
    }
};