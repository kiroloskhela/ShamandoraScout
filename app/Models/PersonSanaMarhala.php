<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * PersonSanaMarhala is a pure junction table: (PersonID, SanaMarhalaID), no
 * surrogate key column. Package C (2026_07_15_100002_package_c_org_tree_indexes)
 * added a composite PRIMARY KEY (PersonID, SanaMarhalaID) plus an index on
 * SanaMarhalaID.
 *
 * Eloquent does not support composite primary keys natively, so
 * $primaryKey is set to PersonID for informational purposes only and
 * $incrementing is disabled. Use explicit where() clauses rather than
 * find()/save() for row-level identity here.
 */
class PersonSanaMarhala extends Model
{
    protected $table = 'PersonSanaMarhala';
    protected $primaryKey = 'PersonID';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'PersonID',
        'SanaMarhalaID',
    ];

    public function person()
    {
        return $this->belongsTo(User::class, 'PersonID', 'PersonID');
    }

    public function sanaMarhala()
    {
        return $this->belongsTo(SanaMarhala::class, 'SanaMarhalaID', 'SanaMarhalaID');
    }
}
