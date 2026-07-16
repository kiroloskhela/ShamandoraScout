<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satellite phone row for PersonInformation (hardened identity package).
 */
class PersonPhoneNumber extends Model
{
    protected $table = 'PersonPhoneNumbers';
    protected $primaryKey = 'PersonID';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'PersonID',
        'PersonPersonalMobileNumber',
        'FatherMobileNumber',
        'MotherMobileNumber',
        'HomePhoneNumber',
        'IsOPersonalPhoneNumberHavingWhatsapp',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(User::class, 'PersonID', 'PersonID');
    }
}
