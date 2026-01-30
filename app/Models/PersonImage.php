<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonImage extends Model
{
    protected $table = 'PersonImages';
    protected $primaryKey = 'PersonID';

    public $timestamps = false;
    public $incrementing = false; // because PersonID is PK but not auto-increment in this table
    protected $keyType = 'int';

    protected $fillable = [
        'PersonID',
        'PersonSystemImagePath',
        'PersonSystemImageThumbnailPath',
        'ScoutOfficialUniformImagePath',
        'ScoutOfficialUniformImageThumbnailPath',
    ];
}