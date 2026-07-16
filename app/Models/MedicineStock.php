<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineStock extends Model
{
    protected $table = 'MedicineStock';
    protected $primaryKey = 'MedicineStockID';

    protected $fillable = [
        'MedicineID',
        'LocationID',
        'Amount',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(MedicineInventory::class, 'MedicineID', 'MedicineID');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(MedicineLocation::class, 'LocationID', 'LocationID');
    }
}
