<?php
// app/Models/User.php
namespace App\Models;

use App\Support\PersonAvatar;
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
    public function image()
    {
        return $this->hasOne(\App\Models\PersonImage::class, 'PersonID', 'PersonID');
    }

    public function role()
    {
        return $this->belongsToMany(Roles::class, 'PersonRole', 'PersonID', 'RoleID');
    }

    public function personGroups()
    {
        return $this->hasMany(PersonGroup::class, 'PersonID', 'PersonID');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'PersonGroup', 'PersonID', 'GroupID');
    }

    public function qetaas()
    {
        return $this->belongsToMany(Qetaa::class, 'PersonQetaa', 'PersonID', 'QetaaID');
    }

    public function sanaMarhalas()
    {
        return $this->belongsToMany(SanaMarhala::class, 'PersonSanaMarhala', 'PersonID', 'SanaMarhalaID');
    }

    public function getAvatarUrlAttribute(): string
    {
        $path = $this->image?->PersonSystemImageThumbnailPath
            ?: $this->image?->PersonSystemImagePath;

        return PersonAvatar::url($path, $this->Gender ?? null);
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