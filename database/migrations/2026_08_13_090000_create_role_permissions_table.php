<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('RoleID');
            $table->string('permission_key', 120);
            $table->timestamps();

            $table->unique(['RoleID', 'permission_key']);
            $table->index('permission_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
