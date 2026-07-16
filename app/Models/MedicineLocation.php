<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicineLocation extends Model
{
    protected $table = 'MedicineLocations';

    protected $primaryKey = 'LocationID';

    protected $fillable = [
        'LocationName',
        'IsActive',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(MedicineStock::class, 'LocationID', 'LocationID');
    }
}
