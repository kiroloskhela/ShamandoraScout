<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('PersonExamMark')) {
            return;
        }

        Schema::create('PersonExamMark', function (Blueprint $table) {
            $table->increments('ExamMarkID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('ServentID');
            $table->unsignedInteger('QetaaID');
            $table->unsignedInteger('SanaMarhalaID');
            $table->integer('TheoreticalMark');
            $table->integer('PracticalMark');
            $table->date('ExamDate');
            $table->string('Note', 500)->nullable();

            $table->index('PersonID');
            $table->index('ServentID');
            $table->index(['PersonID', 'SanaMarhalaID'], 'idx_exam_person_sana');
            $table->index('QetaaID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PersonExamMark');
    }
};
