<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('SeasonEventBookingAttendance')) {
            return;
        }

        Schema::create('SeasonEventBookingAttendance', function (Blueprint $table) {
            $table->increments('SeasonEventBookingAttendanceID');
            $table->unsignedInteger('SeasonEventParticipantFinanceID');
            $table->unsignedInteger('SeasonEventID');
            $table->string('AttendanceStatus', 20);
            $table->unsignedInteger('ServentID');
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->useCurrent()->useCurrentOnUpdate();

            $table->unique('SeasonEventParticipantFinanceID', 'uq_seba_booking');
            $table->index(['SeasonEventID', 'UpdatedAt'], 'idx_seba_event_updated');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('SeasonEventBookingAttendance');
    }
};
