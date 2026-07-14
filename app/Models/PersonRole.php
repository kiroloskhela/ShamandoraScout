<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * PersonRole is the pivot table backing User::role() / Roles::users().
 *
 * Package B (2026_07_15_100001_package_b_roles_personrole) gave this table
 * a real PRIMARY KEY on the pre-existing PersonRoleID column, plus a
 * UNIQUE(PersonID, RoleID) index and an index on RoleID.
 */
class PersonRole extends Model
{
    protected $table = 'PersonRole';
    protected $primaryKey = 'PersonRoleID';
    public $timestamps = false;

    protected $fillable = [
        'PersonRoleID',
        'PersonID',
        'RoleID',
        'RequestPersonID',
        'CreationTimestamp',
    ];

    protected $casts = [
        'CreationTimestamp' => 'datetime',
    ];

    public function person()
    {
        return $this->belongsTo(User::class, 'PersonID', 'PersonID');
    }

    public function role()
    {
        return $this->belongsTo(Roles::class, 'RoleID', 'RoleID');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'RequestPersonID', 'PersonID');
    }
}
