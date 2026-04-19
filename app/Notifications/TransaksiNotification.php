<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TransaksiNotification extends Notification
{
    use Queueable;

    protected $transaksi;

    public function __construct($transaksi)
    {
        $this->transaksi = $transaksi;
    }

    public function via($notifiable)
    {
        return ['database']; 
    }

    public function toArray($notifiable)
{
    $status = $this->transaksi->status;
    
    $judulBuku = $this->transaksi->details
        ->map(fn($d) => $d->buku->judul ?? '-')
        ->join(', ');
    
    $pesan = "";
    if ($status === 'dipinjam') {
        $pesan = "Peminjaman buku '$judulBuku' telah DISETUJUI.";
    } elseif ($status === 'ditolak') {
        $pesan = "Peminjaman buku '$judulBuku' DITOLAK. Alasan: " . ($this->transaksi->pesan_ditolak ?? '-');
    }

    return [
        'transaksi_id' => $this->transaksi->id,
        'judul'        => $judulBuku,
        'status'       => $status,
        'message'      => $pesan,
    ];
}
}