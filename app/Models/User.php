<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Roles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'PersonID',
        'FirstName',
        'SecondName',
        'ThirdName',
        'ShamandoraCode'
    ];

    protected $table = "PersonInformation";
    protected $primaryKey = 'PersonID';
    public $timestamps = false;


    public function role()
    {
        return $this->belongsToMany(Roles::class, 'PersonRole', 'PersonID', 'RoleID');
    }

    public function password()
    {
        return $this->hasOne(Password::class, 'PersonID');
    }

}