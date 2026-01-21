<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'judul',
        'penulis',
        'penerbit',
        'tahun_terbit',
        'stok',
        'kondisi',
        'cover'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function peminjamans()
    {
        return $this->hasMany(Peminjaman::class);
    }
}

