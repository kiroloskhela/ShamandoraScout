<?php
// database/migrations/2025_09_27_000000_create_refresh_tokens_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
       // database/migrations/xxxx_xx_xx_create_refresh_tokens_table.php
Schema::create('refresh_tokens', function (Blueprint $table) {
    $table->id();

    // must match PersonInformation.PersonID exactly: INT (signed)
    $table->integer('user_id');  // NOT unsigned, NOT bigInteger

    $table->string('token_hash', 64)->unique();
    $table->timestamp('expires_at');
    $table->timestamp('revoked_at')->nullable();
    $table->foreignId('replaced_by_id')->nullable()
          ->constrained('refresh_tokens')->nullOnDelete();

    $table->string('ip')->nullable();
    $table->text('user_agent')->nullable();
    $table->timestamps();

    // add FK AFTER the column is defined
    $table->foreign('user_id')
          ->references('PersonID')
          ->on('PersonInformation')
          ->cascadeOnDelete();

    $table->index(['user_id', 'expires_at']);
});


    }

    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};