<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('MedicineInventory', function (Blueprint $table) {
            $table->increments('MedicineID');
            $table->string('MedicineName');
            $table->enum('MedicineType', [
                'tablet',
                'drinkable',
                'injectable',
                'ampoule',
                'ointment',
                'lotion',
                'drops',
            ]);
            $table->date('ExpirationDate');
            $table->unsignedInteger('Amount')->default(0);
            $table->text('Notes')->nullable();
            $table->timestamps();

            $table->index(['MedicineType', 'ExpirationDate']);
            $table->index('MedicineName');
        });

        Schema::create('MedicineDispenseRecords', function (Blueprint $table) {
            $table->increments('MedicineDispenseID');
            $table->unsignedInteger('MedicineID');
            $table->integer('PersonID');
            $table->integer('GivenByPersonID')->nullable();
            $table->unsignedInteger('Quantity')->default(1);
            $table->string('QuantityUnit', 50);
            $table->dateTime('DispensedAt');
            $table->text('Notes')->nullable();
            $table->timestamps();

            $table->foreign('MedicineID')
                ->references('MedicineID')
                ->on('MedicineInventory')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('PersonID')
                ->references('PersonID')
                ->on('PersonInformation')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('GivenByPersonID')
                ->references('PersonID')
                ->on('PersonInformation')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index(['PersonID', 'DispensedAt']);
            $table->index(['MedicineID', 'DispensedAt']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('MedicineDispenseRecords');
        Schema::dropIfExists('MedicineInventory');
    }
};
