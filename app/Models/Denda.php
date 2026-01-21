<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Denda extends Model
{
    use HasFactory;

    protected $fillable = [
        'peminjaman_id',
        'nominal',
        'status_pembayaran',
        'tgl_pembayaran',
        'operator_id'
    ];

    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}

