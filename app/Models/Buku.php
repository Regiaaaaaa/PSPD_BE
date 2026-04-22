<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buku extends Model
{
    use HasFactory;

    protected $table = 'buku';

    protected $fillable = [
        'isbn',
        'judul',
        'penulis',
        'penerbit',
        'tahun_terbit',
        'stok_total',
        'stok_tersedia',   
        'dalam_perbaikan',
        'harga_buku',
        'persen_rusak_ringan',
        'persen_rusak_sedang',
        'persen_rusak_berat',
        'cover',
    ];

    public function kategori()
    {
        return $this->belongsToMany(Kategori::class, 'buku_kategori');
    }

    public function details()
    {
        return $this->hasMany(DetailTransaksi::class, 'buku_id');
    }

    public function transaksi()
    {
        return $this->hasManyThrough(
            Transaksi::class,
            DetailTransaksi::class,
            'buku_id',
            'id',
            'id',
            'transaksi_id'
        );
    }
}

