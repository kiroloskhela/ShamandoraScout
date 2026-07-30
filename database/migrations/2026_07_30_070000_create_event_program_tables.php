<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_programs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('SeasonEventID')->unique();
            $table->string('title');
            $table->string('status', 20)->default('draft'); // draft|published
            $table->text('intro_template')->nullable();
            $table->text('outro_template')->nullable();
            $table->timestamps();
        });

        Schema::create('event_program_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_program_id')->constrained('event_programs')->cascadeOnDelete();
            $table->unsignedSmallInteger('day_number');
            $table->date('date')->nullable();
            $table->string('label')->nullable();
            $table->timestamps();

            $table->unique(['event_program_id', 'day_number']);
        });

        Schema::create('event_program_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_program_day_id')->constrained('event_program_days')->cascadeOnDelete();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('activity_label');
            $table->string('slot_kind', 20)->default('general'); // general|games|lecture|duty|other
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('event_program_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_program_slot_id')->constrained('event_program_slots')->cascadeOnDelete();
            $table->unsignedInteger('person_id');
            $table->string('mission_text')->nullable();
            $table->string('team_label')->nullable();
            $table->timestamps();

            $table->unique(['event_program_slot_id', 'person_id'], 'epa_slot_person_unique');
            $table->index('person_id');
        });

        Schema::create('event_program_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_program_id')->constrained('event_programs')->cascadeOnDelete();
            $table->foreignId('event_program_day_id')->nullable()->constrained('event_program_days')->nullOnDelete();
            $table->foreignId('event_program_slot_id')->nullable()->constrained('event_program_slots')->nullOnDelete();
            $table->string('kind', 20); // game|lecture
            $table->string('title');
            $table->text('url')->nullable();
            $table->string('slot_label')->nullable();
            $table->timestamps();
        });

        Schema::create('event_program_import_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_program_id')->nullable()->constrained('event_programs')->nullOnDelete();
            $table->unsignedInteger('SeasonEventID');
            $table->unsignedInteger('created_by')->nullable();
            $table->string('status', 30)->default('pending_review'); // pending_review|ready|committed|cancelled
            $table->string('source', 30)->default('upload'); // upload|google|cli
            $table->json('parsed_json')->nullable();
            $table->json('issues_json')->nullable();
            $table->json('questions_json')->nullable();
            $table->json('answers_json')->nullable();
            $table->timestamps();

            $table->index('SeasonEventID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_program_import_sessions');
        Schema::dropIfExists('event_program_resources');
        Schema::dropIfExists('event_program_assignments');
        Schema::dropIfExists('event_program_slots');
        Schema::dropIfExists('event_program_days');
        Schema::dropIfExists('event_programs');
    }
};
