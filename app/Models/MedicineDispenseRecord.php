<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineDispenseRecord extends Model
{
    protected $table = 'MedicineDispenseRecords';

    protected $primaryKey = 'MedicineDispenseID';

    protected $fillable = [
        'MedicineID',
        'PersonID',
        'GivenByPersonID',
        'LocationID',
        'Quantity',
        'QuantityUnit',
        'DispensedAt',
        'Notes',
    ];

    protected $casts = [
        'DispensedAt' => 'datetime',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(MedicineInventory::class, 'MedicineID', 'MedicineID');
    }
}
