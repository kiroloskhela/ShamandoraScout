<?php
// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'PersonInformation';
    protected $primaryKey = 'PersonID';
    public $timestamps = false;

    // If PersonID is integer & auto-increment (typical):
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'PersonID','FirstName','SecondName','ThirdName','ShamandoraCode'
    ];

    public function role()
    {
        return $this->belongsToMany(Roles::class, 'PersonRole', 'PersonID', 'RoleID');
    }

    // Make sure the Password model exists & matches your real table/columns.
    // Example:
    //   table: PersonSystemPassword
    //   FK:    PersonID
    //   col:   Password
    public function password()
    {
        return $this->hasOne(\App\Models\Password::class, 'PersonID', 'PersonID');
    }
}