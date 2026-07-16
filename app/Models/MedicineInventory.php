<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicineInventory extends Model
{
    protected $table = 'MedicineInventory';
    protected $primaryKey = 'MedicineID';

    protected $fillable = [
        'MedicineName',
        'MedicineType',
        'ExpirationDate',
        'Amount',
        'Notes',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(MedicineStock::class, 'MedicineID', 'MedicineID');
    }

    public function dispenseRecords(): HasMany
    {
        return $this->hasMany(MedicineDispenseRecord::class, 'MedicineID', 'MedicineID');
    }
}
