<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';
    protected $fillable = [
        'user_id',
        'nomor_induk_siswa',
        'tingkat',
        'jurusan',
        'kelas',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
