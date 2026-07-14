<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('person_id')->nullable()->index();
            $table->string('actor_name')->nullable();
            $table->string('method', 16);
            $table->string('path', 512);
            $table->string('route_name')->nullable();
            $table->string('action', 512);
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('request_payload')->nullable();
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index('method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
