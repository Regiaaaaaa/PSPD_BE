<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $table = 'otp';
    protected $fillable = [
        'email',
        'otp',
        'expired_at',
        'verified_at'
    ];

    protected $dates = [
        'expired_at',
        'verified_at'
    ];
}
