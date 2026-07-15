<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('whatsapp_campaign_recipients')) {
            return;
        }

        Schema::create('whatsapp_campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('whatsapp_campaigns')->cascadeOnDelete();
            $table->unsignedInteger('person_id')->nullable()->index();
            $table->string('phone', 32);
            $table->text('personalized_message')->nullable();
            $table->string('status', 32)->default('pending')->index();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->string('whatsapp_message_id')->nullable();
            $table->text('error_message')->nullable();
            $table->string('error_kind', 16)->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'phone'], 'uq_wa_campaign_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_campaign_recipients');
    }
};
