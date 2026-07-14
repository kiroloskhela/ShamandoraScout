<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated Person is a duplicate mapping of PersonInformation, the same
 * table backing the `User` auth model. Having two Eloquent models for one
 * table risks stale/conflicting instances in the same request. Prefer
 * `App\Models\User` for anything involving the PersonInformation table.
 *
 * This class is kept only because it may still be referenced by
 * class_exists()/autoload lookups elsewhere; it is not used by any
 * controller as of this change. Its previous `roles()` relation called
 * hasOne() with 4 arguments (a hasOne signature only accepts 3), which is
 * invalid — a belongsToMany through PersonRole is the correct shape, so it
 * has been fixed to mirror User::role().
 */
class Person extends Model
{
    protected $table = 'PersonInformation';
    protected $primaryKey = 'PersonID';
    public $timestamps = false;

    protected $fillable = [
        'PersonID',
        'FirstName',
        'SecondName',
        'ThirdName',
        'ShamandoraCode',
    ];

    public function roles()
    {
        return $this->belongsToMany(Roles::class, 'PersonRole', 'PersonID', 'RoleID');
    }
}
