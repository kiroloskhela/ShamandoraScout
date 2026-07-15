<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $table = 'Games';

    protected $primaryKey = 'GameID';

    public $timestamps = false;

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'Title',
        'GameDescription',
        'Rules',
        'PointSystem',
        'AgeGroup',
        'Target',
        'RequireCustody',
        'ReferenceLink',
    ];
}
