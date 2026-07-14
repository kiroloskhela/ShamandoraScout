<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

/**
 * `Roles` is a lookup/reference table, not an authenticatable principal.
 * It previously extended Authenticatable, but the app never authenticates
 * as a Roles instance (config/auth.php only registers App\Models\User),
 * so that inheritance was both incorrect and unnecessary.
 *
 * Package B (2026_07_15_100001_package_b_roles_personrole) added a real
 * PRIMARY KEY on RoleID, so $primaryKey below now matches the DB.
 */
class Roles extends Model
{
    use HasFactory;

    protected $table = 'Roles';
    protected $primaryKey = 'RoleID';
    public $timestamps = false;

    protected $fillable = [
        'RoleID',
        'RoleName',
        'RoleDescription',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'PersonRole', 'RoleID', 'PersonID');
    }

    public function personRoles()
    {
        return $this->hasMany(PersonRole::class, 'RoleID', 'RoleID');
    }
}
