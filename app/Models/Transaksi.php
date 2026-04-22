<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    protected $fillable = [
        'user_id',
        'kepentingan',
        'pesan_ditolak',
        'pesan_diterima',
        'tgl_pinjam',
        'tgl_deadline',
        'status',
        'disetujui_oleh',
        'diterima_oleh',
        'ditolak_oleh'
    ];

    protected $appends = ['denda_berjalan'];

    public function details()
    {
        return $this->hasMany(DetailTransaksi::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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

    // Denda Berjalan
    public function getDendaBerjalanAttribute()
    {
        if ($this->status !== 'dipinjam') {
            return 0;
        }
        if (now()->lte($this->tgl_deadline)) {
            return 0;
        }
        $jumlahBuku = $this->details()
            ->where('status', 'dipinjam')
            ->count();

        if ($jumlahBuku == 0) {
            return 0;
        }
        $hariTelat = now()->diffInDays($this->tgl_deadline);
        return $hariTelat * 1000 * $jumlahBuku;
    }
}