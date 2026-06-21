<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('MedicineLocations')->where('LocationName', 'ستوك')->exists()) {
            DB::table('MedicineLocations')->insert([
                'LocationName' => 'ستوك',
                'IsActive' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $locationId = DB::table('MedicineLocations')
            ->where('LocationName', 'ستوك')
            ->value('LocationID');

        if (!$locationId) {
            return;
        }

        $hasStock = DB::table('MedicineStock')
            ->where('LocationID', $locationId)
            ->where('Amount', '>', 0)
            ->exists();
        $hasLocks = DB::table('MedicineStockLocks')
            ->where('LocationID', $locationId)
            ->exists();

        if (!$hasStock && !$hasLocks) {
            DB::table('MedicineStock')
                ->where('LocationID', $locationId)
                ->delete();
            DB::table('MedicineLocations')
                ->where('LocationID', $locationId)
                ->delete();
        }
    }
};
