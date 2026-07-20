<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('Season') && ! Schema::hasColumn('Season', 'IsActive')) {
            Schema::table('Season', function (Blueprint $table) {
                $table->unsignedTinyInteger('IsActive')->default(0)->after('SeasonYear');
            });

            $activeId = DB::table('Season')->orderByDesc('SeasonYear')->value('SeasonID');
            if ($activeId) {
                DB::table('Season')->where('SeasonID', $activeId)->update(['IsActive' => 1]);
            }
        }

        if (! Schema::hasTable('season_person_roll_batches')) {
            Schema::create('season_person_roll_batches', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('season_id');
                $table->unsignedInteger('ran_by')->nullable();
                $table->string('status', 32)->default('applied');
                $table->unsignedInteger('persons_count')->default(0);
                $table->unsignedInteger('qetaa_changed_count')->default(0);
                $table->unsignedInteger('groups_cleared_count')->default(0);
                $table->timestamps();

                $table->index(['season_id', 'status']);
            });
        }

        if (! Schema::hasTable('season_person_roll_snapshots')) {
            Schema::create('season_person_roll_snapshots', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('batch_id');
                $table->unsignedInteger('person_id');
                $table->unsignedInteger('old_sana_marhala_id')->nullable();
                $table->unsignedInteger('new_sana_marhala_id')->nullable();
                $table->unsignedInteger('old_qetaa_id')->nullable();
                $table->unsignedInteger('new_qetaa_id')->nullable();
                $table->json('cleared_person_group_json')->nullable();
                $table->string('jump_type', 32)->nullable();
                $table->timestamps();

                $table->index('batch_id');
                $table->index('person_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('season_person_roll_snapshots');
        Schema::dropIfExists('season_person_roll_batches');

        if (Schema::hasTable('Season') && Schema::hasColumn('Season', 'IsActive')) {
            Schema::table('Season', function (Blueprint $table) {
                $table->dropColumn('IsActive');
            });
        }
    }
};
