<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';

    protected $fillable = [
        'user_id',
        'buku_id',
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
        'tgl_pinjam'   => 'date',
        'tgl_deadline' => 'date',
        'tgl_kembali'  => 'date',
    ];

    protected $appends = ['denda_berjalan'];

    public function getDendaBerjalanAttribute() 
    {
        if ($this->status === 'dipinjam' && !$this->tgl_kembali) {
            
            $deadline = Carbon::parse($this->tgl_deadline)->startOfDay();
            $hariIni = now()->startOfDay();
            if ($hariIni->gt($deadline)) {
                $selisihHari = $deadline->diffInDays($hariIni);
                return $selisihHari * 1000; 
            }
        }
        
        return 0;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function buku()
    {
        return $this->belongsTo(Buku::class, 'buku_id');
    }

    public function disetujuiOleh()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function diterimaOleh()
    {
        return $this->belongsTo(User::class, 'diterima_oleh');
    }
    
    public function ditolakOleh()
    {
        return $this->belongsTo(User::class, 'ditolak_oleh');
    }

    public function denda()
    {
        return $this->hasOne(Denda::class);
    }
}