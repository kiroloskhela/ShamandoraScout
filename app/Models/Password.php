<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Password extends Model
{
    protected $table = 'PersonSystemPassword';
    protected $primaryKey = 'PersonID';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'PersonID',
        'Password',
    ];

    protected $hidden = ['Password'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'PersonID', 'PersonID');
    }
}