<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Roster attendance gains a fourth state: the person came, but late.
 * Booking attendance (present / absent / outside) is a different column.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('Attendance') || ! Schema::hasColumn('Attendance', 'AttendanceStatus')) {
            return;
        }

        $row = DB::selectOne("SHOW COLUMNS FROM `Attendance` WHERE Field = 'AttendanceStatus'");
        $type = strtolower((string) ($row->Type ?? ''));
        if (! str_starts_with($type, 'enum(') || str_contains($type, 'present_late')) {
            return;
        }

        DB::statement("ALTER TABLE `Attendance` MODIFY `AttendanceStatus` ENUM('present','present_late','absent','excused') NOT NULL DEFAULT 'absent'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('Attendance') || ! Schema::hasColumn('Attendance', 'AttendanceStatus')) {
            return;
        }

        DB::table('Attendance')->where('AttendanceStatus', 'present_late')->update([
            'AttendanceStatus' => 'present',
        ]);

        $row = DB::selectOne("SHOW COLUMNS FROM `Attendance` WHERE Field = 'AttendanceStatus'");
        $type = strtolower((string) ($row->Type ?? ''));
        if (! str_starts_with($type, 'enum(') || ! str_contains($type, 'present_late')) {
            return;
        }

        DB::statement("ALTER TABLE `Attendance` MODIFY `AttendanceStatus` ENUM('present','absent','excused') NOT NULL DEFAULT 'absent'");
    }
};
