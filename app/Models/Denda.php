<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Denda extends Model
{
    use HasFactory;

    protected $table = 'denda';

    protected $fillable = [
        'detail_transaksi_id',
        'nominal',
        'status_pembayaran',
        'tgl_pembayaran',
        'operator_id'
    ];

    public function transaksiDetail()
    {
        return $this->belongsTo(DetailTransaksi::class, 'detail_transaksi_id');
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}