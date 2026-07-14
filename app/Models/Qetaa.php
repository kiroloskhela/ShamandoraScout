<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * `Qetaa` (region/division) lookup table. PK already existed on QetaaID.
 */
class Qetaa extends Model
{
    protected $table = 'Qetaa';
    protected $primaryKey = 'QetaaID';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'QetaaID',
        'QetaaName',
    ];

    public function personQetaas()
    {
        return $this->hasMany(PersonQetaa::class, 'QetaaID', 'QetaaID');
    }

    public function persons()
    {
        return $this->belongsToMany(User::class, 'PersonQetaa', 'QetaaID', 'PersonID');
    }
}
