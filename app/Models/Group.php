<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Maps to the legacy `GroupTable` (org tree: Qetaa -> Ferqa -> Sarb, etc).
 * Named `Group` rather than `GroupTable` to avoid confusion with the
 * table-name property, matching the User -> PersonInformation convention.
 *
 * Package C (2026_07_15_100002_package_c_org_tree_indexes) added indexes on
 * IncludedUnderGroupID and GroupTypeID; GroupID was already a PRIMARY KEY.
 */
class Group extends Model
{
    protected $table = 'GroupTable';
    protected $primaryKey = 'GroupID';
    public $timestamps = false;

    protected $fillable = [
        'GroupID',
        'GroupTypeID',
        'IncludedUnderGroupID',
        'GroupName',
    ];

    public function parent()
    {
        return $this->belongsTo(Group::class, 'IncludedUnderGroupID', 'GroupID');
    }

    public function children()
    {
        return $this->hasMany(Group::class, 'IncludedUnderGroupID', 'GroupID');
    }

    public function personGroups()
    {
        return $this->hasMany(PersonGroup::class, 'GroupID', 'GroupID');
    }
}
