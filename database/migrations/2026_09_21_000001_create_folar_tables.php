<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('Folar')) {
            Schema::create('Folar', function (Blueprint $table) {
                $table->increments('FolarID');
                $table->string('FolarName')->unique();
            });
        }

        if (! Schema::hasTable('PersonFolar')) {
            Schema::create('PersonFolar', function (Blueprint $table) {
                $table->unsignedInteger('PersonID')->primary();
                $table->unsignedInteger('FolarID');
                $table->index('FolarID');
            });
        }

        $names = [
            'فولار براعم',
            'فولار ساده',
            'فولار بخط',
            'فولار بخطين',
            'فولار الخشبيه',
        ];

        foreach ($names as $name) {
            if (! DB::table('Folar')->where('FolarName', $name)->exists()) {
                DB::table('Folar')->insert(['FolarName' => $name]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('PersonFolar');
        Schema::dropIfExists('Folar');
    }
};
