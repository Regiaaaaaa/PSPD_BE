<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';
    protected $fillable = [
        'user_id',
        'buku_id',

        'jumlah',
        'kepentingan',
        'pesan_ditolak',


        'tgl_pinjam',
        'tgl_deadline',
        'tgl_kembali',


        'status',


        'disetujui_oleh',
        'diterima_oleh',
        'ditolak_oleh',
    ];

        protected $casts = [
        'tgl_pinjam' => 'date',
        'tgl_deadline' => 'date',
        'tgl_kembali' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function buku()
    {
        return $this->belongsTo(Buku::class);
    }

    // Operator yang menyetujui
    public function disetujuiOleh()
    {
    return $this->belongsTo(User::class, 'disetujui_oleh');
    }


    // Operator yang menerima pengembalian
    public function diterimaOleh()
    {
    return $this->belongsTo(User::class, 'diterima_oleh');
    }

    // Operator yang menolak
    public function ditolakOleh()
    {
    return $this->belongsTo(User::class, 'ditolak_oleh');
    }

    public function denda()
    {
        return $this->hasOne(Denda::class);
    }
}

