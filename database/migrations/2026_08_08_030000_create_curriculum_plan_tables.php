<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('CurriculumPlan')) {
            Schema::create('CurriculumPlan', function (Blueprint $table) {
                $table->increments('PlanID');
                $table->unsignedInteger('QetaaID');
                $table->string('PlanName');
                $table->integer('SortOrder')->default(0);
                $table->unsignedTinyInteger('IsActive')->default(0);
                $table->timestamps();

                $table->index('QetaaID');
                $table->index(['QetaaID', 'IsActive']);

                if (Schema::hasTable('Qetaa')) {
                    $table->foreign('QetaaID')
                        ->references('QetaaID')
                        ->on('Qetaa');
                }
            });
        }

        if (! Schema::hasTable('CurriculumPlanLecture')) {
            Schema::create('CurriculumPlanLecture', function (Blueprint $table) {
                $table->unsignedInteger('PlanID');
                $table->unsignedInteger('CurriculaID');
                $table->integer('SortOrder')->default(0);

                $table->primary(['PlanID', 'CurriculaID']);
                $table->index('CurriculaID');

                $table->foreign('PlanID')
                    ->references('PlanID')
                    ->on('CurriculumPlan')
                    ->onDelete('cascade');

                if (Schema::hasTable('Curricula')) {
                    $table->foreign('CurriculaID')
                        ->references('CurriculaID')
                        ->on('Curricula')
                        ->onDelete('restrict');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('CurriculumPlanLecture');
        Schema::dropIfExists('CurriculumPlan');
    }
};
