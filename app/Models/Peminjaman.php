<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',


        'kepentingan',
        'pesan_ditolak',


        'tgl_pinjam',
        'tgl_deadline',
        'tgl_kembali',


        'status',


        'disetujui_oleh',
        'diterima_oleh',
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

    public function book()
    {
        return $this->belongsTo(Book::class);
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

    public function denda()
    {
        return $this->hasOne(Denda::class);
    }
}

