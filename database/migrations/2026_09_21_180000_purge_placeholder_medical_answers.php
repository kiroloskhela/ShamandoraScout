<?php

use App\Domain\Enrolment\PlaceholderMedicalAnswerCleaner;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(PlaceholderMedicalAnswerCleaner::class)->run();
    }

    public function down(): void
    {
        // One-way data cleanup: placeholder answers cannot be restored.
    }
};
