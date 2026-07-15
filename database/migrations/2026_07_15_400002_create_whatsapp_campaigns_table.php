<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('whatsapp_campaigns')) {
            return;
        }

        Schema::create('whatsapp_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('message_template');
            $table->string('status', 32)->default('draft')->index();
            $table->string('missing_variable_behavior', 16)->default('fallback');
            $table->string('fallback_name')->nullable();
            $table->unsignedInteger('min_delay_seconds')->default(8);
            $table->unsignedInteger('max_delay_seconds')->default(15);
            $table->unsignedInteger('max_messages_per_hour')->default(60);
            $table->unsignedInteger('created_by')->nullable()->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_campaigns');
    }
};
