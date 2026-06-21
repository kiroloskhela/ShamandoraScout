<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('MedicineLocations', function (Blueprint $table) {
            $table->increments('LocationID');
            $table->string('LocationName');
            $table->boolean('IsActive')->default(true);
            $table->timestamps();

            $table->unique('LocationName');
        });

        $now = now();
        DB::table('MedicineLocations')->insert([
            ['LocationName' => 'صندوق 1', 'IsActive' => true, 'created_at' => $now, 'updated_at' => $now],
            ['LocationName' => 'صندوق 2', 'IsActive' => true, 'created_at' => $now, 'updated_at' => $now],
            ['LocationName' => 'صندوق 3', 'IsActive' => true, 'created_at' => $now, 'updated_at' => $now],
            ['LocationName' => 'المعسكر', 'IsActive' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::create('MedicineStock', function (Blueprint $table) {
            $table->increments('MedicineStockID');
            $table->unsignedInteger('MedicineID');
            $table->unsignedInteger('LocationID');
            $table->unsignedInteger('Amount')->default(0);
            $table->timestamps();

            $table->foreign('MedicineID')
                ->references('MedicineID')
                ->on('MedicineInventory')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('LocationID')
                ->references('LocationID')
                ->on('MedicineLocations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->unique(['MedicineID', 'LocationID']);
        });

        Schema::create('MedicineStockLocks', function (Blueprint $table) {
            $table->increments('MedicineStockLockID');
            $table->unsignedInteger('MedicineID');
            $table->unsignedInteger('LocationID');
            $table->integer('CreatedByPersonID')->nullable();
            $table->unsignedInteger('Quantity');
            $table->string('QuantityUnit', 50);
            $table->string('LockReason')->nullable();
            $table->date('StartsAt');
            $table->date('EndsAt');
            $table->dateTime('ReleasedAt')->nullable();
            $table->text('Notes')->nullable();
            $table->timestamps();

            $table->foreign('MedicineID')
                ->references('MedicineID')
                ->on('MedicineInventory')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('LocationID')
                ->references('LocationID')
                ->on('MedicineLocations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('CreatedByPersonID')
                ->references('PersonID')
                ->on('PersonInformation')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index(['MedicineID', 'LocationID', 'StartsAt', 'EndsAt']);
        });

        Schema::table('MedicineDispenseRecords', function (Blueprint $table) {
            $table->unsignedInteger('LocationID')->nullable()->after('MedicineID');

            $table->foreign('LocationID')
                ->references('LocationID')
                ->on('MedicineLocations')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        $defaultLocationId = DB::table('MedicineLocations')->where('LocationName', 'صندوق 1')->value('LocationID');
        $medicines = DB::table('MedicineInventory')->select('MedicineID', 'Amount')->get();

        foreach ($medicines as $medicine) {
            DB::table('MedicineStock')->insert([
                'MedicineID' => $medicine->MedicineID,
                'LocationID' => $defaultLocationId,
                'Amount' => (int) $medicine->Amount,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('MedicineDispenseRecords', function (Blueprint $table) {
            $table->dropForeign(['LocationID']);
            $table->dropColumn('LocationID');
        });

        Schema::dropIfExists('MedicineStockLocks');
        Schema::dropIfExists('MedicineStock');
        Schema::dropIfExists('MedicineLocations');
    }
};
