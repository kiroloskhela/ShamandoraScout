<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * `SanaMarhala` (year/stage) lookup table. PK already existed on
 * SanaMarhalaID.
 */
class SanaMarhala extends Model
{
    protected $table = 'SanaMarhala';
    protected $primaryKey = 'SanaMarhalaID';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'SanaMarhalaID',
        'SanaID',
        'MarhalaID',
        'SanaMarhalaName',
    ];

    public function personSanaMarhalas()
    {
        return $this->hasMany(PersonSanaMarhala::class, 'SanaMarhalaID', 'SanaMarhalaID');
    }

    public function persons()
    {
        return $this->belongsToMany(User::class, 'PersonSanaMarhala', 'SanaMarhalaID', 'PersonID');
    }
}
