<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consent / DNC flags on PersonPhoneNumbers for WhatsApp campaigns.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('PersonPhoneNumbers')) {
            return;
        }

        Schema::table('PersonPhoneNumbers', function (Blueprint $table) {
            if (!Schema::hasColumn('PersonPhoneNumbers', 'WhatsAppConsent')) {
                $table->unsignedTinyInteger('WhatsAppConsent')->default(1);
            }
            if (!Schema::hasColumn('PersonPhoneNumbers', 'DoNotContact')) {
                $table->unsignedTinyInteger('DoNotContact')->default(0);
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('PersonPhoneNumbers')) {
            return;
        }

        Schema::table('PersonPhoneNumbers', function (Blueprint $table) {
            if (Schema::hasColumn('PersonPhoneNumbers', 'DoNotContact')) {
                $table->dropColumn('DoNotContact');
            }
            if (Schema::hasColumn('PersonPhoneNumbers', 'WhatsAppConsent')) {
                $table->dropColumn('WhatsAppConsent');
            }
        });
    }
};
