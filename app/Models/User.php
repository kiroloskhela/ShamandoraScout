<?php
// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

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

public function getAvatarUrlAttribute(): string
{
    $path = $this->image?->PersonSystemImageThumbnailPath
        ?: $this->image?->PersonSystemImagePath;

    if (!$path) {
        return 'https://i.pravatar.cc/60?img=7';
    }

    // If DB already has a full URL
    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    // normalize if someone saved "storage/..." in DB
    $path = preg_replace('#^storage/#', '', ltrim($path, '/'));

    // This returns "/storage/person_images/xxx.jpg"
return asset('storage/' . ltrim($path, '/'));
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