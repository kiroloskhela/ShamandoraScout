<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * PersonGroup links a person to a GroupTable entry with a role in that
 * group (GroupRoleID). The real PK is the pre-existing PersonGroupRoleID
 * AUTO_INCREMENT column (not PersonID).
 *
 * Package C (2026_07_15_100002_package_c_org_tree_indexes) added indexes on
 * GroupID / GroupRoleID plus a UNIQUE(PersonID, GroupID, GroupRoleID).
 */
class PersonGroup extends Model
{
    protected $table = 'PersonGroup';
    protected $primaryKey = 'PersonGroupRoleID';
    public $timestamps = false;

    protected $fillable = [
        'PersonGroupRoleID',
        'PersonID',
        'GroupID',
        'GroupRoleID',
    ];

    public function person()
    {
        return $this->belongsTo(User::class, 'PersonID', 'PersonID');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'GroupID', 'GroupID');
    }
}
