<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();
            // Column name should be user_id to match your controller
            // But reference the PersonID column in users table
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('PersonID')->on('users')->onDelete('cascade');
            $table->string('token')->unique();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('refresh_tokens');
    }
};