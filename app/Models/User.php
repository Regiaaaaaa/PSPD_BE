<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    public function siswa()
    {
        return $this->hasOne(Siswa::class);
    }

    
    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    // User punya banyak peminjaman
    public function peminjamans()
    {
        return $this->hasMany(Peminjaman::class);
    }

    // Operator punya input banyak denda
    public function dendasDiinput()
    {
        return $this->hasMany(Denda::class, 'operator_id');
    }
}
