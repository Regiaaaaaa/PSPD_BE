<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';

    protected $fillable = [
        'transaksi_id',
        'buku_id',
        'status',
        'tgl_kembali'
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function buku()
    {
        return $this->belongsTo(Buku::class);
    }

    public function denda()
    {
        return $this->hasOne(Denda::class, 'detail_transaksi_id');
    }
}