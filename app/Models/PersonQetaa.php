<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * PersonQetaa is a pure junction table: (PersonID, QetaaID), no surrogate
 * key column. Package C (2026_07_15_100002_package_c_org_tree_indexes)
 * added a composite PRIMARY KEY (PersonID, QetaaID) plus an index on
 * QetaaID.
 *
 * Eloquent does not support composite primary keys natively, so
 * $primaryKey is set to PersonID for informational purposes only and
 * $incrementing is disabled. Use explicit where() clauses (as the existing
 * controllers do) rather than find()/save() for row-level identity here.
 */
class PersonQetaa extends Model
{
    protected $table = 'PersonQetaa';
    protected $primaryKey = 'PersonID';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'PersonID',
        'QetaaID',
    ];

    public function person()
    {
        return $this->belongsTo(User::class, 'PersonID', 'PersonID');
    }

    public function qetaa()
    {
        return $this->belongsTo(Qetaa::class, 'QetaaID', 'QetaaID');
    }
}
